<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_backgrounds', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained(table: 'users', column: 'id')
                ->cascadeOnDelete();

            $table->string('level');
            $table->string('school')->nullable();
            $table->string('degree')->nullable();
            $table->string('year')->nullable();
            $table->text('awards')->nullable();

            $table->timestamps();
        });
    }
};
