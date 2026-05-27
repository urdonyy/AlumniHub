<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $body = fake()->paragraphs(3, true);

        return [
            'community_id' => \App\Models\Community::factory(),
            'user_id' => \App\Models\User::factory(),
            'title' => $title,
            'body_markdown' => $body,
            'body_html' => '<p>' . htmlspecialchars($body) . '</p>',
            'status' => 'published',
            'visibility' => 'public',
            'pinned' => false,
            'published_at' => now(),
        ];
    }

    /**
     * Create a draft post.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Set visibility to members only.
     */
    public function membersOnly(): static
    {
        return $this->state(fn(array $attributes) => [
            'visibility' => 'members',
        ]);
    }

    /**
     * Set visibility to connections only.
     */
    public function connectionsOnly(): static
    {
        return $this->state(fn(array $attributes) => [
            'visibility' => 'connections',
        ]);
    }

    /**
     * Pin the post.
     */
    public function pinned(): static
    {
        return $this->state(fn(array $attributes) => [
            'pinned' => true,
        ]);
    }
}
