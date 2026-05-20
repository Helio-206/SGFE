import Image from "next/image";
import { MINFIN_LOGO_URL } from "@/lib/branding";
import { cn } from "@/lib/utils";

export function InstitutionalBrand({ compact = false, inverted = false }: { compact?: boolean; inverted?: boolean }) {
  return (
    <div className="flex min-w-0 items-center gap-3">
      <Image
        src={MINFIN_LOGO_URL}
        alt="Logo oficial do Ministerio das Financas de Angola"
        width={compact ? 178 : 240}
        height={compact ? 28 : 38}
        className="h-auto max-w-[178px] object-contain"
        unoptimized
        priority
      />
      <div className={cn("min-w-0 border-l pl-3 leading-tight", compact && "hidden sm:block", inverted ? "border-white/15" : "border-border")}>
        <div className={cn("text-[11px] font-semibold uppercase text-institutional-gold", inverted && "text-institutional-gold")}>SGFE</div>
        <div className={cn(compact ? "text-sm font-bold" : "text-base font-bold", inverted ? "text-white" : "text-institutional-ink")}>
          Financas Publicas
        </div>
      </div>
    </div>
  );
}
