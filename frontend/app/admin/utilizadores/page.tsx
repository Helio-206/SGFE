"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable } from "@/components/tables/data-table";
import { Badge } from "@/components/ui/badge";
import { apiFetch, type PageResponse, type User } from "@/lib/api";

const columns: ColumnDef<User>[] = [
  { accessorKey: "nome", header: "Nome" },
  { accessorKey: "email", header: "Email" },
  { accessorKey: "role", header: "Perfil", cell: ({ row }) => <Badge variant="gold">{row.original.role}</Badge> },
  { accessorKey: "codigoUo", header: "UO" },
  { accessorKey: "status", header: "Estado", cell: ({ row }) => <Badge variant={row.original.status === "ATIVO" ? "success" : "danger"}>{row.original.status}</Badge> }
];

export default function UtilizadoresPage() {
  const { data } = useQuery({
    queryKey: ["users"],
    queryFn: () => apiFetch<PageResponse<User>>("/users?size=100"),
    retry: false
  });

  return (
    <AppShell title="Utilizadores e perfis">
      <DataTable columns={columns} data={data?.content ?? []} searchPlaceholder="Pesquisar utilizador" />
    </AppShell>
  );
}
