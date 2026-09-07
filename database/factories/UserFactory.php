<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'role'              => 'user',
            'perusahaan_id'     => Perusahaan::factory(),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'          => 'super_admin',
            'perusahaan_id' => null,
        ]);
    }
}