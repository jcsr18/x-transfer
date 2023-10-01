<?php

namespace Database\Factories;

use App\Models\User;
use App\Supports\UserSupport;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'balance' => $this->faker->randomNumber(),
            'transfer_key' => UserSupport::generateSafeTransferKey(),
        ];
    }
}
