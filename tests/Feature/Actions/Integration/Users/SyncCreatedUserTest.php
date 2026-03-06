<?php

declare(strict_types=1);

use App\Actions\Integration\Users\SyncCreatedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ResourceType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserCreatedEvent;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(SyncCreatedUser::class);
});

// Helper to generate the Event DTO
function createCreatedEvent(string $targetId, UserAttributes $attributes): UserCreatedEvent
{
    return new UserCreatedEvent(
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
        occurredAt: now()->toIso8601String(),
    );
}

it('successfully creates a new user in the local PMS database', function () {
    // Arrange: Generate a new ULID/UUID for the incoming user
    $newUserId = (string) Str::ulid();

    $attributes = new UserAttributes(
        email: 'new.hire@pms.gov.ph',
        displayName: 'New Hire',
        firstName: 'New',
        lastName: 'Hire',
        status: UserStatus::ACTIVE,
        avatarUrl: null,
    );

    $event = createCreatedEvent($newUserId, $attributes);

    // Act
    $this->action->handle($event);

    // Assert: The user should now exist locally with the exact ID from the central service
    $user = User::query()->find($newUserId);

    expect($user)->not->toBeNull()
        ->and($user->id)->toBe($newUserId)
        ->and($user->email)->toBe('new.hire@pms.gov.ph')
        ->and($user->first_name)->toBe('New')
        ->and($user->status)->toBe(UserStatus::ACTIVE);
});

it('is strictly idempotent and does not crash on duplicate creation events', function () {
    // Arrange
    $userId = (string) Str::ulid();

    $attributes = new UserAttributes(
        email: 'duplicate.test@pms.gov.ph',
        displayName: 'Duplicate',
        firstName: 'Duplicate',
        lastName: 'Test',
        status: UserStatus::ACTIVE,
    );

    $event = createCreatedEvent($userId, $attributes);

    // Act 1: The first delivery creates the user
    $this->action->handle($event);

    // Act 2: RabbitMQ accidentally delivers the exact same message a second time
    // If we used a blind `insert()`, this would throw a fatal SQLite/MySQL constraint exception!
    $this->action->handle($event);

    // Assert: We prove the code survived, and we still only have ONE user with this ID
    $userCount = User::query()->where('id', $userId)->count();

    expect($userCount)->toBe(1);
});

it('updates the existing user if a creation event arrives but the user already exists', function () {
    // Arrange: A rare race condition where the user exists locally but we get a "Created" event
    $user = User::factory()->create([
        'first_name' => 'Old Name',
        'email' => 'race.condition@pms.gov.ph',
    ]);

    // The central system thinks it's creating them, and sends newer data
    $attributes = new UserAttributes(
        email: 'race.condition@pms.gov.ph',
        displayName: 'New Name',
        firstName: 'New Name',
        lastName: $user->last_name,
        status: UserStatus::ACTIVE,
    );

    $event = createCreatedEvent($user->id, $attributes);

    // Act
    $this->action->handle($event);

    // Assert: Instead of crashing, your consumer should smartly execute an "Upsert"
    // and apply the latest attributes to the existing local record.
    $user->refresh();

    expect($user->first_name)->toBe('New Name');
});
