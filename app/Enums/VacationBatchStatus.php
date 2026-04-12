<?php

namespace App\Enums;

enum VacationBatchStatus: string
{
    case Rascunho = 'rascunho';
    case Calculado = 'calculado';
    case EmRevisao = 'em_revisao';
    case Confirmado = 'confirmado';
    case Concluido = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Calculado => 'Calculado',
            self::EmRevisao => 'Em Revisão',
            self::Confirmado => 'Confirmado',
            self::Concluido => 'Concluído',
        };
    }
}
