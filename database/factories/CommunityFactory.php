<?php

namespace Database\Factories;

use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Community>
 */
class CommunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'name' => ucwords($name),
            'slug' => str($name)->slug(),
            'description' => fake()->paragraph(),
            'created_by' => \App\Models\User::factory(),
            'is_system' => false,
            'system_key' => null,
        ];
    }

    /**
     * Create a system community.
     */
    public function system(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_system' => true,
            'system_key' => str($attributes['name'])->slug(),
        ]);
    }
}
