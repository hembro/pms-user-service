<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\User;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserAvatarUpdatedEvent;
use Psr\Log\LoggerInterface;

final readonly class SyncUserAvatarUpdated
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(UserAvatarUpdatedEvent $event): void
    {
        $user = User::query()->find($event->target->id);

        if ($user === null) {
            $this->logger->warning('Received avatar update for unknown user in PMS.', [
                'target_user_id' => $event->target->id,
            ]);

            return;
        }

        if ($user->avatar_url === $event->newAvatarUrl) {
            return;
        }

        $user->updateQuietly([
            'avatar_url' => $event->newAvatarUrl,
        ]);
    }
}
