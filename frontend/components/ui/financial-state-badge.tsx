import { Badge } from "@/components/ui/badge";
import { financialStateMeta, type FinancialState } from "@/lib/finance";
import { cn } from "@/lib/utils";

export function FinancialStateBadge({ state }: { state: FinancialState }) {
  const meta = financialStateMeta[state];
  return (
    <Badge className={cn("whitespace-nowrap", meta.className)} title={meta.description}>
      {meta.label}
    </Badge>
  );
}
