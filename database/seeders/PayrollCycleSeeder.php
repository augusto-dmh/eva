<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\PayrollCycleStatus;
use App\Enums\PayrollEntryStatus;
use App\Enums\PjInvoiceStatus;
use App\Models\Collaborator;
use App\Models\PayrollCycle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollCycleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->first();

        // ── Past 15 closed cycles (Jan 2025 – Mar 2026) ──
        $closedMonths = [];
        for ($y = 2025; $y <= 2026; $y++) {
            $maxMonth = ($y === 2026) ? 3 : 12;
            for ($m = 1; $m <= $maxMonth; $m++) {
                $closedMonths[] = [$y, $m];
            }
        }

        foreach ($closedMonths as [$year, $month]) {
            $salariosBrutos = fake()->randomFloat(2, 1_300_000, 1_600_000);
            $comissoes      = fake()->randomFloat(2, 45_000, 95_000);
            $pj             = fake()->randomFloat(2, 380_000, 520_000);
            $deducoes       = round($salariosBrutos * fake()->randomFloat(4, 0.14, 0.18), 2);
            $liquido        = round($salariosBrutos - $deducoes + $comissoes, 2);
            $fechamento     = \Carbon\Carbon::create($year, $month, 28);

            PayrollCycle::create([
                'mes_referencia'           => sprintf('%04d-%02d', $year, $month),
                'ano'                      => $year,
                'mes'                      => $month,
                'status'                   => PayrollCycleStatus::Fechado,
                'data_abertura'            => \Carbon\Carbon::create($year, $month, 1),
                'data_fechamento'          => $fechamento,
                'data_pagamento_folha'     => \Carbon\Carbon::create($year, $month, 5)->addMonth(),
                'data_pagamento_comissao'  => \Carbon\Carbon::create($year, $month, 10)->addMonth(),
                'salarios_brutos'          => $salariosBrutos,
                'comissoes'                => $comissoes,
                'deducoes'                 => $deducoes,
                'liquido'                  => $liquido,
                'pj'                       => $pj,
                'observacoes'              => null,
            ]);
        }

        // ── April 2026: open current cycle with real entries ──
        $currentCycle = PayrollCycle::create([
            'mes_referencia'  => '2026-04',
            'ano'             => 2026,
            'mes'             => 4,
            'status'          => PayrollCycleStatus::Aberto,
            'data_abertura'   => now()->startOfMonth(),
            'data_fechamento' => null,
            'salarios_brutos' => 0,
            'comissoes'       => 0,
            'deducoes'        => 0,
            'liquido'         => 0,
            'pj'              => 0,
        ]);

        // ── Generate PayrollEntries for all active CLT / Estagiário / Sócio ──
        $cltCollaborators = Collaborator::with('legalEntity')
            ->whereIn('tipo_contrato', [ContractType::Clt, ContractType::Estagiario, ContractType::Socio])
            ->where('status', CollaboratorStatus::Ativo)
            ->get();

        $entries    = [];
        $totalBruto = 0;
        $totalDeducoes = 0;
        $totalLiquido  = 0;
        $now = now();

        foreach ($cltCollaborators as $collab) {
            $salario  = (float) ($collab->salario_base ?? fake()->randomFloat(2, 1_800, 15_000));
            $inss     = round($salario * 0.075, 2);
            $irrf     = $salario > 4_664 ? round(($salario - 4_664) * 0.15, 2) : 0;
            $deducoes = $inss + $irrf;
            $liquido  = round($salario - $deducoes, 2);
            $fgts     = round($salario * 0.08, 2);
            $inssPatr = round($salario * 0.20, 2);

            $totalBruto    += $salario;
            $totalDeducoes += $deducoes;
            $totalLiquido  += $liquido;

            $entries[] = [
                'payroll_cycle_id'                 => $currentCycle->id,
                'collaborator_id'                  => $collab->id,
                'tipo_contrato'                    => $collab->tipo_contrato->value,
                'legal_entity_id'                  => $collab->legal_entity_id,
                'salario_bruto'                    => $salario,
                'salario_proporcional'             => false,
                'dias_trabalhados'                 => null,
                'dias_uteis_mes'                   => null,
                'valor_comissao_bruta'             => 0,
                'valor_dsr'                        => 0,
                'valor_comissao_total'             => 0,
                'desconto_inss'                    => $inss,
                'desconto_irrf'                    => $irrf,
                'desconto_contribuicao_assistencial' => 0,
                'desconto_petlove'                 => 0,
                'desconto_outros'                  => 0,
                'descricao_desconto_outros'        => null,
                'bonificacoes'                     => 0,
                'descricao_bonificacoes'           => null,
                'valor_liquido'                    => $liquido,
                'valor_fgts'                       => $fgts,
                'valor_inss_patronal'              => $inssPatr,
                'valor_nota_fiscal_pj'             => null,
                'status'                           => PayrollEntryStatus::Pendente->value,
                'observacoes'                      => null,
                'created_at'                       => $now,
                'updated_at'                       => $now,
            ];
        }

        // Chunk inserts for performance
        foreach (array_chunk($entries, 100) as $chunk) {
            DB::table('payroll_entries')->insert($chunk);
        }

        // ── PJ invoices for current cycle ──
        $pjCollaborators = Collaborator::where('tipo_contrato', ContractType::Pj)
            ->where('status', CollaboratorStatus::Ativo)
            ->get();

        $invoices     = [];
        $totalPj      = 0;
        $statuses     = [PjInvoiceStatus::Pendente, PjInvoiceStatus::Recebida, PjInvoiceStatus::Aprovada];

        foreach ($pjCollaborators as $pjCollab) {
            $valor = (float) ($pjCollab->salario_base ?? fake()->randomFloat(2, 4_000, 25_000));
            $totalPj += $valor;
            $status   = fake()->randomElement($statuses);

            $invoices[] = [
                'payroll_entry_id'      => null,
                'collaborator_id'       => $pjCollab->id,
                'payroll_cycle_id'      => $currentCycle->id,
                'numero_nota'           => 'NF-' . fake()->numerify('####'),
                'valor'                 => $valor,
                'arquivo_path'          => 'pj-invoices/2026-04/' . fake()->uuid() . '.pdf',
                'arquivo_nome_original' => 'nota-fiscal-' . fake()->numerify('####') . '.pdf',
                'data_upload'           => now(),
                'data_emissao'          => now()->subDays(rand(1, 10))->toDateString(),
                'cnpj_emissor'          => fake('pt_BR')->cnpj(false),
                'cnpj_destinatario'     => fake('pt_BR')->cnpj(false),
                'status'                => $status->value,
                'observacoes'           => null,
                'uploaded_by_id'        => $admin?->id,
                'revisado_por_id'       => null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
        }

        foreach (array_chunk($invoices, 100) as $chunk) {
            DB::table('pj_invoices')->insert($chunk);
        }

        // ── Update current cycle aggregates ──
        $currentCycle->update([
            'salarios_brutos' => round($totalBruto, 2),
            'deducoes'        => round($totalDeducoes, 2),
            'liquido'         => round($totalLiquido, 2),
            'pj'              => round($totalPj, 2),
        ]);
    }
}
