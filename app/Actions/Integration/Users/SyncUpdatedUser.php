<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class SyncUpdatedUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(): void {}
}
