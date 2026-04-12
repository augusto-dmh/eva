<?php

namespace App\Enums;

enum CommissionType: string
{
    case None = 'none';
    case Closer = 'closer';
    case Advisor = 'advisor';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sem comissão',
            self::Closer => 'Closer',
            self::Advisor => 'Advisor',
        };
    }
}
