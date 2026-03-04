<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\Division;
use App\Models\User;
use jeremyaliparo\IntegrationSchemas\Attributes\UserAttributes;
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
        $context = $event->systemContext;

        $user = User::query()->find($event->target->id);

        if (! $user) {
            $this->logger->warning('Received profile update for unknown user in PMS.', ['target_user_id' => $event->target->id]);

            return;
        }

        if (isset($context['division_ids']) && is_array($context['division_ids'])) {

            $validDivisionIds = Division::query()
                ->whereIn('id', $context['division_ids'])
                ->pluck('id')
                ->toArray();

            $user->divisions()->sync($validDivisionIds);
        }
    }
}
