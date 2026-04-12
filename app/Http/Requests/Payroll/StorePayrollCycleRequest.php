<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'mes_referencia' => [
                'required',
                'string',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
                'unique:payroll_cycles,mes_referencia',
            ],
            'data_pagamento_folha' => ['nullable', 'date'],
            'data_pagamento_comissao' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
