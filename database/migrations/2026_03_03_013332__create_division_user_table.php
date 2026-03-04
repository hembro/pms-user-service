<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_user', function (Blueprint $table) {
            $table->foreignUlid('user_id')
                ->constrained(table: 'users', column: 'id')
                ->cascadeOnDelete();

            $table->foreignUlid('division_id')
                ->constrained(table: 'divisions', column: 'id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }
};
