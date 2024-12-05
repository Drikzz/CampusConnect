<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'wmsu_email' => fake()->unique()->freeEmail(),
            'user_type_id' => rand(1, 3), // Adjust based on your user types
            'wmsu_dept_id' => rand(1, 5), // Adjust based on your departments
            'grade_level_id' => rand(1, 4), // Adjust based on your grade levels
            'profile_picture' => null,
            'email_verified_at' => now(),
            'is_seller' => fake()->boolean(),
            'is_verified' => fake()->boolean(),
            'verified_at' => now(),
            'wmsu_id_front' => null,
            'wmsu_id_back' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
            'is_verified' => false,
            'verified_at' => null,
        ]);
    }
}
