<?php

namespace App\Models;

use App\Enums\VacationBatchStatus;
use App\Enums\VacationBatchType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacationBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'mes_referencia',
        'tipo',
        'periodo_aquisitivo_minimo_meses',
        'dias_ferias',
        'status',
        'data_abertura',
        'data_fechamento',
        'observacoes',
        'criado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => VacationBatchStatus::class,
            'tipo' => VacationBatchType::class,
            'data_abertura' => 'datetime',
            'data_fechamento' => 'datetime',
        ];
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(VacationBatchCollaborator::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
