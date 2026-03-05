<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

final class AreaAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
        ];
    }
}
