<?php

namespace App\Http\Controllers;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\PayrollCycleStatus;
use App\Enums\PjInvoiceStatus;
use App\Enums\VacationBatchStatus;
use App\Models\Collaborator;
use App\Models\DissidioRound;
use App\Models\PayrollCycle;
use App\Models\PlrRound;
use App\Models\ProfessionalHistoryEntry;
use App\Models\ThirteenthSalaryRound;
use App\Models\VacationBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();

        // ── Collaborator stats ──────────────────────────────────────────────
        $collabs = Collaborator::where('status', CollaboratorStatus::Ativo)->get();

        $collaboratorStats = [
            'total' => $collabs->count(),
            'clt' => $collabs->where('tipo_contrato', ContractType::Clt)->count(),
            'pj' => $collabs->where('tipo_contrato', ContractType::Pj)->count(),
            'estagiario' => $collabs->where('tipo_contrato', ContractType::Estagiario)->count(),
            'socio' => $collabs->where('tipo_contrato', ContractType::Socio)->count(),
            'novos_mes' => Collaborator::where('data_admissao', '>=', $now->copy()->startOfMonth()->toDateString())->count(),
        ];

        // ── Current payroll cycle ───────────────────────────────────────────
        $currentCycle = PayrollCycle::whereNotIn('status', [PayrollCycleStatus::Fechado])
            ->orderByDesc('ano')
            ->orderByDesc('mes')
            ->first();

        $pendingPjInvoices = $currentCycle
            ? DB::table('pj_invoices')
                ->where('payroll_cycle_id', $currentCycle->id)
                ->where('status', PjInvoiceStatus::Pendente->value)
                ->count()
            : 0;

        $payrollSummary = $currentCycle ? [
            'mes_referencia' => $currentCycle->mes_referencia,
            'status' => $currentCycle->status->value,
            'status_label' => $currentCycle->status->label(),
            'liquido' => (float) $currentCycle->liquido,
            'salarios_brutos' => (float) $currentCycle->salarios_brutos,
            'pj' => (float) $currentCycle->pj,
            'id' => $currentCycle->id,
        ] : null;

        // ── Vacation batches ────────────────────────────────────────────────
        $activeVacationBatches = VacationBatch::whereNotIn('status', [VacationBatchStatus::Concluido])
            ->count();

        $nextVacationBatch = VacationBatch::where('status', VacationBatchStatus::Rascunho)
            ->orWhere('status', VacationBatchStatus::Calculado)
            ->orderBy('mes_referencia')
            ->first();

        // ── Latest dissidio ─────────────────────────────────────────────────
        $latestDissidio = DissidioRound::orderByDesc('ano_referencia')->first();
        $dissidioSummary = $latestDissidio ? [
            'ano' => $latestDissidio->ano_referencia,
            'percentual' => (float) $latestDissidio->percentual,
            'status' => $latestDissidio->status->value,
            'label' => $latestDissidio->status->label(),
        ] : null;

        // ── Current year 13th salary ────────────────────────────────────────
        $thirteenth = ThirteenthSalaryRound::where('ano_referencia', $now->year)->first();
        $thirteenthSummary = $thirteenth ? [
            'ano' => $thirteenth->ano_referencia,
            'status' => $thirteenth->status->value,
            'label' => $thirteenth->status->label(),
        ] : null;

        // ── Latest PLR round ────────────────────────────────────────────────
        $latestPlr = PlrRound::orderByDesc('ano_referencia')->first();
        $plrSummary = $latestPlr ? [
            'ano' => $latestPlr->ano_referencia,
            'status' => $latestPlr->status->value,
            'label' => $latestPlr->status->label(),
            'valor_total' => $latestPlr->valor_total_distribuido
                ? (float) $latestPlr->valor_total_distribuido
                : null,
        ] : null;

        // ── Recent professional history activity ────────────────────────────
        $recentActivity = ProfessionalHistoryEntry::with('collaborator:id,nome_completo')
            ->orderByDesc('data_efetivacao')
            ->limit(8)
            ->get()
            ->map(fn ($e) => [
                'texto' => $this->activityText($e),
                'tipo' => $this->activityType($e),
                'data' => Carbon::parse($e->data_efetivacao)->diffForHumans(),
            ]);

        return Inertia::render('Dashboard', [
            'collaboratorStats' => $collaboratorStats,
            'payrollSummary' => $payrollSummary,
            'pendingPjInvoices' => $pendingPjInvoices,
            'activeVacationBatches' => $activeVacationBatches,
            'nextVacationBatch' => $nextVacationBatch?->mes_referencia,
            'dissidioSummary' => $dissidioSummary,
            'thirteenthSummary' => $thirteenthSummary,
            'plrSummary' => $plrSummary,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function activityText(ProfessionalHistoryEntry $entry): string
    {
        $name = $entry->collaborator?->nome_completo ?? 'Colaborador';

        return match ($entry->tipo_evento?->value) {
            'reajuste_salarial' => "{$name} — reajuste salarial de R$ {$entry->valor_anterior} → R$ {$entry->valor_novo}",
            'dissidio' => "{$name} — dissídio aplicado ({$entry->valor_anterior} → R$ {$entry->valor_novo})",
            'promocao' => "{$name} — promoção registrada",
            'troca_cargo' => "{$name} — mudança de cargo",
            'troca_time' => "{$name} — mudança de time",
            default => "{$name} — {$entry->tipo_evento?->label()}",
        };
    }

    private function activityType(ProfessionalHistoryEntry $entry): string
    {
        return match ($entry->tipo_evento?->value) {
            'reajuste_salarial', 'dissidio', 'promocao' => 'success',
            'troca_cargo', 'troca_time', 'troca_entidade' => 'info',
            default => 'info',
        };
    }
}
