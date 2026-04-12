<?php

use App\Enums\CollaboratorStatus;
use App\Enums\CommissionType;
use App\Enums\ContractType;
use App\Enums\FlashStatus;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;

// --- Enum Tests ---

test('ContractType has 4 cases', function () {
    expect(ContractType::cases())->toHaveCount(4);
});

test('ContractType labels are in Portuguese', function () {
    expect(ContractType::Clt->label())->toBe('CLT');
    expect(ContractType::Pj->label())->toBe('PJ');
    expect(ContractType::Estagiario->label())->toBe('Estagiário');
    expect(ContractType::Socio->label())->toBe('Sócio');
});

test('ContractType vacation entitlement is correct', function () {
    expect(ContractType::Clt->hasVacationEntitlement())->toBeTrue();
    expect(ContractType::Estagiario->hasVacationEntitlement())->toBeTrue();
    expect(ContractType::Pj->hasVacationEntitlement())->toBeFalse();
    expect(ContractType::Socio->hasVacationEntitlement())->toBeFalse();
});

test('ContractType vacation accrual months are correct', function () {
    expect(ContractType::Clt->vacationAccrualMonths())->toBe(12);
    expect(ContractType::Estagiario->vacationAccrualMonths())->toBe(6);
    expect(ContractType::Pj->vacationAccrualMonths())->toBeNull();
    expect(ContractType::Socio->vacationAccrualMonths())->toBeNull();
});

test('ContractType vacation days are correct', function () {
    expect(ContractType::Clt->vacationDays())->toBe(30);
    expect(ContractType::Estagiario->vacationDays())->toBe(15);
    expect(ContractType::Pj->vacationDays())->toBeNull();
    expect(ContractType::Socio->vacationDays())->toBeNull();
});

test('CollaboratorStatus has 3 cases', function () {
    expect(CollaboratorStatus::cases())->toHaveCount(3);
});

test('CollaboratorStatus labels are in Portuguese', function () {
    expect(CollaboratorStatus::Ativo->label())->toBe('Ativo');
    expect(CollaboratorStatus::Desligado->label())->toBe('Desligado');
    expect(CollaboratorStatus::Afastado->label())->toBe('Afastado');
});

test('CommissionType has 3 cases', function () {
    expect(CommissionType::cases())->toHaveCount(3);
});

test('CommissionType labels are in Portuguese', function () {
    expect(CommissionType::None->label())->toBe('Sem comissão');
    expect(CommissionType::Closer->label())->toBe('Closer');
    expect(CommissionType::Advisor->label())->toBe('Advisor');
});

test('FlashStatus has 4 cases', function () {
    expect(FlashStatus::cases())->toHaveCount(4);
});

test('FlashStatus labels are in Portuguese', function () {
    expect(FlashStatus::Pendente->label())->toBe('Pendente');
    expect(FlashStatus::Ativo->label())->toBe('Ativo');
    expect(FlashStatus::Suspenso->label())->toBe('Suspenso');
    expect(FlashStatus::Cancelado->label())->toBe('Cancelado');
});

// --- Factory Tests ---

test('factory creates valid collaborator', function () {
    $collaborator = Collaborator::factory()->create();

    expect($collaborator)->toBeInstanceOf(Collaborator::class);
    expect($collaborator->id)->toBeGreaterThan(0);
    expect($collaborator->nome_completo)->toBeString();
    expect($collaborator->cpf)->toBeString();
    expect($collaborator->tipo_contrato)->toBeInstanceOf(ContractType::class);
    expect($collaborator->status)->toBe(CollaboratorStatus::Ativo);
});

test('clt factory state sets correct contract type', function () {
    $collaborator = Collaborator::factory()->clt()->create();

    expect($collaborator->tipo_contrato)->toBe(ContractType::Clt);
});

test('pj factory state sets correct contract type', function () {
    $collaborator = Collaborator::factory()->pj()->create();

    expect($collaborator->tipo_contrato)->toBe(ContractType::Pj);
});

test('estagiario factory state sets correct contract type', function () {
    $collaborator = Collaborator::factory()->estagiario()->create();

    expect($collaborator->tipo_contrato)->toBe(ContractType::Estagiario);
});

