import Link from "next/link";
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
  WalletCards
} from "lucide-react";
import { InstitutionalBrand } from "./institutional-brand";
import { LogoutButton } from "./logout-button";

const nav = [
  { href: "/dashboard", label: "Dashboard", icon: Gauge },
  { href: "/admin", label: "Administracao", icon: Landmark },
  { href: "/admin/instituicoes", label: "Unidades Orcamentais", icon: Building2 },
  { href: "/admin/orcamentos", label: "Orcamentos", icon: WalletCards },
  { href: "/admin/classificacoes", label: "Classificacoes", icon: ClipboardList },
  { href: "/gestao/receitas", label: "Receitas RUPE", icon: ReceiptText },
  { href: "/gestao/despesas", label: "Despesas", icon: BarChart3 },
  { href: "/relatorios", label: "Relatorios", icon: FileText },
  { href: "/auditoria", label: "Auditoria", icon: ShieldCheck },
  { href: "/perfil", label: "Perfil", icon: UserCircle }
];

export function AppShell({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-[linear-gradient(180deg,#f6f7f4_0%,#eef2f5_48%,#f8fbfd_100%)]">
      <aside className="fixed inset-y-0 left-0 hidden w-72 overflow-hidden border-r border-white/60 bg-institutional-deep text-white lg:block">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(210,169,40,0.18),_transparent_38%),linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0))]" />
        <div className="relative border-b border-white/10 px-5 py-5">
          <InstitutionalBrand compact />
        </div>
        <div className="relative px-4 pt-5">
          <div className="rounded-3xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200 shadow-2xl shadow-black/20">
            <p className="text-[11px] font-semibold uppercase tracking-[0.26em] text-institutional-gold">Operacao segura</p>
            <p className="mt-3 leading-6">Navegacao institucional com sessao por cookie HttpOnly, auditoria persistente e segregacao por papel.</p>
          </div>
        </div>
        <nav className="relative space-y-1 px-3 py-5">
          {nav.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10 hover:text-white"
            >
              <item.icon className="h-4 w-4 text-institutional-gold" />
              {item.label}
            </Link>
          ))}
        </nav>
      </aside>
      <div className="lg:pl-72">
        <header className="sticky top-0 z-20 border-b border-white/70 bg-white/90 backdrop-blur-xl">
          <div className="flex flex-col gap-4 px-5 py-4 lg:px-8">
            <div className="flex items-start justify-between gap-5">
              <div>
                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-institutional-gold">Centro operativo SGFE</p>
                <h1 className="mt-1 text-2xl font-bold text-institutional-ink">{title}</h1>
                <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                  Fluxo mais leve, controlo visivel e sessao protegida por credenciais de servidor e rastreabilidade continua.
                </p>
              </div>
              <div className="flex flex-col items-end gap-3">
                <div className="hidden rounded-2xl border border-institutional-gold/30 bg-amber-50 px-4 py-3 text-right text-xs text-slate-600 md:block">
                  <div className="font-semibold text-institutional-ink">Sessao protegida</div>
                  <div>Cookies HttpOnly · RBAC · Auditoria</div>
                </div>
                <LogoutButton />
              </div>
            </div>
            <nav className="flex gap-2 overflow-x-auto pb-1 lg:hidden">
              {nav.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className="whitespace-nowrap rounded-full border border-border bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-institutional-gold/40 hover:text-institutional-ink"
                >
                  {item.label}
                </Link>
              ))}
            </nav>
          </div>
        </header>
        <main className="px-5 py-6 lg:px-8">{children}</main>
      </div>
    </div>
  );
}
