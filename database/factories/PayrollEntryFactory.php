<?php

namespace Database\Factories;

use App\Enums\ContractType;
use App\Enums\PayrollEntryStatus;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\PayrollCycle;
use App\Models\PayrollEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollEntry>
 */
class PayrollEntryFactory extends Factory
{
    public function definition(): array
    {
        $salario = fake()->randomFloat(2, 2000, 15000);

        return [
            'payroll_cycle_id' => PayrollCycle::factory(),
            'collaborator_id' => Collaborator::factory(),
            'tipo_contrato' => ContractType::Clt->value,
            'legal_entity_id' => LegalEntity::factory(),
            'salario_bruto' => $salario,
            'salario_proporcional' => false,
            'dias_trabalhados' => null,
            'dias_uteis_mes' => null,
            'valor_comissao_bruta' => 0,
            'valor_dsr' => 0,
            'valor_comissao_total' => 0,
            'desconto_inss' => fake()->randomFloat(2, 100, 800),
            'desconto_irrf' => fake()->randomFloat(2, 0, 500),
            'desconto_contribuicao_assistencial' => 0,
            'desconto_petlove' => 0,
            'desconto_outros' => 0,
            'descricao_desconto_outros' => null,
            'bonificacoes' => 0,
            'descricao_bonificacoes' => null,
            'valor_liquido' => fake()->randomFloat(2, 1500, 12000),
            'valor_fgts' => fake()->randomFloat(2, 100, 1000),
            'valor_inss_patronal' => fake()->randomFloat(2, 100, 1000),
            'valor_nota_fiscal_pj' => null,
            'status' => PayrollEntryStatus::Pendente,
            'observacoes' => null,
        ];
    }
}
