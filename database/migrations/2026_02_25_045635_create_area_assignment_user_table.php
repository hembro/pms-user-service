<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_assignment_user', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained(table: 'users', column: 'id')
                ->cascadeOnDelete();

            $table->foreignUlid('area_assignment_id')
                ->constrained(table: 'area_assignments', column: 'id')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'area_assignment_id']);

            $table->timestamps();
        });
    }
};
