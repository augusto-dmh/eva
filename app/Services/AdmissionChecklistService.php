<?php

namespace App\Services;

use App\Enums\ChecklistStatus;
use App\Enums\ContractType;
use App\Models\AdmissionChecklist;
use App\Models\AdmissionChecklistItem;
use App\Models\Collaborator;
use App\Models\User;
use Carbon\Carbon;

class AdmissionChecklistService
{
    public function createForCollaborator(Collaborator $c): AdmissionChecklist
    {
        $checklist = AdmissionChecklist::create([
            'collaborator_id' => $c->id,
            'tipo_contrato' => $c->tipo_contrato->value,
            'status' => ChecklistStatus::Pendente,
            'data_limite' => Carbon::parse($c->data_admissao)->addDays(30),
        ]);

        $templates = $this->getTemplateItems($c->tipo_contrato);
        foreach ($templates as $index => $template) {
            AdmissionChecklistItem::create([
                'admission_checklist_id' => $checklist->id,
                'descricao' => $template['descricao'],
                'obrigatorio' => $template['obrigatorio'] ?? true,
                'ordem' => $index + 1,
            ]);
        }

        return $checklist;
    }

    public function confirmItem(AdmissionChecklistItem $item, User $user): void
    {
        $item->update([
            'confirmado' => true,
            'confirmado_em' => now(),
            'confirmado_por_id' => $user->id,
        ]);

        $checklist = $item->admissionChecklist()->first();

        if ($checklist->status === ChecklistStatus::Pendente) {
            $checklist->update(['status' => ChecklistStatus::EmAndamento]);
        }

        $allMandatoryDone = $checklist->items()
            ->where('obrigatorio', true)
            ->where('confirmado', false)
            ->doesntExist();

        if ($allMandatoryDone && $checklist->status !== ChecklistStatus::Completo) {
            $checklist->update([
                'status' => ChecklistStatus::Completo,
                'completado_em' => now(),
                'completado_por_id' => $user->id,
            ]);
        }
    }

    public function getTemplateItems(ContractType $type): array
    {
        return match ($type) {
            ContractType::Clt => [
                ['descricao' => 'Carteira de Trabalho (CTPS)', 'obrigatorio' => true],
                ['descricao' => 'Exame Admissional (ASO)', 'obrigatorio' => true],
                ['descricao' => 'CPF', 'obrigatorio' => true],
                ['descricao' => 'RG ou CNH', 'obrigatorio' => true],
                ['descricao' => 'Comprovante de Endereço', 'obrigatorio' => true],
                ['descricao' => 'Dados Bancários para Depósito', 'obrigatorio' => true],
                ['descricao' => 'Cadastro no Vale-Transporte', 'obrigatorio' => false],
                ['descricao' => 'Cadastro no Plano de Saúde', 'obrigatorio' => false],
            ],
            ContractType::Pj => [
                ['descricao' => 'Contrato PJ assinado', 'obrigatorio' => true],
                ['descricao' => 'CNPJ da empresa prestadora', 'obrigatorio' => true],
                ['descricao' => 'Dados Bancários (conta PJ)', 'obrigatorio' => true],
                ['descricao' => 'CPF do sócio responsável', 'obrigatorio' => true],
                ['descricao' => 'Contrato Social', 'obrigatorio' => false],
            ],
            ContractType::Estagiario => [
                ['descricao' => 'Contrato de Estágio assinado', 'obrigatorio' => true],
                ['descricao' => 'Carta de Apresentação da Instituição de Ensino', 'obrigatorio' => true],
                ['descricao' => 'CPF', 'obrigatorio' => true],
                ['descricao' => 'RG ou CNH', 'obrigatorio' => true],
                ['descricao' => 'Dados Bancários', 'obrigatorio' => true],
                ['descricao' => 'Comprovante de Matrícula', 'obrigatorio' => false],
            ],
            ContractType::Socio => [
                ['descricao' => 'Contrato Social', 'obrigatorio' => true],
                ['descricao' => 'CPF', 'obrigatorio' => true],
                ['descricao' => 'RG ou CNH', 'obrigatorio' => true],
                ['descricao' => 'Dados Bancários (conta PJ/pessoal)', 'obrigatorio' => true],
            ],
        };
    }
}
