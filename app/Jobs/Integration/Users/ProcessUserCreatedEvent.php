<?php

declare(strict_types=1);

namespace App\Jobs\Integration\Users;

use App\Actions\Integration\Users\SyncCreatedUser;
use jeremyaliparo\IntegrationContracts\IntegrationEventData;
use jeremyaliparo\IntegrationCore\Consuming\Jobs\AbstractIntegrationJob;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserCreatedEvent;

final class ProcessUserCreatedEvent extends AbstractIntegrationJob
{
    public function handle(SyncCreatedUser $action): void
    {
        $action->handle(
            event: $this->hydrateData()
        );
    }

    protected function hydrateData(): IntegrationEventData
    {
        return UserCreatedEvent::fromArray($this->payload['data']);
    }
}
