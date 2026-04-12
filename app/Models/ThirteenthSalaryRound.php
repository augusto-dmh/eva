<?php

namespace App\Models;

use App\Enums\ThirteenthRoundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThirteenthSalaryRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'ano_referencia',
        'status',
        'primeira_parcela_data_limite',
        'segunda_parcela_data_limite',
        'observacoes',
        'criado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ThirteenthRoundStatus::class,
            'primeira_parcela_data_limite' => 'date',
            'segunda_parcela_data_limite' => 'date',
        ];
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ThirteenthSalaryEntry::class);
    }
}
