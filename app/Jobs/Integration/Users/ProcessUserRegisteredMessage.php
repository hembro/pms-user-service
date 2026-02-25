<?php

declare(strict_types=1);

namespace App\Jobs\Integration\Users;

use App\Actions\Integration\Users\SyncRegisteredUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

final class ProcessUserRegisteredMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public readonly array $payload
    ) {}

    public function handle(SyncRegisteredUser $action): void
    {
        $action->handle($this->payload);
    }

    public function failed(Throwable $exception): void
    {
        // Send an alert
    }
}
