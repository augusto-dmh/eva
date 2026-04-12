<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class SelfServiceController extends Controller
{
    public function profile(): Response
    {
        $collaborator = auth()->user()->collaborator;

        abort_unless($collaborator, 404);

        return Inertia::render('self-service/Profile', [
            'collaborator' => $collaborator->load('legalEntity'),
        ]);
    }
}
