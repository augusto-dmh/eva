<?php

namespace App\Enums;

enum FlashStatus: string
{
    case Pendente = 'pendente';
    case Ativo = 'ativo';
    case Suspenso = 'suspenso';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Ativo => 'Ativo',
            self::Suspenso => 'Suspenso',
            self::Cancelado => 'Cancelado',
        };
    }
}
