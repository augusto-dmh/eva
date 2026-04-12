<?php

namespace App\Enums;

enum AdjustmentReason: string
{
    case Merito = 'merito';
    case Dissidio = 'dissidio';
    case Promocao = 'promocao';
    case Reajuste = 'reajuste';
    case Correcao = 'correcao';
    case Politica = 'politica';

    public function label(): string
    {
        return match ($this) {
            self::Merito => 'Mérito',
            self::Dissidio => 'Dissídio',
            self::Promocao => 'Promoção',
            self::Reajuste => 'Reajuste',
            self::Correcao => 'Correção',
            self::Politica => 'Política Salarial',
        };
    }
}
