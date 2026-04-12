<?php

namespace App\Services;

use App\Enums\VacationBatchStatus;
use App\Exceptions\InvalidTransitionException;
use App\Jobs\CalculateVacationEligibilityBatchJob;
use App\Models\VacationBatch;

class VacationBatchService
{
    private const ALLOWED_TRANSITIONS = [
        'rascunho' => ['calculado'],
        'calculado' => ['em_revisao'],
        'em_revisao' => ['confirmado'],
        'confirmado' => ['concluido'],
        'concluido' => [],
    ];

    public function transition(VacationBatch $batch, VacationBatchStatus $to): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$batch->status->value] ?? [];

        if (! in_array($to->value, $allowed)) {
            throw new InvalidTransitionException(
                "Cannot transition from {$batch->status->value} to {$to->value}"
            );
        }

        $batch->update(['status' => $to]);

        if ($to === VacationBatchStatus::Calculado) {
            CalculateVacationEligibilityBatchJob::dispatch($batch);
        }
    }
}
