<?php

namespace App\Jobs;

use App\Models\PayrollCycle;
use App\Services\SlackNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPjInvoiceChannelReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly PayrollCycle $cycle)
    {
        $this->onQueue('slack');
    }

    public function handle(SlackNotificationService $slack): void
    {
        $invoices = $this->cycle->pjInvoices()->with('collaborator')->get();
        $total = $invoices->count();
        $received = $invoices->whereNotIn('status', ['pendente'])->count();

        $month = $this->cycle->mes_referencia;
        $deadline = $this->cycle->data_pagamento_folha
            ? $this->cycle->data_pagamento_folha->format('d/m/Y')
            : '—';

        $message = "Atenção colaboradores PJ! O prazo para envio das notas fiscais de {$month} encerra em {$deadline}. Por favor, acesse o portal Eva e faça o upload da sua NF. Contagem: {$received} de {$total} notas recebidas.";

        $slack->sendChannelMessage('#departamento-pessoal', $message);
    }
}
