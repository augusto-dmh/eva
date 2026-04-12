<?php

namespace App\Enums;

enum PjInvoiceStatus: string
{
    case Pendente = 'pendente';
    case Recebida = 'recebida';
    case EmRevisao = 'em_revisao';
    case Aprovada = 'aprovada';
    case Rejeitada = 'rejeitada';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Recebida => 'Recebida',
            self::EmRevisao => 'Em Revisão',
            self::Aprovada => 'Aprovada',
            self::Rejeitada => 'Rejeitada',
        };
    }
}
