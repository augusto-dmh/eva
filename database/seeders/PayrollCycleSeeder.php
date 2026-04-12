<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\PayrollCycleStatus;
use App\Enums\PayrollEntryStatus;
use App\Models\Collaborator;
use App\Models\PayrollCycle;
use App\Models\PayrollEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayrollCycleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->firstOrFail();

        // Cycle 1 — Janeiro 2025 (Fechado)
        $cycle1 = PayrollCycle::create([
            'mes_referencia' => '2025-01',
            'ano' => 2025,
            'mes' => 1,
            'status' => PayrollCycleStatus::Fechado,
            'data_abertura' => '2025-01-01',
            'data_fechamento' => '2025-01-31',
            'data_pagamento_folha' => '2025-01-31',
            'salarios_brutos' => 185000.00,
            'comissoes' => 45000.00,
            'deducoes' => 32000.00,
            'liquido' => 198000.00,
            'pj' => 65000.00,
            'fechado_por_id' => $admin->id,
        ]);

        // Cycle 2 — Fevereiro 2025 (ConferidoContabilidade)
        PayrollCycle::create([
            'mes_referencia' => '2025-02',
            'ano' => 2025,
            'mes' => 2,
            'status' => PayrollCycleStatus::ConferidoContabilidade,
            'data_abertura' => '2025-02-01',
            'data_fechamento' => null,
            'data_pagamento_folha' => '2025-02-28',
            'salarios_brutos' => 185000.00,
            'comissoes' => 38000.00,
            'deducoes' => 31000.00,
            'liquido' => 192000.00,
            'pj' => 60000.00,
            'fechado_por_id' => $admin->id,
        ]);

        // Cycle 3 — Março 2025 (Aberto)
        PayrollCycle::create([
            'mes_referencia' => '2025-03',
            'ano' => 2025,
            'mes' => 3,
            'status' => PayrollCycleStatus::Aberto,
            'data_abertura' => '2025-03-01',
            'fechado_por_id' => $admin->id,
        ]);

        // Entries for the closed cycle — first 3 CLT collaborators
        $cltCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->limit(3)
            ->get();

        foreach ($cltCollaborators as $collaborator) {
            $salarioBruto = (float) $collaborator->salario_base;
            $descontoInss = round($salarioBruto * 0.08, 2);
            $valorLiquido = round($salarioBruto - $descontoInss, 2);
            $valorFgts = round($salarioBruto * 0.08, 2);

            PayrollEntry::create([
                'payroll_cycle_id' => $cycle1->id,
                'collaborator_id' => $collaborator->id,
                'tipo_contrato' => ContractType::Clt->value,
                'legal_entity_id' => $collaborator->legal_entity_id,
                'salario_bruto' => $salarioBruto,
                'dias_trabalhados' => 22,
                'dias_uteis_mes' => 22,
                'desconto_inss' => $descontoInss,
                'valor_liquido' => $valorLiquido,
                'valor_fgts' => $valorFgts,
                'status' => PayrollEntryStatus::Aprovado,
            ]);
        }
    }
}
