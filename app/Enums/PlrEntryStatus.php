<?php

namespace App\Enums;

enum PlrEntryStatus: string
{
    case Simulado = 'simulado';
    case Aprovado = 'aprovado';
    case Pago = 'pago';

    public function label(): string
    {
        return match ($this) {
            self::Simulado => 'Simulado',
            self::Aprovado => 'Aprovado',
            self::Pago => 'Pago',
        };
    }
}
