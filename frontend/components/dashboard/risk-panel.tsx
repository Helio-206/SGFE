import { AlertTriangle, CheckCircle2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function RiskPanel({ risk }: { risk: "CONTROLADO" | "MODERADO" | "ALTO" | "CRITICO" }) {
  const critical = risk === "ALTO" || risk === "CRITICO";
  return (
    <Card>
      <CardHeader>
        <CardTitle>Risco orcamental</CardTitle>
      </CardHeader>
      <CardContent className="flex items-start gap-4">
        <div className={critical ? "rounded-md bg-red-50 p-3 text-red-800" : "rounded-md bg-emerald-50 p-3 text-emerald-800"}>
          {critical ? <AlertTriangle className="h-6 w-6" /> : <CheckCircle2 className="h-6 w-6" />}
        </div>
        <div>
          <Badge variant={critical ? "danger" : "success"}>{risk}</Badge>
          <p className="mt-3 text-sm leading-6 text-muted-foreground">
            Indicador calculado no backend a partir da execucao comprometida face ao tecto do ano fiscal.
          </p>
        </div>
      </CardContent>
    </Card>
  );
}
