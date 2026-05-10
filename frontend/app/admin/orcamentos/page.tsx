"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
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

  return (
    <AppShell title="Tectos Orcamentais">
      <DataTable columns={columns} data={data?.content ?? []} searchPlaceholder="Pesquisar orcamento" />
    </AppShell>
  );
}
