<?php

namespace App\Jobs;

use App\Enums\CollaboratorStatus;
use App\Enums\VacationCollaboratorStatus;
use App\Models\Collaborator;
use App\Models\VacationBatch;
use App\Models\VacationBatchCollaborator;
use App\Services\Payroll\VacationEligibilityService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateVacationEligibilityBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public VacationBatch $batch) {}

    public function handle(VacationEligibilityService $service): void
    {
        $referenceDate = Carbon::createFromFormat('Y-m', $this->batch->mes_referencia)->startOfMonth();
        $tipo = $this->batch->tipo->value;

        $collaborators = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('tipo_contrato', $tipo)
            ->get();

        foreach ($collaborators as $collaborator) {
            $eligibility = $service->computeEligibility($collaborator, $referenceDate);
            $pay = $eligibility['elegivel']
                ? $service->computeVacationPay($collaborator)
                : ['valor_ferias' => null, 'valor_terco_constitucional' => null];

            VacationBatchCollaborator::updateOrCreate(
                [
                    'vacation_batch_id' => $this->batch->id,
                    'collaborator_id' => $collaborator->id,
                ],
                [
                    'data_admissao' => $collaborator->data_admissao,
                    'periodo_aquisitivo_inicio' => $eligibility['periodo_aquisitivo_inicio'],
                    'periodo_aquisitivo_fim' => $eligibility['periodo_aquisitivo_fim'],
                    'meses_acumulados' => $eligibility['meses_acumulados'],
                    'elegivel' => $eligibility['elegivel'],
                    'valor_ferias' => $pay['valor_ferias'],
                    'valor_terco_constitucional' => $pay['valor_terco_constitucional'],
                    'status' => VacationCollaboratorStatus::Pendente,
                ]
            );
        }
    }
}
