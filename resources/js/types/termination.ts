export type TerminationType =
    | 'pedido_demissao'
    | 'dispensa_sem_justa_causa'
    | 'dispensa_com_justa_causa'
    | 'mutuo_acordo'
    | 'termino_contrato';
export type TerminationStatus =
    | 'iniciado'
    | 'simulacao_realizada'
    | 'previa_solicitada'
    | 'previa_conferida'
    | 'documentacao_enviada'
    | 'concluido';

export interface TerminationRecord {
    id: number;
    collaborator_id: number;
    tipo_desligamento: TerminationType;
    data_comunicacao: string;
    data_efetivacao: string;
    motivo: string | null;
    salario_proporcional_dias: number;
    salario_proporcional_valor: string;
    ferias_proporcionais_valor: string;
    terco_ferias_proporcionais: string;
    decimo_terceiro_proporcional: string;
    multa_fgts: string;
    aviso_previo_valor: string;
    indenizacao_rescisoria: string;
    valor_total_rescisao: string;
    ajuste_flash_valor: string;
    flash_cancelado: boolean;
    exame_demissional_agendado: boolean;
    exame_demissional_data: string | null;
    previa_contabilidade_solicitada: boolean;
    previa_contabilidade_conferida: boolean;
    documentos_enviados_rh: boolean;
    status: TerminationStatus;
    collaborator?: { id: number; nome_completo: string; tipo_contrato: string };
}
