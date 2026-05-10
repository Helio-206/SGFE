"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { InstituicaoForm } from "@/components/forms/instituicao-form";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { apiFetch, type Instituicao, type PageResponse } from "@/lib/api";

const columns: ColumnDef<Instituicao>[] = [
  { accessorKey: "codigo", header: "Codigo" },
  { accessorKey: "nome", header: "Unidade Orcamental" },
  { accessorKey: "tipo", header: "Tipo" },
  { accessorKey: "responsavel", header: "Responsavel" },
  { accessorKey: "status", header: "Estado", cell: ({ row }) => <Badge variant="success">{row.original.status}</Badge> }
];

export default function InstituicoesPage() {
  const query = useQuery({
    queryKey: ["instituicoes"],
    queryFn: () => apiFetch<PageResponse<Instituicao>>("/instituicoes?size=100"),
    retry: false
  });

  return (
    <AppShell title="Unidades Orcamentais">
      <div className="space-y-5">
        <InstituicaoForm onSaved={() => query.refetch()} />
        <DataTable columns={columns} data={query.data?.content ?? []} searchPlaceholder="Pesquisar UO" />
      </div>
    </AppShell>
  );
}
