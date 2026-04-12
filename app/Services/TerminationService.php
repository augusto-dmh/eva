<?php

namespace App\Services;

use App\Enums\CollaboratorStatus;
use App\Enums\TerminationStatus;
use App\Enums\TerminationType;
use App\Exceptions\InvalidTransitionException;
use App\Models\Collaborator;
use App\Models\TerminationRecord;
use App\Models\User;
use App\Services\Payroll\TerminationCalculationService;
use Carbon\Carbon;

class TerminationService
{
    private const ALLOWED_TRANSITIONS = [
        'iniciado' => ['simulacao_realizada'],
        'simulacao_realizada' => ['previa_solicitada'],
        'previa_solicitada' => ['previa_conferida'],
        'previa_conferida' => ['documentacao_enviada'],
        'documentacao_enviada' => ['concluido'],
        'concluido' => [],
    ];

    public function __construct(private TerminationCalculationService $calcService) {}

    public function createTermination(Collaborator $c, array $data, User $admin): TerminationRecord
    {
        $type = TerminationType::from($data['tipo_desligamento']);
        $effectiveDate = Carbon::parse($data['data_efetivacao']);
        $computed = $this->calcService->simulate($c, $type, $effectiveDate);

        return TerminationRecord::create(array_merge($computed, [
            'collaborator_id' => $c->id,
            'tipo_desligamento' => $type,
            'data_comunicacao' => $data['data_comunicacao'],
            'data_efetivacao' => $data['data_efetivacao'],
            'motivo' => $data['motivo'] ?? null,
            'status' => TerminationStatus::Iniciado,
            'processado_por_id' => $admin->id,
        ]));
    }

    public function transition(TerminationRecord $record, TerminationStatus $to): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$record->status->value] ?? [];

        if (! in_array($to->value, $allowed)) {
            throw new InvalidTransitionException(
                "Cannot transition from {$record->status->value} to {$to->value}"
            );
        }

        $record->update(['status' => $to]);

        if ($to === TerminationStatus::Concluido) {
            $record->collaborator->update(['status' => CollaboratorStatus::Desligado]);
        }
    }

    public function markFlashCancelled(TerminationRecord $record): void
    {
        $record->update(['flash_cancelado' => true]);
    }
}
