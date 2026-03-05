<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email')->unique();

            // --- UI Projection Data ---
            $table->string('display_name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('status')->index();
            $table->string('avatar_url')->nullable();

            // --- PMS Specific Data ---
            $table->foreignUlid('expertise_id')
                ->nullable()
                ->constrained(table: 'expertises', column: 'id')
                ->nullOnDelete();

            $table->foreignUlid('designation_id')
                ->nullable()
                ->constrained(table: 'designations', column: 'id')
                ->nullOnDelete();

            $table->string('employment_status')
                ->nullable()
                ->index();

            $table->string('dpmis_pm_id')->nullable()->unique();
            $table->timestamp('submitted_to_dpmis_at')->nullable();

            // --- Synchronization Telemetry ---
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }
};
