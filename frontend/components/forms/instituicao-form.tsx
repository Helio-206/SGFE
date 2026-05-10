"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Save } from "lucide-react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { apiFetch } from "@/lib/api";

const schema = z.object({
  codigo: z.string().min(3).max(20).regex(/^[A-Za-z0-9-]+$/, "Use apenas letras, numeros e hifen."),
  tipo: z.string().min(2).max(50),
  nome: z.string().min(3).max(150),
  responsavel: z.string().min(3).max(100)
});

type Values = z.infer<typeof schema>;

export function InstituicaoForm({ onSaved }: { onSaved?: () => void }) {
  const form = useForm<Values>({
    resolver: zodResolver(schema),
    defaultValues: { codigo: "", tipo: "", nome: "", responsavel: "" }
  });

  async function onSubmit(values: Values) {
    await apiFetch("/instituicoes", {
      method: "POST",
      body: JSON.stringify(values)
    });
    form.reset();
    onSaved?.();
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Nova Unidade Orcamental</CardTitle>
      </CardHeader>
      <CardContent>
        <form className="grid gap-4 lg:grid-cols-2" onSubmit={form.handleSubmit(onSubmit)}>
          <FormField label="Codigo UO" error={form.formState.errors.codigo?.message}>
            <Input placeholder="UO-MFN" {...form.register("codigo")} />
          </FormField>
          <FormField label="Tipo" error={form.formState.errors.tipo?.message}>
            <Input placeholder="Ministerio" {...form.register("tipo")} />
          </FormField>
          <FormField label="Nome" error={form.formState.errors.nome?.message}>
            <Input placeholder="Ministerio das Financas" {...form.register("nome")} />
          </FormField>
          <FormField label="Responsavel" error={form.formState.errors.responsavel?.message}>
            <Input placeholder="Responsavel institucional" {...form.register("responsavel")} />
          </FormField>
          <div className="lg:col-span-2">
            <Button type="submit" disabled={form.formState.isSubmitting}>
              <Save className="h-4 w-4" />
              Guardar
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
