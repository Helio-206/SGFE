"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useMemo, useState } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
import { Button } from "@/components/ui/button";
import { DespesaForm } from "@/components/forms/despesa-form";
import { FinancialStateBadge } from "@/components/ui/financial-state-badge";
import { apiFetch, type Despesa, type PageResponse } from "@/lib/api";
import { formatCurrency, formatDate } from "@/lib/utils";


export default function DespesasPage() {
  const queryClient = useQueryClient();
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);
  const { data } = useQuery({
    queryKey: ["despesas"],
    queryFn: () => apiFetch<PageResponse<Despesa>>("/despesas?size=100"),
    retry: false
  });
  const despesas = data?.content ?? [];
  const uoOptions = useMemo(
    () =>
      Array.from(new Map(despesas.map((item) => [item.codigoUo, `${item.codigoUo} - ${item.instituicao}`])).entries())
        .filter(([value]) => Boolean(value))
        .map(([value, label]) => ({ value, label })),
    [despesas]
  );

  const { mutate: liquidar } = useMutation({
    mutationFn: async (id: number) => {
      setMessage(null);
      return apiFetch<Despesa>(`/despesas/${id}/liquidar`, { method: "POST" });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["despesas"] });
      setMessage({ type: "success", text: "Despesa liquidada com sucesso." });
    },
    onError: (error: Error) => {
      setMessage({ type: "error", text: error.message || "Nao foi possivel liquidar a despesa." });
    },
  });

  const { mutate: pagar } = useMutation({
    mutationFn: async (id: number) => {
      setMessage(null);
      return apiFetch<Despesa>(`/despesas/${id}/pagar`, { method: "POST" });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["despesas"] });
      setMessage({ type: "success", text: "Despesa paga com sucesso." });
    },
    onError: (error: Error) => {
      setMessage({ type: "error", text: error.message || "Nao foi possivel pagar a despesa." });
    },
  });

  const columns: ColumnDef<Despesa>[] = [
    { accessorKey: "dataRegistro", header: "Data", cell: ({ row }) => formatDate(row.original.dataRegistro) },
    { accessorKey: "descricao", header: "Descricao" },
    { accessorKey: "codigoClasse", header: "Rubrica" },
    { accessorKey: "valorBruto", header: "Valor", cell: ({ row }) => formatCurrency(row.original.valorBruto) },
    { accessorKey: "estado", header: "Estado", cell: ({ row }) => <FinancialStateBadge state={row.original.estado} /> },
    {
      id: "acoes",
      header: "Accoes",
      cell: ({ row }) => {
        const despesa = row.original;
        return (
          <div className="flex gap-2">
            <Button 
              size="sm" 
              variant="secondary"
              disabled={despesa.estado !== "PENDENTE_CABIMENTADA"}
              onClick={() => liquidar(despesa.id)}
            >
              Liquidar
            </Button>
            <Button 
              size="sm" 
              variant="gold"
              disabled={despesa.estado !== "LIQUIDADA_APROVADA"}
              onClick={() => pagar(despesa.id)}
            >
              Pagar
            </Button>
          </div>
        );
      }
    }
  ];
  const filters = useMemo<DataTableFilter<Despesa>[]>(
    () => [
      {
        id: "estado",
        label: "Estado",
        type: "select",
        placeholder: "Todos os estados",
        options: [
          { value: "EM_ANALISE", label: "Em analise" },
          { value: "PENDENTE_CABIMENTADA", label: "Pendente cabimentada" },
          { value: "LIQUIDADA_APROVADA", label: "Liquidada aprovada" },
          { value: "PAGA", label: "Paga" },
          { value: "REJEITADA", label: "Rejeitada" },
          { value: "CANCELADA", label: "Cancelada" }
        ]
      },
      { id: "codigoUo", label: "UO", type: "select", placeholder: "Todas as UO", options: uoOptions },
      { id: "dataRegistro", label: "Periodo", type: "date-range" },
      { id: "valorBruto", label: "Valor AOA", type: "number-range", minPlaceholder: "Min.", maxPlaceholder: "Max." }
    ],
    [uoOptions]
  );

  return (
    <AppShell title="Execucao da despesa">
      <div className="space-y-4">
        <DespesaForm />
        {message ? (
          <p className={message.type === "success" ? "rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" : "rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red"}>
            {message.text}
          </p>
        ) : null}
        <DataTable columns={columns} data={despesas} searchPlaceholder="Pesquisar descricao, rubrica ou UO" filters={filters} />
      </div>
    </AppShell>
  );
}
