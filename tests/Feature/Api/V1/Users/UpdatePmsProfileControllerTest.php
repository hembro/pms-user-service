<?php

declare(strict_types=1);

use App\Enums\EducationLevel;
use App\Enums\EmploymentStatus;
use App\Enums\Role;
use App\Models\AreaAssignment;
use App\Models\Designation;
use App\Models\Division;
use App\Models\Expertise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to keep the route generation clean
function getRoute(string $userId): string
{
    return route('api.v1.users.profile.update', ['user' => $userId]);
}

describe('Update PMS Profile Feature: The Unhappy Path', function () {

    it('rejects unauthenticated requests', function () {
        $user = User::factory()->create();

        $response = $this->putJson(getRoute($user->id), []);

        // Assert
        $response->assertUnauthorized();
    });

    it('validates the incoming request payload', function () {

        $user = User::factory()->create();

        // Act: Send garbage data to trigger the Form Request validator
        $response = $this->withHeaders([
            'X-User-Id' => $user->id,
            'X-User-Roles' => 'pms.proponent',
        ])->putJson(getRoute($user->id), [
            'employment_status' => 'INVALID_STATUS', // Not a real Enum value
            'divisions' => 'not-an-array', // Should be an array of IDs
            'education_backgrounds' => [
                ['level' => 'NOT_A_LEVEL'], // Missing required fields
            ],
        ]);

        // Assert: Proves your Form Request rules are catching bad data
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'employment_status',
                'divisions',
                'education_backgrounds.0.level',
                'education_backgrounds.0.school',
            ]);
    });
    it('allows a pms admin to update ANY user\'s profile', function () {
        $user = User::factory()->create();
        $admin = User::factory()->create(); // No shared division needed

        $division = Division::factory()->create();
        $area = AreaAssignment::factory()->create();

        $response = $this->withHeaders([
            'X-User-Id' => $admin->id,
            'X-User-Roles' => Role::PMS_ADMIN->value,
        ])->putJson(getRoute($user->id), [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'divisions' => [$division->id],
            'area_assignments' => [$area->id],
        ]);

        $response->assertOk();
    });

    it('allows a division admin to update a user in their SHARED division', function () {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $area = AreaAssignment::factory()->create();

        // 1. Setup the shared boundary!
        $sharedDivision = Division::factory()->create();
        $admin->divisions()->attach($sharedDivision->id);
        $user->divisions()->attach($sharedDivision->id);

        $response = $this->withHeaders([
            'X-User-Id' => $admin->id,
            'X-User-Roles' => Role::PMS_DIVISION_ADMIN->value,
        ])->putJson(getRoute($user->id), [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'divisions' => [$sharedDivision->id],
            'area_assignments' => [$area->id],
        ]);

        $response->assertOk();
    });

    it('denies a division admin from updating a user outside their division', function () {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $area = AreaAssignment::factory()->create();

        $adminDivision = Division::factory()->create();
        $userDivision = Division::factory()->create();

        $admin->divisions()->attach($adminDivision->id);
        $user->divisions()->attach($userDivision->id); // They do not match!

        $response = $this->withHeaders([
            'X-User-Id' => $admin->id,
            'X-User-Roles' => Role::PMS_DIVISION_ADMIN->value,
        ])->putJson(getRoute($user->id), [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'divisions' => [$userDivision->id],
            'area_assignments' => [$area->id],
        ]);

        $response->assertForbidden();
    });
});

