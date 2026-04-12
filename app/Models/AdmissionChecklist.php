<?php

namespace App\Models;

use App\Enums\ChecklistStatus;
use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'collaborator_id',
        'tipo_contrato',
        'status',
        'data_limite',
        'completado_em',
        'completado_por_id',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChecklistStatus::class,
            'tipo_contrato' => ContractType::class,
            'data_limite' => 'date',
            'completado_em' => 'datetime',
        ];
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AdmissionChecklistItem::class)->orderBy('ordem');
    }

    public function completadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completado_por_id');
    }
}
