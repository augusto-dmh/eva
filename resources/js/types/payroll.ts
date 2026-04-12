export type PayrollCycleStatus =
    | 'aberto'
    | 'aguardando_nf_pj'
    | 'aguardando_comissoes'
    | 'em_revisao'
    | 'conferido_contabilidade'
    | 'fechado';

export type PayrollEntryStatus =
    | 'pendente'
    | 'preenchido'
    | 'revisado'
    | 'aprovado';

export interface PayrollCycle {
    id: number;
    mes_referencia: string;
    ano: number;
    mes: number;
    status: PayrollCycleStatus;
    data_abertura: string;
    data_fechamento: string | null;
    data_pagamento_folha: string | null;
    data_pagamento_comissao: string | null;
    salarios_brutos: string;
    comissoes: string;
    deducoes: string;
    liquido: string;
    pj: string;
    observacoes: string | null;
    entries?: PayrollEntry[];
    created_at: string;
    updated_at: string;
}

export interface PayrollEntry {
    id: number;
    payroll_cycle_id: number;
    collaborator_id: number;
    tipo_contrato: string;
    legal_entity_id: number;
    salario_bruto: string;
    salario_proporcional: boolean;
    dias_trabalhados: number | null;
    dias_uteis_mes: number | null;
    valor_comissao_bruta: string;
    valor_dsr: string;
    valor_comissao_total: string;
    desconto_inss: string;
    desconto_irrf: string;
    desconto_contribuicao_assistencial: string;
    desconto_petlove: string;
    desconto_outros: string;
    descricao_desconto_outros: string | null;
    bonificacoes: string;
    descricao_bonificacoes: string | null;
    valor_liquido: string;
    valor_fgts: string;
    valor_inss_patronal: string;
    valor_nota_fiscal_pj: string | null;
    status: PayrollEntryStatus;
    observacoes: string | null;
    collaborator?: { id: number; nome_completo: string; tipo_contrato: string };
    legalEntity?: { id: number; nome: string; apelido: string };
}
