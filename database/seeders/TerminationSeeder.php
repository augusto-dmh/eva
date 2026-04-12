<?php

namespace Database\Seeders;

use App\Enums\TerminationStatus;
use App\Enums\TerminationType;
use App\Models\Collaborator;
use App\Models\TerminationRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class TerminationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->first();

        $desligados = Collaborator::where('status', 'desligado')->get();

        $types = [
            TerminationType::PedidoDemissao,
            TerminationType::DispensaSemJustaCausa,
            TerminationType::MutuoAcordo,
            TerminationType::TerminoContrato,
        ];

        $statuses = [
            TerminationStatus::Concluido,
            TerminationStatus::Concluido,
            TerminationStatus::Concluido,
            TerminationStatus::DocumentacaoEnviada,
            TerminationStatus::PreviaConferida,
        ];

        foreach ($desligados as $collab) {
            $salario         = (float) ($collab->salario_base ?? 3000);
            $diasProp        = rand(5, 28);
            $salarioProp     = round($salario / 30 * $diasProp, 2);
            $feriasProp      = round($salario / 12 * rand(1, 11), 2);
            $terce           = round($feriasProp / 3, 2);
            $decimoTerceiro  = round($salario / 12 * rand(1, 11), 2);
            $tipo            = fake()->randomElement($types);
            $multa           = $tipo === TerminationType::DispensaSemJustaCausa
                ? round($salario * 0.40, 2) : 0;
            $total           = $salarioProp + $feriasProp + $terce + $decimoTerceiro + $multa;

            TerminationRecord::create([
                'collaborator_id'              => $collab->id,
                'tipo_desligamento'            => $tipo,
                'data_comunicacao'             => $collab->data_desligamento
                    ? \Carbon\Carbon::parse($collab->data_desligamento)->subDays(30)->toDateString()
                    : now()->subDays(60)->toDateString(),
                'data_efetivacao'              => $collab->data_desligamento ?? now()->subDays(30)->toDateString(),
                'status'                       => fake()->randomElement($statuses),
                'salario_proporcional_dias'    => $diasProp,
                'salario_proporcional_valor'   => $salarioProp,
                'ferias_proporcionais_valor'   => $feriasProp,
                'terco_ferias_proporcionais'   => $terce,
                'decimo_terceiro_proporcional' => $decimoTerceiro,
                'multa_fgts'                   => $multa,
                'aviso_previo_valor'           => 0,
                'indenizacao_rescisoria'       => 0,
                'valor_total_rescisao'         => $total,
                'ajuste_flash_valor'           => round(fake()->randomFloat(2, 0, 500), 2),
                'processado_por_id'            => $admin?->id,
            ]);
        }
    }
}
