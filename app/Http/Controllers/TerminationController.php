<?php

namespace App\Http\Controllers;

use App\Enums\TerminationStatus;
use App\Enums\TerminationType;
use App\Exceptions\InvalidTransitionException;
use App\Http\Requests\StoreTerminationRequest;
use App\Models\Collaborator;
use App\Models\TerminationRecord;
use App\Services\TerminationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TerminationController extends Controller
{
    public function __construct(private TerminationService $service) {}

    public function create(Collaborator $collaborator): Response
    {
        return Inertia::render('terminations/Create', [
            'collaborator' => $collaborator->load('legalEntity'),
            'terminationTypes' => array_map(
                fn ($t) => ['value' => $t->value, 'label' => $t->label()],
                TerminationType::cases()
            ),
        ]);
    }

    public function store(StoreTerminationRequest $request, Collaborator $collaborator): RedirectResponse
    {
        $record = $this->service->createTermination($collaborator, $request->validated(), $request->user());

        return redirect()->route('termination-records.show', $record)
            ->with('success', 'Rescisão iniciada com sucesso.');
    }

    public function show(TerminationRecord $terminationRecord): Response
    {
        $terminationRecord->load(['collaborator', 'processadoPor']);

        return Inertia::render('terminations/Show', [
            'termination' => $terminationRecord,
            'allStatuses' => array_map(
                fn ($s) => ['value' => $s->value, 'label' => $s->label()],
                TerminationStatus::cases()
            ),
        ]);
    }

    public function update(Request $request, TerminationRecord $terminationRecord): RedirectResponse
    {
        if ($request->has('flash_cancelado')) {
            $this->service->markFlashCancelled($terminationRecord);

            return back()->with('success', 'Flash cancelado registrado.');
        }

        $request->validate(['status' => ['required', 'string']]);

        try {
            $to = TerminationStatus::from($request->status);
            $this->service->transition($terminationRecord, $to);
        } catch (InvalidTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        } catch (\ValueError) {
            return back()->withErrors(['status' => 'Status inválido.']);
        }

        return back()->with('success', 'Status atualizado com sucesso.');
    }
}
