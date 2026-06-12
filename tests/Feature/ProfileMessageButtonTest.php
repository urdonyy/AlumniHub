<?php

use App\Models\Connection;
use App\Models\User;
use function Pest\Laravel\actingAs;

function connect(User $a, User $b): void
{
    Connection::create([
        'sender_id' => $a->id, 'recipient_id' => $b->id,
        'user_low_id' => min($a->id, $b->id), 'user_high_id' => max($a->id, $b->id),
        'status' => Connection::STATUS_ACCEPTED, 'acted_at' => now(),
    ]);
}

test('a Message button linking to the conversation shows on a connected user\'s profile', function () {
    $viewer = User::factory()->create(['account_status' => 'approved']);
    $other = User::factory()->create(['account_status' => 'approved']);
    connect($viewer, $other);

    actingAs($viewer)
        ->get(route('profiles.show', $other))
        ->assertOk()
        ->assertSee(route('messages.show', $other), false);
});

test('no Message button on a non-connected user\'s profile', function () {
    $viewer = User::factory()->create(['account_status' => 'approved']);
    $other = User::factory()->create(['account_status' => 'approved']);

    actingAs($viewer)
        ->get(route('profiles.show', $other))
        ->assertOk()
        ->assertDontSee(route('messages.show', $other), false);
});
