<?php

namespace App\Http\Controllers;

use App\Enums\CollaboratorStatus;
use App\Enums\CommissionType;
use App\Enums\ContractType;
use App\Http\Requests\Collaborator\StoreCollaboratorRequest;
use App\Http\Requests\Collaborator\UpdateCollaboratorRequest;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Services\AdmissionChecklistService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CollaboratorController extends Controller
{
    public function __construct(private AdmissionChecklistService $checklistService) {}

    public function index(): Response
    {
        $query = Collaborator::with('legalEntity');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome_completo', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('email_corporativo', 'like', "%{$search}%");
            });
        }

        if ($tipoContrato = request('tipo_contrato')) {
            $query->where('tipo_contrato', $tipoContrato);
        }

        if ($legalEntityId = request('legal_entity_id')) {
            $query->where('legal_entity_id', $legalEntityId);
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('collaborators/Index', [
            'collaborators' => $query->orderBy('nome_completo')->paginate(20)->withQueryString(),
            'filters' => request()->only(['search', 'tipo_contrato', 'legal_entity_id', 'status']),
            'legalEntities' => LegalEntity::where('ativo', true)->get(),
            'contractTypes' => collect(ContractType::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
            'statuses' => collect(CollaboratorStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'stats' => [
                'headcount_ativo' => Collaborator::where('status', CollaboratorStatus::Ativo)->count(),
                'clt_ativo' => Collaborator::where('tipo_contrato', ContractType::Clt)->where('status', CollaboratorStatus::Ativo)->count(),
                'pj_ativo' => Collaborator::where('tipo_contrato', ContractType::Pj)->where('status', CollaboratorStatus::Ativo)->count(),
                'estagiario_ativo' => Collaborator::where('tipo_contrato', ContractType::Estagiario)->where('status', CollaboratorStatus::Ativo)->count(),
                'socio_ativo' => Collaborator::where('tipo_contrato', ContractType::Socio)->where('status', CollaboratorStatus::Ativo)->count(),
                'admissoes_mes' => Collaborator::whereMonth('data_admissao', now()->month)->whereYear('data_admissao', now()->year)->count(),
                'total' => Collaborator::count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('collaborators/Create', [
            'legalEntities' => LegalEntity::where('ativo', true)->get(),
            'contractTypes' => collect(ContractType::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
            'commissionTypes' => collect(CommissionType::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
            'statuses' => collect(CollaboratorStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function store(StoreCollaboratorRequest $request): RedirectResponse
    {
        $collaborator = Collaborator::create($request->validated());
        $this->checklistService->createForCollaborator($collaborator);

        return redirect()->route('collaborators.index')
            ->with('success', 'Colaborador cadastrado com sucesso.');
    }

    public function show(Collaborator $collaborator): Response
    {
        $collaborator->load([
            'legalEntity',
            'admissionChecklist.items',
            'terminationRecord',
            'professionalHistory',
        ]);

        return Inertia::render('collaborators/Show', [
            'collaborator' => $collaborator,
            'checklist' => $collaborator->admissionChecklist,
            'terminationRecord' => $collaborator->terminationRecord,
        ]);
    }

    public function edit(Collaborator $collaborator): Response
    {
        return Inertia::render('collaborators/Edit', [
            'collaborator' => $collaborator,
            'legalEntities' => LegalEntity::where('ativo', true)->get(),
            'contractTypes' => collect(ContractType::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
            'commissionTypes' => collect(CommissionType::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
            'statuses' => collect(CollaboratorStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function update(UpdateCollaboratorRequest $request, Collaborator $collaborator): RedirectResponse
    {
        $collaborator->update($request->validated());

        return redirect()->route('collaborators.show', $collaborator)
            ->with('success', 'Colaborador atualizado com sucesso.');
    }
}
