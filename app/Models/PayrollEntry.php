<?php

namespace App\Models;

use App\Enums\PayrollEntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_cycle_id',
        'collaborator_id',
        'tipo_contrato',
        'legal_entity_id',
        'salario_bruto',
        'salario_proporcional',
        'dias_trabalhados',
        'dias_uteis_mes',
        'valor_comissao_bruta',
        'valor_dsr',
        'valor_comissao_total',
        'desconto_inss',
        'desconto_irrf',
        'desconto_contribuicao_assistencial',
        'desconto_petlove',
        'desconto_outros',
        'descricao_desconto_outros',
        'bonificacoes',
        'descricao_bonificacoes',
        'valor_liquido',
        'valor_fgts',
        'valor_inss_patronal',
        'valor_nota_fiscal_pj',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PayrollEntryStatus::class,
            'salario_proporcional' => 'boolean',
            'salario_bruto' => 'decimal:2',
            'valor_comissao_bruta' => 'decimal:2',
            'valor_dsr' => 'decimal:2',
            'valor_comissao_total' => 'decimal:2',
            'desconto_inss' => 'decimal:2',
            'desconto_irrf' => 'decimal:2',
            'desconto_contribuicao_assistencial' => 'decimal:2',
            'desconto_petlove' => 'decimal:2',
            'desconto_outros' => 'decimal:2',
            'bonificacoes' => 'decimal:2',
            'valor_liquido' => 'decimal:2',
            'valor_fgts' => 'decimal:2',
            'valor_inss_patronal' => 'decimal:2',
            'valor_nota_fiscal_pj' => 'decimal:2',
        ];
    }

    public function payrollCycle(): BelongsTo
    {
        return $this->belongsTo(PayrollCycle::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