test('socio factory state sets correct contract type and salary', function () {
    $collaborator = Collaborator::factory()->socio()->create();

    expect($collaborator->tipo_contrato)->toBe(ContractType::Socio);
    expect((float) $collaborator->salario_base)->toBe(1412.00);
});

test('closer factory state sets commission type', function () {
    $collaborator = Collaborator::factory()->clt()->closer()->create();

    expect($collaborator->tipo_comissao)->toBe(CommissionType::Closer);
    expect($collaborator->elegivel_comissao)->toBeTrue();
});

test('advisor factory state sets commission type and minimum', function () {
    $collaborator = Collaborator::factory()->socio()->advisor()->create();

    expect($collaborator->tipo_comissao)->toBe(CommissionType::Advisor);
    expect($collaborator->elegivel_comissao)->toBeTrue();
    expect($collaborator->minimo_garantido)->not->toBeNull();
});

test('terminated factory state sets status and date', function () {
    $collaborator = Collaborator::factory()->clt()->terminated()->create();

    expect($collaborator->status)->toBe(CollaboratorStatus::Desligado);
    expect($collaborator->data_desligamento)->not->toBeNull();
});

// --- Relationship Tests ---

test('collaborator belongs to user', function () {
    $user = User::factory()->create();
    $collaborator = Collaborator::factory()->create(['user_id' => $user->id]);

    expect($collaborator->user)->toBeInstanceOf(User::class);
    expect($collaborator->user->id)->toBe($user->id);
});

test('user has one collaborator', function () {
    $user = User::factory()->create();
    $collaborator = Collaborator::factory()->create(['user_id' => $user->id]);

    expect($user->collaborator)->toBeInstanceOf(Collaborator::class);
    expect($user->collaborator->id)->toBe($collaborator->id);
});

test('collaborator belongs to legal entity', function () {
    $entity = LegalEntity::factory()->create();
    $collaborator = Collaborator::factory()->create(['legal_entity_id' => $entity->id]);

    expect($collaborator->legalEntity)->toBeInstanceOf(LegalEntity::class);
    expect($collaborator->legalEntity->id)->toBe($entity->id);
});

test('legal entity has many collaborators', function () {
    $entity = LegalEntity::factory()->create();
    Collaborator::factory(3)->create(['legal_entity_id' => $entity->id]);

    expect($entity->collaborators)->toHaveCount(3);
});

// --- Policy Tests ---

test('admin can viewAny collaborators', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', Collaborator::class))->toBeTrue();
});

test('collaborator cannot viewAny collaborators', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', Collaborator::class))->toBeFalse();
});

test('admin can view any collaborator', function () {
    $admin = User::factory()->admin()->create();
    $collaborator = Collaborator::factory()->create();

    expect($admin->can('view', $collaborator))->toBeTrue();
});

test('collaborator can view own record', function () {
    $user = User::factory()->create();
    $collaborator = Collaborator::factory()->create(['user_id' => $user->id]);

    expect($user->can('view', $collaborator))->toBeTrue();
});

test('collaborator cannot view another record', function () {
    $user = User::factory()->create();
    $collaborator = Collaborator::factory()->create();

    expect($user->can('view', $collaborator))->toBeFalse();
});

test('admin can create collaborators', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', Collaborator::class))->toBeTrue();
});

test('collaborator cannot create collaborators', function () {
    $user = User::factory()->create();

    expect($user->can('create', Collaborator::class))->toBeFalse();
});

test('admin can update collaborators', function () {
    $admin = User::factory()->admin()->create();
    $collaborator = Collaborator::factory()->create();

    expect($admin->can('update', $collaborator))->toBeTrue();
});

test('collaborator cannot update collaborators', function () {
    $user = User::factory()->create();
    $collaborator = Collaborator::factory()->create();

    expect($user->can('update', $collaborator))->toBeFalse();
});

test('admin can delete collaborators', function () {
    $admin = User::factory()->admin()->create();
    $collaborator = Collaborator::factory()->create();

    expect($admin->can('delete', $collaborator))->toBeTrue();
});

test('collaborator cannot delete collaborators', function () {
    $user = User::factory()->create();
    $collaborator = Collaborator::factory()->create();

    expect($user->can('delete', $collaborator))->toBeFalse();
});
