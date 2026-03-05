<?php

declare(strict_types=1);

use App\Actions\Integration\Users\SyncDeletedUser;
use App\Enums\EducationLevel;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserDeletedEvent;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(SyncDeletedUser::class);
});

// Helper to generate the Integration Event
function createDeletedEvent(string $targetId): UserDeletedEvent
{
    return new UserDeletedEvent(
        actor: new Actor(
            id: 'admin-ulid-id',
            type: ActorType::USER,
            name: 'Admin',
            email: 'admin@test.com',
        ),
        userId: $targetId,
        occurredAt: now()->toIso8601String(),
    );
}

it('successfully anonymizes PII and deletes the user', function () {
    // Arrange
    $user = User::factory()->create([
        'email' => 'juan.delacruz@pms.gov.ph',
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'display_name' => 'Juan D.',
        'status' => UserStatus::ACTIVE,
    ]);

    // Give the user some highly sensitive PII (Education) that MUST be deleted
    $user->educationBackgrounds()->create([
        'level' => EducationLevel::COLLEGE,
        'school' => 'University of the Philippines',
        'degree' => 'BS Computer Science',
        'year' => '2015',
    ]);

    $event = createDeletedEvent($user->id);

    // Act
    $this->action->handle($event);

    // Assert
    $user->refresh();

    expect($user->status)->toBe(UserStatus::DELETED)
        ->and($user->first_name)->toBe('Deleted')
        ->and($user->last_name)->toBe('User')
        ->and($user->display_name)->toBe('Deleted User')
        ->and($user->email)->toContain('juan.delacruz@pms.gov.ph::deleted_')
        ->and($user->educationBackgrounds()->count())->toBe(0); // Proves PII was wiped!
});

it('preserves division assignments to maintain historical project integrity', function () {
    // Arrange
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $division = Division::factory()->create();

    $user->divisions()->attach($division->id);

    $event = createDeletedEvent($user->id);

    // Act
    $this->action->handle($event);

    // Assert: Even though the user is "deleted", they still belong to the division
    // so old reports don't break.
    expect($user->divisions()->count())->toBe(1)
        ->and($user->divisions->first()->id)->toBe($division->id);
});

it('is strictly idempotent and does not re-suffix the email on duplicate events', function () {
    // Arrange: Create a user that is ALREADY deleted and anonymized
    $user = User::factory()->create([
        'email' => 'juan.delacruz@pms.gov.ph::deleted_1700000000',
        'first_name' => 'Deleted',
        'status' => UserStatus::DELETED,
    ]);

    $event = createDeletedEvent($user->id);

    // Act
    $this->action->handle($event);

    // Assert: If it wasn't idempotent, the email would become
    // "juan.delacruz@pms.gov.ph::deleted_1700000000::deleted_1700000500"
    expect($user->fresh()->email)->toBe('juan.delacruz@pms.gov.ph::deleted_1700000000');
});

it('gracefully ignores deletion events for non-existent users', function () {
    // Arrange
    $event = createDeletedEvent('01H_FAKE_USER_ID');

    // Act & Assert: We just expect it not to throw a fatal error.
    // If it survives the execution, the early return worked.
    $this->action->handle($event);

    expect(true)->toBeTrue();
});
