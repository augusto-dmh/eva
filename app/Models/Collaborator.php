<?php

namespace App\Models;

use App\Enums\CollaboratorStatus;
use App\Enums\CommissionType;
use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collaborator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nexus_employee_id',
        'nome_completo',
        'cpf',
        'email_corporativo',
        'email_pessoal',
        'data_nascimento',
        'telefone',
        'tipo_contrato',
        'legal_entity_id',
        'departamento',
        'cargo',
        'nivel',
        'trilha_carreira',
        'lider_direto',
        'status',
        'data_admissao',
        'data_desligamento',
        'flash_numero_cartao',
        'flash_vale_alimentacao',
        'flash_vale_refeicao',
        'flash_vale_transporte',
        'flash_saude',
        'flash_cultura',
        'flash_educacao',
        'flash_home_office',
        'flash_total',
        'salario_base',
        'tipo_comissao',
        'minimo_garantido',
        'elegivel_comissao',
        'desconto_petlove',
        'banco',
        'agencia',
        'conta',
        'chave_pix',
        'pis',
        'slack_user_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo_contrato' => ContractType::class,
            'status' => CollaboratorStatus::class,
            'tipo_comissao' => CommissionType::class,
            'data_nascimento' => 'date',
            'data_admissao' => 'date',
            'data_desligamento' => 'date',
            'salario_base' => 'decimal:2',
            'minimo_garantido' => 'decimal:2',
            'flash_vale_alimentacao' => 'decimal:2',
            'flash_vale_refeicao' => 'decimal:2',
            'flash_vale_transporte' => 'decimal:2',
            'flash_saude' => 'decimal:2',
            'flash_cultura' => 'decimal:2',
            'flash_educacao' => 'decimal:2',
            'flash_home_office' => 'decimal:2',
            'flash_total' => 'decimal:2',
            'desconto_petlove' => 'decimal:2',
            'elegivel_comissao' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
