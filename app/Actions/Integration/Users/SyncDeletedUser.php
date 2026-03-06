<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserDeletedEvent;

final readonly class SyncDeletedUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UserDeletedEvent $event): void
    {
        $user = User::query()->find($event->userId);

        if ($user === null || $user->status === UserStatus::DELETED) {
            return;
        }

        $this->db->transaction(
            callback: function () use ($user): void {

                $originalEmail = $user->email;

                $user->updateQuietly([
                    'email' => sprintf('%s::deleted_%s', $originalEmail, now()->timestamp),
                    'first_name' => 'Deleted',
                    'last_name' => 'User',
                    'display_name' => 'Deleted User',
                    'status' => UserStatus::DELETED,
                ]);

                $user->educationBackgrounds()->delete();
            }
        );
    }
}
