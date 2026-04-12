<?php

namespace App\Enums;

enum TerminationType: string
{
    case PedidoDemissao = 'pedido_demissao';
    case DispensaSemJustaCausa = 'dispensa_sem_justa_causa';
    case DispensaComJustaCausa = 'dispensa_com_justa_causa';
    case MutuoAcordo = 'mutuo_acordo';
    case TerminoContrato = 'termino_contrato';

    public function label(): string
    {
        return match ($this) {
            self::PedidoDemissao => 'Pedido de Demissão',
            self::DispensaSemJustaCausa => 'Dispensa Sem Justa Causa',
            self::DispensaComJustaCausa => 'Dispensa Com Justa Causa',
            self::MutuoAcordo => 'Acordo Mútuo',
            self::TerminoContrato => 'Término de Contrato',
        };
    }
}
