<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DpAssistantAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

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
