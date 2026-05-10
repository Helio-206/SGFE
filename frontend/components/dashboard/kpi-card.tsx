import type { LucideIcon } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";

export function KpiCard({
  title,
  value,
  detail,
  icon: Icon,
  tone = "blue"
}: {
  title: string;
  value: string;
  detail: string;
  icon: LucideIcon;
  tone?: "blue" | "gold" | "red" | "green";
}) {
  const tones = {
    blue: "bg-blue-50 text-institutional-blue",
    gold: "bg-yellow-50 text-yellow-800",
    red: "bg-red-50 text-red-800",
    green: "bg-emerald-50 text-emerald-800"
  };

  return (
    <Card>
      <CardContent className="flex min-h-32 items-start justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">{title}</p>
          <p className="mt-3 text-2xl font-bold text-institutional-ink">{value}</p>
          <p className="mt-2 text-sm text-muted-foreground">{detail}</p>
        </div>
        <div className={cn("rounded-md p-2", tones[tone])}>
          <Icon className="h-5 w-5" />
        </div>
      </CardContent>
    </Card>
  );
}
