<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\User;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserProfileUpdatedEvent;
use Psr\Log\LoggerInterface;

final readonly class SyncUpdatedUser
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(UserProfileUpdatedEvent $event): void
    {
        /** @var UserAttributes $attributes */
        $attributes = $event->target->attributes;

        $user = User::query()->find($event->target->id);

        if (! $user) {
            $this->logger->warning('Received profile update for unknown user in PMS.', [
                'target_user_id' => $event->target->id,
            ]);

            return;
        }

        if ($user->status === UserStatus::DELETED) {
            $this->logger->warning('Received profile update for deleted user in PMS.', [
                'target_user_id' => $event->target->id,
            ]);

            return;
        }

        $user->fill([
            'email' => $attributes->email,
            'display_name' => $attributes->displayName,
            'first_name' => $attributes->firstName,
            'last_name' => $attributes->lastName,
            'status' => $attributes->status,
            'avatar_url' => $attributes->avatarUrl,
        ]);

        if (! $user->isDirty()) {
            return;
        }

        $user->last_synced_at = $event->occurredAt;

        $user->saveQuietly();
    }
}
