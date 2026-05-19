import { AlertTriangle, CheckCircle2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";

export function RiskPanel({ risk }: { risk: "CONTROLADO" | "MODERADO" | "ALTO" | "CRITICO" }) {
  const critical = risk === "ALTO" || risk === "CRITICO";
  const moderate = risk === "MODERADO";
  const tone = critical
    ? "border-red-200 bg-red-50 text-red-900"
    : moderate
      ? "border-amber-200 bg-amber-50 text-amber-900"
      : "border-emerald-200 bg-emerald-50 text-emerald-900";
  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between gap-4">
        <div>
          <CardTitle>Risco orcamental</CardTitle>
          <p className="mt-1 text-sm text-muted-foreground">Sinal operacional do exercicio corrente.</p>
        </div>
        <Badge variant={critical ? "danger" : moderate ? "warning" : "success"}>{risk}</Badge>
      </CardHeader>
      <CardContent>
        <div className={cn("flex items-start gap-4 rounded-lg border p-4", tone)}>
          <div className="rounded-md bg-white/75 p-3 shadow-line">
          {critical ? <AlertTriangle className="h-6 w-6" /> : <CheckCircle2 className="h-6 w-6" />}
          </div>
          <div>
            <p className="text-sm font-semibold">{critical ? "Requer acompanhamento imediato" : moderate ? "Acompanhar evolucao" : "Dentro do limite esperado"}</p>
            <p className="mt-2 text-sm leading-6 opacity-85">
              Calculado a partir da execucao comprometida face ao tecto do ano fiscal e actualizado pelo backend.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
