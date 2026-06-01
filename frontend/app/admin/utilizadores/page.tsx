"use client";

import type { ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { useMemo } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { DataTable, type DataTableFilter } from "@/components/tables/data-table";
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
  const users = data?.content ?? [];
  const uoOptions = useMemo(
    () =>
      Array.from(new Map(users.map((item) => [item.codigoUo, `${item.codigoUo} - ${item.instituicao}`])).entries())
        .filter(([value]) => Boolean(value))
        .map(([value, label]) => ({ value, label })),
    [users]
  );
  const filters = useMemo<DataTableFilter<User>[]>(
    () => [
      {
        id: "role",
        label: "Perfil",
        type: "select",
        placeholder: "Todos os perfis",
        options: [
          { value: "ADMIN", label: "Admin" },
          { value: "GESTOR", label: "Gestor" },
          { value: "AUDITOR", label: "Auditor" }
        ]
      },
      {
        id: "status",
        label: "Estado",
        type: "select",
        placeholder: "Todos os estados",
        options: [
          { value: "ATIVO", label: "Ativo" },
          { value: "INATIVO", label: "Inativo" }
        ]
      },
      { id: "codigoUo", label: "UO", type: "select", placeholder: "Todas as UO", options: uoOptions }
    ],
    [uoOptions]
  );

  return (
    <AppShell title="Utilizadores e perfis">
      <DataTable columns={columns} data={users} searchPlaceholder="Pesquisar nome, email ou UO" filters={filters} />
    </AppShell>
  );
}
