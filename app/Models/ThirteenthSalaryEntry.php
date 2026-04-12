<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThirteenthSalaryEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'thirteenth_salary_round_id',
        'collaborator_id',
        'meses_trabalhados',
        'salario_base',
        'media_comissoes',
        'base_calculo',
        'valor_integral',
        'primeira_parcela_valor',
        'segunda_parcela_valor',
        'desconto_inss',
        'desconto_irrf',
        'primeira_parcela_status',
        'segunda_parcela_status',
    ];

    protected function casts(): array
    {
        return [
            'primeira_parcela_status' => InstallmentStatus::class,
            'segunda_parcela_status' => InstallmentStatus::class,
            'salario_base' => 'decimal:2',
            'media_comissoes' => 'decimal:2',
            'base_calculo' => 'decimal:2',
            'valor_integral' => 'decimal:2',
            'primeira_parcela_valor' => 'decimal:2',
            'segunda_parcela_valor' => 'decimal:2',
            'desconto_inss' => 'decimal:2',
            'desconto_irrf' => 'decimal:2',
        ];
    }

    public function thirteenthSalaryRound(): BelongsTo
    {
        return $this->belongsTo(ThirteenthSalaryRound::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
