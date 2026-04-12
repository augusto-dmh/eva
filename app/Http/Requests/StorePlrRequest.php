<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ano_referencia' => ['required', 'integer', 'min:2000', 'max:2100', Rule::unique('plr_rounds', 'ano_referencia')],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
