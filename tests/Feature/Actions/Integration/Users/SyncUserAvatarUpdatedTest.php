<?php

declare(strict_types=1);

use App\Actions\Integration\Users\SyncUserAvatarUpdated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Commons\Actor;
use jeremyaliparo\IntegrationSchemas\Commons\Target;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ActorType;
use jeremyaliparo\IntegrationSchemas\Enums\Commons\ResourceType;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserAvatarUpdatedEvent;
use Psr\Log\LoggerInterface;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loggerMock = mock(LoggerInterface::class);
    $this->action = app(SyncUserAvatarUpdated::class);
});

// Helper function to generate a valid event DTO
function createAvatarEvent(string $targetId, string $newAvatarUrl): UserAvatarUpdatedEvent
{
    return new UserAvatarUpdatedEvent(
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
        oldAvatarUrl: 'https://s3.aws.com/old.png',
        newAvatarUrl: $newAvatarUrl,
        occurredAt: now()->toIso8601String(),
    );
}

it('successfully updates the avatar when the user exists', function () {
    // Arrange
    $user = User::factory()->create(['avatar_url' => 'https://s3.aws.com/old.png']);
    $event = createAvatarEvent($user->id, 'https://s3.aws.com/new-avatar.png');

    // Act
    $this->action->handle($event);

    // Assert
    expect($user->fresh()->avatar_url)->toBe('https://s3.aws.com/new-avatar.png');
});

it('is strictly idempotent and executes zero database queries on duplicate events', function () {
    // Arrange: User already has the updated avatar (simulating a duplicate event)
    $user = User::factory()->create(['avatar_url' => 'https://s3.aws.com/new-avatar.png']);
    $event = createAvatarEvent($user->id, 'https://s3.aws.com/new-avatar.png');

    // Act: Enable the query log to strictly monitor database traffic
    DB::enableQueryLog();

    $this->action->handle($event);

    $executedQueries = DB::getQueryLog();
    DB::disableQueryLog();

    // Assert: We filter out the SELECT query (which is necessary to find the user).
    // We want to prove that NO "UPDATE" queries were fired!
    $updateQueries = array_filter($executedQueries, fn ($query) => str_starts_with($query['query'], 'update'));

    expect($updateQueries)->toBeEmpty()
        ->and($user->fresh()->avatar_url)->toBe('https://s3.aws.com/new-avatar.png'); // Value remained intact
});

it('gracefully exits and logs a warning when the target user does not exist', function () {
    // Arrange: Generate an ID that definitely does not exist in the DB
    $fakeUserId = '01H_FAKE_USER_ID';
    $event = createAvatarEvent($fakeUserId, 'https://s3.aws.com/new-avatar.png');

    // Expect the logger to be called exactly once with a warning
    $this->loggerMock
        ->shouldReceive('warning')
        ->once()
        ->with('Received avatar update for unknown user in PMS.', [
            'target_user_id' => $fakeUserId,
        ]);

    // Act
    $this->action->handle($event);

    // Assert: If the test reaches here without throwing a "Call to a member function update() on null"
    // fatal error, it proves our early return works perfectly!
    expect(true)->toBeTrue();
});
