<?php

declare(strict_types=1);

namespace App\Jobs\Integration\Users;

use App\Actions\Integration\Users\SyncRegisteredUser;
use jeremyaliparo\IntegrationContracts\IntegrationEventData;
use jeremyaliparo\IntegrationCore\Consuming\Jobs\AbstractIntegrationJob;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserRegisteredEvent;

final class ProcessUserRegisteredEvent extends AbstractIntegrationJob
{
    public function handle(SyncRegisteredUser $action): void
    {
        $action->handle(
            event: $this->hydrateData()
        );
    }

    protected function hydrateData(): IntegrationEventData
    {
        return UserRegisteredEvent::fromArray($this->payload['data']);
    }
}
