<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'salario_bruto' => ['nullable', 'numeric', 'min:0'],
            'salario_proporcional' => ['nullable', 'boolean'],
            'dias_trabalhados' => ['nullable', 'integer', 'min:0', 'max:31'],
            'dias_uteis_mes' => ['nullable', 'integer', 'min:0', 'max:31'],
            'valor_comissao_bruta' => ['nullable', 'numeric', 'min:0'],
            'valor_dsr' => ['nullable', 'numeric', 'min:0'],
            'valor_comissao_total' => ['nullable', 'numeric', 'min:0'],
            'desconto_inss' => ['nullable', 'numeric', 'min:0'],
            'desconto_irrf' => ['nullable', 'numeric', 'min:0'],
            'desconto_contribuicao_assistencial' => ['nullable', 'numeric', 'min:0'],
            'desconto_petlove' => ['nullable', 'numeric', 'min:0'],
            'desconto_outros' => ['nullable', 'numeric', 'min:0'],
            'descricao_desconto_outros' => ['nullable', 'string'],
            'bonificacoes' => ['nullable', 'numeric', 'min:0'],
            'descricao_bonificacoes' => ['nullable', 'string'],
            'valor_liquido' => ['nullable', 'numeric', 'min:0'],
            'valor_fgts' => ['nullable', 'numeric', 'min:0'],
            'valor_inss_patronal' => ['nullable', 'numeric', 'min:0'],
            'valor_nota_fiscal_pj' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:pendente,preenchido,revisado,aprovado'],
        ];
    }
}
