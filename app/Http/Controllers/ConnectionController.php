<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use App\Services\ConnectionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ConnectionService $connectionService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $this->authorize('viewAny', Connection::class);

        $pendingReceived = $this->connectionService->pendingReceivedInvites($user);
        $pendingSent = $this->connectionService->pendingSentInvites($user);
        $acceptedConnections = $this->connectionService->acceptedConnections($user);

        return view('connections.index', [
            'pendingReceived' => $pendingReceived,
            'pendingSent' => $pendingSent,
            'acceptedConnections' => $acceptedConnections,
        ]);
    }

    public function invite(Request $request, User $user): RedirectResponse
    {
        $this->authorize('create', [Connection::class, $user]);

        $result = $this->connectionService->sendInvite($request->user(), $user);

        $message = match ($result['result']) {
            'created' => 'Invite sent successfully.',
            'resent' => 'Invite re-sent successfully.',
            'already_pending' => 'You already sent an invite to this user.',
            'invite_received' => 'This user already invited you. Check your inbox to respond.',
            'already_connected' => 'You are already connected with this user.',
            default => 'Invite request processed.',
        };

        return back()->with('status', $message);
    }

    public function accept(Request $request, Connection $connection): RedirectResponse
    {
        $this->authorize('respond', $connection);

        $this->connectionService->acceptInvite($connection, $request->user());

        return back()->with('status', 'Connection invite accepted.');
    }

    public function ignore(Request $request, Connection $connection): RedirectResponse
    {
        $this->authorize('respond', $connection);

        $this->connectionService->ignoreInvite($connection, $request->user());

        return back()->with('status', 'Connection invite ignored.');
    }
}
