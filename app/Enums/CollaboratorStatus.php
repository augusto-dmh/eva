<?php

namespace App\Enums;

enum CollaboratorStatus: string
{
    case Ativo = 'ativo';
    case Desligado = 'desligado';
    case Afastado = 'afastado';

    public function label(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Desligado => 'Desligado',
            self::Afastado => 'Afastado',
        };
    }
}
