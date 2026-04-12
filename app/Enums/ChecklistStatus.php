<?php

namespace App\Enums;

enum ChecklistStatus: string
{
    case Pendente = 'pendente';
    case EmAndamento = 'em_andamento';
    case Completo = 'completo';
    case Bloqueado = 'bloqueado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::EmAndamento => 'Em Andamento',
            self::Completo => 'Completo',
            self::Bloqueado => 'Bloqueado',
        };
    }
}
