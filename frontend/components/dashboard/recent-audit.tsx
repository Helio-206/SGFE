"use client";

import { useQuery } from "@tanstack/react-query";
import { ShieldCheck } from "lucide-react";
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
      <CardHeader>
        <CardTitle>Ultimas acoes auditaveis</CardTitle>
        <p className="mt-1 text-sm text-muted-foreground">Eventos recentes registados para controlo e rastreabilidade.</p>
      </CardHeader>
      <CardContent className="space-y-3">
        {items.length ? (
          items.map((item) => (
            <div key={item.id} className="flex items-center justify-between gap-4 rounded-md border border-border bg-slate-50/60 p-3">
              <div className="flex min-w-0 items-center gap-3">
                <ShieldCheck className="h-4 w-4 shrink-0 text-institutional-gold" />
                <div className="min-w-0">
                  <div className="truncate text-sm font-semibold text-institutional-ink" title={item.acao}>
                    {item.acao}
                  </div>
                  <div className="text-xs text-muted-foreground">
                    {item.usuario ?? "Sistema"} | {formatDateTime(item.createdAt)}
                  </div>
                </div>
              </div>
              <Badge variant={item.severidade === "CRITICO" ? "danger" : item.severidade === "ALERTA" ? "warning" : "info"}>
                {item.entidade ?? item.severidade}
              </Badge>
            </div>
          ))
        ) : (
          <div className="rounded-md border border-dashed border-border bg-slate-50/70 p-6 text-center text-sm text-muted-foreground">
            {isError ? "Sem permissao para consultar auditoria neste perfil." : "Ainda nao ha eventos recentes para apresentar."}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
