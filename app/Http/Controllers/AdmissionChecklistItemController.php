<?php

namespace App\Http\Controllers;

use App\Models\AdmissionChecklistItem;
use App\Services\AdmissionChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdmissionChecklistItemController extends Controller
{
    public function __construct(private AdmissionChecklistService $service) {}

    public function update(Request $request, AdmissionChecklistItem $admissionChecklistItem): RedirectResponse
    {
        $this->service->confirmItem($admissionChecklistItem, $request->user());

        return back()->with('success', 'Item confirmado com sucesso.');
    }
}
