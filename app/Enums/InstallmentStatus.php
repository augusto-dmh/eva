<?php

namespace App\Enums;

enum InstallmentStatus: string
{
    case Pendente = 'pendente';
    case Simulado = 'simulado';
    case Pago = 'pago';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Simulado => 'Simulado',
            self::Pago => 'Pago',
        };
    }
}
