"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { apiFetch, type AuditLog, type PageResponse } from "@/lib/api";

const columns: ColumnDef<AuditLog>[] = [
  { accessorKey: "createdAt", header: "Data" },
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
  const { data } = useQuery({
    queryKey: ["audit-logs"],
    queryFn: () => apiFetch<PageResponse<AuditLog>>("/auditoria/logs?size=100&sort=createdAt,desc"),
    retry: false
  });

  return (
    <AppShell title="Auditoria">
      <DataTable columns={columns} data={data?.content ?? []} searchPlaceholder="Filtrar por utilizador, accao, entidade, data ou IP" />
    </AppShell>
  );
}
