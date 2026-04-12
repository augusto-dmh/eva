<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\DissidioRoundStatus;
use App\Enums\ProfessionalEventType;
use App\Models\Collaborator;
use App\Models\DissidioEntry;
use App\Models\DissidioRound;
use App\Models\ProfessionalHistoryEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class DissidioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->firstOrFail();

        $round = DissidioRound::create([
            'ano_referencia' => 2025,
            'data_base' => '2025-01-01',
            'data_publicacao' => '2025-02-15',
            'percentual' => 4.50,
            'aplica_estagiarios' => false,
            'status' => DissidioRoundStatus::Aplicado,
            'criado_por_id' => $admin->id,
            'aplicado_por_id' => $admin->id,
            'aplicado_em' => '2025-03-01 09:00:00',
        ]);

        $eligibleCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->get();

        foreach ($eligibleCollaborators as $collaborator) {
            $salarioNovo = (float) $collaborator->salario_base;
            $salarioAnterior = round($salarioNovo / 1.045, 2);

            DissidioEntry::create([
                'dissidio_round_id' => $round->id,
                'collaborator_id' => $collaborator->id,
                'salario_anterior' => $salarioAnterior,
                'percentual_aplicado' => 4.50,
                'salario_novo' => $salarioNovo,
                'status' => 'aplicado',
            ]);

            ProfessionalHistoryEntry::create([
                'collaborator_id' => $collaborator->id,
                'tipo_evento' => ProfessionalEventType::Dissidio,
                'data_efetivacao' => '2025-03-01',
                'campo_alterado' => 'salario_base',
                'valor_anterior' => (string) $salarioAnterior,
                'valor_novo' => (string) $salarioNovo,
                'motivo' => 'Dissídio coletivo 2025 — 4,5% conforme CCT',
                'dissidio_round_id' => $round->id,
                'registrado_por_id' => $admin->id,
            ]);
        }
    }
}
