<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_desligamento' => ['required', 'string', 'in:pedido_demissao,dispensa_sem_justa_causa,dispensa_com_justa_causa,mutuo_acordo,termino_contrato'],
            'data_comunicacao' => ['required', 'date'],
            'data_efetivacao' => ['required', 'date', 'after_or_equal:data_comunicacao'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
