import Link from "next/link";
import { LogIn } from "lucide-react";
import { Button } from "@/components/ui/button";
import { InstitutionalBrand } from "./institutional-brand";

export function PublicHeader() {
  return (
    <header className="fixed left-0 right-0 top-0 z-30 border-b border-white/10 bg-[#071b33]/75 backdrop-blur-xl">
      <div className="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4">
        <InstitutionalBrand compact inverted />
        <div className="hidden items-center gap-6 text-sm font-medium text-slate-200 md:flex">
          <Link href="#plataforma" className="transition hover:text-white">Plataforma</Link>
          <Link href="#fluxo" className="transition hover:text-white">Fluxo</Link>
          <Link href="#parceiros" className="transition hover:text-white">Parceiros</Link>
        </div>
        <Button asChild variant="gold" size="sm">
          <Link href="/login">
            <LogIn className="h-4 w-4" />
            Entrar
          </Link>
        </Button>
      </div>
    </header>
  );
}
