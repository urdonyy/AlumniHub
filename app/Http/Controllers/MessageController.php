<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly MessageService $messageService) {}

    /**
     * Inbox: list of conversations + connections you can start a new chat with.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->forUser($user)
            ->with(['userLow', 'userHigh', 'latestMessage'])
            ->whereNotNull('last_message_at')
            ->orderByDesc('last_message_at')
            ->get();

        // Per-conversation unread count (messages from the other party, unread).
        $unreadByConversation = $this->unreadCountsFor($user, $conversations->pluck('id')->all());

        // Accepted connections that don't yet have a started conversation, so the
        // user can open a fresh thread with them.
        $partneredIds = $conversations
            ->map(fn ($c) => $c->otherParticipantFor($user)?->id)
            ->filter()
            ->all();

        $newChatPartners = $user->connections()->get()
            ->map(fn ($connection) => $connection->otherPartyFor($user))
            ->filter()
            ->reject(fn ($partner) => in_array($partner->id, $partneredIds, true))
            ->values();

        return view('messages.index', [
            'conversations'        => $conversations,
            'unreadByConversation' => $unreadByConversation,
            'newChatPartners'      => $newChatPartners,
            'authUser'             => $user,
        ]);
    }

    /**
     * Thread view with a specific connected user.
     */
    public function show(Request $request, User $user)
    {
        $authUser = $request->user();

        abort_if($user->id === $authUser->id, 404);

        $canSend = $authUser->isConnectedWith($user);

        // Look up an existing thread WITHOUT creating one.
        [$lowId, $highId] = Connection::normalizedPair($authUser->id, $user->id);
        $conversation = Conversation::where('user_low_id', $lowId)
            ->where('user_high_id', $highId)
            ->first();

        // Disconnected users may still READ an existing thread, but can't open a
        // brand-new one. Block only when not connected AND no history exists.
        if (! $canSend && ! $conversation) {
            return redirect()->route('messages.index')
                ->with('error', 'You can only message your connections.');
        }

        // Connected users opening a fresh thread create it here.
        $conversation ??= Conversation::betweenUsers($authUser, $user);

        $this->messageService->markConversationRead($conversation, $authUser);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('messages.show', [
            'conversation' => $conversation,
            'partner'      => $user,
            'messages'     => $messages,
            'authUser'     => $authUser,
            'canSend'      => $canSend,
        ]);
    }

    /**
     * Send a message to a connected user.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_if($user->id === $authUser->id, 404);
        abort_unless($authUser->isConnectedWith($user), 403, 'You can only message your connections.');

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = $this->messageService->send($authUser, $user, $validated['body']);

        return response()->json([
            'success' => true,
            'message' => [
                'id'               => $message->id,
                'conversation_id'  => $message->conversation_id,
                'sender_id'        => $message->sender_id,
                'body'             => $message->body,
                'created_at_human' => $message->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Mark a thread read (called after Echo delivers a message while viewing).
     */
    public function markRead(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_unless($authUser->isConnectedWith($user), 403);

        $conversation = Conversation::betweenUsers($authUser, $user);
        $this->messageService->markConversationRead($conversation, $authUser);

        return response()->json(['success' => true]);
    }

    /**
     * Total unread message count for the nav badge (JSON).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadMessagesCount(),
        ]);
    }

    /**
     * Per-conversation unread counts keyed by conversation id.
     *
     * @param  array<int, int>  $conversationIds
     * @return array<int, int>
     */
    private function unreadCountsFor(User $user, array $conversationIds): array
    {
        if (empty($conversationIds)) {
            return [];
        }

        return \App\Models\Message::query()
            ->selectRaw('conversation_id, COUNT(*) as aggregate')
            ->whereIn('conversation_id', $conversationIds)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->groupBy('conversation_id')
            ->pluck('aggregate', 'conversation_id')
            ->all();
    }
}
