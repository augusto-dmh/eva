<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThirteenthSalaryRequest;
use App\Models\ThirteenthSalaryRound;
use App\Services\Payroll\ThirteenthSalaryCalculationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ThirteenthSalaryController extends Controller
{
    public function __construct(private ThirteenthSalaryCalculationService $service) {}

    public function index(): Response
    {
        $rounds = ThirteenthSalaryRound::with('criadoPor')
            ->orderByDesc('ano_referencia')
            ->get();

        return inertia('thirteenth-salary/Index', ['rounds' => $rounds]);
    }

    public function create(): Response
    {
        return inertia('thirteenth-salary/Create');
    }

    public function store(StoreThirteenthSalaryRequest $request): RedirectResponse
    {
        $round = ThirteenthSalaryRound::create([
            ...$request->validated(),
            'status' => 'aberto',
            'criado_por_id' => $request->user()->id,
        ]);

        return redirect()->route('thirteenth-salary.show', $round)
            ->with('success', 'Décimo terceiro criado com sucesso.');
    }

    public function show(ThirteenthSalaryRound $thirteenthSalaryRound): Response
    {
        $thirteenthSalaryRound->load(['criadoPor', 'entries.collaborator']);

        return inertia('thirteenth-salary/Show', ['round' => $thirteenthSalaryRound]);
    }

    public function simulate(ThirteenthSalaryRound $thirteenthSalaryRound): RedirectResponse
    {
        $this->service->simulate($thirteenthSalaryRound);

        return back()->with('success', 'Simulação realizada com sucesso.');
    }
}
