"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { Pencil, Trash2 } from "lucide-react";
import { useMemo, useState } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { ClassificacaoForm } from "@/components/forms/classificacao-form";
import { apiFetch, type Classificacao, type PageResponse } from "@/lib/api";

export default function ClassificacoesPage() {
  const queryClient = useQueryClient();
  const [editing, setEditing] = useState<Classificacao | null>(null);
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);
  const { data } = useQuery({
    queryKey: ["classificacoes"],
    queryFn: () => apiFetch<PageResponse<Classificacao>>("/classificacoes?size=100"),
    retry: false
  });
  const classificacoes = data?.content ?? [];
  const tipoOptions = useMemo(
    () =>
      Array.from(new Set(classificacoes.map((item) => item.tipo).filter(Boolean))).map((value) => ({
        value,
        label: value
      })),
    [classificacoes]
  );
  const filters = useMemo<DataTableFilter<Classificacao>[]>(
    () => [{ id: "tipo", label: "Tipo", type: "select", placeholder: "Todos os tipos", options: tipoOptions }],
    [tipoOptions]
  );

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiFetch<void>(`/classificacoes/${id}`, { method: "DELETE" }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["classificacoes"] });
      setEditing(null);
      setMessage({ type: "success", text: "Classificacao removida com sucesso." });
    },
    onError: (error: Error) => {
      setMessage({ type: "error", text: error.message || "Nao foi possivel remover a classificacao." });
    }
  });

  const columns = useMemo<ColumnDef<Classificacao>[]>(
    () => [
      { accessorKey: "codigo", header: "Codigo" },
      { accessorKey: "descricao", header: "Descricao" },
      { accessorKey: "tipo", header: "Tipo", cell: ({ row }) => <Badge variant="gold">{row.original.tipo}</Badge> },
      {
        id: "acoes",
        header: "Acoes",
        cell: ({ row }) => (
          <div className="flex flex-wrap gap-2">
            <Button type="button" variant="secondary" size="sm" onClick={() => setEditing(row.original)}>
              <Pencil className="h-4 w-4" />
              Editar
            </Button>
            <Button
              type="button"
              variant="danger"
              size="sm"
              disabled={deleteMutation.isPending}
              onClick={() => {
                if (window.confirm("Remover esta classificação económica?")) {
                  deleteMutation.mutate(row.original.id);
                }
              }}
            >
              <Trash2 className="h-4 w-4" />
              Remover
            </Button>
          </div>
        )
      }
    ],
    [deleteMutation]
  );

  return (
    <AppShell title="Classificacoes Economicas">
      <div className="space-y-4">
        <ClassificacaoForm classificacao={editing} onCancel={() => setEditing(null)} onSuccess={() => setEditing(null)} />
        {message ? (
          <p className={message.type === "success" ? "rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" : "rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red"}>
            {message.text}
          </p>
        ) : null}
        <DataTable columns={columns} data={classificacoes} searchPlaceholder="Pesquisar codigo ou descricao" filters={filters} />
      </div>
    </AppShell>
  );
}
