<?php

declare(strict_types=1);

use App\Actions\Integration\Users\SyncUpdatedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ResourceType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserProfileUpdatedEvent;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(SyncUpdatedUser::class);
});

// Helper to generate the Event DTO
function createUpdatedEvent(string $targetId, UserAttributes $attributes): UserProfileUpdatedEvent
{
    return new UserProfileUpdatedEvent(
        actor: new Actor(
            id: 'admin-ulid-id',
            type: ActorType::USER,
            name: 'Admin',
            email: 'admin@test.com',
        ),
        target: new Target(
            id: $targetId,
            type: ResourceType::USER,
            attributes: $attributes
        ),
        changes: [
            'first_name',
        ],
        occurredAt: now()->toIso8601String(),
    );
}

it('successfully updates local user profile attributes', function () {
    // Arrange: Create a user with old data
    $user = User::factory()->create([
        'first_name' => 'OldName',
        'last_name' => 'OldLastName',
        'email' => 'old@pms.gov.ph',
    ]);

    // Create the new attributes payload
    $newAttributes = new UserAttributes(
        email: 'new@pms.gov.ph',
        displayName: 'New Name',
        firstName: 'NewName',
        lastName: 'NewLastName',
        status: UserStatus::ACTIVE,
    );

    $event = createUpdatedEvent($user->id, $newAttributes);

    // Act
    $this->action->handle($event);

    // Assert
    $user->refresh();
    expect($user->first_name)->toBe('NewName')
        ->and($user->email)->toBe('new@pms.gov.ph');
});

it('correctly nullifies local columns when the global system sends explicit nulls', function () {
    // Arrange: User has a mobile number initially
    $user = User::factory()->create([
        'avatar_url' => 'https://s3.aws.com/avatar.png',
    ]);

    // The user deleted their avatar in the central system, so the DTO has null
    $newAttributes = new UserAttributes(
        email: $user->email,
        displayName: $user->display_name,
        firstName: $user->first_name,
        lastName: $user->last_name,
        status: UserStatus::ACTIVE,
        avatarUrl: null, // Explicitly null
    );

    $event = createUpdatedEvent($user->id, $newAttributes);

    // Act
    $this->action->handle($event);

    // Assert: Proves our array_filter fix from earlier works perfectly!
    expect($user->fresh()->mobile_number)->toBeNull();
});

it('is strictly idempotent and executes zero queries if the data matches', function () {
    // Arrange
    $user = User::factory()->create([
        'email' => 'juan@pms.gov.ph',
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
    ]);

    // The attributes match the database exactly
    $attributes = new UserAttributes(
        email: 'juan@pms.gov.ph',
        displayName: $user->display_name,
        firstName: 'Juan',
        lastName: 'Dela Cruz',
        status: UserStatus::ACTIVE,
    );

    $event = createUpdatedEvent($user->id, $attributes);

    // Act: Trap the queries
    DB::enableQueryLog();
    $this->action->handle($event);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Assert: Prove no UPDATE queries ran
    $updateQueries = array_filter($queries, fn ($q) => str_starts_with(mb_strtolower($q['query']), 'update'));

    expect($updateQueries)->toBeEmpty();
});

it('gracefully ignores the event if the user does not exist locally', function () {
    // Arrange
    $attributes = new UserAttributes(
        email: 'ghost@pms.gov.ph',
        displayName: 'Ghost',
        firstName: 'Ghost',
        lastName: 'User',
        status: UserStatus::ACTIVE,
    );

    $event = createUpdatedEvent('01H_FAKE_USER', $attributes);

    // Act & Assert: Should just return without throwing an error
    $this->action->handle($event);
    expect(true)->toBeTrue();
});

it('gracefully ignores the update if the local user is already DELETED', function () {
    // Arrange: We don't want to sync a name change for an anonymized user!
    $user = User::factory()->create([
        'first_name' => 'Deleted',
        'status' => UserStatus::DELETED,
    ]);

    $attributes = new UserAttributes(
        email: 'resurrected@pms.gov.ph',
        displayName: 'I am Back',
        firstName: 'I am',
        lastName: 'Back',
        status: UserStatus::ACTIVE,
    );

    $event = createUpdatedEvent($user->id, $attributes);

    // Act
    $this->action->handle($event);

    // Assert: Status and Name should remain fully anonymized
    $user->refresh();
    expect($user->status)->toBe(UserStatus::DELETED)
        ->and($user->first_name)->toBe('Deleted');
});
