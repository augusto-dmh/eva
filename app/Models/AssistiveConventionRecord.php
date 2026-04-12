<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistiveConventionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'collaborator_id',
        'ano_referencia',
        'fez_oposicao',
        'data_oposicao',
        'comprovante_ar_path',
        'confirmado_sindicato',
        'parcelas_descontadas',
        'total_parcelas',
        'valor_parcela',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'fez_oposicao' => 'boolean',
            'confirmado_sindicato' => 'boolean',
            'data_oposicao' => 'date',
            'valor_parcela' => 'decimal:2',
        ];
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