describe('Update PMS Profile Feature: The Happy Path', function () {

    it('successfully updates a profile using existing relationship IDs', function () {

        $user = User::factory()->create();
        $division = Division::factory()->create();
        $expertise = Expertise::factory()->create();
        $designation = Designation::factory()->create();
        $area = AreaAssignment::factory()->create();

        $payload = [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'expertise' => $expertise->id, // Passing an existing ULID/UUID
            'designation' => $designation->id,
            'divisions' => [$division->id],
            'area_assignments' => [$area->id],
            'education_backgrounds' => null,
        ];

        $response = $this->withHeaders([
            'X-User-Id' => $user->id,
            'X-User-Roles' => 'pms.proponent',
        ])->putJson(getRoute($user->id), $payload);

        $response->assertOk();

        // Assert the database actually synced everything
        $user->refresh();

        expect($user->expertise_id)->toBe($expertise->id)
            ->and($user->designation_id)->toBe($designation->id)
            ->and($user->divisions->pluck('id')->toArray())->toContain($division->id)
            ->and($user->areaAssignments->pluck('id')->toArray())->toContain($area->id);
    });

    it('resolves creatable dropdowns dynamically if the user types a new string', function () {

        $user = User::factory()->create();
        $division = Division::factory()->create();
        $area = AreaAssignment::factory()->create();

        $payload = [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'expertise' => '   Senior Laravel Architect   ', // Messy input
            'designation' => 'chief technology officer', // Lowercase input
            'divisions' => [$division->id],
            'area_assignments' => [$area->id],
        ];

        $response = $this->withHeaders([
            'X-User-Id' => $user->id,
            'X-User-Roles' => 'pms.proponent',
        ])->putJson(getRoute($user->id), $payload);

        $response->assertOk();

        // Assert: Proves your resolveCreatableId() function cleaned the string,
        // Title Cased it, and created new database records!
        $user->refresh();
        expect($user->expertise->name)->toBe('Senior Laravel Architect')
            ->and($user->designation->name)->toBe('Chief Technology Officer');
    });

    it('completely replaces education backgrounds when a new array is provided', function () {

        $user = User::factory()->create();
        $division = Division::factory()->create();
        $area = AreaAssignment::factory()->create();

        // Arrange: Give the user an old education record
        $user->educationBackgrounds()->create([
            'level' => EducationLevel::MASTERS,
            'school' => 'Old University',
            'degree' => 'Old Degree',
            'year' => '2010',
        ]);

        $payload = [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'divisions' => [$division->id],
            'area_assignments' => [$area->id],
            'education_backgrounds' => [
                [
                    'level' => EducationLevel::COLLEGE,
                    'school' => 'University of the Philippines',
                    'degree' => 'BS Computer Science',
                    'year' => '2015',
                    'awards' => 'Cum Laude',
                ],
                [
                    'level' => EducationLevel::MASTERS,
                    'school' => 'Ateneo de Manila',
                    'degree' => 'MS IT',
                    'year' => '2018',
                    'awards' => null,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'X-User-Id' => $user->id,
            'X-User-Roles' => 'pms.proponent',
        ])->putJson(getRoute($user->id), $payload);

        $response->assertOk();

        // Assert: Proves the old record was deleted and EXACTLY two new ones were created!
        $education = $user->fresh()->educationBackgrounds;

        expect($education->count())->toBe(2)
            ->and($education->contains('school', 'Old University'))->toBeFalse() // Proves deletion
            ->and($education->contains('school', 'University of the Philippines'))->toBeTrue()
            ->and($education->contains('awards', 'Cum Laude'))->toBeTrue();
    });

    it('returns 403 Forbidden if a user tries to update another user\'s profile without admin rights', function () {

        $actor = User::factory()->create();
        $targetUser = User::factory()->create();
        $division = Division::factory()->create();
        $area = AreaAssignment::factory()->create();

        // The malicious actor tries to send a valid payload to the TARGET's URL
        $payload = [
            'employment_status' => EmploymentStatus::PERMANENT_PERSONNEL->value,
            'divisions' => [$division->id],
            'area_assignments' => [$area->id],
        ];

        // We simulate the Gateway authenticating the $actor, but with standard roles
        $response = $this->withHeaders([
            'X-User-Id' => $actor->id,
            'X-User-Roles' => 'pms.proponent', // No admin role!
        ])
            ->putJson(getRoute($targetUser->id), $payload);

        // Assert: The system successfully blocked the unauthorized access
        $response->assertForbidden(); // 403
    });
});
