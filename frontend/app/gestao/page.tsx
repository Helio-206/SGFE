import { DashboardClient } from "@/components/dashboard/dashboard-client";
import { AppShell } from "@/components/layout/app-shell";

export default function GestaoPage() {
  return (
    <AppShell title="Dashboard da Unidade Orcamental">
      <DashboardClient mode="uo" />
    </AppShell>
  );
}
