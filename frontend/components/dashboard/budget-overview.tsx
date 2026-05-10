"use client";

import { Bar, BarChart, CartesianGrid, Cell, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { formatCurrency } from "@/lib/utils";

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
  const donutData = [
    { name: "Comprometido", value: comprometido, color: "#12355B" },
    { name: "Saldo", value: Math.max(tecto - comprometido, 0), color: "#D8DEE8" }
  ];

  return (
    <div className="grid gap-5 xl:grid-cols-[0.95fr_1.4fr]">
      <Card>
        <CardHeader>
          <CardTitle>Execucao orcamental</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-72">
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
          </div>
          <div className="grid grid-cols-2 gap-3 text-sm">
            <div className="rounded-md border border-border p-3">
              <p className="text-muted-foreground">Pago</p>
              <p className="font-semibold text-institutional-ink">{formatCurrency(pago)}</p>
            </div>
            <div className="rounded-md border border-border p-3">
              <p className="text-muted-foreground">Receita</p>
              <p className="font-semibold text-institutional-ink">{formatCurrency(receita)}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>UOs com maior execucao</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-72">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={topUos} layout="vertical" margin={{ left: 16, right: 16 }}>
                <CartesianGrid stroke="#E5EAF0" horizontal={false} />
                <XAxis type="number" domain={[0, 100]} tickFormatter={(value) => `${value}%`} />
                <YAxis type="category" dataKey="codigo" width={74} />
                <Tooltip
                  formatter={(value, name) =>
                    name === "percentual" ? [`${Number(value).toFixed(1)}%`, "Execucao"] : [formatCurrency(Number(value)), "Valor"]
                  }
                />
                <Bar dataKey="percentual" fill="#12355B" radius={[0, 4, 4, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
