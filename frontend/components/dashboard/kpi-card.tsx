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
  tone?: "blue" | "gold" | "red" | "green";
  progress?: number;
}) {
  const tones = {
    blue: {
      icon: "bg-blue-50 text-institutional-blue ring-blue-100",
      line: "bg-institutional-blue"
    },
    gold: {
      icon: "bg-yellow-50 text-yellow-800 ring-yellow-100",
      line: "bg-institutional-gold"
    },
    red: {
      icon: "bg-red-50 text-red-800 ring-red-100",
      line: "bg-institutional-red"
    },
    green: {
      icon: "bg-emerald-50 text-emerald-800 ring-emerald-100",
      line: "bg-emerald-600"
    }
  };
  const safeProgress = Math.max(0, Math.min(progress ?? 0, 100));

  return (
    <article className="group relative min-w-0 overflow-hidden rounded-lg border border-border bg-white p-5 shadow-line transition duration-200 hover:-translate-y-0.5 hover:border-institutional-gold/40 hover:shadow-institutional">
      <div className="flex min-w-0 items-start justify-between gap-4">
        <div className="min-w-0 flex-1">
          <p className="truncate text-[11px] font-bold uppercase text-muted-foreground" title={title}>
            {title}
          </p>
          <p
            className="mt-4 min-w-0 break-words text-[clamp(1.45rem,1.8vw,2rem)] font-bold leading-tight tabular-nums text-institutional-ink"
            title={value}
          >
            {value}
          </p>
          <p className="mt-3 text-sm leading-5 text-muted-foreground">{detail}</p>
        </div>
        <div className={cn("shrink-0 rounded-md p-2 ring-1", tones[tone].icon)}>
          <Icon className="h-5 w-5" />
        </div>
      </div>
      {progress !== undefined ? (
        <div className="mt-5 h-1.5 overflow-hidden rounded-full bg-slate-100">
          <div className={cn("h-full rounded-full transition-all", tones[tone].line)} style={{ width: `${safeProgress}%` }} />
        </div>
      ) : (
        <div className={cn("mt-5 h-1.5 w-14 rounded-full transition-all group-hover:w-24", tones[tone].line)} />
      )}
    </article>
  );
}
