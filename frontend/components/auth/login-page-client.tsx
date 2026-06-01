"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Eye, ShieldCheck } from "lucide-react";
import Link from "next/link";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { FormField } from "@/components/forms/form-field";
import { InstitutionalBrand } from "@/components/layout/institutional-brand";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { API_BASE_URL } from "@/lib/api";

const schema = z.object({
  email: z.string().email("Informe um email institucional valido."),
  password: z.string().min(8, "A palavra-passe deve ter pelo menos 8 caracteres.")
});

type LoginValues = z.infer<typeof schema>;

function resolveNextPath(nextPath?: string) {
  if (nextPath && nextPath.startsWith("/") && !nextPath.startsWith("//") && !nextPath.includes("\\")) {
    return nextPath;
  }

  return "/dashboard";
}

export function LoginPageClient({ nextPath }: { nextPath?: string }) {
  const [error, setError] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);
  const form = useForm<LoginValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: "", password: "" }
  });

  async function onSubmit(values: LoginValues) {
    setError(null);
    try {
      const response = await fetch(`${API_BASE_URL}/auth/login`, {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
        body: JSON.stringify(values)
      });
      if (!response.ok) {
        const payload = (await response.json().catch(() => null)) as { message?: string } | null;
        throw new Error(payload?.message ?? "Credenciais invalidas ou conta inativa.");
      }
      await response.json().catch(() => null);
      window.location.assign(resolveNextPath(nextPath));
    } catch (err) {
      setError(err instanceof Error ? err.message : "Nao foi possivel iniciar sessao.");
    }
  }

  return (
    <main className="min-h-screen bg-background px-5 py-8 md:py-10">
      <section className="mx-auto grid min-h-[calc(100vh-5rem)] w-full max-w-6xl overflow-hidden rounded-lg border border-border/75 bg-surface/95 shadow-institutional lg:grid-cols-[0.92fr_1.08fr]">
        <div className="flex flex-col justify-between bg-[linear-gradient(145deg,#071b33_0%,#0b3154_52%,#0e6b6b_100%)] p-6 text-white md:p-8">
          <div>
            <InstitutionalBrand compact inverted />
            <div className="mt-12 max-w-md">
              <p className="text-xs font-bold uppercase text-institutional-gold">Acesso institucional</p>
              <h1 className="mt-4 text-3xl font-bold leading-tight md:text-4xl">Sistema de Gestao das Financas do Estado</h1>
              <p className="mt-4 text-sm leading-6 text-slate-200">
                Ambiente reservado para utilizadores autorizados do circuito financeiro publico.
              </p>
            </div>
          </div>
          <div className="mt-10 rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
            <div className="flex items-center gap-3">
              <ShieldCheck className="h-5 w-5 text-institutional-gold" />
              <div>
                <div className="text-sm font-semibold text-white">Sessao protegida</div>
                <div className="text-xs text-slate-200">SGFE</div>
              </div>
            </div>
          </div>
        </div>
        <div className="flex items-center justify-center bg-surface-muted/50 p-6 md:p-10">
          <div className="w-full max-w-md rounded-lg border border-border/80 bg-surface-strong/75 p-5 shadow-quiet md:p-6">
            <div className="mb-7">
              <p className="text-xs font-bold uppercase text-institutional-gold">SGFE</p>
              <h2 className="mt-2 text-3xl font-bold text-institutional-ink">Iniciar sessao</h2>
              <p className="mt-3 text-sm leading-6 text-muted-foreground">Use a sua credencial institucional para continuar.</p>
            </div>
            {error ? <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-800">{error}</div> : null}
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              <FormField label="Email institucional" error={form.formState.errors.email?.message}>
                <Input type="email" autoComplete="email" {...form.register("email")} />
              </FormField>
              <FormField label="Palavra-passe" error={form.formState.errors.password?.message}>
                <div className="relative">
                  <Input type={showPassword ? "text" : "password"} autoComplete="current-password" {...form.register("password")} />
                  <button
                    type="button"
                    className="absolute right-3 top-2.5 text-muted-foreground"
                    onClick={() => setShowPassword((value) => !value)}
                    aria-label="Alternar visibilidade da palavra-passe"
                  >
                    <Eye className="h-4 w-4" />
                  </button>
                </div>
              </FormField>
              <Button className="w-full" type="submit" disabled={form.formState.isSubmitting}>
                Entrar
              </Button>
            </form>
            <div className="mt-6 text-center text-sm text-muted-foreground">
              <Link href="/recuperar-senha" className="font-semibold text-institutional-blue hover:underline">Recuperar palavra-passe</Link>
              <span className="mx-2">|</span>
              <Link href="/" className="font-semibold text-institutional-blue hover:underline">Voltar a pagina institucional</Link>
            </div>
            <div className="mt-6 border-t border-border/75 pt-4 text-center text-xs font-medium uppercase text-muted-foreground">
              Ministerio das Financas | Plataforma SGFE
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
