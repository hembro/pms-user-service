<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\User;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserStatusChangedEvent;

final readonly class SyncUserStatusChange
{
    public function handle(UserStatusChangedEvent $event): void
    {
        if ($event->newStatus === UserStatus::DELETED || $event->newStatus === $event->oldStatus) {
            return;
        }

        $user = User::query()->find($event->target->id);

        if ($user === null || $user->status === $event->newStatus) {
            return;
        }

        $user->update([
            'status' => $event->newStatus,
        ]);
    }
}
