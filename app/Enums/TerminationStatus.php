<?php

namespace App\Enums;

enum TerminationStatus: string
{
    case Iniciado = 'iniciado';
    case SimulacaoRealizada = 'simulacao_realizada';
    case PreviaSolicitada = 'previa_solicitada';
    case PreviaConferida = 'previa_conferida';
    case DocumentacaoEnviada = 'documentacao_enviada';
    case Concluido = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::Iniciado => 'Iniciado',
            self::SimulacaoRealizada => 'Simulação Realizada',
            self::PreviaSolicitada => 'Prévia Solicitada',
            self::PreviaConferida => 'Prévia Conferida',
            self::DocumentacaoEnviada => 'Documentação Enviada',
            self::Concluido => 'Concluído',
        };
    }
}
