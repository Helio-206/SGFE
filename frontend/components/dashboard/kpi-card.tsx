import type { LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";

export function KpiCard({
  title,
  value,
  detail,
  icon: Icon,
  tone = "blue",
  progress
}: {
  title: string;
  value: string;
  detail: string;
  icon: LucideIcon;
  tone?: "blue" | "gold" | "red" | "green" | "teal";
  progress?: number;
}) {
  const tones = {
    blue: {
      accent: "bg-institutional-blue",
      icon: "bg-blue-50 text-institutional-blue ring-blue-100",
      line: "bg-institutional-blue"
    },
    gold: {
      accent: "bg-institutional-gold",
      icon: "bg-yellow-50 text-yellow-800 ring-yellow-100",
      line: "bg-institutional-gold"
    },
    red: {
      accent: "bg-institutional-red",
      icon: "bg-red-50 text-red-800 ring-red-100",
      line: "bg-institutional-red"
    },
    green: {
      accent: "bg-emerald-600",
      icon: "bg-emerald-50 text-emerald-800 ring-emerald-100",
      line: "bg-emerald-600"
    },
    teal: {
      accent: "bg-institutional-teal",
      icon: "bg-teal-50 text-institutional-teal ring-teal-100",
      line: "bg-institutional-teal"
    }
  };
  const safeProgress = Math.max(0, Math.min(progress ?? 0, 100));

  return (
    <article className="relative min-w-0 overflow-hidden rounded-lg border border-border/80 bg-surface/95 p-5 shadow-quiet transition duration-200 hover:border-institutional-gold/40 hover:shadow-institutional">
      <div className={cn("absolute inset-x-0 top-0 h-1", tones[tone].accent)} />
      <div className="flex min-w-0 items-start justify-between gap-4">
        <div className="min-w-0 flex-1">
          <p className="truncate text-[11px] font-bold uppercase text-muted-foreground" title={title}>
            {title}
          </p>
          <p
            className="mt-3 min-w-0 break-words text-[clamp(1.35rem,1.6vw,1.85rem)] font-bold leading-tight tabular-nums text-institutional-ink"
            title={value}
          >
            {value}
          </p>
          <p className="mt-2 text-sm font-semibold leading-5 text-muted-foreground">{detail}</p>
        </div>
        <div className={cn("shrink-0 rounded-md p-2 ring-1", tones[tone].icon)}>
          <Icon className="h-5 w-5" />
        </div>
      </div>
      {progress !== undefined ? (
        <div className="mt-5 h-1.5 overflow-hidden rounded-full bg-surface-muted">
          <div className={cn("h-full rounded-full transition-all", tones[tone].line)} style={{ width: `${safeProgress}%` }} />
        </div>
      ) : (
        <div className={cn("mt-5 h-1.5 w-16 rounded-full", tones[tone].line)} />
      )}
    </article>
  );
}
