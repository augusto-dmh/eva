<?php

namespace App\Enums;

enum SyndicateType: string
{
    case Patronal = 'patronal';
    case Trabalhadores = 'trabalhadores';

    public function label(): string
    {
        return match ($this) {
            self::Patronal => 'Patronal',
            self::Trabalhadores => 'Trabalhadores',
        };
    }
}
