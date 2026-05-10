"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Button } from "@/components/ui/button";
import { FinancialStateBadge } from "@/components/ui/financial-state-badge";
import { apiFetch, type Despesa, type PageResponse } from "@/lib/api";
import { formatCurrency } from "@/lib/utils";

const columns: ColumnDef<Despesa>[] = [
  { accessorKey: "dataRegistro", header: "Data" },
  { accessorKey: "descricao", header: "Descricao" },
  { accessorKey: "codigoClasse", header: "Rubrica" },
  { accessorKey: "valorBruto", header: "Valor", cell: ({ row }) => formatCurrency(row.original.valorBruto) },
  { accessorKey: "estado", header: "Estado", cell: ({ row }) => <FinancialStateBadge state={row.original.estado} /> },
  {
    id: "acoes",
    header: "Accoes",
    cell: () => (
      <div className="flex gap-2">
        <Button size="sm" variant="secondary">Liquidar</Button>
        <Button size="sm" variant="gold">Pagar</Button>
      </div>
    )
  }
];

export default function DespesasPage() {
  const { data } = useQuery({
    queryKey: ["despesas"],
    queryFn: () => apiFetch<PageResponse<Despesa>>("/despesas?size=100"),
    retry: false
  });

  return (
    <AppShell title="Execucao da despesa">
      <DataTable columns={columns} data={data?.content ?? []} searchPlaceholder="Pesquisar despesa" />
    </AppShell>
  );
}
