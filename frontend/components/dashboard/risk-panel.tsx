import { AlertTriangle, CheckCircle2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";

export function RiskPanel({ risk }: { risk: "CONTROLADO" | "MODERADO" | "ALTO" | "CRITICO" }) {
  const levels = ["CONTROLADO", "MODERADO", "ALTO", "CRITICO"] as const;
  const activeIndex = levels.indexOf(risk);
  const critical = risk === "ALTO" || risk === "CRITICO";
  const moderate = risk === "MODERADO";
  const tone = critical
    ? {
        panel: "border-red-200 bg-red-50 text-red-900",
        icon: "bg-white/70 text-red-800",
        bar: "bg-institutional-red",
        label: "Contencao"
      }
    : moderate
      ? {
          panel: "border-amber-200 bg-amber-50 text-amber-900",
          icon: "bg-white/70 text-amber-800",
          bar: "bg-institutional-gold",
          label: "Vigilancia"
        }
      : {
          panel: "border-emerald-200 bg-emerald-50 text-emerald-900",
          icon: "bg-white/70 text-emerald-800",
          bar: "bg-emerald-600",
          label: "Normal"
        };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between gap-4">
        <div>
          <CardTitle>Risco</CardTitle>
          <p className="mt-1 text-sm text-muted-foreground">Estado orcamental</p>
        </div>
        <Badge variant={critical ? "danger" : moderate ? "warning" : "success"}>{risk}</Badge>
      </CardHeader>
      <CardContent>
        <div className={cn("rounded-lg border p-4", tone.panel)}>
          <div className="flex items-start gap-4">
            <div className={cn("rounded-md p-3 shadow-line", tone.icon)}>
              {critical ? <AlertTriangle className="h-6 w-6" /> : <CheckCircle2 className="h-6 w-6" />}
            </div>
            <div className="min-w-0">
              <p className="text-base font-bold">{tone.label}</p>
              <p className="mt-1 text-sm leading-6 opacity-85">
                {critical ? "Rever compromissos em aberto." : moderate ? "Monitorizar novas cabimentacoes." : "Execucao dentro do intervalo esperado."}
              </p>
            </div>
          </div>

          <div className="mt-5 grid grid-cols-4 gap-2">
            {levels.map((level, index) => (
              <div key={level}>
                <div className={cn("h-1.5 rounded-full bg-white/70", index <= activeIndex && tone.bar)} />
                <div className="mt-2 truncate text-[10px] font-bold uppercase opacity-75">{level}</div>
              </div>
            ))}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
