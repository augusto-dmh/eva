export interface PlrRound {
    id: number;
    ano_referencia: number;
    status: string;
    status_sindicato: string;
    valor_total_distribuido: number | null;
    observacoes: string | null;
    criado_por: { id: number; name: string } | null;
    entries?: PlrEntry[];
    committee_members?: PlrCommitteeMember[];
    created_at: string;
    updated_at: string;
}

export interface PlrEntry {
    id: number;
    plr_round_id: number;
    collaborator_id: number;
    collaborator?: {
        id: number;
        nome_completo: string;
        cargo: string;
    };
    media_salarios_ano: number;
    meses_trabalhados: number;
    valor_simulado: number;
    valor_pago: number | null;
    desconto_irrf: number;
    status: string;
}

export interface PlrCommitteeMember {
    id: number;
    collaborator?: {
        id: number;
        nome_completo: string;
    };
    legal_entity?: {
        id: number;
        apelido: string;
    };
    papel: string;
    ativo: boolean;
}
