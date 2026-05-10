"use client";

import { useQuery } from "@tanstack/react-query";
import { Landmark, LineChart, ReceiptText, WalletCards } from "lucide-react";
import { BudgetOverview } from "@/components/dashboard/budget-overview";
import { KpiCard } from "@/components/dashboard/kpi-card";
import { RecentAudit } from "@/components/dashboard/recent-audit";
import { RiskPanel } from "@/components/dashboard/risk-panel";
import { apiFetch, emptyDashboard, type DashboardData } from "@/lib/api";
import { formatCurrency, formatPercent } from "@/lib/utils";

export function DashboardClient({ mode = "nacional" }: { mode?: "nacional" | "uo" }) {
  const { data } = useQuery({
    queryKey: ["dashboard"],
    queryFn: () => apiFetch<DashboardData>("/dashboard"),
    retry: false
  });
  const dashboard = data ?? { ...emptyDashboard, contexto: mode === "uo" ? "UO" : "NACIONAL" };

  return (
    <div className="space-y-5">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <KpiCard title="Tecto orcamental" value={formatCurrency(dashboard.tectoTotal)} detail={`Ano fiscal ${dashboard.anoFiscal}`} icon={Landmark} />
        <KpiCard title="Comprometido" value={formatCurrency(dashboard.valorComprometido)} detail={formatPercent(dashboard.percentualExecucao)} icon={WalletCards} tone="gold" />
        <KpiCard title="Pago" value={formatCurrency(dashboard.valorPago)} detail="Pagamentos registados" icon={LineChart} tone="green" />
        <KpiCard title="Receita RUPE" value={formatCurrency(dashboard.totalReceita)} detail="Arrecadacao integrada" icon={ReceiptText} tone="blue" />
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
