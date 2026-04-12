<?php

namespace App\Policies;

use App\Models\PjInvoice;
use App\Models\User;

class PjInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, PjInvoice $invoice): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollaborator()) {
            return $user->collaborator?->id === $invoice->collaborator_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, PjInvoice $invoice): bool
    {
        return $user->isAdmin();
    }
}
