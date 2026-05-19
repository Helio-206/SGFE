"use client";

import { Bar, BarChart, CartesianGrid, Cell, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import { TrendingUp } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { formatCurrency, formatCurrencyShort, formatPercent } from "@/lib/utils";

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
  const donutData = [
    { name: "Comprometido", value: comprometido, color: "#12355B" },
    { name: "Saldo", value: saldo, color: "#D8DEE8" }
  ];

  return (
    <div className="grid gap-5 xl:grid-cols-[0.95fr_1.4fr]">
      <Card>
        <CardHeader className="flex flex-row items-center justify-between gap-4">
          <div>
            <CardTitle>Execucao orcamental</CardTitle>
            <p className="mt-1 text-sm text-muted-foreground">Distribuicao entre valor comprometido e saldo disponivel.</p>
          </div>
          <div className="hidden rounded-md bg-institutional-mist p-2 text-institutional-blue sm:block">
            <TrendingUp className="h-5 w-5" />
          </div>
        </CardHeader>
        <CardContent>
          <div className="relative h-72">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={donutData} dataKey="value" innerRadius={72} outerRadius={104} paddingAngle={2}>
                  {donutData.map((entry) => (
                    <Cell key={entry.name} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip formatter={(value) => formatCurrency(Number(value))} />
              </PieChart>
            </ResponsiveContainer>
            <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
              <div className="text-center">
                <div className="text-3xl font-bold tabular-nums text-institutional-ink">{formatPercent(execucao)}</div>
                <div className="mt-1 text-xs font-semibold uppercase text-muted-foreground">Comprometido</div>
              </div>
            </div>
          </div>
          <div className="grid gap-3 text-sm sm:grid-cols-3">
            <div className="rounded-md border border-border bg-slate-50/60 p-3">
              <p className="text-muted-foreground">Saldo</p>
              <p className="mt-1 break-words font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(saldo)}</p>
            </div>
            <div className="rounded-md border border-border bg-slate-50/60 p-3">
              <p className="text-muted-foreground">Pago</p>
              <p className="mt-1 break-words font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(pago)}</p>
            </div>
            <div className="rounded-md border border-border bg-slate-50/60 p-3">
              <p className="text-muted-foreground">Receita</p>
              <p className="mt-1 break-words font-semibold tabular-nums text-institutional-ink">{formatCurrencyShort(receita)}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>UOs com maior execucao</CardTitle>
          <p className="mt-1 text-sm text-muted-foreground">Ranking por percentagem de tecto comprometido.</p>
        </CardHeader>
        <CardContent>
          {topUos.length ? (
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={topUos} layout="vertical" margin={{ left: 16, right: 24 }}>
                  <CartesianGrid stroke="#E5EAF0" horizontal={false} />
                  <XAxis type="number" domain={[0, 100]} tickFormatter={(value) => `${value}%`} tickLine={false} axisLine={false} />
                  <YAxis type="category" dataKey="codigo" width={74} tickLine={false} axisLine={false} />
                  <Tooltip
                    formatter={(value, name) =>
                      name === "percentual" ? [`${Number(value).toFixed(1)}%`, "Execucao"] : [formatCurrency(Number(value)), "Valor"]
                    }
                    labelFormatter={(label) => `UO ${label}`}
                  />
                  <Bar dataKey="percentual" fill="#12355B" radius={[0, 4, 4, 0]} barSize={18} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          ) : (
            <div className="flex h-72 items-center justify-center rounded-md border border-dashed border-border bg-slate-50/70 text-center text-sm font-medium text-muted-foreground">
              Sem execucao suficiente para apresentar ranking.
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
