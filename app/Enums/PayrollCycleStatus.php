<?php

namespace App\Enums;

enum PayrollCycleStatus: string
{
    case Aberto = 'aberto';
    case AguardandoNfPj = 'aguardando_nf_pj';
    case AguardandoComissoes = 'aguardando_comissoes';
    case EmRevisao = 'em_revisao';
    case ConferidoContabilidade = 'conferido_contabilidade';
    case Fechado = 'fechado';

    public function label(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::AguardandoNfPj => 'Aguardando NF PJ',
            self::AguardandoComissoes => 'Aguardando Comissões',
            self::EmRevisao => 'Em Revisão',
            self::ConferidoContabilidade => 'Conferido Contabilidade',
            self::Fechado => 'Fechado',
        };
    }
}
