<?php

namespace Database\Factories;

use App\Enums\PjInvoiceStatus;
use App\Models\Collaborator;
use App\Models\PayrollCycle;
use App\Models\PjInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PjInvoice>
 */
class PjInvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payroll_entry_id' => null,
            'collaborator_id' => Collaborator::factory(),
            'payroll_cycle_id' => PayrollCycle::factory(),
            'numero_nota' => 'NF-'.fake()->numerify('###'),
            'valor' => fake()->randomFloat(2, 1000, 20000),
            'arquivo_path' => 'pj-invoices/2026-01/'.fake()->uuid().'.pdf',
            'arquivo_nome_original' => 'nota-fiscal-'.fake()->numerify('###').'.pdf',
            'data_upload' => now(),
            'data_emissao' => now()->toDateString(),
            'cnpj_emissor' => fake('pt_BR')->cnpj(true),
            'cnpj_destinatario' => fake('pt_BR')->cnpj(true),
            'status' => PjInvoiceStatus::Pendente,
            'observacoes' => null,
            'uploaded_by_id' => User::factory(),
            'revisado_por_id' => null,
        ];
    }

    public function aprovada(): static
    {
        return $this->state(fn () => [
            'status' => PjInvoiceStatus::Aprovada,
        ]);
    }

    public function rejeitada(): static
    {
        return $this->state(fn () => [
            'status' => PjInvoiceStatus::Rejeitada,
        ]);
    }
}
