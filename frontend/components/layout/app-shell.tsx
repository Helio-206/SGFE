"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import {
  BarChart3,
  Building2,
  ClipboardList,
  FileText,
  Gauge,
  Landmark,
  ReceiptText,
  ShieldCheck,
  UserCircle,
  UsersRound,
  WalletCards
} from "lucide-react";
import { apiFetch } from "@/lib/api";
import type { Role } from "@/lib/rbac";
import { cn } from "@/lib/utils";
import { InstitutionalBrand } from "./institutional-brand";
import { LogoutButton } from "./logout-button";

const nav = [
  { href: "/dashboard", label: "Dashboard", icon: Gauge, roles: ["ADMIN", "GESTOR", "AUDITOR"], section: "Operacao" },
  { href: "/gestao/receitas", label: "Receitas RUPE", icon: ReceiptText, roles: ["ADMIN", "GESTOR"], section: "Operacao" },
  { href: "/gestao/despesas", label: "Despesas", icon: BarChart3, roles: ["ADMIN", "GESTOR"], section: "Operacao" },
  { href: "/relatorios", label: "Relatorios", icon: FileText, roles: ["ADMIN", "GESTOR", "AUDITOR"], section: "Operacao" },
  { href: "/admin", label: "Administracao", icon: Landmark, roles: ["ADMIN"], section: "Administracao" },
  { href: "/admin/instituicoes", label: "Unidades Orcamentais", icon: Building2, roles: ["ADMIN"], section: "Administracao" },
  { href: "/admin/orcamentos", label: "Orcamentos", icon: WalletCards, roles: ["ADMIN"], section: "Administracao" },
  { href: "/admin/classificacoes", label: "Classificacoes", icon: ClipboardList, roles: ["ADMIN"], section: "Administracao" },
  { href: "/admin/utilizadores", label: "Utilizadores", icon: UsersRound, roles: ["ADMIN"], section: "Administracao" },
  { href: "/auditoria", label: "Auditoria", icon: ShieldCheck, roles: ["ADMIN", "AUDITOR"], section: "Controlo" },
  { href: "/perfil", label: "Perfil", icon: UserCircle, roles: ["ADMIN", "GESTOR", "AUDITOR"], section: "Conta" }
] as const;

type SessionUser = {
  role: Role;
  nome?: string;
  codigoUo?: string;
  instituicao?: string;
};

export function AppShell({ title, children }: { title: string; children: React.ReactNode }) {
  const pathname = usePathname();
  const { data: user } = useQuery({
    queryKey: ["users", "me"],
    queryFn: () => apiFetch<SessionUser>("/users/me"),
    retry: false
  });
  const visibleNav = nav.filter((item) =>
    user ? (item.roles as readonly Role[]).includes(user.role) : item.href === "/dashboard" || item.href === "/perfil"
  );
  const groupedNav = visibleNav.reduce<Record<string, typeof visibleNav>>((groups, item) => {
    groups[item.section] = groups[item.section] ?? [];
    groups[item.section].push(item);
    return groups;
  }, {});

  return (
    <div className="min-h-screen bg-[#f5f7fa]">
      <aside className="fixed inset-y-0 left-0 hidden w-72 overflow-hidden border-r border-white/60 bg-institutional-deep text-white lg:block">
        <div className="absolute inset-y-0 right-0 w-px bg-institutional-gold/30" />
        <div className="relative border-b border-white/10 px-5 py-5">
          <InstitutionalBrand compact />
        </div>
        <div className="relative px-4 pt-5">
          <div className="rounded-lg border border-white/10 bg-white/5 p-4 text-sm text-slate-200 shadow-2xl shadow-black/20">
            <p className="text-[11px] font-semibold uppercase text-institutional-gold">Sessao ativa</p>
            <p className="mt-3 font-semibold text-white">{user?.nome ?? "Utilizador SGFE"}</p>
            <p className="mt-1 text-xs text-slate-300">{user?.role ?? "PERFIL"}{user?.codigoUo ? ` | ${user.codigoUo}` : ""}</p>
          </div>
        </div>
        <nav className="relative space-y-5 px-3 py-5">
          {Object.entries(groupedNav).map(([section, items]) => (
            <div key={section}>
              <div className="px-3 pb-2 text-[10px] font-bold uppercase text-slate-400">{section}</div>
              <div className="space-y-1">
                {items.map((item) => {
                  const active = pathname === item.href || (item.href !== "/dashboard" && pathname.startsWith(`${item.href}/`));
                  return (
                    <Link
                      key={item.href}
                      href={item.href}
                      className={cn(
                        "flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10 hover:text-white",
                        active && "bg-white text-institutional-deep shadow-institutional hover:bg-white"
                      )}
                    >
                      <item.icon className={cn("h-4 w-4", active ? "text-institutional-blue" : "text-institutional-gold")} />
                      {item.label}
                    </Link>
                  );
                })}
              </div>
            </div>
          ))}
        </nav>
      </aside>
      <div className="lg:pl-72">
        <header className="sticky top-0 z-20 border-b border-white/70 bg-white/90 backdrop-blur-xl">
          <div className="flex flex-col gap-4 px-5 py-4 lg:px-8">
            <div className="flex items-start justify-between gap-5">
              <div>
                <p className="text-xs font-semibold uppercase text-institutional-gold">Centro operativo SGFE</p>
                <h1 className="mt-1 text-2xl font-bold text-institutional-ink">{title}</h1>
                <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{user?.instituicao ?? "Ministerio das Financas"} | Africa/Luanda</p>
              </div>
              <div className="flex flex-col items-end gap-3">
                <div className="hidden rounded-lg border border-institutional-gold/30 bg-amber-50 px-4 py-3 text-right text-xs text-slate-600 md:block">
                  <div className="font-semibold text-institutional-ink">{user?.role ?? "SGFE"}</div>
                  <div>{new Intl.DateTimeFormat("pt-AO", { dateStyle: "medium" }).format(new Date())}</div>
                </div>
                <LogoutButton />
              </div>
            </div>
            <nav className="flex gap-2 overflow-x-auto pb-1 lg:hidden">
              {visibleNav.map((item) => {
                const active = pathname === item.href || (item.href !== "/dashboard" && pathname.startsWith(`${item.href}/`));
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    className={cn(
                      "whitespace-nowrap rounded-md border border-border bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-institutional-gold/40 hover:text-institutional-ink",
                      active && "border-institutional-blue bg-institutional-blue text-white hover:text-white"
                    )}
                  >
                    {item.label}
                  </Link>
                );
              })}
            </nav>
          </div>
        </header>
        <main className="px-5 py-6 lg:px-8">{children}</main>
      </div>
    </div>
  );
}
