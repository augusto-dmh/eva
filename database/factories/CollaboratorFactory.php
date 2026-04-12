<?php

namespace Database\Factories;

use App\Enums\CollaboratorStatus;
use App\Enums\CommissionType;
use App\Enums\ContractType;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Collaborator>
 */
class CollaboratorFactory extends Factory
{
    public function definition(): array
    {
        $contractType = fake()->randomElement(ContractType::cases());
        $salary = match ($contractType) {
            ContractType::Clt => fake()->randomFloat(2, 2500, 15000),
            ContractType::Pj => fake()->randomFloat(2, 5000, 25000),
            ContractType::Estagiario => fake()->randomFloat(2, 1000, 2500),
            ContractType::Socio => 1412.00,
        };

        return [
            'nome_completo' => fake('pt_BR')->name(),
            'cpf' => fake('pt_BR')->unique()->cpf(),
            'email_corporativo' => fake()->unique()->safeEmail(),
            'email_pessoal' => fake()->optional()->safeEmail(),
            'data_nascimento' => fake()->dateTimeBetween('-50 years', '-20 years'),
            'telefone' => fake('pt_BR')->phoneNumber(),
            'tipo_contrato' => $contractType,
            'legal_entity_id' => LegalEntity::factory(),
            'departamento' => fake()->randomElement(['Comercial', 'Educação', 'Tecnologia', 'Financeiro', 'RH', 'Marketing', 'Operações', 'Gestão']),
            'cargo' => fake('pt_BR')->jobTitle(),
            'nivel' => fake()->optional()->randomElement(['Junior', 'Pleno', 'Senior', 'Especialista']),
            'trilha_carreira' => null,
            'lider_direto' => fake('pt_BR')->optional()->name(),
            'status' => CollaboratorStatus::Ativo,
            'data_admissao' => fake()->dateTimeBetween('-3 years', '-1 month'),
            'data_desligamento' => null,
            'flash_vale_alimentacao' => fake()->randomFloat(2, 200, 800),
            'flash_vale_refeicao' => fake()->randomFloat(2, 0, 500),
            'flash_vale_transporte' => fake()->randomFloat(2, 0, 300),
            'flash_saude' => fake()->randomFloat(2, 0, 200),
            'flash_cultura' => fake()->randomFloat(2, 0, 100),
            'flash_educacao' => fake()->randomFloat(2, 0, 100),
            'flash_home_office' => fake()->randomFloat(2, 0, 150),
            'flash_total' => null,
            'salario_base' => $salary,
            'tipo_comissao' => CommissionType::None,
            'minimo_garantido' => null,
            'elegivel_comissao' => false,
            'desconto_petlove' => fake()->optional(0.3)->randomFloat(2, 30, 80),
        ];
    }

    public function clt(): static
    {
        return $this->state(fn () => [
            'tipo_contrato' => ContractType::Clt,
            'salario_base' => fake()->randomFloat(2, 2500, 15000),
        ]);
    }

    public function pj(): static
    {
        return $this->state(fn () => [
            'tipo_contrato' => ContractType::Pj,
            'salario_base' => fake()->randomFloat(2, 5000, 25000),
        ]);
    }

    public function estagiario(): static
    {
        return $this->state(fn () => [
            'tipo_contrato' => ContractType::Estagiario,
            'salario_base' => fake()->randomFloat(2, 1000, 2500),
        ]);
    }

    public function socio(): static
    {
        return $this->state(fn () => [
            'tipo_contrato' => ContractType::Socio,
            'salario_base' => 1412.00,
        ]);
    }

    public function closer(): static
    {
        return $this->state(fn () => [
            'tipo_comissao' => CommissionType::Closer,
            'elegivel_comissao' => true,
        ]);
    }

    public function advisor(): static
    {
        return $this->state(fn () => [
            'tipo_comissao' => CommissionType::Advisor,
            'elegivel_comissao' => true,
            'minimo_garantido' => fake()->randomFloat(2, 5000, 15000),
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn () => [
            'status' => CollaboratorStatus::Desligado,
            'data_desligamento' => fake()->dateTimeBetween('-6 months', '-1 week'),
        ]);
    }
}
