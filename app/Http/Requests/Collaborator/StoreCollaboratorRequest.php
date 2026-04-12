<?php

namespace App\Http\Requests\Collaborator;

use App\Enums\CollaboratorStatus;
use App\Enums\CommissionType;
use App\Enums\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'size:14', 'unique:collaborators'],
            'email_corporativo' => ['required', 'email', 'unique:collaborators'],
            'tipo_contrato' => ['required', new Enum(ContractType::class)],
            'legal_entity_id' => ['required', 'exists:legal_entities,id'],
            'data_admissao' => ['required', 'date'],
            'salario_base' => ['required', 'numeric', 'min:0'],
            'email_pessoal' => ['nullable', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'telefone' => ['nullable', 'string', 'max:255'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'nivel' => ['nullable', 'string', 'max:255'],
            'trilha_carreira' => ['nullable', 'string', 'max:255'],
            'lider_direto' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', new Enum(CollaboratorStatus::class)],
            'data_desligamento' => ['nullable', 'date'],
            'nexus_employee_id' => ['nullable', 'string', 'max:255'],
            'flash_numero_cartao' => ['nullable', 'numeric', 'min:0'],
            'flash_vale_alimentacao' => ['nullable', 'numeric', 'min:0'],
            'flash_vale_refeicao' => ['nullable', 'numeric', 'min:0'],
            'flash_vale_transporte' => ['nullable', 'numeric', 'min:0'],
            'flash_saude' => ['nullable', 'numeric', 'min:0'],
            'flash_cultura' => ['nullable', 'numeric', 'min:0'],
            'flash_educacao' => ['nullable', 'numeric', 'min:0'],
            'flash_home_office' => ['nullable', 'numeric', 'min:0'],
            'flash_total' => ['nullable', 'numeric', 'min:0'],
            'tipo_comissao' => ['nullable', new Enum(CommissionType::class)],
            'minimo_garantido' => ['nullable', 'numeric', 'min:0'],
            'elegivel_comissao' => ['nullable', 'boolean'],
            'desconto_petlove' => ['nullable', 'numeric', 'min:0'],
            'banco' => ['nullable', 'string', 'max:255'],
            'agencia' => ['nullable', 'string', 'max:255'],
            'conta' => ['nullable', 'string', 'max:255'],
            'chave_pix' => ['nullable', 'string', 'max:255'],
            'pis' => ['nullable', 'string', 'max:255'],
            'slack_user_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
