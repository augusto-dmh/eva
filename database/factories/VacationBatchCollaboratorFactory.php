<?php

namespace Database\Factories;

use App\Enums\VacationCollaboratorStatus;
use App\Models\Collaborator;
use App\Models\VacationBatch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacationBatchCollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vacation_batch_id' => VacationBatch::factory(),
            'collaborator_id' => Collaborator::factory(),
            'data_admissao' => Carbon::now()->subMonths(14)->toDateString(),
            'periodo_aquisitivo_inicio' => Carbon::now()->subMonths(14)->toDateString(),
            'periodo_aquisitivo_fim' => Carbon::now()->subMonths(2)->toDateString(),
            'meses_acumulados' => 14,
            'elegivel' => true,
            'valor_ferias' => 3000.00,
            'valor_terco_constitucional' => 1000.00,
            'status' => VacationCollaboratorStatus::Pendente,
            'aviso_enviado' => false,
            'aviso_assinado' => false,
        ];
    }

    public function eligible(): static
    {
        return $this->state(['elegivel' => true, 'valor_ferias' => 3000.00, 'valor_terco_constitucional' => 1000.00]);
    }

    public function ineligible(): static
    {
        return $this->state(['elegivel' => false, 'meses_acumulados' => 3, 'valor_ferias' => null, 'valor_terco_constitucional' => null]);
    }
}
