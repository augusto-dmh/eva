import type { ContractType } from './collaborator';

export type ChecklistStatus =
    | 'pendente'
    | 'em_andamento'
    | 'completo'
    | 'bloqueado';

export interface AdmissionChecklist {
    id: number;
    collaborator_id: number;
    tipo_contrato: ContractType;
    status: ChecklistStatus;
    data_limite: string;
    completado_em: string | null;
    completado_por_id: number | null;
    observacoes: string | null;
    items?: AdmissionChecklistItem[];
    collaborator?: { id: number; nome_completo: string };
}

export interface AdmissionChecklistItem {
    id: number;
    admission_checklist_id: number;
    descricao: string;
    obrigatorio: boolean;
    confirmado: boolean;
    confirmado_em: string | null;
    confirmado_por_id: number | null;
    documento_path: string | null;
    observacoes: string | null;
    ordem: number;
}
