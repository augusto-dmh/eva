<?php

namespace App\Models;

use App\Enums\VacationCollaboratorStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationBatchCollaborator extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacation_batch_id',
        'collaborator_id',
        'data_admissao',
        'periodo_aquisitivo_inicio',
        'periodo_aquisitivo_fim',
        'meses_acumulados',
        'elegivel',
        'data_inicio_ferias',
        'data_fim_ferias',
        'valor_ferias',
        'valor_terco_constitucional',
        'status',
        'aviso_enviado',
        'aviso_assinado',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'status' => VacationCollaboratorStatus::class,
            'elegivel' => 'boolean',
            'aviso_enviado' => 'boolean',
            'aviso_assinado' => 'boolean',
            'data_admissao' => 'date',
            'periodo_aquisitivo_inicio' => 'date',
            'periodo_aquisitivo_fim' => 'date',
            'data_inicio_ferias' => 'date',
            'data_fim_ferias' => 'date',
            'valor_ferias' => 'decimal:2',
            'valor_terco_constitucional' => 'decimal:2',
        ];
    }

    public function vacationBatch(): BelongsTo
    {
        return $this->belongsTo(VacationBatch::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
