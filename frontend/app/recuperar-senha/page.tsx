"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { API_BASE_URL } from "@/lib/api";

const schema = z.object({ email: z.string().email() });
type Values = z.infer<typeof schema>;

export default function RecuperarSenhaPage() {
  const [message, setMessage] = useState<string | null>(null);
  const form = useForm<Values>({ resolver: zodResolver(schema), defaultValues: { email: "" } });

  async function submit(values: Values) {
    const response = await fetch(`${API_BASE_URL}/auth/forgot-password`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(values)
    });
    const data = await response.json();
    setMessage(data.message ?? "Pedido recebido.");
  }

  return (
    <main className="flex min-h-screen items-center justify-center bg-institutional-deep px-5">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Recuperar palavra-passe</CardTitle>
        </CardHeader>
        <CardContent>
          {message ? <div className="mb-4 rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">{message}</div> : null}
          <form className="space-y-4" onSubmit={form.handleSubmit(submit)}>
            <FormField label="Email institucional" error={form.formState.errors.email?.message}>
              <Input type="email" {...form.register("email")} />
            </FormField>
            <Button className="w-full" type="submit">Enviar instrucoes</Button>
          </form>
          <Link href="/login" className="mt-5 block text-center text-sm font-semibold text-institutional-blue">Voltar ao login</Link>
        </CardContent>
      </Card>
    </main>
  );
}
