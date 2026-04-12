<?php

namespace App\Http\Requests\PjInvoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePjInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pendente,recebida,em_revisao,aprovada,rejeitada'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
