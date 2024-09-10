<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            // A = Active, C=Completed, H=Hold, X=Cancel
            'status' => fake()->randomElement(['A', 'C', 'H', 'X']),
            'created_at' => fake()->dateTimeBetween('-1 years', 'now'),
        ];
    }
}
