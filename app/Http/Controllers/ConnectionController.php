<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use App\Services\ConnectionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
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

        $excludedIds = Connection::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->whereIn('status', [Connection::STATUS_PENDING, Connection::STATUS_ACCEPTED])
            ->get(['sender_id', 'recipient_id'])
            ->flatMap(fn ($c) => [$c->sender_id, $c->recipient_id])
            ->push($user->id)
            ->unique()
            ->values()
            ->all();

        $suggestedPeople = User::query()
            ->whereIn('role', ['alumni', 'student'])
            ->where('account_status', 'approved')
            ->whereNotIn('id', $excludedIds)
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'batch_year', 'program_course', 'avatar_path', 'role']);

        // Surface the PUP-ITECH Official account to verified members (so they can
        // connect with the institution), unless it's already connected/pending or
        // the viewer *is* the institution.
        if ($user->isVerified() && ! $user->isInstitution()) {
            $institution = User::query()
                ->where('role', 'superadmin')
                ->whereNotIn('id', $excludedIds)
                ->first(['id', 'name', 'batch_year', 'program_course', 'avatar_path', 'role']);

            if ($institution) {
                $suggestedPeople = $suggestedPeople->prepend($institution);
            }
        }

        return view('connections.index', [
            'pendingReceived' => $pendingReceived,
            'pendingSent' => $pendingSent,
            'acceptedConnections' => $acceptedConnections,
            'suggestedPeople' => $suggestedPeople,
        ]);
    }

    public function counts(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('viewAny', Connection::class);

        return response()->json([
            'pending_received' => $this->connectionService->pendingReceivedInvites($user)->count(),
            'pending_sent' => $this->connectionService->pendingSentInvites($user)->count(),
            'accepted' => $this->connectionService->acceptedConnections($user)->count(),
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

    public function withdraw(Request $request, Connection $connection)
    {
        $this->authorize('withdraw', $connection);

        $this->connectionService->withdrawInvite($connection, $request->user());

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Connection invite withdrawn.');
    }

    public function remove(Request $request, Connection $connection): RedirectResponse
    {
        $this->authorize('remove', $connection);

        $this->connectionService->removeConnection($connection, $request->user());

        return back()->with('status', 'Connection removed.');
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
