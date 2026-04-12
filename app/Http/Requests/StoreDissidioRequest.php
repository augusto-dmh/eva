<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDissidioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ano_referencia' => ['required', 'integer', 'min:2000', 'max:2100'],
            'data_base' => ['required', 'date'],
            'percentual' => ['required', 'numeric', 'between:0,1'],
            'aplica_estagiarios' => ['boolean'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
