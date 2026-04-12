<?php

namespace App\Http\Requests\PjInvoice;

use Illuminate\Foundation\Http\FormRequest;

class StorePjInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'arquivo' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'numero_nota' => ['required', 'string', 'max:50'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_emissao' => ['required', 'date'],
            'cnpj_emissor' => ['required', 'string', 'size:18'],
            'cnpj_destinatario' => ['required', 'string', 'size:18'],
        ];
    }
}
