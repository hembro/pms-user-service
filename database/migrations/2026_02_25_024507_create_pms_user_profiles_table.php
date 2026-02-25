<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pms_user_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained(table: 'users', column: 'id')
                ->cascadeOnDelete();

            $table->foreignUlid('expertise_id')
                ->nullable()
                ->constrained(table: 'expertises', column: 'id')
                ->nullOnDelete();

            $table->foreignUlid('designation_id')
                ->nullable()
                ->constrained(table: 'designations', column: 'id')
                ->nullOnDelete();

            $table->string('employment_status')->index();
            $table->string('dpmis_pm_id')->nullable()->unique();

            $table->timestamp('submitted_to_dpmis_at')->nullable();
            $table->timestamps();
        });
    }
};
