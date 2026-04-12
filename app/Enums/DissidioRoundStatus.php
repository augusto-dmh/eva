<?php

namespace App\Enums;

enum DissidioRoundStatus: string
{
    case Rascunho = 'rascunho';
    case Simulado = 'simulado';
    case AguardandoAprovacao = 'aguardando_aprovacao';
    case Aplicado = 'aplicado';
    case RelatorioGerado = 'relatorio_gerado';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Simulado => 'Simulado',
            self::AguardandoAprovacao => 'Aguardando Aprovação',
            self::Aplicado => 'Aplicado',
            self::RelatorioGerado => 'Relatório Gerado',
        };
    }
}
