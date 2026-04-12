<?php

namespace App\Enums;

enum PayrollEntryStatus: string
{
    case Pendente = 'pendente';
    case Preenchido = 'preenchido';
    case Revisado = 'revisado';
    case Aprovado = 'aprovado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Preenchido => 'Preenchido',
            self::Revisado => 'Revisado',
            self::Aprovado => 'Aprovado',
        };
    }
}
