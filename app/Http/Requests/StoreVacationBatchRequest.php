<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacationBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mes_referencia' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'tipo' => ['required', 'string', 'in:clt,estagiario'],
            'observacoes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
