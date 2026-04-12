export type ContractType = 'clt' | 'pj' | 'estagiario' | 'socio';

export interface ProfessionalHistoryEntry {
    id: number;
    collaborator_id: number;
    tipo_evento: string;
    data_efetivacao: string;
    campo_alterado: string;
    valor_anterior: string | null;
    valor_novo: string | null;
    motivo: string;
    observacoes: string | null;
    created_at: string;
}
export type CollaboratorStatus = 'ativo' | 'desligado' | 'afastado';
export type CommissionType = 'none' | 'closer' | 'advisor';

export interface LegalEntity {
    id: number;
    nome: string;
    apelido: string;
    cnpj: string;
}

export interface EnumOption {
    value: string;
    label: string;
}

export interface Collaborator {
    id: number;
    user_id: number | null;
    nome_completo: string;
    cpf: string;
    email_corporativo: string;
    email_pessoal: string | null;
    data_nascimento: string | null;
    telefone: string | null;
    tipo_contrato: ContractType;
    legal_entity_id: number;
    departamento: string | null;
    cargo: string | null;
    nivel: string | null;
    trilha_carreira: string | null;
    lider_direto: string | null;
    status: CollaboratorStatus;
    data_admissao: string;
    data_desligamento: string | null;
    flash_numero_cartao: string | null;
    flash_vale_alimentacao: string | null;
    flash_vale_refeicao: string | null;
    flash_vale_transporte: string | null;
    flash_saude: string | null;
    flash_cultura: string | null;
    flash_educacao: string | null;
    flash_home_office: string | null;
    flash_total: string | null;
    salario_base: string;
    tipo_comissao: CommissionType;
    minimo_garantido: string | null;
    elegivel_comissao: boolean;
    desconto_petlove: string | null;
    banco: string | null;
    agencia: string | null;
    conta: string | null;
    chave_pix: string | null;
    pis: string | null;
    slack_user_id: string | null;
    created_at: string;
    updated_at: string;
    legal_entity?: LegalEntity;
}
