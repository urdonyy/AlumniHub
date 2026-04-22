<?php

namespace Database\Factories;

use App\Models\ProfileExperience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileExperience>
 */
class ProfileExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-8 years', '-1 year');

        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'organization' => fake()->company(),
            'start_date' => $startDate,
            'end_date' => fake()->boolean(65) ? fake()->dateTimeBetween($startDate, 'now') : null,
            'description' => fake()->optional()->sentence(16),
        ];
    }
}
