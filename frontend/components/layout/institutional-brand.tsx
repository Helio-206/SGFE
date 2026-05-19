import Image from "next/image";
import { MINFIN_LOGO_URL } from "@/lib/branding";

export function InstitutionalBrand({ compact = false }: { compact?: boolean }) {
  return (
    <div className="flex items-center gap-3">
      <Image
        src={MINFIN_LOGO_URL}
        alt="Logo oficial do Ministerio das Financas de Angola"
        width={compact ? 190 : 240}
        height={compact ? 30 : 38}
        className="h-auto w-auto object-contain"
        priority
      />
      <div className="leading-tight">
        <div className="text-xs font-semibold uppercase text-institutional-gold">Portal central</div>
        <div className={compact ? "text-sm font-bold text-white" : "text-base font-bold text-institutional-ink"}>
          Ministerio das Financas
        </div>
      </div>
    </div>
  );
}
