<?php

namespace App\Enums;

enum VacationCollaboratorStatus: string
{
    case Pendente = 'pendente';
    case Agendado = 'agendado';
    case AvisoEnviado = 'aviso_enviado';
    case Confirmado = 'confirmado';
    case Concluido = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Agendado => 'Agendado',
            self::AvisoEnviado => 'Aviso Enviado',
            self::Confirmado => 'Confirmado',
            self::Concluido => 'Concluído',
        };
    }
}
