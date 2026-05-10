export type FinancialState =
  | "PENDENTE_CABIMENTADA"
  | "LIQUIDADA_APROVADA"
  | "PAGA"
  | "REJEITADA"
  | "CANCELADA"
  | "EM_ANALISE";

export const financialStateMeta: Record<FinancialState, { label: string; description: string; className: string }> = {
  PENDENTE_CABIMENTADA: {
    label: "Cabimentada",
    description: "Despesa reservada no tecto orcamental.",
    className: "border-amber-200 bg-amber-50 text-amber-800"
  },
  LIQUIDADA_APROVADA: {
    label: "Liquidada",
    description: "Obrigacao verificada e aprovada para pagamento.",
    className: "border-blue-200 bg-blue-50 text-blue-800"
  },
  PAGA: {
    label: "Paga",
    description: "Pagamento registado.",
    className: "border-emerald-200 bg-emerald-50 text-emerald-800"
  },
  REJEITADA: {
    label: "Rejeitada",
    description: "Operacao recusada por regra de negocio ou controlo.",
    className: "border-red-200 bg-red-50 text-red-800"
  },
  CANCELADA: {
    label: "Cancelada",
    description: "Despesa anulada sem compromisso activo.",
    className: "border-zinc-200 bg-zinc-100 text-zinc-700"
  },
  EM_ANALISE: {
    label: "Em analise",
    description: "Operacao em verificacao tecnica.",
    className: "border-sky-200 bg-sky-50 text-sky-800"
  }
};
