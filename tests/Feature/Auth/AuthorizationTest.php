<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

// --- UserRole Enum ---

test('UserRole enum has exactly 2 cases', function () {
    expect(UserRole::cases())->toHaveCount(2);
});

test('UserRole Admin label returns Administrador', function () {
    expect(UserRole::Admin->label())->toBe('Administrador');
});

test('UserRole Collaborator label returns Colaborador', function () {
    expect(UserRole::Collaborator->label())->toBe('Colaborador');
});

test('UserRole Admin value is admin', function () {
    expect(UserRole::Admin->value)->toBe('admin');
});

test('UserRole Collaborator value is collaborator', function () {
    expect(UserRole::Collaborator->value)->toBe('collaborator');
});

// --- User Model ---

test('default factory creates collaborator role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Collaborator);
});

test('admin factory state creates admin role', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin);
});

test('isAdmin returns true for admin user', function () {
    $user = User::factory()->admin()->create();

    expect($user->isAdmin())->toBeTrue();
    expect($user->isCollaborator())->toBeFalse();
});

test('isCollaborator returns true for collaborator user', function () {
    $user = User::factory()->create();

    expect($user->isCollaborator())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});

test('role is cast to UserRole enum', function () {
    $user = User::factory()->create();

    expect($user->role)->toBeInstanceOf(UserRole::class);
});

// --- Gate ---

test('admin gate passes for admin user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    expect(Gate::allows('admin'))->toBeTrue();
});

test('admin gate fails for collaborator user', function () {
    $collaborator = User::factory()->create();

    $this->actingAs($collaborator);

    expect(Gate::denies('admin'))->toBeTrue();
});

// --- UserPolicy ---

test('admin can viewAny users', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', User::class))->toBeTrue();
});

test('collaborator cannot viewAny users', function () {
    $collaborator = User::factory()->create();

    expect($collaborator->can('viewAny', User::class))->toBeFalse();
});

test('admin can view any user', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    expect($admin->can('view', $other))->toBeTrue();
});

test('collaborator can view own record', function () {
    $collaborator = User::factory()->create();

    expect($collaborator->can('view', $collaborator))->toBeTrue();
});

test('collaborator cannot view other user record', function () {
    $collaborator = User::factory()->create();
    $other = User::factory()->create();

    expect($collaborator->can('view', $other))->toBeFalse();
});

test('admin can create users', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', User::class))->toBeTrue();
});

test('collaborator cannot create users', function () {
    $collaborator = User::factory()->create();

    expect($collaborator->can('create', User::class))->toBeFalse();
});

test('admin can update other users', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    expect($admin->can('update', $other))->toBeTrue();
});

test('collaborator cannot update users', function () {
    $collaborator = User::factory()->create();
    $other = User::factory()->create();

    expect($collaborator->can('update', $other))->toBeFalse();
});

test('admin can delete other users but not self', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    expect($admin->can('delete', $other))->toBeTrue();
    expect($admin->can('delete', $admin))->toBeFalse();
});

test('collaborator cannot delete users', function () {
    $collaborator = User::factory()->create();
    $other = User::factory()->create();

    expect($collaborator->can('delete', $other))->toBeFalse();
});
