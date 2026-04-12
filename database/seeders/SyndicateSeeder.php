<?php

namespace Database\Seeders;

use App\Enums\SyndicateType;
use App\Models\LegalEntity;
use App\Models\Syndicate;
use App\Models\SyndicateBinding;
use Illuminate\Database\Seeder;

class SyndicateSeeder extends Seeder
{
    public function run(): void
    {
        $patronal1 = Syndicate::create([
            'nome' => 'SIESPM - Sindicato das Empresas de Serviços e Profissionais de SP',
            'tipo' => SyndicateType::Patronal,
            'uf' => 'SP',
        ]);

        $trabalhadores1 = Syndicate::create([
            'nome' => 'SEEB-SP - Sindicato dos Empregados em Estabelecimentos Bancários de SP',
            'tipo' => SyndicateType::Trabalhadores,
            'uf' => 'SP',
        ]);

        Syndicate::create([
            'nome' => 'FIESP - Federação das Indústrias do Estado de São Paulo',
            'tipo' => SyndicateType::Patronal,
            'uf' => 'SP',
        ]);

        Syndicate::create([
            'nome' => 'SETURB-SP - Sindicato dos Empregados no Setor de Turismo',
            'tipo' => SyndicateType::Trabalhadores,
            'uf' => 'SP',
        ]);

        $legalEntities = LegalEntity::all();

        foreach ($legalEntities as $entity) {
            SyndicateBinding::create([
                'legal_entity_id' => $entity->id,
                'syndicate_id' => $patronal1->id,
            ]);

            SyndicateBinding::create([
                'legal_entity_id' => $entity->id,
                'syndicate_id' => $trabalhadores1->id,
            ]);
        }
    }
}
