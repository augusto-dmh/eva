<?php

namespace App\Policies;

use App\Models\Collaborator;
use App\Models\User;

class CollaboratorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Collaborator $collaborator): bool
    {
        return $user->isAdmin() || $collaborator->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Collaborator $collaborator): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Collaborator $collaborator): bool
    {
        return $user->isAdmin();
    }
}
