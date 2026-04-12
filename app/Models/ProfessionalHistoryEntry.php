<?php

namespace App\Models;

use App\Enums\ProfessionalEventType;
use App\Exceptions\ImmutableModelException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalHistoryEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'collaborator_id',
        'tipo_evento',
        'data_efetivacao',
        'campo_alterado',
        'valor_anterior',
        'valor_novo',
        'motivo',
        'dissidio_round_id',
        'observacoes',
        'registrado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo_evento' => ProfessionalEventType::class,
            'created_at' => 'datetime',
            'data_efetivacao' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new ImmutableModelException('ProfessionalHistoryEntry is immutable.'));
        static::deleting(fn () => throw new ImmutableModelException('ProfessionalHistoryEntry is immutable.'));
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}
