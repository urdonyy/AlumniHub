<?php

use App\Models\Connection;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

function createPendingInvite(User $sender, User $recipient): Connection
{
    [$lowId, $highId] = Connection::normalizedPair($sender->id, $recipient->id);

    return Connection::create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'user_low_id' => $lowId,
        'user_high_id' => $highId,
        'status' => Connection::STATUS_PENDING,
    ]);
}

test('verified user can send invite', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    actingAs($sender)
        ->post(route('connections.invite', $recipient))
        ->assertSessionHas('status', 'Invite sent successfully.');

    [$lowId, $highId] = Connection::normalizedPair($sender->id, $recipient->id);

    expect(Connection::query()
        ->where('user_low_id', $lowId)
        ->where('user_high_id', $highId)
        ->where('status', Connection::STATUS_PENDING)
        ->exists())->toBeTrue();
});

test('self invite is blocked', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    actingAs($user)
        ->post(route('connections.invite', $user))
        ->assertForbidden();

    assertDatabaseCount('connections', 0);
});

test('duplicate invite from same sender remains single pending row', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    actingAs($sender)->post(route('connections.invite', $recipient));

    actingAs($sender)
        ->post(route('connections.invite', $recipient))
        ->assertSessionHas('status', 'You already sent an invite to this user.');

    assertDatabaseCount('connections', 1);
});

test('reverse invite does not create duplicate row and asks recipient to respond', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    actingAs($sender)->post(route('connections.invite', $recipient));

    actingAs($recipient)
        ->post(route('connections.invite', $sender))
        ->assertSessionHas('status', 'This user already invited you. Check your inbox to respond.');

    assertDatabaseCount('connections', 1);
});

test('recipient can accept pending invite', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $invite = createPendingInvite($sender, $recipient);

    actingAs($recipient)
        ->post(route('connections.accept', $invite))
        ->assertSessionHas('status', 'Connection invite accepted.');

    expect($invite->fresh()->status)->toBe(Connection::STATUS_ACCEPTED);
});

test('recipient can ignore pending invite', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $invite = createPendingInvite($sender, $recipient);

    actingAs($recipient)
        ->post(route('connections.ignore', $invite))
        ->assertSessionHas('status', 'Connection invite ignored.');

    expect($invite->fresh()->status)->toBe(Connection::STATUS_IGNORED);
});

test('non recipient cannot accept invite', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $thirdUser = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $invite = createPendingInvite($sender, $recipient);

    actingAs($thirdUser)
        ->post(route('connections.accept', $invite))
        ->assertForbidden();

    expect($invite->fresh()->status)->toBe(Connection::STATUS_PENDING);
});

test('connections index shows pending invite for recipient', function () {
    $sender = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
        'name' => 'Sender User',
    ]);

    $recipient = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    createPendingInvite($sender, $recipient);

    actingAs($recipient)
        ->get(route('connections.index'))
        ->assertOk()
        ->assertSee('Sender User')
        ->assertSee('Pending Invites Received');
});
