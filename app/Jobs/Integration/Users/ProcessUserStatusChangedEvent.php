<?php

declare(strict_types=1);

namespace App\Jobs\Integration\Users;

use App\Actions\Integration\Users\SyncUserStatusChange;
use jeremyaliparo\IntegrationContracts\IntegrationEventData;
use jeremyaliparo\IntegrationCore\Consuming\Jobs\AbstractIntegrationJob;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserStatusChangedEvent;

final class ProcessUserStatusChangedEvent extends AbstractIntegrationJob
{
    public function handle(SyncUserStatusChange $action): void
    {
        $action->handle(
            event: $this->hydrateData()
        );
    }

    protected function hydrateData(): IntegrationEventData
    {
        return UserStatusChangedEvent::fromArray($this->payload['data']);
    }
}
