<?php

declare(strict_types=1);

namespace App\Actions\Integration\Users;

use App\Models\User;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

final readonly class SyncRegisteredUser
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(array $payload): void
    {
        $this->validatePayload($payload);

        User::query()->upsert(
            values: [
                'id' => $payload['id'],
                'email' => $payload['email'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            uniqueBy: ['id'],
            update: ['email', 'updated_at']
        );

        $this->logger->info('PMS read-model synced for registered user.', [
            'user_id' => $payload['id'],
        ]);
    }

    private function validatePayload(array $payload): void
    {
        if (empty($payload['id']) || empty($payload['email'])) {
            $this->logger->critical('Malformed UserRegistered integration message.', [
                'payload' => $payload,
            ]);

            throw new InvalidArgumentException('Missing required fields [id, email] in UserRegistered payload.');
        }
    }
}
