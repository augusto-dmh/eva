<?php

namespace Database\Factories;

use App\Enums\ChecklistStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdmissionChecklistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaborator_id' => Collaborator::factory(),
            'tipo_contrato' => ContractType::Clt,
            'status' => ChecklistStatus::Pendente,
            'data_limite' => Carbon::now()->addDays(30),
        ];
    }

    public function pendente(): static
    {
        return $this->state(['status' => ChecklistStatus::Pendente]);
    }

    public function emAndamento(): static
    {
        return $this->state(['status' => ChecklistStatus::EmAndamento]);
    }

    public function completo(): static
    {
        return $this->state([
            'status' => ChecklistStatus::Completo,
            'completado_em' => now(),
        ]);
    }
}
