import { ShieldCheck } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const items = [
  { acao: "CRIAR_NCD", entidade: "Despesa", user: "Gestor UO-MFN", tone: "gold" as const },
  { acao: "LIQUIDAR_NLD", entidade: "Despesa", user: "Gestor UO-MSA", tone: "info" as const },
  { acao: "REGISTRAR_PAGAMENTO", entidade: "Despesa", user: "Tesouraria", tone: "success" as const }
];

export function RecentAudit() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Ultimas acoes auditaveis</CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        {items.map((item) => (
          <div key={item.acao} className="flex items-center justify-between rounded-md border border-border p-3">
            <div className="flex items-center gap-3">
              <ShieldCheck className="h-4 w-4 text-institutional-gold" />
              <div>
                <div className="text-sm font-semibold text-institutional-ink">{item.acao}</div>
                <div className="text-xs text-muted-foreground">{item.user}</div>
              </div>
            </div>
            <Badge variant={item.tone}>{item.entidade}</Badge>
          </div>
        ))}
      </CardContent>
    </Card>
  );
}
