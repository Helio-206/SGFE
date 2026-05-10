import { Label } from "@/components/ui/label";

export function FormField({
  label,
  error,
  children
}: {
  label: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="space-y-2">
      <Label>{label}</Label>
      {children}
      {error ? <p className="text-sm font-medium text-institutional-red">{error}</p> : null}
    </div>
  );
}
