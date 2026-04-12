<?php

namespace Tests\Feature\PjInvoice;

use App\Enums\PayrollCycleStatus;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\PayrollCycle;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('PjInvoice upload', function () {
    beforeEach(function () {
        Storage::fake('private');
    });

    it('PJ collaborator can upload a valid PDF invoice', function () {
        $user = User::factory()->create();
        $entity = LegalEntity::factory()->create();
        Collaborator::factory()->pj()->create([
            'user_id' => $user->id,
            'legal_entity_id' => $entity->id,
        ]);

        PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::AguardandoNfPj,
        ]);

        $file = UploadedFile::fake()->create('nota-fiscal.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post('/self-service/invoices', [
                'arquivo' => $file,
                'numero_nota' => 'NF-001',
                'valor' => '5000.00',
                'data_emissao' => '2026-04-01',
                'cnpj_emissor' => '12.345.678/0001-90',
                'cnpj_destinatario' => '98.765.432/0001-10',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pj_invoices', [
            'numero_nota' => 'NF-001',
            'uploaded_by_id' => $user->id,
        ]);
    });

    it('non-PJ collaborator gets 403', function () {
        $user = User::factory()->create();
        $entity = LegalEntity::factory()->create();
        Collaborator::factory()->clt()->create([
            'user_id' => $user->id,
            'legal_entity_id' => $entity->id,
        ]);

        PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::AguardandoNfPj,
        ]);

        $file = UploadedFile::fake()->create('nota-fiscal.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post('/self-service/invoices', [
                'arquivo' => $file,
                'numero_nota' => 'NF-001',
                'valor' => '5000.00',
                'data_emissao' => '2026-04-01',
                'cnpj_emissor' => '12.345.678/0001-90',
                'cnpj_destinatario' => '98.765.432/0001-10',
            ])
            ->assertForbidden();
    });

    it('rejects file larger than 10MB', function () {
        $user = User::factory()->create();
        $entity = LegalEntity::factory()->create();
        Collaborator::factory()->pj()->create([
            'user_id' => $user->id,
            'legal_entity_id' => $entity->id,
        ]);

        PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::AguardandoNfPj,
        ]);

        $file = UploadedFile::fake()->create('grande.pdf', 11000, 'application/pdf');

        $this->actingAs($user)
            ->post('/self-service/invoices', [
                'arquivo' => $file,
                'numero_nota' => 'NF-001',
                'valor' => '5000.00',
                'data_emissao' => '2026-04-01',
                'cnpj_emissor' => '12.345.678/0001-90',
                'cnpj_destinatario' => '98.765.432/0001-10',
            ])
            ->assertSessionHasErrors('arquivo');
    });

    it('rejects non-PDF file', function () {
        $user = User::factory()->create();
        $entity = LegalEntity::factory()->create();
        Collaborator::factory()->pj()->create([
            'user_id' => $user->id,
            'legal_entity_id' => $entity->id,
        ]);

        PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::AguardandoNfPj,
        ]);

        $file = UploadedFile::fake()->create('nota.jpg', 100, 'image/jpeg');

        $this->actingAs($user)
            ->post('/self-service/invoices', [
                'arquivo' => $file,
                'numero_nota' => 'NF-001',
                'valor' => '5000.00',
                'data_emissao' => '2026-04-01',
                'cnpj_emissor' => '12.345.678/0001-90',
                'cnpj_destinatario' => '98.765.432/0001-10',
            ])
            ->assertSessionHasErrors('arquivo');
    });

    it('returns 404 when no active cycle is in aguardando_nf_pj status', function () {
        $user = User::factory()->create();
        $entity = LegalEntity::factory()->create();
        Collaborator::factory()->pj()->create([
            'user_id' => $user->id,
            'legal_entity_id' => $entity->id,
        ]);

        // No cycle exists in AguardandoNfPj status
        PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::Aberto,
        ]);

        $file = UploadedFile::fake()->create('nota-fiscal.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post('/self-service/invoices', [
                'arquivo' => $file,
                'numero_nota' => 'NF-001',
                'valor' => '5000.00',
                'data_emissao' => '2026-04-01',
                'cnpj_emissor' => '12.345.678/0001-90',
                'cnpj_destinatario' => '98.765.432/0001-10',
            ])
            ->assertNotFound();
    });

    it('redirects guest to login', function () {
        $this->post('/self-service/invoices', [])->assertRedirect('/login');
    });
});
