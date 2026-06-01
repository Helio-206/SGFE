"use client";

import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from "recharts";
import { BarChart3, TrendingUp } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn, formatCurrency, formatCurrencyShort, formatPercent } from "@/lib/utils";

export function BudgetOverview({
  tecto,
  comprometido,
  pago,
  receita,
  topUos
}: {
  tecto: number;
  comprometido: number;
  pago: number;
  receita: number;
  topUos: Array<{ codigo: string; nome: string; percentual: number; comprometido: number }>;
}) {
  const execucao = tecto > 0 ? (comprometido / tecto) * 100 : 0;
  const saldo = Math.max(tecto - comprometido, 0);
  const pagoNoCompromisso = Math.min(Math.max(pago, 0), Math.max(comprometido, 0));
  const compromissoEmAberto = Math.max(comprometido - pagoNoCompromisso, 0);
  const donutData = [
    { name: "Pago", value: pagoNoCompromisso, color: "#0E6B6B" },
    { name: "Compromisso", value: compromissoEmAberto, color: "#12355B" },
    { name: "Saldo", value: saldo, color: "#D8DEE8" }
  ];
  const ranking = [...topUos].sort((a, b) => b.percentual - a.percentual).slice(0, 6);

  return (
    <div className="grid gap-5 xl:grid-cols-[0.88fr_1.12fr]">
      <Card>
        <CardHeader className="flex flex-row items-center justify-between gap-4">
          <div>
            <CardTitle>Composicao do tecto</CardTitle>
            <p className="mt-1 text-sm text-muted-foreground">Compromisso, pagamento e saldo</p>
          </div>
          <div className="hidden rounded-md bg-institutional-mist p-2 text-institutional-blue sm:block">
            <TrendingUp className="h-5 w-5" />
          </div>
        </CardHeader>
        <CardContent>
          <div className="relative h-64">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={donutData} dataKey="value" innerRadius={68} outerRadius={98} paddingAngle={2} strokeWidth={0}>
                  {donutData.map((entry) => (
                    <Cell key={entry.name} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip
                  formatter={(value) => formatCurrency(Number(value))}
                  contentStyle={{ borderRadius: 8, borderColor: "#D8DEE8", boxShadow: "0 12px 32px rgba(8, 26, 45, 0.10)" }}
                />
              </PieChart>
            </ResponsiveContainer>
            <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
              <div className="text-center">
                <div className="text-3xl font-bold tabular-nums text-institutional-ink">{formatPercent(execucao)}</div>
                <div className="mt-1 text-xs font-semibold uppercase text-muted-foreground">Execucao</div>
              </div>
            </div>
          </div>
          <div className="grid gap-3 text-sm sm:grid-cols-3">
            <div className="rounded-md border border-border/80 bg-surface-muted/60 p-3">
              <p className="text-muted-foreground">Saldo</p>
              <p className="mt-1 break-words font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(saldo)}</p>
            </div>
            <div className="rounded-md border border-border/80 bg-surface-muted/60 p-3">
              <p className="text-muted-foreground">Pago</p>
              <p className="mt-1 break-words font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(pago)}</p>
            </div>
            <div className="rounded-md border border-border/80 bg-surface-muted/60 p-3">
              <p className="text-muted-foreground">Receita</p>
              <p className="mt-1 break-words font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(receita)}</p>
            </div>
          </div>
          <div className="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-muted-foreground">
            {donutData.map((item) => (
              <span key={item.name} className="inline-flex items-center gap-2">
                <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: item.color }} />
                {item.name}
              </span>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="flex flex-row items-center justify-between gap-4">
          <div>
            <CardTitle>Execucao por UO</CardTitle>
            <p className="mt-1 text-sm text-muted-foreground">Maior compromisso sobre tecto</p>
          </div>
          <div className="hidden rounded-md bg-institutional-mist p-2 text-institutional-blue sm:block">
            <BarChart3 className="h-5 w-5" />
          </div>
        </CardHeader>
        <CardContent>
          {ranking.length ? (
            <div className="space-y-3">
              {ranking.map((uo, index) => {
                const percent = Math.max(0, Math.min(uo.percentual, 100));
                const pressure = percent >= 95 ? "red" : percent >= 75 ? "gold" : "blue";
                return (
                  <div key={`${uo.codigo}-${index}`} className="rounded-md border border-border/75 bg-surface-strong/70 p-3">
                    <div className="flex items-start justify-between gap-4">
                      <div className="min-w-0">
                        <div className="flex items-center gap-2">
                          <span className="inline-flex h-6 w-6 items-center justify-center rounded-md bg-institutional-deep text-[11px] font-bold text-white">
                            {index + 1}
                          </span>
                          <span className="font-mono text-xs font-bold text-institutional-blue">{uo.codigo}</span>
                        </div>
                        <p className="mt-2 truncate text-sm font-semibold text-institutional-ink" title={uo.nome}>
                          {uo.nome}
                        </p>
                      </div>
                      <div className="text-right">
                        <div className="text-sm font-bold tabular-nums text-institutional-ink">{formatPercent(percent)}</div>
                        <div className="mt-1 text-xs text-muted-foreground">{formatCurrencyShort(uo.comprometido)}</div>
                      </div>
                    </div>
                    <div className="mt-3 h-2 overflow-hidden rounded-full bg-surface-muted">
                      <div
                        className={cn(
                          "h-full rounded-full",
                          pressure === "red" ? "bg-institutional-red" : pressure === "gold" ? "bg-institutional-gold" : "bg-institutional-blue"
                        )}
                        style={{ width: `${percent}%` }}
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="flex min-h-64 items-center justify-center rounded-md border border-dashed border-border bg-surface-muted/60 text-center text-sm font-medium text-muted-foreground">
              Sem ranking disponivel.
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
