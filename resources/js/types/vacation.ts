export type VacationBatchType = 'clt' | 'estagiario';
export type VacationBatchStatus =
    | 'rascunho'
    | 'calculado'
    | 'em_revisao'
    | 'confirmado'
    | 'concluido';
export type VacationCollaboratorStatus =
    | 'pendente'
    | 'agendado'
    | 'aviso_enviado'
    | 'confirmado'
    | 'concluido';

export interface VacationBatch {
    id: number;
    mes_referencia: string;
    tipo: VacationBatchType;
    periodo_aquisitivo_minimo_meses: number;
    dias_ferias: number;
    status: VacationBatchStatus;
    data_abertura: string | null;
    data_fechamento: string | null;
    observacoes: string | null;
    criado_por_id: number;
    collaborators_count?: number;
}

export interface VacationBatchCollaborator {
    id: number;
    vacation_batch_id: number;
    collaborator_id: number;
    data_admissao: string;
    periodo_aquisitivo_inicio: string;
    periodo_aquisitivo_fim: string;
    meses_acumulados: number;
    elegivel: boolean;
    data_inicio_ferias: string | null;
    data_fim_ferias: string | null;
    valor_ferias: string | null;
    valor_terco_constitucional: string | null;
    status: VacationCollaboratorStatus;
    aviso_enviado: boolean;
    aviso_assinado: boolean;
    collaborator?: {
        id: number;
        nome_completo: string;
        tipo_contrato: string;
    };
}
