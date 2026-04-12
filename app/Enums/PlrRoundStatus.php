<?php

namespace App\Enums;

enum PlrRoundStatus: string
{
    case Rascunho = 'rascunho';
    case DocumentoEnviado = 'documento_enviado';
    case ComiteCriado = 'comite_criado';
    case AguardandoSindicato = 'aguardando_sindicato';
    case Aprovado = 'aprovado';
    case Simulado = 'simulado';
    case Pago = 'pago';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::DocumentoEnviado => 'Documento Enviado',
            self::ComiteCriado => 'Comitê Criado',
            self::AguardandoSindicato => 'Aguardando Sindicato',
            self::Aprovado => 'Aprovado',
            self::Simulado => 'Simulado',
            self::Pago => 'Pago',
        };
    }
}
