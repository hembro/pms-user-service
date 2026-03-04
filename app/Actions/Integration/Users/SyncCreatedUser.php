<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Actions\Integration\Users\Concerns\SyncsUserContext;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserCreatedEvent;

final readonly class SyncCreatedUser
{
    use SyncsUserContext;

    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UserCreatedEvent $event): void
    {
        /** @var UserAttributes $attributes */
        $attributes = $event->target->attributes;

        User::query()->updateOrCreate(
            ['id' => $event->target->id],
            [
                'email' => $attributes->email,
                'display_name' => $attributes->displayName,
                'first_name' => $attributes->firstName,
                'last_name' => $attributes->lastName,
                'status' => $attributes->status,
                'last_synced_at' => $event->occurredAt,
            ]
        );
    }
}
