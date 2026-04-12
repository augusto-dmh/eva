<?php

namespace App\Enums;

enum VacationBatchType: string
{
    case Clt = 'clt';
    case Estagiario = 'estagiario';

    public function label(): string
    {
        return match ($this) {
            self::Clt => 'CLT',
            self::Estagiario => 'Estagiário',
        };
    }

    public function periodoAquisitivoMeses(): int
    {
        return match ($this) {
            self::Clt => 12,
            self::Estagiario => 6,
        };
    }

    public function diasFerias(): int
    {
        return match ($this) {
            self::Clt => 30,
            self::Estagiario => 15,
        };
    }
}
