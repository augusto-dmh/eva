<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlrRequest;
use App\Models\PlrRound;
use App\Services\Payroll\PlrSimulatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class PlrController extends Controller
{
    public function __construct(private PlrSimulatorService $service) {}

    public function index(): Response
    {
        $rounds = PlrRound::with('criadoPor')
            ->orderByDesc('ano_referencia')
            ->get();

        return inertia('plr/Index', ['rounds' => $rounds]);
    }

    public function create(): Response
    {
        return inertia('plr/Create');
    }

    public function store(StorePlrRequest $request): RedirectResponse
    {
        $round = PlrRound::create([
            ...$request->validated(),
            'status' => 'rascunho',
            'status_sindicato' => 'nao_iniciado',
            'documento_politica_revisado' => false,
            'criado_por_id' => $request->user()->id,
        ]);

        return redirect()->route('plr.show', $round)
            ->with('success', 'PLR criado com sucesso.');
    }

    public function show(PlrRound $plrRound): Response
    {
        $plrRound->load(['criadoPor', 'entries.collaborator', 'committeeMembers.collaborator', 'committeeMembers.legalEntity']);

        return inertia('plr/Show', ['round' => $plrRound]);
    }

    public function simulate(Request $request, PlrRound $plrRound): RedirectResponse
    {
        $request->validate(['valor_total' => 'required|numeric|min:0.01']);

        $this->service->simulate($plrRound, (float) $request->valor_total);

        return back()->with('success', 'Simulação PLR realizada com sucesso.');
    }
}
