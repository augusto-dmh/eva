<?php

namespace App\Enums;

enum ProfessionalEventType: string
{
    case Admissao = 'admissao';
    case Promocao = 'promocao';
    case AjusteSalarial = 'ajuste_salarial';
    case AlteracaoTipoContrato = 'alteracao_tipo_contrato';
    case Desligamento = 'desligamento';
    case Dissidio = 'dissidio';
    case AlteracaoCargo = 'alteracao_cargo';
    case AlteracaoDepartamento = 'alteracao_departamento';

    public function label(): string
    {
        return match ($this) {
            self::Admissao => 'Admissão',
            self::Promocao => 'Promoção',
            self::AjusteSalarial => 'Ajuste Salarial',
            self::AlteracaoTipoContrato => 'Alteração de Tipo de Contrato',
            self::Desligamento => 'Desligamento',
            self::Dissidio => 'Dissídio',
            self::AlteracaoCargo => 'Alteração de Cargo',
            self::AlteracaoDepartamento => 'Alteração de Departamento',
        };
    }
}
