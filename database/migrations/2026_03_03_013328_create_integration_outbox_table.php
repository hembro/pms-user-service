<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use jeremyaliparo\IntegrationCore\Enums\OutboxStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_outbox', function (Blueprint $table) {
            // Using string instead of id() because we generate ULIDs in the package
            $table->string('id', 26)->primary();

            // The AMQP Routing Key (e.g., 'user.updated')
            $table->string('routing_key');

            // The JSON envelope payload
            $table->jsonb('payload');

            $table->string('status', 20)->default(OutboxStatus::PENDING->value);

            $table->text('error')->nullable();

            // Audit trailing
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // The Outbox Worker Job queries `WHERE status = 'PENDING'`.
            // Without this index, your database does a full table scan
            // every time an event fires. This index prevents database locks and timeouts.
            $table->index(['status', 'created_at']);
        });
    }
};
