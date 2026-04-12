<?php

namespace Database\Seeders;

use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;
use Illuminate\Database\Seeder;

class CollaboratorSeeder extends Seeder
{
    public function run(): void
    {
        $entities = LegalEntity::all();

        // Link the test collaborator user to a collaborator record
        $collabUser = User::where('email', 'colaborador@clubedovalor.com.br')->first();

        // CLT collaborators (majority)
        Collaborator::factory()
            ->clt()
            ->create([
                'nome_completo' => 'Colaborador Teste',
                'email_corporativo' => 'colaborador@clubedovalor.com.br',
                'user_id' => $collabUser?->id,
                'legal_entity_id' => $entities->where('apelido', 'consultoria')->first()->id,
                'departamento' => 'Tecnologia',
                'cargo' => 'Desenvolvedor',
                'data_admissao' => '2024-03-01',
            ]);

        Collaborator::factory(5)->clt()->create([
            'legal_entity_id' => $entities->random()->id,
            'data_admissao' => fake()->dateTimeBetween('-3 years', '-18 months'),
        ]);

        // CLT Closer (comissioned)
        Collaborator::factory(3)->clt()->closer()->create([
            'legal_entity_id' => $entities->where('apelido', 'consultoria')->first()->id,
            'departamento' => 'Comercial',
            'data_admissao' => fake()->dateTimeBetween('-3 years', '-18 months'),
        ]);

        // Advisor (socio + commission)
        Collaborator::factory(2)->socio()->advisor()->create([
            'legal_entity_id' => $entities->where('apelido', 'consultoria')->first()->id,
            'departamento' => 'Comercial',
            'trilha_carreira' => 'Advisor',
        ]);

        // PJ contractors
        Collaborator::factory(4)->pj()->create([
            'legal_entity_id' => $entities->random()->id,
        ]);

        // Estagiários
        Collaborator::factory(3)->estagiario()->create([
            'legal_entity_id' => $entities->random()->id,
            'data_admissao' => fake()->dateTimeBetween('-8 months', '-2 months'),
        ]);

        // Sócios
        Collaborator::factory(2)->socio()->create([
            'legal_entity_id' => $entities->where('apelido', 'holding')->first()->id,
            'departamento' => 'Gestão',
        ]);

        // Terminated collaborator
        Collaborator::factory()->clt()->terminated()->create([
            'legal_entity_id' => $entities->random()->id,
        ]);

        // One more linked user for self-service testing
        $adminUser = User::where('email', 'admin@clubedovalor.com.br')->first();
        Collaborator::factory()->clt()->create([
            'nome_completo' => 'Admin DP',
            'email_corporativo' => 'admin.dp@clubedovalor.com.br',
            'user_id' => $adminUser?->id,
            'legal_entity_id' => $entities->where('apelido', 'holding')->first()->id,
            'departamento' => 'Financeiro',
            'cargo' => 'Analista de DP',
            'data_admissao' => '2023-01-15',
        ]);
    }
}
