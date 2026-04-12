<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DpAssistantAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DpAssistantController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $request->validate(['question' => 'required|string|max:500']);

        $answer = (new DpAssistantAgent)->ask($request->question);

        return response()->json(['answer' => $answer]);
    }
}
