<?php

declare(strict_types=1);

use jeremyaliparo\IntegrationSchemas\Enums\Users\UserRoutingKey;

return [
    /*
    |--------------------------------------------------------------------------
    | Integration Event Subscriptions
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        UserRoutingKey::USER_CREATED->value => App\Jobs\Integration\Users\ProcessUserCreatedEvent::class,
        UserRoutingKey::USER_REGISTERED->value => App\Jobs\Integration\Users\ProcessUserRegisteredEvent::class,
        UserRoutingKey::USER_PROFILE_UPDATED->value => App\Jobs\Integration\Users\ProcessUserProfileUpdatedEvent::class,
        UserRoutingKey::USER_STATUS_UPDATED->value => App\Jobs\Integration\Users\ProcessUserStatusChangedEvent::class,
        UserRoutingKey::USER_AVATAR_UPDATED->value => App\Jobs\Integration\Users\ProcessUserAvatarUpdatedEvent::class,
        UserRoutingKey::USER_DELETED->value => App\Jobs\Integration\Users\ProcessUserDeletedEvent::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Consumer Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue_name' => env('INTEGRATION_QUEUE_NAME', 'pms_user_service_queue'),

    /**
     * |--------------------------------------------------------------------------
     * | RabbitMQ Configuration
     * |--------------------------------------------------------------------------
     */
    'rabbitmq' => [
        'driver' => 'rabbitmq',
        'queue' => env('RABBITMQ_QUEUE', 'service_name_integration_events'),
        'connection' => 'default',
        'hosts' => [
            [
                'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                'port' => (int) env('RABBITMQ_PORT', 5672),
                'user' => env('RABBITMQ_USER', 'guest'),
                'password' => env('RABBITMQ_PASSWORD', 'guest'),
                'vhost' => env('RABBITMQ_VHOST', '/'),
            ],
        ],
        'options' => [
            'ssl_options' => [
                'cafile' => env('RABBITMQ_SSL_CAFILE', null),
                'local_cert' => env('RABBITMQ_SSL_LOCAL_CERT', null),
                'local_pk' => env('RABBITMQ_SSL_LOCAL_KEY', null),
                'verify_peer' => (bool) env('RABBITMQ_SSL_VERIFY_PEER', true),
                'passphrase' => env('RABBITMQ_SSL_PASSPHRASE', null),
            ],
            'publish_confirms' => true,
            'heartbeat' => 60,
            'timeout' => 120,
            'exchange' => [
                'name' => env('RABBITMQ_EXCHANGE_NAME', 'microservices.topic'),
                'type' => 'topic',
                'durable' => true,
            ],
        ],
        'worker' => 'default',
        'after_commit' => false,
        'prefetch_count' => 20,
    ],
];
