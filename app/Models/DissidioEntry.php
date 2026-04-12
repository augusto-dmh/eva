<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DissidioEntry extends Model
{
    protected $fillable = [
        'dissidio_round_id',
        'collaborator_id',
        'salario_anterior',
        'percentual_aplicado',
        'salario_novo',
        'diferenca_retroativa',
        'meses_retroativos',
        'status',
    ];

    public function dissidioRound(): BelongsTo
    {
        return $this->belongsTo(DissidioRound::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
