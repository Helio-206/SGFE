"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Eye } from "lucide-react";
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
    <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(210,169,40,0.18),_transparent_26%),linear-gradient(180deg,#f4f6f8_0%,#e8edf2_52%,#dde6ee_100%)] px-5 py-10">
      <div className="absolute inset-x-0 top-0 h-72 bg-[linear-gradient(180deg,rgba(7,27,51,0.96),rgba(18,53,91,0.88))]" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(255,255,255,0.2),transparent_18%),radial-gradient(circle_at_85%_25%,rgba(210,169,40,0.15),transparent_16%)]" />
      <section className="relative w-full max-w-md">
        <div className="mb-6 flex justify-center">
          <div className="rounded-[28px] border border-white/18 bg-white/8 px-6 py-4 shadow-[0_24px_70px_rgba(6,18,33,0.26)] backdrop-blur-xl">
            <InstitutionalBrand />
          </div>
        </div>
        <Card className="w-full max-w-md border-white/70 bg-white/92 shadow-[0_32px_100px_rgba(8,26,45,0.16)] backdrop-blur-xl">
          <CardContent className="p-7 md:p-8">
            <div className="mb-7 text-center">
              <p className="text-xs font-bold uppercase tracking-[0.22em] text-institutional-gold">SGFE</p>
              <h2 className="mt-2 text-3xl font-bold text-institutional-ink">Iniciar sessao</h2>
              <p className="mt-3 text-sm leading-6 text-slate-600">Acesso institucional com identidade visual oficial do Ministerio das Financas.</p>
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
              <span className="mx-2">·</span>
              <Link href="/" className="font-semibold text-institutional-blue hover:underline">Voltar a pagina institucional</Link>
            </div>
            <div className="mt-6 border-t border-slate-200 pt-4 text-center text-xs font-medium uppercase tracking-[0.18em] text-slate-500">
              Ministerio das Financas · Plataforma SGFE
            </div>
          </CardContent>
        </Card>
      </section>
    </main>
  );
}