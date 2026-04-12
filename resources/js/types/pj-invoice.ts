export type PjInvoiceStatus =
    | 'pendente'
    | 'recebida'
    | 'em_revisao'
    | 'aprovada'
    | 'rejeitada';

export interface PjInvoice {
    id: number;
    payroll_entry_id: number | null;
    collaborator_id: number;
    payroll_cycle_id: number;
    numero_nota: string;
    valor: string;
    arquivo_path: string;
    arquivo_nome_original: string;
    data_upload: string;
    data_emissao: string;
    cnpj_emissor: string;
    cnpj_destinatario: string;
    status: PjInvoiceStatus;
    observacoes: string | null;
    uploaded_by_id: number;
    revisado_por_id: number | null;
    collaborator?: { id: number; nome_completo: string };
    payrollCycle?: { id: number; mes_referencia: string };
    created_at: string;
    updated_at: string;
}
