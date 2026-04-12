export interface DissidioRound {
    id: number;
    ano_referencia: number;
    data_base: string;
    data_publicacao: string | null;
    percentual: number;
    aplica_estagiarios: boolean;
    status: string;
    observacoes: string | null;
    criado_por: { id: number; name: string } | null;
    aplicado_por: { id: number; name: string } | null;
    aplicado_em: string | null;
    entries?: DissidioEntry[];
    created_at: string;
    updated_at: string;
}

export interface DissidioEntry {
    id: number;
    dissidio_round_id: number;
    collaborator_id: number;
    collaborator?: {
        id: number;
        nome_completo: string;
        cargo: string;
        tipo_contrato: string;
    };
    salario_anterior: number;
    percentual_aplicado: number;
    salario_novo: number;
    diferenca_retroativa: number;
    meses_retroativos: number;
    status: string;
}
