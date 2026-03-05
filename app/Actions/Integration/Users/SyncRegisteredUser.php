<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\User;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserRegisteredEvent;

final readonly class SyncRegisteredUser
{
    public function handle(UserRegisteredEvent $event): void
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
