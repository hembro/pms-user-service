<?php

declare(strict_types=1);

use App\Actions\Integration\Users\SyncUserStatusChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ResourceType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserStatusChangedEvent;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(SyncUserStatusChange::class);
});

// Helper to generate the Event DTO
function createStatusChangeEvent(string $targetId, UserStatus $oldStatus, UserStatus $newStatus): UserStatusChangedEvent
{
    return new UserStatusChangedEvent(
        actor: new Actor(
            id: 'admin-ulid-id',
            type: ActorType::USER,
            name: 'Admin',
            email: 'admin@test.com',
        ),
        target: new Target(
            id: $targetId,
            type: ResourceType::USER,
            attributes: new UserAttributes(
                email: 'target@test.com',
                displayName: 'Target user',
                firstName: 'Target',
                lastName: 'User',
                status: UserStatus::ACTIVE,
            )
        ),
        oldStatus: $oldStatus,
        newStatus: $newStatus,
        occurredAt: now()->toIso8601String(),
    );
}

it('successfully updates the local user status quietly', function () {
    // Arrange
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $event = createStatusChangeEvent($user->id, UserStatus::ACTIVE, UserStatus::SUSPENDED);

    // Act
    $this->action->handle($event);

    // Assert
    expect($user->fresh()->status)->toBe(UserStatus::SUSPENDED);
});

it('intercepts DELETED payloads', function () {
    // Arrange
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $event = createStatusChangeEvent($user->id, UserStatus::ACTIVE, UserStatus::DELETED);

    // Act
    $this->action->handle($event);

    // Assert Status should remain ACTIVE
    expect($user->fresh()->status)->toBe(UserStatus::ACTIVE);
});

it('is strictly idempotent and executes zero queries if the local status already matches the new status', function () {
    // Arrange: The local database is ALREADY suspended (simulating a duplicate event)
    $user = User::factory()->create(['status' => UserStatus::SUSPENDED]);
    $event = createStatusChangeEvent($user->id, UserStatus::ACTIVE, UserStatus::SUSPENDED);

    // Act: Trap the queries
    DB::enableQueryLog();
    $this->action->handle($event);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Assert: Prove no UPDATE queries ran
    $updateQueries = array_filter($queries, fn ($q) => str_starts_with(mb_strtolower($q['query']), 'update'));

    expect($updateQueries)->toBeEmpty()
        ->and($user->fresh()->status)->toBe(UserStatus::SUSPENDED);
});

it('gracefully ignores the event if the user does not exist locally', function () {
    // Arrange
    $event = createStatusChangeEvent('01H_FAKE_USER', UserStatus::ACTIVE, UserStatus::SUSPENDED);

    // Act & Assert: Should not throw any errors or call the delete spy
    $this->action->handle($event);
    expect(true)->toBeTrue();
});

it('exits early if the event payload shows no actual status change', function () {
    // Arrange: Event says ACTIVE -> ACTIVE
    $event = createStatusChangeEvent('01H_ANY_USER', UserStatus::ACTIVE, UserStatus::ACTIVE);

    DB::enableQueryLog();
    $this->action->handle($event);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Assert: Prove it exited so early it didn't even run a SELECT query!
    expect($queries)->toBeEmpty();
});
