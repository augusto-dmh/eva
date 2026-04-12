<?php

namespace App\Models;

use App\Enums\TerminationStatus;
use App\Enums\TerminationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'collaborator_id', 'tipo_desligamento', 'data_comunicacao', 'data_efetivacao',
        'motivo', 'salario_proporcional_dias', 'salario_proporcional_valor',
        'ferias_proporcionais_valor', 'terco_ferias_proporcionais',
        'decimo_terceiro_proporcional', 'multa_fgts', 'aviso_previo_valor',
        'indenizacao_rescisoria', 'valor_total_rescisao', 'ajuste_flash_valor',
        'flash_cancelado', 'exame_demissional_agendado', 'exame_demissional_data',
        'previa_contabilidade_solicitada', 'previa_contabilidade_conferida',
        'documentos_enviados_rh', 'status', 'processado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo_desligamento' => TerminationType::class,
            'status' => TerminationStatus::class,
            'flash_cancelado' => 'boolean',
            'exame_demissional_agendado' => 'boolean',
            'previa_contabilidade_solicitada' => 'boolean',
            'previa_contabilidade_conferida' => 'boolean',
            'documentos_enviados_rh' => 'boolean',
            'data_comunicacao' => 'date',
            'data_efetivacao' => 'date',
            'exame_demissional_data' => 'date',
            'salario_proporcional_valor' => 'decimal:2',
            'ferias_proporcionais_valor' => 'decimal:2',
            'terco_ferias_proporcionais' => 'decimal:2',
            'decimo_terceiro_proporcional' => 'decimal:2',
            'multa_fgts' => 'decimal:2',
            'aviso_previo_valor' => 'decimal:2',
            'indenizacao_rescisoria' => 'decimal:2',
            'valor_total_rescisao' => 'decimal:2',
            'ajuste_flash_valor' => 'decimal:2',
        ];
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function processadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processado_por_id');
    }
}
