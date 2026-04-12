<?php

namespace App\Http\Controllers;

use App\Enums\VacationBatchStatus;
use App\Enums\VacationBatchType;
use App\Exceptions\InvalidTransitionException;
use App\Http\Requests\StoreVacationBatchRequest;
use App\Models\VacationBatch;
use App\Services\VacationBatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VacationBatchController extends Controller
{
    public function __construct(private VacationBatchService $service) {}

    public function index(): Response
    {
        $batches = VacationBatch::withCount('collaborators')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('vacation-batches/Index', [
            'batches' => $batches,
        ]);
    }

    public function store(StoreVacationBatchRequest $request): RedirectResponse
    {
        $tipo = VacationBatchType::from($request->tipo);

        $batch = VacationBatch::create([
            'mes_referencia' => $request->mes_referencia,
            'tipo' => $tipo,
            'periodo_aquisitivo_minimo_meses' => $tipo->periodoAquisitivoMeses(),
            'dias_ferias' => $tipo->diasFerias(),
            'status' => VacationBatchStatus::Rascunho,
            'data_abertura' => now(),
            'observacoes' => $request->observacoes,
            'criado_por_id' => $request->user()->id,
        ]);

        return redirect()->route('vacation-batches.show', $batch)
            ->with('success', 'Lote de férias criado com sucesso.');
    }

    public function show(VacationBatch $vacationBatch): Response
    {
        $vacationBatch->load(['collaborators.collaborator', 'criadoPor']);

        $eligible = $vacationBatch->collaborators->where('elegivel', true)->values();
        $ineligible = $vacationBatch->collaborators->where('elegivel', false)->values();

        return Inertia::render('vacation-batches/Show', [
            'batch' => $vacationBatch,
            'eligible' => $eligible,
            'ineligible' => $ineligible,
            'allStatuses' => array_map(
                fn ($s) => ['value' => $s->value, 'label' => $s->label()],
                VacationBatchStatus::cases()
            ),
        ]);
    }

    public function update(Request $request, VacationBatch $vacationBatch): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        try {
            $to = VacationBatchStatus::from($request->status);
            $this->service->transition($vacationBatch, $to);
        } catch (InvalidTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        } catch (\ValueError $e) {
            return back()->withErrors(['status' => 'Status inválido.']);
        }

        return back()->with('success', 'Status atualizado com sucesso.');
    }
}
