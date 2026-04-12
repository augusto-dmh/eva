<?php

namespace App\Models;

use App\Enums\DissidioRoundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DissidioRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'ano_referencia',
        'data_base',
        'data_publicacao',
        'percentual',
        'aplica_estagiarios',
        'status',
        'observacoes',
        'criado_por_id',
        'aplicado_por_id',
        'aplicado_em',
    ];

    protected function casts(): array
    {
        return [
            'status' => DissidioRoundStatus::class,
            'percentual' => 'decimal:4',
            'aplica_estagiarios' => 'boolean',
            'aplicado_em' => 'datetime',
            'data_base' => 'date',
            'data_publicacao' => 'date',
        ];
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function aplicadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aplicado_por_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DissidioEntry::class);
    }
}
