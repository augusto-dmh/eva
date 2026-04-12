<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DpAssistantAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
