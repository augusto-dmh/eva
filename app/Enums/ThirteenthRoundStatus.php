<?php

namespace App\Enums;

enum ThirteenthRoundStatus: string
{
    case Aberto = 'aberto';
    case PrimeiraParcelaSimulada = 'primeira_parcela_simulada';
    case PrimeiraParcelaPaga = 'primeira_parcela_paga';
    case SegundaParcelaSimulada = 'segunda_parcela_simulada';
    case SegundaParcelaPaga = 'segunda_parcela_paga';
    case Concluido = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::PrimeiraParcelaSimulada => '1ª Parcela Simulada',
            self::PrimeiraParcelaPaga => '1ª Parcela Paga',
            self::SegundaParcelaSimulada => '2ª Parcela Simulada',
            self::SegundaParcelaPaga => '2ª Parcela Paga',
            self::Concluido => 'Concluído',
        };
    }
}
