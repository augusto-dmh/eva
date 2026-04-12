<?php

namespace App\Enums;

enum PlrSyndicateStatus: string
{
    case NaoIniciado = 'nao_iniciado';
    case Enviado = 'enviado';
    case Aprovado = 'aprovado';
    case Rejeitado = 'rejeitado';

    public function label(): string
    {
        return match ($this) {
            self::NaoIniciado => 'Não Iniciado',
            self::Enviado => 'Enviado',
            self::Aprovado => 'Aprovado',
            self::Rejeitado => 'Rejeitado',
        };
    }
}
