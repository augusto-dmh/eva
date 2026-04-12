export interface ThirteenthSalaryRound {
    id: number;
    ano_referencia: number;
    status: string;
    primeira_parcela_data_limite: string;
    segunda_parcela_data_limite: string;
    observacoes: string | null;
    criado_por: { id: number; name: string } | null;
    entries?: ThirteenthSalaryEntry[];
    created_at: string;
    updated_at: string;
}

export interface ThirteenthSalaryEntry {
    id: number;
    thirteenth_salary_round_id: number;
    collaborator_id: number;
    collaborator?: {
        id: number;
        nome_completo: string;
        cargo: string;
    };
    meses_trabalhados: number;
    salario_base: number;
    media_comissoes: number;
    base_calculo: number;
    valor_integral: number;
    primeira_parcela_valor: number;
    segunda_parcela_valor: number;
    desconto_inss: number;
    desconto_irrf: number;
    primeira_parcela_status: string;
    segunda_parcela_status: string;
}
