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
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { API_BASE_URL } from "@/lib/api";

const schema = z.object({
  email: z.string().email("Informe um email institucional valido."),
  password: z.string().min(8, "A palavra-passe deve ter pelo menos 8 caracteres.")
});

type LoginValues = z.infer<typeof schema>;

function resolveNextPath(nextPath?: string) {
  if (nextPath && nextPath.startsWith("/")) {
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
    <main className="min-h-screen bg-[#f4f6f8] px-5 py-10">
      <section className="mx-auto grid min-h-[calc(100vh-5rem)] w-full max-w-6xl overflow-hidden rounded-lg border border-border bg-white shadow-institutional lg:grid-cols-[0.9fr_1.1fr]">
        <div className="flex flex-col justify-between bg-institutional-deep p-6 text-white md:p-8">
          <div>
            <InstitutionalBrand compact inverted />
            <div className="mt-12 max-w-md">
              <p className="text-xs font-bold uppercase text-institutional-gold">Acesso institucional</p>
              <h1 className="mt-4 text-3xl font-bold leading-tight md:text-4xl">Sistema de Gestao das Financas do Estado</h1>
              <p className="mt-4 text-sm leading-6 text-slate-300">
                Ambiente reservado para utilizadores autorizados do circuito financeiro publico.
              </p>
            </div>
          </div>
          <div className="mt-10 rounded-lg border border-white/10 bg-white/5 p-4">
            <div className="flex items-center gap-3">
              <ShieldCheck className="h-5 w-5 text-institutional-gold" />
              <div>
                <div className="text-sm font-semibold text-white">Sessao protegida</div>
                <div className="text-xs text-slate-300">Africa/Luanda | SGFE</div>
              </div>
            </div>
          </div>
        </div>
        <div className="flex items-center justify-center p-6 md:p-10">
          <Card className="w-full max-w-md border-0 bg-transparent shadow-none">
            <CardContent className="p-0">
            <div className="mb-7">
              <p className="text-xs font-bold uppercase text-institutional-gold">SGFE</p>
              <h2 className="mt-2 text-3xl font-bold text-institutional-ink">Iniciar sessao</h2>
              <p className="mt-3 text-sm leading-6 text-slate-600">Use a sua credencial institucional para continuar.</p>
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
            <div className="mt-6 border-t border-slate-200 pt-4 text-center text-xs font-medium uppercase text-slate-500">
              Ministerio das Financas | Plataforma SGFE
            </div>
          </CardContent>
        </Card>
        </div>
      </section>
    </main>
  );
}
