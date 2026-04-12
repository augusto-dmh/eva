<?php

namespace App\Http\Controllers;

use App\Models\AssistiveConventionRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AssistiveConventionController extends Controller
{
    public function index(): Response
    {
        $year = request('ano', now()->year);

        $records = AssistiveConventionRecord::with('collaborator')
            ->where('ano_referencia', $year)
            ->get();

        return inertia('union/Opposition', [
            'records' => $records,
            'ano' => $year,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'collaborator_id' => 'required|exists:collaborators,id',
            'ano_referencia' => 'required|integer',
            'fez_oposicao' => 'boolean',
            'data_oposicao' => 'nullable|date',
            'valor_parcela' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
        ]);

        AssistiveConventionRecord::updateOrCreate(
            [
                'collaborator_id' => $request->collaborator_id,
                'ano_referencia' => $request->ano_referencia,
            ],
            $request->only(['fez_oposicao', 'data_oposicao', 'comprovante_ar_path', 'confirmado_sindicato', 'valor_parcela', 'observacoes'])
        );

        return back()->with('success', 'Registro atualizado com sucesso.');
    }
}
