<?php

namespace Database\Factories;

use App\Models\AdmissionChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdmissionChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_checklist_id' => AdmissionChecklist::factory(),
            'descricao' => $this->faker->sentence(3),
            'obrigatorio' => true,
            'confirmado' => false,
            'ordem' => 1,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(['confirmado' => true, 'confirmado_em' => now()]);
    }

    public function unconfirmed(): static
    {
        return $this->state(['confirmado' => false]);
    }

    public function optional(): static
    {
        return $this->state(['obrigatorio' => false]);
    }
}
