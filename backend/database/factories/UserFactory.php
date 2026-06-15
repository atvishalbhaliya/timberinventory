<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'branch_id' => null,
            'login_id' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'employee_code' => fake()->unique()->bothify('EMP###'),
            'full_name' => fake()->name(),
            'mobile' => fake()->numerify('9#########'),
            'email' => fake()->unique()->safeEmail(),
            'role_id' => null,
            'status' => 'Active',
        ];
    }
}
