"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { useMemo } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { ReceitaForm } from "@/components/forms/receita-form";
import { apiFetch, type PageResponse, type Receita } from "@/lib/api";
import { formatCurrency, formatDate } from "@/lib/utils";

const columns: ColumnDef<Receita>[] = [
  { accessorKey: "dataRegistro", header: "Data", cell: ({ row }) => formatDate(row.original.dataRegistro) },
  { accessorKey: "codigoRupe", header: "RUPE", cell: ({ row }) => <span className="font-mono text-xs text-institutional-blue">{row.original.codigoRupe}</span> },
  { accessorKey: "fonteReceita", header: "Fonte", cell: ({ row }) => <Badge variant="info">{row.original.fonteReceita}</Badge> },
  { accessorKey: "codigoClasse", header: "Rubrica" },
  { accessorKey: "valorArrecadado", header: "Valor", cell: ({ row }) => formatCurrency(row.original.valorArrecadado) }
];

export default function ReceitasPage() {
  const { data } = useQuery({
    queryKey: ["receitas"],
    queryFn: () => apiFetch<PageResponse<Receita>>("/receitas?size=100"),
    retry: false
  });
  const receitas = data?.content ?? [];
  const uoOptions = useMemo(
    () =>
      Array.from(new Map(receitas.map((item) => [item.codigoUo, `${item.codigoUo} - ${item.instituicao}`])).entries())
        .filter(([value]) => Boolean(value))
        .map(([value, label]) => ({ value, label })),
    [receitas]
  );
  const filters = useMemo<DataTableFilter<Receita>[]>(
    () => [
      {
        id: "fonteReceita",
        label: "Fonte",
        type: "select",
        placeholder: "Todas as fontes",
        options: [
          { value: "PETROLIFERA", label: "Petrolifera" },
          { value: "NAO_PETROLIFERA", label: "Nao petrolifera" },
          { value: "PATRIMONIAL", label: "Patrimonial" }
        ]
      },
      { id: "codigoUo", label: "UO", type: "select", placeholder: "Todas as UO", options: uoOptions },
      { id: "dataRegistro", label: "Periodo", type: "date-range" },
      { id: "valorArrecadado", label: "Valor AOA", type: "number-range", minPlaceholder: "Min.", maxPlaceholder: "Max." }
    ],
    [uoOptions]
  );

  return (
    <AppShell title="Receitas RUPE">
      <div className="space-y-4">
        <ReceitaForm />
        <DataTable columns={columns} data={receitas} searchPlaceholder="Pesquisar RUPE, rubrica ou UO" filters={filters} />
      </div>
    </AppShell>
  );
}
