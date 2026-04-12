<?php

namespace App\Models;

use App\Enums\PayrollCycleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'mes_referencia',
        'ano',
        'mes',
        'status',
        'data_abertura',
        'data_fechamento',
        'data_pagamento_folha',
        'data_pagamento_comissao',
        'salarios_brutos',
        'comissoes',
        'deducoes',
        'liquido',
        'pj',
        'observacoes',
        'fechado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PayrollCycleStatus::class,
            'data_abertura' => 'datetime',
            'data_fechamento' => 'datetime',
            'data_pagamento_folha' => 'date',
            'data_pagamento_comissao' => 'date',
            'salarios_brutos' => 'decimal:2',
            'comissoes' => 'decimal:2',
            'deducoes' => 'decimal:2',
            'liquido' => 'decimal:2',
            'pj' => 'decimal:2',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PayrollCycleEvent::class);
    }

    public function fechadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fechado_por_id');
    }
}
