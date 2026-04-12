<?php

namespace Database\Seeders;

use App\Models\LegalEntity;
use Illuminate\Database\Seeder;

class LegalEntitySeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            [
                'nome' => 'Clube do Valor Holding Ltda',
                'apelido' => 'holding',
                'cnpj' => '12.345.678/0001-00',
                'sindicato_patronal' => 'SESCON',
                'sindicato_trabalhadores' => 'SEMAPI',
            ],
            [
                'nome' => 'Clube do Valor Educação Ltda',
                'apelido' => 'educacao',
                'cnpj' => '23.456.789/0001-11',
                'sindicato_patronal' => 'SESCON',
                'sindicato_trabalhadores' => 'SEMAPI',
            ],
            [
                'nome' => 'Clube do Valor Consultoria Ltda',
                'apelido' => 'consultoria',
                'cnpj' => '34.567.890/0001-22',
                'sindicato_patronal' => 'SESCON',
                'sindicato_trabalhadores' => 'SEMAPI',
            ],
            [
                'nome' => 'Clube do Valor Gestora de Recursos Ltda',
                'apelido' => 'gestora',
                'cnpj' => '45.678.901/0001-33',
                'sindicato_patronal' => 'SINDAESP',
                'sindicato_trabalhadores' => 'FEBRAD',
            ],
            [
                'nome' => 'Clube do Valor Corretora de Seguros Ltda',
                'apelido' => 'corretora',
                'cnpj' => '56.789.012/0001-44',
                'sindicato_patronal' => 'CONSIF',
                'sindicato_trabalhadores' => 'Securitários',
            ],
        ];

        foreach ($entities as $entity) {
            LegalEntity::create($entity);
        }
    }
}
