<?php

namespace App\Jobs;

use App\Models\PayrollCycle;
use App\Services\SlackNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPjInvoiceIndividualRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly PayrollCycle $cycle)
    {
        $this->onQueue('slack');
    }

    public function handle(SlackNotificationService $slack): void
    {
        $month = $this->cycle->mes_referencia;

        // Get PJ collaborators in this cycle that have no invoice or status pendente
        $entries = $this->cycle->entries()
            ->with('collaborator')
            ->whereHas('collaborator', fn ($q) => $q->where('tipo_contrato', 'pj'))
            ->get();

        $invoicedCollaboratorIds = $this->cycle->pjInvoices()
            ->where('status', '!=', 'pendente')
            ->pluck('collaborator_id')
            ->toArray();

        foreach ($entries as $entry) {
            $collaborator = $entry->collaborator;

            if (! $collaborator) {
                continue;
            }

            // Skip if they already have a non-pending invoice
            if (in_array($collaborator->id, $invoicedCollaboratorIds, true)) {
                continue;
            }

            $slackUserId = $collaborator->slack_user_id;

            if (! $slackUserId) {
                continue;
            }

            $name = $collaborator->nome_completo;
            $message = "Olá {$name}! Identificamos que sua nota fiscal de {$month} ainda não foi enviada. O prazo encerra amanhã. Acesse o portal Eva para realizar o envio.";

            $slack->sendDirectMessage($slackUserId, $message);
        }
    }
}
