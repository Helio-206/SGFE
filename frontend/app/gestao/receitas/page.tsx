"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { apiFetch, type PageResponse, type Receita } from "@/lib/api";
import { formatCurrency } from "@/lib/utils";

const columns: ColumnDef<Receita>[] = [
  { accessorKey: "dataRegistro", header: "Data" },
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

  return (
    <AppShell title="Receitas RUPE">
      <DataTable columns={columns} data={data?.content ?? []} searchPlaceholder="Pesquisar receita" />
    </AppShell>
  );
}
