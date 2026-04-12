<?php

namespace App\Http\Controllers;

use App\Models\AdmissionChecklist;
use Inertia\Inertia;
use Inertia\Response;

class AdmissionChecklistController extends Controller
{
    public function show(AdmissionChecklist $admissionChecklist): Response
    {
        $admissionChecklist->load(['items', 'collaborator', 'completadoPor']);

        return Inertia::render('admission-checklists/Show', [
            'checklist' => $admissionChecklist,
        ]);
    }
}
