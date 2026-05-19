"use client";

import { useQuery } from "@tanstack/react-query";
import { Activity, Landmark, LineChart, ReceiptText, WalletCards } from "lucide-react";
import { BudgetOverview } from "@/components/dashboard/budget-overview";
import { KpiCard } from "@/components/dashboard/kpi-card";
import { RecentAudit } from "@/components/dashboard/recent-audit";
import { RiskPanel } from "@/components/dashboard/risk-panel";
import { apiFetch, emptyDashboard, type DashboardData } from "@/lib/api";
import { formatCurrencyShort, formatPercent } from "@/lib/utils";

export function DashboardClient({ mode = "nacional" }: { mode?: "nacional" | "uo" }) {
  const { data } = useQuery({
    queryKey: ["dashboard"],
    queryFn: () => apiFetch<DashboardData>("/dashboard"),
    retry: false
  });
  const dashboard = data ?? { ...emptyDashboard, contexto: mode === "uo" ? "UO" : "NACIONAL" };
  const executionPercent = Math.max(0, Math.min(dashboard.percentualExecucao, 100));
  const saldo = Math.max(dashboard.tectoTotal - dashboard.valorComprometido, 0);

  return (
    <div className="space-y-5">
      <section className="overflow-hidden rounded-lg border border-institutional-blue/15 bg-white shadow-line">
        <div className="grid gap-0 xl:grid-cols-[1fr_360px]">
          <div className="p-5 md:p-6">
            <div className="flex flex-wrap items-center gap-2">
              <span className="inline-flex items-center gap-2 rounded-md border border-institutional-blue/15 bg-institutional-mist px-3 py-1 text-xs font-bold uppercase text-institutional-blue">
                <Activity className="h-3.5 w-3.5" />
                {dashboard.contexto === "UO" ? "Unidade Orcamental" : "Consolidado nacional"}
              </span>
              <span className="rounded-md border border-institutional-gold/30 bg-amber-50 px-3 py-1 text-xs font-semibold text-yellow-900">
                Ano fiscal {dashboard.anoFiscal}
              </span>
            </div>
            <div className="mt-6 max-w-3xl">
              <h2 className="text-2xl font-bold leading-tight text-institutional-ink md:text-3xl">
                Execucao financeira com leitura imediata de tecto, despesa e receita.
              </h2>
              <p className="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground">
                Os indicadores abaixo consolidam o ponto de controlo do exercicio e destacam o que ja compromete o limite aprovado.
              </p>
            </div>
          </div>
          <div className="border-t border-border bg-institutional-deep p-5 text-white xl:border-l xl:border-t-0">
            <div className="text-xs font-bold uppercase text-institutional-gold">Execucao do tecto</div>
            <div className="mt-4 text-4xl font-bold tabular-nums">{formatPercent(executionPercent)}</div>
            <div className="mt-4 h-2 overflow-hidden rounded-full bg-white/12">
              <div className="h-full rounded-full bg-institutional-gold" style={{ width: `${executionPercent}%` }} />
            </div>
            <div className="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-300">
              <div>
                <div className="font-semibold text-white">{formatCurrencyShort(saldo)}</div>
                <div>Saldo disponivel</div>
              </div>
              <div>
                <div className="font-semibold text-white">{dashboard.riscoOrcamental}</div>
                <div>Risco actual</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div className="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
        <KpiCard title="Tecto orcamental" value={formatCurrencyShort(dashboard.tectoTotal)} detail={`Ano fiscal ${dashboard.anoFiscal}`} icon={Landmark} progress={100} />
        <KpiCard title="Comprometido" value={formatCurrencyShort(dashboard.valorComprometido)} detail={formatPercent(dashboard.percentualExecucao)} icon={WalletCards} tone="gold" progress={executionPercent} />
        <KpiCard title="Pago" value={formatCurrencyShort(dashboard.valorPago)} detail="Pagamentos registados" icon={LineChart} tone="green" progress={dashboard.valorComprometido > 0 ? (dashboard.valorPago / dashboard.valorComprometido) * 100 : 0} />
        <KpiCard title="Receita RUPE" value={formatCurrencyShort(dashboard.totalReceita)} detail="Arrecadacao integrada" icon={ReceiptText} tone="blue" progress={dashboard.tectoTotal > 0 ? (dashboard.totalReceita / dashboard.tectoTotal) * 100 : 0} />
      </div>
      <BudgetOverview
        tecto={dashboard.tectoTotal}
        comprometido={dashboard.valorComprometido}
        pago={dashboard.valorPago}
        receita={dashboard.totalReceita}
        topUos={dashboard.topUos}
      />
      <div className="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
        <RiskPanel risk={dashboard.riscoOrcamental} />
        <RecentAudit />
      </div>
    </div>
  );
}
