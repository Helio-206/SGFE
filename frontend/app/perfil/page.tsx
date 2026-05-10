"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useQuery } from "@tanstack/react-query";
import { Save } from "lucide-react";
import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { FormField } from "@/components/forms/form-field";
import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { apiFetch, type User } from "@/lib/api";

const profileSchema = z.object({
  nome: z.string().min(3).max(100),
  email: z.string().email()
});

const passwordSchema = z.object({
  currentPassword: z.string().min(1),
  newPassword: z.string().min(8)
});

type ProfileValues = z.infer<typeof profileSchema>;
type PasswordValues = z.infer<typeof passwordSchema>;

export default function PerfilPage() {
  const { data, refetch } = useQuery({
    queryKey: ["me"],
    queryFn: () => apiFetch<User>("/users/me"),
    retry: false
  });

  const profile = useForm<ProfileValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: { nome: "", email: "" }
  });
  const password = useForm<PasswordValues>({
    resolver: zodResolver(passwordSchema),
    defaultValues: { currentPassword: "", newPassword: "" }
  });

  useEffect(() => {
    if (data) {
      profile.reset({ nome: data.nome, email: data.email });
    }
  }, [data, profile]);

  async function saveProfile(values: ProfileValues) {
    await apiFetch("/users/me", { method: "PUT", body: JSON.stringify(values) });
    await refetch();
  }

  async function savePassword(values: PasswordValues) {
    await apiFetch("/users/me/password", { method: "PATCH", body: JSON.stringify(values) });
    password.reset();
  }

  return (
    <AppShell title="Perfil do utilizador">
      <div className="grid gap-5 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Dados pessoais</CardTitle>
          </CardHeader>
          <CardContent>
            <form className="space-y-4" onSubmit={profile.handleSubmit(saveProfile)}>
              <FormField label="Nome" error={profile.formState.errors.nome?.message}>
                <Input {...profile.register("nome")} />
              </FormField>
              <FormField label="Email" error={profile.formState.errors.email?.message}>
                <Input type="email" {...profile.register("email")} />
              </FormField>
              <Button type="submit" disabled={profile.formState.isSubmitting}>
                <Save className="h-4 w-4" />
                Guardar perfil
              </Button>
            </form>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Seguranca da conta</CardTitle>
          </CardHeader>
          <CardContent>
            <form className="space-y-4" onSubmit={password.handleSubmit(savePassword)}>
              <FormField label="Palavra-passe actual" error={password.formState.errors.currentPassword?.message}>
                <Input type="password" {...password.register("currentPassword")} />
              </FormField>
              <FormField label="Nova palavra-passe" error={password.formState.errors.newPassword?.message}>
                <Input type="password" {...password.register("newPassword")} />
              </FormField>
              <Button type="submit" variant="gold" disabled={password.formState.isSubmitting}>
                Alterar palavra-passe
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppShell>
  );
}
