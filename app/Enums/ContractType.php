<?php

namespace App\Enums;

enum ContractType: string
{
    case Clt = 'clt';
    case Pj = 'pj';
    case Estagiario = 'estagiario';
    case Socio = 'socio';

    public function label(): string
    {
        return match ($this) {
            self::Clt => 'CLT',
            self::Pj => 'PJ',
            self::Estagiario => 'Estagiário',
            self::Socio => 'Sócio',
        };
    }

    public function hasVacationEntitlement(): bool
    {
        return match ($this) {
            self::Clt, self::Estagiario => true,
            self::Pj, self::Socio => false,
        };
    }

    public function vacationAccrualMonths(): ?int
    {
        return match ($this) {
            self::Clt => 12,
            self::Estagiario => 6,
            self::Pj, self::Socio => null,
        };
    }

    public function vacationDays(): ?int
    {
        return match ($this) {
            self::Clt => 30,
            self::Estagiario => 15,
            self::Pj, self::Socio => null,
        };
    }
}
