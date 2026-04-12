<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;
use Illuminate\Database\Seeder;

class CollaboratorSeeder extends Seeder
{
    public function run(): void
    {
        $entities = LegalEntity::all();
        $entityIds = $entities->pluck('id')->toArray();

        $holding = $entities->firstWhere('apelido', 'holding');
        $educacao = $entities->firstWhere('apelido', 'educacao');
        $consultoria = $entities->firstWhere('apelido', 'consultoria');
        $gestora = $entities->firstWhere('apelido', 'gestora');
        $corretora = $entities->firstWhere('apelido', 'corretora');

        // Fallbacks in case apelidos differ
        $holdingId = $holding?->id ?? $entities->first()->id;
        $educacaoId = $educacao?->id ?? $entities->skip(1)->first()->id ?? $holdingId;
        $consultoriaId = $consultoria?->id ?? $entities->skip(2)->first()->id ?? $holdingId;
        $gestoraId = $gestora?->id ?? $entities->skip(3)->first()->id ?? $holdingId;
        $corretoraId = $corretora?->id ?? $entities->last()->id ?? $holdingId;

        // ── Named test accounts ──
        $collabUser = User::where('email', 'colaborador@clubedovalor.com.br')->first();
        Collaborator::factory()->clt()->create([
            'nome_completo' => 'Colaborador Teste',
            'email_corporativo' => 'colaborador@clubedovalor.com.br',
            'user_id' => $collabUser?->id,
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Tecnologia',
            'cargo' => 'Desenvolvedor',
            'data_admissao' => '2024-03-01',
        ]);

        $adminUser = User::where('email', 'admin@clubedovalor.com.br')->first();
        Collaborator::factory()->clt()->create([
            'nome_completo' => 'Admin DP',
            'email_corporativo' => 'admin.dp@clubedovalor.com.br',
            'user_id' => $adminUser?->id,
            'legal_entity_id' => $holdingId,
            'departamento' => 'Financeiro',
            'cargo' => 'Analista de DP',
            'data_admissao' => '2023-01-15',
        ]);

        // ── CLT — Comercial (Consultoria) — includes Closers ──
        Collaborator::factory(18)->clt()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Comercial',
        ]);
        Collaborator::factory(20)->clt()->closer()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Comercial',
            'cargo' => 'Closer',
        ]);
        Collaborator::factory(8)->clt()->create([
            'legal_entity_id' => $corretoraId,
            'departamento' => 'Comercial',
        ]);

        // ── CLT — Tecnologia ──
        Collaborator::factory(12)->clt()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Tecnologia',
        ]);
        Collaborator::factory(8)->clt()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'Tecnologia',
        ]);
        Collaborator::factory(5)->clt()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Tecnologia',
        ]);

        // ── CLT — Educação ──
        Collaborator::factory(15)->clt()->create([
            'legal_entity_id' => $educacaoId,
            'departamento' => 'Educação',
        ]);
        Collaborator::factory(6)->clt()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Educação',
        ]);

        // ── CLT — Financeiro ──
        Collaborator::factory(10)->clt()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'Financeiro',
        ]);
        Collaborator::factory(6)->clt()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Financeiro',
        ]);

        // ── CLT — RH ──
        Collaborator::factory(8)->clt()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'RH',
        ]);

        // ── CLT — Marketing ──
        Collaborator::factory(10)->clt()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Marketing',
        ]);
        Collaborator::factory(4)->clt()->create([
            'legal_entity_id' => $educacaoId,
            'departamento' => 'Marketing',
        ]);

        // ── CLT — Operações ──
        Collaborator::factory(8)->clt()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Operações',
        ]);
        Collaborator::factory(5)->clt()->create([
            'legal_entity_id' => $corretoraId,
            'departamento' => 'Operações',
        ]);

        // ── CLT — Gestão ──
        Collaborator::factory(6)->clt()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'Gestão',
        ]);

        // ── CLT — Produtos ──
        Collaborator::factory(7)->clt()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Produtos',
        ]);

        // ── CLT — Jurídico ──
        Collaborator::factory(5)->clt()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'Jurídico',
        ]);

        // ── CLT — Afastados ──
        Collaborator::factory(8)->clt()->state([
            'status' => CollaboratorStatus::Afastado,
        ])->create([
            'legal_entity_id' => fn () => fake()->randomElement($entityIds),
        ]);

        // ── CLT — Desligados ──
        Collaborator::factory(10)->clt()->terminated()->create([
            'legal_entity_id' => fn () => fake()->randomElement($entityIds),
        ]);

        // ── PJ Contractors ──
        Collaborator::factory(15)->pj()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Tecnologia',
        ]);
        Collaborator::factory(10)->pj()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Financeiro',
        ]);
        Collaborator::factory(8)->pj()->create([
            'legal_entity_id' => $educacaoId,
            'departamento' => 'Educação',
        ]);
        Collaborator::factory(7)->pj()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Marketing',
        ]);
        Collaborator::factory(6)->pj()->create([
            'legal_entity_id' => $corretoraId,
            'departamento' => 'Comercial',
        ]);
        Collaborator::factory(6)->pj()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'Jurídico',
        ]);

        // ── Estagiários ──
        Collaborator::factory(8)->estagiario()->create([
            'legal_entity_id' => $educacaoId,
            'departamento' => 'Educação',
            'data_admissao' => fn () => fake()->dateTimeBetween('-8 months', '-1 month'),
        ]);
        Collaborator::factory(7)->estagiario()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Tecnologia',
            'data_admissao' => fn () => fake()->dateTimeBetween('-8 months', '-1 month'),
        ]);
        Collaborator::factory(5)->estagiario()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Marketing',
            'data_admissao' => fn () => fake()->dateTimeBetween('-8 months', '-1 month'),
        ]);
        Collaborator::factory(4)->estagiario()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'RH',
            'data_admissao' => fn () => fake()->dateTimeBetween('-8 months', '-1 month'),
        ]);
        Collaborator::factory(4)->estagiario()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Financeiro',
            'data_admissao' => fn () => fake()->dateTimeBetween('-8 months', '-1 month'),
        ]);

        // ── Sócios — Advisors ──
        Collaborator::factory(5)->socio()->advisor()->create([
            'legal_entity_id' => $consultoriaId,
            'departamento' => 'Comercial',
            'trilha_carreira' => 'Advisor',
        ]);
        Collaborator::factory(3)->socio()->advisor()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Gestão',
            'trilha_carreira' => 'Advisor',
        ]);

        // ── Sócios — Gestão ──
        Collaborator::factory(4)->socio()->create([
            'legal_entity_id' => $holdingId,
            'departamento' => 'Gestão',
        ]);
        Collaborator::factory(2)->socio()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Gestão',
        ]);

        // ── Extra CLT — Comercial Corretora ──
        Collaborator::factory(10)->clt()->create([
            'legal_entity_id' => $corretoraId,
            'departamento' => 'Comercial',
        ]);

        // ── Extra PJ — Gestora ──
        Collaborator::factory(8)->pj()->create([
            'legal_entity_id' => $gestoraId,
            'departamento' => 'Produtos',
        ]);

        // ── Recent admissions this month (for stats) ──
        Collaborator::factory(6)->clt()->create([
            'legal_entity_id' => fn () => fake()->randomElement($entityIds),
            'data_admissao' => now()->startOfMonth()->addDays(fake()->numberBetween(0, 10)),
        ]);
        Collaborator::factory(3)->pj()->create([
            'legal_entity_id' => fn () => fake()->randomElement($entityIds),
            'data_admissao' => now()->startOfMonth()->addDays(fake()->numberBetween(0, 10)),
        ]);
    }
}
