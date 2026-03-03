<?php

declare(strict_types=1);

namespace App\Console\Commands\Integration;

use Illuminate\Console\Command;

final class ConsumeUserEventsCommand extends Command
{
    protected $signature = 'integration:consume-user-events';

    protected $description = 'Consumes integration events for users from RabbitMQ with enterprise resilience';

    private bool $shouldQuit = false;

    public function handle() {}
}
