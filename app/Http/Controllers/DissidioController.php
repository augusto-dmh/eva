<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDissidioRequest;
use App\Models\DissidioRound;
use App\Services\Payroll\DissidioSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class DissidioController extends Controller
{
    public function __construct(private DissidioSimulationService $service) {}

    public function index(): Response
    {
        $rounds = DissidioRound::with('criadoPor')
            ->orderByDesc('ano_referencia')
            ->get();

        return inertia('dissidio/Index', ['rounds' => $rounds]);
    }

    public function create(): Response
    {
        return inertia('dissidio/Create');
    }

    public function store(StoreDissidioRequest $request): RedirectResponse
    {
        $round = DissidioRound::create([
            ...$request->validated(),
            'status' => 'rascunho',
            'criado_por_id' => $request->user()->id,
        ]);

        return redirect()->route('dissidio-rounds.show', $round)
            ->with('success', 'Dissídio criado com sucesso.');
    }

    public function show(DissidioRound $dissidioRound): Response
    {
        $dissidioRound->load(['criadoPor', 'aplicadoPor', 'entries.collaborator']);

        return inertia('dissidio/Show', ['round' => $dissidioRound]);
    }

    public function simulate(DissidioRound $dissidioRound): RedirectResponse
    {
        $this->service->simulate($dissidioRound);

        return back()->with('success', 'Simulação realizada com sucesso.');
    }

    public function apply(Request $request, DissidioRound $dissidioRound): RedirectResponse
    {
        $this->service->apply($dissidioRound, $request->user());

        return back()->with('success', 'Dissídio aplicado com sucesso.');
    }
}
