"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useMemo, useState } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { apiFetch, downloadPostFile, type AuditLog, type AutorizacaoReceitaRetroativa, type PageResponse } from "@/lib/api";
import type { Role } from "@/lib/rbac";
import { formatDate, formatDateTime } from "@/lib/utils";

type SessionUser = {
  role: Role;
};

const auditColumns: ColumnDef<AuditLog>[] = [
  { accessorKey: "createdAt", header: "Data", cell: ({ row }) => formatDateTime(row.original.createdAt) },
  { accessorKey: "usuario", header: "Utilizador" },
  { accessorKey: "acao", header: "Accao" },
  { accessorKey: "entidade", header: "Entidade" },
  { accessorKey: "ipAddress", header: "IP" },
  {
    accessorKey: "severidade",
    header: "Severidade",
    cell: ({ row }) => <Badge variant={row.original.severidade === "CRITICO" ? "danger" : row.original.severidade === "ALERTA" ? "warning" : "info"}>{row.original.severidade}</Badge>
  }
];

export default function AuditoriaPage() {
  const queryClient = useQueryClient();
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const { data: user } = useQuery({
    queryKey: ["auth", "me"],
    queryFn: () => apiFetch<SessionUser>("/auth/me"),
    retry: false
  });
  const isAuditor = user?.role === "AUDITOR";

  const { data: logs } = useQuery({
    queryKey: ["audit-logs"],
    queryFn: () => apiFetch<PageResponse<AuditLog>>("/auditoria/logs?size=100&sort=createdAt,desc"),
    retry: false
  });
  const auditLogs = logs?.content ?? [];

  const { data: autorizacoes } = useQuery({
    queryKey: ["receitas", "autorizacoes-retroativas"],
    queryFn: () => apiFetch<PageResponse<AutorizacaoReceitaRetroativa>>("/receitas/autorizacoes-retroativas?size=100&sort=createdAt,desc"),
    retry: false
  });
  const autorizacaoRows = autorizacoes?.content ?? [];
  const autorizacaoUoOptions = useMemo(
    () =>
      Array.from(new Map(autorizacaoRows.map((item) => [item.codigoUo, `${item.codigoUo} - ${item.instituicao}`])).entries())
        .filter(([value]) => Boolean(value))
        .map(([value, label]) => ({ value, label })),
    [autorizacaoRows]
  );
  const autorizacaoFilters = useMemo<DataTableFilter<AutorizacaoReceitaRetroativa>[]>(
    () => [
      {
        id: "status",
        label: "Estado",
        type: "select",
        placeholder: "Todos os estados",
        options: [
          { value: "PENDENTE", label: "Pendente" },
          { value: "AUTORIZADA", label: "Autorizada" },
          { value: "UTILIZADA", label: "Utilizada" }
        ]
      },
      { id: "codigoUo", label: "UO", type: "select", placeholder: "Todas as UO", options: autorizacaoUoOptions },
      { id: "dataRegistro", label: "Data pretendida", type: "date-range" },
      { id: "createdAt", label: "Pedido", type: "date-range" }
    ],
    [autorizacaoUoOptions]
  );
  const auditFilters = useMemo<DataTableFilter<AuditLog>[]>(
    () => [
      {
        id: "severidade",
        label: "Severidade",
        type: "select",
        placeholder: "Todas",
        options: [
          { value: "INFO", label: "Info" },
          { value: "ALERTA", label: "Alerta" },
          { value: "CRITICO", label: "Critico" }
        ]
      },
      { id: "createdAt", label: "Periodo", type: "date-range" }
    ],
    []
  );

  const { mutate: autorizar, isPending } = useMutation({
    mutationFn: async (id: number) => {
      setErrorMessage(null);
      return downloadPostFile(
        `/receitas/autorizacoes-retroativas/${id}/autorizar`,
        {},
        `autorizacao-receita-retroativa-${id}.pdf`
      );
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["receitas", "autorizacoes-retroativas"] });
      queryClient.invalidateQueries({ queryKey: ["audit-logs"] });
    },
    onError: (error: Error) => {
      setErrorMessage(error.message || "Nao foi possivel autorizar.");
    }
  });

  const autorizacaoColumns = useMemo<ColumnDef<AutorizacaoReceitaRetroativa>[]>(
    () => [
      { accessorKey: "createdAt", header: "Pedido", cell: ({ row }) => formatDateTime(row.original.createdAt) },
      { accessorKey: "codigoUo", header: "UO" },
      { accessorKey: "solicitante", header: "Solicitante" },
      { accessorKey: "dataRegistro", header: "Data", cell: ({ row }) => formatDate(row.original.dataRegistro) },
      { accessorKey: "diasAtraso", header: "Atraso" },
      {
        accessorKey: "status",
        header: "Estado",
        cell: ({ row }) => (
          <Badge variant={row.original.status === "PENDENTE" ? "warning" : row.original.status === "AUTORIZADA" ? "info" : "success"}>
            {row.original.status}
          </Badge>
        )
      },
      {
        id: "acoes",
        header: "Acoes",
        cell: ({ row }) =>
          isAuditor && row.original.status === "PENDENTE" ? (
            <Button size="sm" variant="secondary" disabled={isPending} onClick={() => autorizar(row.original.id)}>
              Autorizar e gerar PDF
            </Button>
          ) : null
      }
    ],
    [autorizar, isAuditor, isPending]
  );

  return (
    <AppShell title="Auditoria">
      <div className="space-y-6">
        <div className="space-y-3">
          <h2 className="text-base font-bold text-institutional-ink">Autorizacoes de receitas retroativas</h2>
          {errorMessage ? (
            <p className="rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red">
              {errorMessage}
            </p>
          ) : null}
          <DataTable
            columns={autorizacaoColumns}
            data={autorizacaoRows}
            searchPlaceholder="Filtrar por UO, solicitante, estado ou data"
            filters={autorizacaoFilters}
          />
        </div>
        <DataTable columns={auditColumns} data={auditLogs} searchPlaceholder="Filtrar por utilizador, accao, entidade, data ou IP" filters={auditFilters} />
      </div>
    </AppShell>
  );
}
