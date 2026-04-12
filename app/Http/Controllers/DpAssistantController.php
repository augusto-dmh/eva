<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DpAssistantAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Ai\Streaming\Events\TextDelta;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DpAssistantController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('dp-assistant/Index');
    }

    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string|max:36',
        ]);

        $result = (new DpAssistantAgent)->ask(
            $request->question,
            $request->user(),
            $request->conversation_id,
        );

        return response()->json($result);
    }

    public function conversations(Request $request): JsonResponse
    {
        $conversations = DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_favorite')
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'is_favorite', 'created_at', 'updated_at']);

        return response()->json(['conversations' => $conversations]);
    }

    public function loadConversation(Request $request, string $id): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first(['id', 'title', 'is_favorite']);

        if (! $conversation) {
            return response()->json(['error' => 'Conversa não encontrada.'], 404);
        }

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get(['role', 'content', 'created_at']);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function destroyConversation(Request $request, string $id): JsonResponse
    {
        $deleted = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (! $deleted) {
            return response()->json(['error' => 'Conversa não encontrada.'], 404);
        }

        DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function streamResponse(Request $request): StreamedResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string|max:36',
        ]);

        $agent = new DpAssistantAgent;
        $streamable = $agent->streamAsk(
            $request->question,
            $request->user(),
            $request->conversation_id,
        );

        return response()->stream(function () use ($streamable) {
            while (ob_get_level()) {
                ob_end_flush();
            }

            foreach ($streamable as $event) {
                if ($event instanceof TextDelta) {
                    echo 'data: '.json_encode(['type' => 'chunk', 'delta' => $event->delta])."\n\n";
                    flush();
                }
            }

            echo 'data: '.json_encode([
                'type' => 'done',
                'conversation_id' => $streamable->conversationId,
            ])."\n\n";
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function renameConversation(Request $request, string $id): JsonResponse
    {
        $request->validate(['title' => 'required|string|max:100']);

        $updated = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['title' => $request->title]);

        if (! $updated) {
            return response()->json(['error' => 'Conversa não encontrada.'], 404);
        }

        return response()->json(['title' => $request->title]);
    }

    public function toggleFavorite(Request $request, string $id): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first(['is_favorite']);

        if (! $conversation) {
            return response()->json(['error' => 'Conversa não encontrada.'], 404);
        }

        DB::table('agent_conversations')
            ->where('id', $id)
            ->update(['is_favorite' => ! $conversation->is_favorite]);

        return response()->json(['is_favorite' => ! $conversation->is_favorite]);
    }
}
