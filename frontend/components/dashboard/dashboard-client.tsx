"use client";

import { useQuery } from "@tanstack/react-query";
import { Building2, CircleGauge, Landmark, LineChart, ReceiptText, ShieldCheck, WalletCards } from "lucide-react";
import { BudgetOverview } from "@/components/dashboard/budget-overview";
import { KpiCard } from "@/components/dashboard/kpi-card";
import { RecentAudit } from "@/components/dashboard/recent-audit";
import { RiskPanel } from "@/components/dashboard/risk-panel";
import { apiFetch, emptyDashboard, type DashboardData } from "@/lib/api";
import { cn, formatCurrencyShort, formatPercent } from "@/lib/utils";

export function DashboardClient({ mode = "nacional" }: { mode?: "nacional" | "uo" }) {
  const { data } = useQuery({
    queryKey: ["dashboard"],
    queryFn: () => apiFetch<DashboardData>("/dashboard"),
    retry: false
  });
  const dashboard = data ?? { ...emptyDashboard, contexto: mode === "uo" ? "UO" : "NACIONAL" };
  const executionPercent = Math.max(0, Math.min(dashboard.percentualExecucao, 100));
  const saldo = Math.max(dashboard.tectoTotal - dashboard.valorComprometido, 0);
  const paidPercent = dashboard.valorComprometido > 0 ? (dashboard.valorPago / dashboard.valorComprometido) * 100 : 0;
  const revenuePercent = dashboard.tectoTotal > 0 ? (dashboard.totalReceita / dashboard.tectoTotal) * 100 : 0;
  const scopeLabel = dashboard.contexto === "UO" ? "Unidade Orcamental" : "Consolidado nacional";
  const pressure =
    executionPercent >= 95 ? "Limite pressionado" : executionPercent >= 75 ? "Acompanhar" : "Dentro do tecto";
  const pressureTone =
    executionPercent >= 95
      ? "border-red-200 bg-red-50 text-red-900"
      : executionPercent >= 75
        ? "border-amber-200 bg-amber-50 text-amber-900"
        : "border-emerald-200 bg-emerald-50 text-emerald-900";

  return (
    <div className="space-y-5">
      <section className="overflow-hidden rounded-lg border border-border/80 bg-surface/95 shadow-quiet">
        <div className="grid gap-0 xl:grid-cols-[minmax(0,1fr)_340px]">
          <div className="p-5 md:p-6">
            <div className="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
              <div>
                <div className="flex flex-wrap items-center gap-2">
                  <span className="inline-flex items-center gap-2 rounded-md border border-institutional-blue/20 bg-institutional-mist px-3 py-1 text-xs font-bold uppercase text-institutional-blue">
                    <Building2 className="h-3.5 w-3.5" />
                    {scopeLabel}
                  </span>
                  <span className="rounded-md border border-border/80 bg-surface-strong/80 px-3 py-1 text-xs font-semibold text-muted-foreground">
                    Exercicio {dashboard.anoFiscal}
                  </span>
                  <span className={cn("rounded-md border px-3 py-1 text-xs font-semibold", pressureTone)}>
                    {pressure}
                  </span>
                </div>
                <h2 className="mt-4 text-2xl font-bold leading-tight text-institutional-ink md:text-3xl">
                  Painel financeiro
                </h2>
              </div>
              <div className="rounded-md border border-border/80 bg-surface-muted/50 px-3 py-2 text-xs text-muted-foreground">
                <div className="font-semibold uppercase text-institutional-ink">Hoje</div>
                <div>{new Intl.DateTimeFormat("pt-AO", { dateStyle: "medium" }).format(new Date())}</div>
              </div>
            </div>

            <div className="mt-6 grid gap-3 md:grid-cols-3">
              {[
                { label: "Tecto aprovado", value: formatCurrencyShort(dashboard.tectoTotal) },
                { label: "Comprometido", value: formatCurrencyShort(dashboard.valorComprometido) },
                { label: "Disponivel", value: formatCurrencyShort(saldo) }
              ].map((item) => (
                <div key={item.label} className="rounded-md border border-border/80 bg-surface-strong/70 p-3">
                  <div className="text-[11px] font-bold uppercase text-muted-foreground">{item.label}</div>
                  <div className="mt-2 min-w-0 break-words text-lg font-bold tabular-nums text-institutional-ink">{item.value}</div>
                </div>
              ))}
            </div>
          </div>

          <div className="border-t border-border bg-[linear-gradient(145deg,#071b33_0%,#0d2f50_58%,#0e6b6b_100%)] p-5 text-white xl:border-l xl:border-t-0">
            <div className="flex items-center justify-between gap-3">
              <div className="text-xs font-bold uppercase text-institutional-gold">Execucao</div>
              <CircleGauge className="h-5 w-5 text-institutional-gold" />
            </div>
            <div className="mt-5 text-5xl font-bold tabular-nums tracking-normal">{formatPercent(executionPercent)}</div>
            <div className="mt-5 h-2 overflow-hidden rounded-full bg-white/10">
              <div className="h-full rounded-full bg-institutional-gold" style={{ width: `${executionPercent}%` }} />
            </div>
            <div className="mt-5 grid grid-cols-2 gap-3">
              {[
                { label: "Risco", value: dashboard.riscoOrcamental },
                { label: "Pago", value: formatPercent(Math.max(0, Math.min(paidPercent, 100))) }
              ].map((item) => (
                <div key={item.label} className="rounded-md border border-white/10 bg-white/10 p-3">
                  <div className="text-[11px] font-semibold uppercase text-slate-300">{item.label}</div>
                  <div className="mt-1 truncate text-sm font-bold text-white">{item.value}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <div className="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
        <KpiCard title="Tecto orcamental" value={formatCurrencyShort(dashboard.tectoTotal)} detail={`Exercicio ${dashboard.anoFiscal}`} icon={Landmark} progress={100} />
        <KpiCard title="Comprometido" value={formatCurrencyShort(dashboard.valorComprometido)} detail={formatPercent(executionPercent)} icon={WalletCards} tone="gold" progress={executionPercent} />
        <KpiCard title="Pago" value={formatCurrencyShort(dashboard.valorPago)} detail={formatPercent(Math.max(0, Math.min(paidPercent, 100)))} icon={LineChart} tone="green" progress={paidPercent} />
        <KpiCard title="Receita RUPE" value={formatCurrencyShort(dashboard.totalReceita)} detail={formatPercent(Math.max(0, Math.min(revenuePercent, 100)))} icon={ReceiptText} tone="teal" progress={revenuePercent} />
      </div>

      <div className="grid gap-5 2xl:grid-cols-[minmax(0,1.4fr)_360px]">
        <BudgetOverview
          tecto={dashboard.tectoTotal}
          comprometido={dashboard.valorComprometido}
          pago={dashboard.valorPago}
          receita={dashboard.totalReceita}
          topUos={dashboard.topUos}
        />
        <div className="grid gap-5 xl:grid-cols-2 2xl:grid-cols-1">
          <RiskPanel risk={dashboard.riscoOrcamental} />
          <section className="rounded-lg border border-border/80 bg-surface/95 p-4 shadow-quiet">
            <div className="flex items-center justify-between gap-3">
              <div>
                <h3 className="text-sm font-bold text-institutional-ink">Controlo</h3>
                <p className="mt-1 text-xs text-muted-foreground">Sessao operacional</p>
              </div>
              <ShieldCheck className="h-5 w-5 text-institutional-gold" />
            </div>
            <div className="mt-4 space-y-3">
              <div className="flex items-center justify-between border-b border-border/70 pb-3 text-sm">
                <span className="text-muted-foreground">Contexto</span>
                <span className="font-semibold text-institutional-ink">{scopeLabel}</span>
              </div>
              <div className="flex items-center justify-between border-b border-border/70 pb-3 text-sm">
                <span className="text-muted-foreground">UOs em foco</span>
                <span className="font-semibold tabular-nums text-institutional-ink">{dashboard.topUos.length}</span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">Saldo</span>
                <span className="font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(saldo)}</span>
              </div>
            </div>
          </section>
        </div>
      </div>

      <RecentAudit />
    </div>
  );
}
