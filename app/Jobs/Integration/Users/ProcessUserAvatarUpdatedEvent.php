<?php

declare(strict_types=1);

namespace App\Jobs\Integration\Users;

use App\Actions\Integration\Users\SyncUserAvatarUpdated;
use jeremyaliparo\IntegrationContracts\IntegrationEventData;
use jeremyaliparo\IntegrationCore\Consuming\Jobs\AbstractIntegrationJob;
use jeremyaliparo\IntegrationSchemas\Events\Users\UserAvatarUpdatedEvent;

final class ProcessUserAvatarUpdatedEvent extends AbstractIntegrationJob
{
    public function handle(SyncUserAvatarUpdated $action): void
    {
        $action->handle(
            event: $this->hydrateData()
        );
    }

    protected function hydrateData(): IntegrationEventData
    {
        return UserAvatarUpdatedEvent::fromArray($this->payload['data']);
    }
}
