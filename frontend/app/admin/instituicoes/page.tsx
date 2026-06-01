"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { Pencil } from "lucide-react";
import { useMemo, useState } from "react";
import { InstituicaoForm } from "@/components/forms/instituicao-form";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { apiFetch, type Instituicao, type PageResponse } from "@/lib/api";

export default function InstituicoesPage() {
  const [editing, setEditing] = useState<Instituicao | null>(null);
  const query = useQuery({
    queryKey: ["instituicoes"],
    queryFn: () => apiFetch<PageResponse<Instituicao>>("/instituicoes?size=100"),
    retry: false
  });
  const instituicoes = query.data?.content ?? [];
  const tipoOptions = useMemo(
    () =>
      Array.from(new Set(instituicoes.map((item) => item.tipo).filter(Boolean))).map((value) => ({
        value,
        label: value
      })),
    [instituicoes]
  );
  const statusOptions = useMemo(
    () =>
      Array.from(new Set(instituicoes.map((item) => item.status).filter(Boolean))).map((value) => ({
        value,
        label: value
      })),
    [instituicoes]
  );
  const filters = useMemo<DataTableFilter<Instituicao>[]>(
    () => [
      { id: "tipo", label: "Tipo", type: "select", placeholder: "Todos os tipos", options: tipoOptions },
      { id: "status", label: "Estado", type: "select", placeholder: "Todos os estados", options: statusOptions }
    ],
    [statusOptions, tipoOptions]
  );
  const columns = useMemo<ColumnDef<Instituicao>[]>(
    () => [
      { accessorKey: "codigo", header: "Codigo" },
      { accessorKey: "nome", header: "Unidade Orcamental" },
      { accessorKey: "tipo", header: "Tipo" },
      { accessorKey: "responsavel", header: "Responsavel" },
      { accessorKey: "status", header: "Estado", cell: ({ row }) => <Badge variant="success">{row.original.status}</Badge> },
      {
        id: "acoes",
        header: "Acoes",
        cell: ({ row }) => (
          <Button size="sm" variant="secondary" onClick={() => setEditing(row.original)}>
            <Pencil className="h-4 w-4" />
            Editar
          </Button>
        )
      }
    ],
    []
  );

  function handleSaved() {
    setEditing(null);
    void query.refetch();
  }

  return (
    <AppShell title="Unidades Orcamentais">
      <div className="space-y-5">
        <InstituicaoForm instituicao={editing} onCancel={() => setEditing(null)} onSaved={handleSaved} />
        <DataTable columns={columns} data={instituicoes} searchPlaceholder="Pesquisar codigo, UO ou responsavel" filters={filters} />
      </div>
    </AppShell>
  );
}
