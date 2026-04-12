export interface AssistiveConventionRecord {
    id: number;
    collaborator_id: number;
    collaborator?: {
        id: number;
        nome_completo: string;
        tipo_contrato: string;
    };
    ano_referencia: number;
    fez_oposicao: boolean;
    data_oposicao: string | null;
    confirmado_sindicato: boolean;
    parcelas_descontadas: number;
    total_parcelas: number;
    valor_parcela: number | null;
    observacoes: string | null;
}
