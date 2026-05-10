"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { apiFetch, type Classificacao, type PageResponse } from "@/lib/api";

const columns: ColumnDef<Classificacao>[] = [
  { accessorKey: "codigo", header: "Codigo" },
  { accessorKey: "descricao", header: "Descricao" },
  { accessorKey: "tipo", header: "Tipo", cell: ({ row }) => <Badge variant="gold">{row.original.tipo}</Badge> }
];

export default function ClassificacoesPage() {
  const { data } = useQuery({
    queryKey: ["classificacoes"],
    queryFn: () => apiFetch<PageResponse<Classificacao>>("/classificacoes?size=100"),
    retry: false
  });

  return (
    <AppShell title="Classificacoes Economicas">
      <DataTable columns={columns} data={data?.content ?? []} searchPlaceholder="Pesquisar classificacao" />
    </AppShell>
  );
}
