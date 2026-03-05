<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

final class ExpertiseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->domainWord(),
        ];
    }
}
