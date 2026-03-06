<?php

declare(strict_types=1);

namespace App\Jobs\Integration\Users;

use App\Actions\Integration\Users\SyncUpdatedUser;
use jeremyaliparo\IntegrationContracts\IntegrationEventData;
use jeremyaliparo\IntegrationCore\Consuming\Jobs\AbstractIntegrationJob;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserProfileUpdatedEvent;

final class ProcessUserProfileUpdatedEvent extends AbstractIntegrationJob
{
    public function handle(SyncUpdatedUser $action): void
    {
        $action->handle(
            event: $this->hydrateData()
        );
    }

    protected function hydrateData(): IntegrationEventData
    {
        return UserProfileUpdatedEvent::fromArray($this->payload['data']);
    }
}
