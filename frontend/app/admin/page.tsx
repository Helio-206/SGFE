import { AlertTriangle, Building2, ClipboardList, Landmark, UsersRound, WalletCards } from "lucide-react";
import Link from "next/link";
import { DashboardClient } from "@/components/dashboard/dashboard-client";
import { AppShell } from "@/components/layout/app-shell";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const actions = [
  { href: "/admin/instituicoes", label: "Unidades Orcamentais", icon: Building2 },
  { href: "/admin/orcamentos", label: "Tectos Orcamentais", icon: WalletCards },
  { href: "/admin/classificacoes", label: "Classificacoes", icon: ClipboardList },
  { href: "/admin/utilizadores", label: "Utilizadores", icon: UsersRound }
];

export default function AdminPage() {
  return (
    <AppShell title="Dashboard administrativo">
      <div className="mb-5 grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
        <section>
          <div className="mb-4">
            <p className="text-xs font-bold uppercase tracking-[0.18em] text-institutional-gold">Nacional</p>
            <h2 className="mt-1 text-xl font-bold text-institutional-ink">Visao consolidada</h2>
          </div>
          <DashboardClient />
        </section>
        <div className="space-y-5">
          <Card>
            <CardHeader>
              <CardTitle>Acessos rapidos</CardTitle>
            </CardHeader>
            <CardContent className="grid gap-3">
              {actions.map((action) => (
                <Link key={action.href} href={action.href} className="flex items-center gap-3 rounded-md border border-border p-3 hover:bg-institutional-mist">
                  <action.icon className="h-4 w-4 text-institutional-gold" />
                  <span className="text-sm font-semibold text-institutional-ink">{action.label}</span>
                </Link>
              ))}
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Alertas de consistencia</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex gap-3 rounded-md border border-amber-200 bg-amber-50 p-3">
                <AlertTriangle className="h-4 w-4 text-amber-800" />
                <div>
                  <Badge variant="warning">Atencao</Badge>
                  <p className="mt-2 text-sm text-amber-900">Despesas sem classificacao devem ser corrigidas antes do fecho.</p>
                </div>
              </div>
              <div className="flex gap-3 rounded-md border border-blue-200 bg-blue-50 p-3">
                <Landmark className="h-4 w-4 text-blue-800" />
                <p className="text-sm text-blue-900">Validar UOs sem tecto atribuido no ano fiscal corrente.</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppShell>
  );
}
