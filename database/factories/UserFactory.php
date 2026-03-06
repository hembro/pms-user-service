<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => $this->faker->email(),
            'display_name' => $this->faker->name(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'status' => UserStatus::ACTIVE,
        ];
    }
}
