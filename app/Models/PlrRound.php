<?php

namespace App\Models;

use App\Enums\PlrRoundStatus;
use App\Enums\PlrSyndicateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlrRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'ano_referencia',
        'documento_politica_path',
        'documento_politica_revisado',
        'status_sindicato',
        'data_aprovacao_sindicato',
        'valor_total_distribuido',
        'status',
        'observacoes',
        'criado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlrRoundStatus::class,
            'status_sindicato' => PlrSyndicateStatus::class,
            'documento_politica_revisado' => 'boolean',
            'data_aprovacao_sindicato' => 'date',
            'valor_total_distribuido' => 'decimal:2',
        ];
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PlrEntry::class);
    }

    public function committeeMembers(): HasMany
    {
        return $this->hasMany(PlrCommitteeMember::class);
    }
}
