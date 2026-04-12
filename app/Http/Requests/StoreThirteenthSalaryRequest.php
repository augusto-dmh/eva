<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThirteenthSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ano_referencia' => ['required', 'integer', 'min:2000', 'max:2100', Rule::unique('thirteenth_salary_rounds', 'ano_referencia')],
            'primeira_parcela_data_limite' => ['required', 'date'],
            'segunda_parcela_data_limite' => ['required', 'date', 'after:primeira_parcela_data_limite'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
