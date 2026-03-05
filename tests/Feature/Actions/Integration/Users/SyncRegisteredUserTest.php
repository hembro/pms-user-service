<?php

declare(strict_types=1);

use App\Actions\Integration\Users\SyncRegisteredUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ResourceType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserRegisteredEvent;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(SyncRegisteredUser::class);
});

// Helper to generate the Event DTO
function createRegisteredEvent(string $targetId, UserAttributes $attributes): UserRegisteredEvent
{
    return new UserRegisteredEvent(
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

it('successfully syncs a newly self-registered user into the PMS database', function () {
    // Arrange
    $newUserId = (string) Str::ulid();

    $attributes = new UserAttributes(
        email: 'self.registered@pms.gov.ph',
        displayName: 'Self Registered',
        firstName: 'Self',
        lastName: 'Registered',
        status: UserStatus::ACTIVE,
        mobileNumber: '09179998888',
        avatarUrl: null,
    );

    $event = createRegisteredEvent($newUserId, $attributes);

    // Act
    $this->action->handle($event);

    // Assert
    $user = User::query()->find($newUserId);

    expect($user)->not->toBeNull()
        ->and($user->id)->toBe($newUserId)
        ->and($user->email)->toBe('self.registered@pms.gov.ph')
        ->and($user->first_name)->toBe('Self');
});

it('is strictly idempotent and does not crash on duplicate registration events', function () {
    // Arrange
    $userId = (string) Str::ulid();

    $attributes = new UserAttributes(
        email: 'duplicate.reg@pms.gov.ph',
        displayName: 'Duplicate Reg',
        firstName: 'Duplicate',
        lastName: 'Reg',
        status: UserStatus::ACTIVE,
    );

    $event = createRegisteredEvent($userId, $attributes);

    // Act 1: Initial delivery
    $this->action->handle($event);

    // Act 2: RabbitMQ redelivers the event.
    // This must not throw a SQL constraint violation!
    $this->action->handle($event);

    // Assert: Only one record should exist
    $userCount = User::query()->where('id', $userId)->count();

    expect($userCount)->toBe(1);
});

it('executes an upsert if the registered user already exists locally', function () {
    // Arrange: The user somehow exists locally with old/placeholder data
    $user = User::factory()->create([
        'first_name' => 'Old Placeholder',
        'email' => 'placeholder@pms.gov.ph',
    ]);

    // The registration event brings the final, confirmed data
    $attributes = new UserAttributes(
        email: 'confirmed.reg@pms.gov.ph',
        displayName: 'Confirmed Reg',
        firstName: 'Confirmed',
        lastName: $user->last_name,
        status: UserStatus::ACTIVE,
    );

    $event = createRegisteredEvent($user->id, $attributes);

    // Act
    $this->action->handle($event);

    // Assert: The consumer gracefully patched the existing record
    $user->refresh();

    expect($user->first_name)->toBe('Confirmed')
        ->and($user->email)->toBe('confirmed.reg@pms.gov.ph');
});
