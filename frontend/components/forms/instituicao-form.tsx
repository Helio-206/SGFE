"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Save } from "lucide-react";
import { useEffect, useState } from "react";
import { useForm, type Resolver } from "react-hook-form";
import { z } from "zod";
import { FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { apiFetch, type Instituicao } from "@/lib/api";

const baseSchema = z.object({
  codigo: z.string().min(3).max(20).regex(/^[A-Za-z0-9-]+$/, "Use apenas letras, numeros e hifen."),
  tipo: z.string().min(2).max(50),
  nome: z.string().min(3).max(150),
  responsavel: z.string().min(3).max(100)
});

const createSchema = baseSchema.extend({
  emailResponsavel: z.string().email("Informe um email valido.").max(100),
  senhaResponsavel: z.string().min(8, "Use pelo menos 8 caracteres.").max(100)
});

const editSchema = baseSchema.extend({
  emailResponsavel: z.string().optional(),
  senhaResponsavel: z.string().optional()
});

type Values = z.infer<typeof createSchema>;

const emptyValues: Values = {
  codigo: "",
  tipo: "",
  nome: "",
  responsavel: "",
  emailResponsavel: "",
  senhaResponsavel: ""
};

type InstituicaoFormProps = {
  instituicao?: Instituicao | null;
  onCancel?: () => void;
  onSaved?: () => void;
};

export function InstituicaoForm({ instituicao, onCancel, onSaved }: InstituicaoFormProps) {
  const isEditing = Boolean(instituicao);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const form = useForm<Values>({
    resolver: zodResolver(isEditing ? editSchema : createSchema) as Resolver<Values>,
    defaultValues: emptyValues
  });

  useEffect(() => {
    setErrorMessage(null);
    form.reset(
      instituicao
        ? {
            codigo: instituicao.codigo,
            tipo: instituicao.tipo,
            nome: instituicao.nome,
            responsavel: instituicao.responsavel,
            emailResponsavel: "",
            senhaResponsavel: ""
          }
        : emptyValues
    );
  }, [form, instituicao]);

  async function onSubmit(values: Values) {
    setErrorMessage(null);
    const payload = isEditing
      ? {
          codigo: values.codigo,
          tipo: values.tipo,
          nome: values.nome,
          responsavel: values.responsavel
        }
      : values;

    try {
      await apiFetch(isEditing ? `/instituicoes/${instituicao?.id}` : "/instituicoes", {
        method: isEditing ? "PUT" : "POST",
        body: JSON.stringify(payload)
      });
      form.reset(emptyValues);
      onSaved?.();
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Nao foi possivel guardar a Unidade Orcamental.");
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>{isEditing ? "Editar Unidade Orcamental" : "Nova Unidade Orcamental"}</CardTitle>
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
          {!isEditing ? (
            <>
              <FormField label="Email do responsavel" error={form.formState.errors.emailResponsavel?.message}>
                <Input
                  type="email"
                  placeholder="responsavel@instituicao.gov.ao"
                  {...form.register("emailResponsavel")}
                />
              </FormField>
              <FormField label="Senha inicial" error={form.formState.errors.senhaResponsavel?.message}>
                <Input type="password" placeholder="Minimo 8 caracteres" {...form.register("senhaResponsavel")} />
              </FormField>
            </>
          ) : null}
          {errorMessage ? (
            <p className="rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red lg:col-span-2">
              {errorMessage}
            </p>
          ) : null}
          <div className="flex flex-wrap gap-3 lg:col-span-2">
            <Button type="submit" disabled={form.formState.isSubmitting}>
              <Save className="h-4 w-4" />
              {isEditing ? "Guardar alteracoes" : "Guardar"}
            </Button>
            {isEditing ? (
              <Button type="button" variant="secondary" onClick={onCancel}>
                Cancelar
              </Button>
            ) : null}
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
