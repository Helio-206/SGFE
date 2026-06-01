"use client";

import { useQuery } from "@tanstack/react-query";
import { Clock3, ShieldCheck } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { apiFetch, type AuditLog, type PageResponse } from "@/lib/api";
import { formatDateTime } from "@/lib/utils";

export function RecentAudit() {
  const { data, isError } = useQuery({
    queryKey: ["dashboard", "audit-logs"],
    queryFn: () => apiFetch<PageResponse<AuditLog>>("/auditoria/logs?size=5&sort=createdAt,desc"),
    retry: false
  });
  const items = data?.content ?? [];

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between gap-4">
        <div>
          <CardTitle>Auditoria recente</CardTitle>
          <p className="mt-1 text-sm text-muted-foreground">Ultimos eventos</p>
        </div>
        <div className="rounded-md bg-institutional-mist p-2 text-institutional-blue">
          <Clock3 className="h-5 w-5" />
        </div>
      </CardHeader>
      <CardContent>
        {items.length ? (
          <div className="divide-y divide-border/75">
            {items.map((item) => (
              <div key={item.id} className="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                <div className="flex min-w-0 gap-3">
                  <div className="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-surface-muted text-institutional-blue">
                    <ShieldCheck className="h-4 w-4" />
                  </div>
                  <div className="min-w-0">
                    <div className="truncate text-sm font-semibold text-institutional-ink" title={item.acao}>
                      {item.acao}
                    </div>
                    <div className="mt-1 text-xs text-muted-foreground">
                      {item.usuario ?? "Sistema"} | {formatDateTime(item.createdAt)}
                    </div>
                  </div>
                </div>
                <div className="min-w-0">
                  <Badge variant={item.severidade === "CRITICO" ? "danger" : item.severidade === "ALERTA" ? "warning" : "info"}>
                    {item.entidade ?? item.severidade}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="rounded-md border border-dashed border-border bg-surface-muted/60 p-6 text-center text-sm text-muted-foreground">
            {isError ? "Auditoria indisponivel para este perfil." : "Sem eventos recentes."}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
