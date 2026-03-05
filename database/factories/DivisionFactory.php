<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DivisionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DivisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'status' => DivisionStatus::ACTIVE,
        ];
    }
}
