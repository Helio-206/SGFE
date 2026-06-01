"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { useMemo } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { OrcamentoForm } from "@/components/forms/orcamento-form";
import { apiFetch, type Orcamento, type PageResponse } from "@/lib/api";
import { formatCurrency } from "@/lib/utils";

const columns: ColumnDef<Orcamento>[] = [
  { accessorKey: "codigoUo", header: "UO" },
  { accessorKey: "instituicao", header: "Instituicao" },
  { accessorKey: "anoFiscal", header: "Ano fiscal" },
  { accessorKey: "valorTotal", header: "Tecto", cell: ({ row }) => formatCurrency(row.original.valorTotal) },
  { accessorKey: "valorComprometido", header: "Comprometido", cell: ({ row }) => formatCurrency(row.original.valorComprometido) },
  { accessorKey: "saldoDisponivel", header: "Saldo", cell: ({ row }) => <Badge variant="info">{formatCurrency(row.original.saldoDisponivel)}</Badge> }
];

export default function OrcamentosPage() {
  const { data } = useQuery({
    queryKey: ["orcamentos"],
    queryFn: () => apiFetch<PageResponse<Orcamento>>("/orcamentos?size=100"),
    retry: false
  });
  const orcamentos = data?.content ?? [];
  const anoOptions = useMemo(
    () =>
      Array.from(new Set(orcamentos.map((item) => String(item.anoFiscal)).filter(Boolean)))
        .sort((a, b) => Number(b) - Number(a))
        .map((value) => ({ value, label: value })),
    [orcamentos]
  );
  const uoOptions = useMemo(
    () =>
      Array.from(new Map(orcamentos.map((item) => [item.codigoUo, `${item.codigoUo} - ${item.instituicao}`])).entries())
        .filter(([value]) => Boolean(value))
        .map(([value, label]) => ({ value, label })),
    [orcamentos]
  );
  const filters = useMemo<DataTableFilter<Orcamento>[]>(
    () => [
      { id: "anoFiscal", label: "Ano", type: "select", placeholder: "Todos os anos", options: anoOptions },
      { id: "codigoUo", label: "UO", type: "select", placeholder: "Todas as UO", options: uoOptions },
      { id: "saldoDisponivel", label: "Saldo AOA", type: "number-range", minPlaceholder: "Min.", maxPlaceholder: "Max." }
    ],
    [anoOptions, uoOptions]
  );

  return (
    <AppShell title="Tectos Orcamentais">
      <div className="space-y-4">
        <OrcamentoForm />
        <DataTable columns={columns} data={orcamentos} searchPlaceholder="Pesquisar UO ou instituicao" filters={filters} />
      </div>
    </AppShell>
  );
}
