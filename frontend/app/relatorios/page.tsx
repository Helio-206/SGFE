"use client";

import { CalendarDays, Download, FileSpreadsheet, FileText, Loader2, ShieldCheck } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { useMemo, useState } from "react";
import { AppShell } from "@/components/layout/app-shell";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { apiFetch, downloadFile } from "@/lib/api";
import type { Role } from "@/lib/rbac";

const reports = [
  {
    title: "Resumo financeiro",
    description: "Saldos, receitas, pagamentos e execucao do exercicio corrente.",
    icon: FileText,
    action: "Exportar PDF",
    path: "/relatorios/exportar/resumo-financeiro.pdf",
    file: "resumo-financeiro.pdf",
    format: "PDF",
    roles: ["ADMIN", "GESTOR", "AUDITOR"] as Role[]
  },
  {
    title: "Despesa por natureza",
    description: "Mapa analitico de despesas pagas por rubrica economica.",
    icon: FileText,
    action: "Exportar PDF",
    path: "/relatorios/exportar/despesa-por-natureza.pdf",
    file: "despesa-por-natureza.pdf",
    format: "PDF",
    roles: ["ADMIN", "GESTOR", "AUDITOR"] as Role[]
  },
  {
    title: "Mapa de receitas RUPE",
    description: "Lista de arrecadacoes RUPE com periodo parametrizavel.",
    icon: FileSpreadsheet,
    action: "Exportar Excel",
    path: "/relatorios/exportar/receitas-rupe.xlsx",
    file: "receitas-rupe.xlsx",
    format: "XLSX",
    roles: ["ADMIN", "GESTOR", "AUDITOR"] as Role[]
  },
  {
    title: "Auditoria operacional",
    description: "Entradas, accoes executadas, resultado, severidade, UO e IP de origem.",
    icon: ShieldCheck,
    action: "Exportar PDF",
    path: "/relatorios/exportar/auditoria-operacional.pdf",
    file: "auditoria-operacional.pdf",
    format: "PDF",
    roles: ["ADMIN", "AUDITOR"] as Role[]
  }
];

type SessionUser = {
  role: Role;
};

export default function RelatoriosPage() {
  const currentYear = new Date().getFullYear();
  const [inicio, setInicio] = useState(`${currentYear}-01-01`);
  const [fim, setFim] = useState(new Date().toISOString().slice(0, 10));
  const [pending, setPending] = useState<string | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const { data: user } = useQuery({
    queryKey: ["auth", "me"],
    queryFn: () => apiFetch<SessionUser>("/auth/me"),
    retry: false
  });
  const visibleReports = useMemo(
    () => reports.filter((item) => item.roles.includes("GESTOR") || (user?.role ? item.roles.includes(user.role) : false)),
    [user?.role]
  );

  async function downloadReport(path: string, fileName: string) {
    const query = path.endsWith(".xlsx") || path.includes("auditoria-operacional") ? buildQuery(inicio, fim) : "";
    setPending(fileName);
    setErrorMessage(null);
    try {
      await downloadFile(`${path}${query}`, fileName);
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Nao foi possivel exportar o relatorio.");
    } finally {
      setPending(null);
    }
  }

  return (
    <AppShell title="Relatorios financeiros">
      <div className="space-y-5">
        <div className="rounded-lg border border-border/80 bg-surface/95 p-4 shadow-quiet">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <div className="flex items-center gap-2 text-xs font-semibold uppercase text-muted-foreground">
                <CalendarDays className="h-4 w-4 text-institutional-gold" />
                Periodo das receitas RUPE e auditoria
              </div>
              <div className="mt-3 grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="relatorio-inicio">Inicio</Label>
                  <Input id="relatorio-inicio" type="date" value={inicio} onChange={(event) => setInicio(event.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="relatorio-fim">Fim</Label>
                  <Input id="relatorio-fim" type="date" value={fim} onChange={(event) => setFim(event.target.value)} />
                </div>
              </div>
            </div>
            {errorMessage ? (
              <p className="rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red">
                {errorMessage}
              </p>
            ) : null}
          </div>
        </div>

        <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
          {visibleReports.map((item) => (
            <Card key={item.title} className="overflow-hidden">
              <CardHeader className="flex flex-row items-start justify-between gap-3">
                <div>
                  <CardTitle>{item.title}</CardTitle>
                  <p className="mt-2 text-sm leading-6 text-muted-foreground">{item.description}</p>
                </div>
                <Badge variant={item.format === "PDF" ? "danger" : "success"}>{item.format}</Badge>
              </CardHeader>
              <CardContent>
                <div className="flex items-center justify-between gap-4">
                  <div className="flex h-11 w-11 items-center justify-center rounded-lg bg-institutional-mist text-institutional-red">
                    <item.icon className="h-5 w-5" />
                  </div>
                  <Button variant="secondary" onClick={() => downloadReport(item.path, item.file)} disabled={pending === item.file}>
                    {pending === item.file ? <Loader2 className="h-4 w-4 animate-spin" /> : <Download className="h-4 w-4" />}
                    {item.action}
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </AppShell>
  );
}

function buildQuery(inicio: string, fim: string) {
  const params = new URLSearchParams();
  if (inicio) {
    params.set("inicio", inicio);
  }
  if (fim) {
    params.set("fim", fim);
  }

  const query = params.toString();
  return query ? `?${query}` : "";
}
