<?php

namespace App\Models;

use App\Enums\PlrEntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlrEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'plr_round_id',
        'collaborator_id',
        'media_salarios_ano',
        'meses_trabalhados',
        'valor_simulado',
        'valor_pago',
        'desconto_irrf',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlrEntryStatus::class,
            'media_salarios_ano' => 'decimal:2',
            'valor_simulado' => 'decimal:2',
            'valor_pago' => 'decimal:2',
            'desconto_irrf' => 'decimal:2',
        ];
    }

    public function plrRound(): BelongsTo
    {
        return $this->belongsTo(PlrRound::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
