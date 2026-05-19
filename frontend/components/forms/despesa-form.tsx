"use client";

import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { PlusCircle, Save, X } from "lucide-react";
import { apiFetch, type Classificacao, type Instituicao, type PageResponse, type Despesa } from "@/lib/api";
import type { Role } from "@/lib/rbac";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

interface DespesaFormProps {
  onSuccess?: () => void;
}

type SessionUser = {
  role: Role;
};

const selectClassName = "focus-ring mt-1 h-10 w-full rounded-md border border-input bg-white px-3 text-sm text-institutional-ink shadow-line hover:border-institutional-blue/35";

export function DespesaForm({ onSuccess }: DespesaFormProps) {
  const queryClient = useQueryClient();
  const [isOpen, setIsOpen] = useState(false);
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);
  const [formData, setFormData] = useState({
    idInst: "",
    descricao: "",
    dataRegistro: new Date().toISOString().split("T")[0],
    valorBruto: "",
    idClasse: "",
  });

  const { data: user } = useQuery({
    queryKey: ["auth", "me"],
    queryFn: () => apiFetch<SessionUser>("/auth/me"),
    retry: false,
  });
  const isAdmin = user?.role === "ADMIN";

  const { data: instituicoes } = useQuery({
    queryKey: ["instituicoes"],
    queryFn: () => apiFetch<PageResponse<Instituicao>>("/instituicoes?size=100"),
    enabled: isAdmin,
  });

  const { data: classificacoes } = useQuery({
    queryKey: ["classificacoes"],
    queryFn: () => apiFetch<PageResponse<Classificacao>>("/classificacoes?size=100"),
  });

  const { mutate: save, isPending } = useMutation({
    mutationFn: async () => {
      setMessage(null);
      return apiFetch<Despesa>("/despesas", {
        method: "POST",
        body: JSON.stringify({
          ...formData,
          idInst: isAdmin ? formData.idInst : undefined,
        }),
      });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["despesas"] });
      setMessage({ type: "success", text: "Despesa criada com sucesso." });
      setIsOpen(false);
      setFormData({
        idInst: "",
        descricao: "",
        dataRegistro: new Date().toISOString().split("T")[0],
        valorBruto: "",
        idClasse: "",
      });
      onSuccess?.();
    },
    onError: (error: Error) => {
      setMessage({ type: "error", text: error.message || "Nao foi possivel criar a despesa." });
    },
  });

  return (
    <div className="space-y-4">
      <Button onClick={() => setIsOpen(!isOpen)} variant="default" size="sm">
        <PlusCircle className="h-4 w-4" />
        Nova Despesa
      </Button>

      {message ? (
        <p className={message.type === "success" ? "rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" : "rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red"}>
          {message.text}
        </p>
      ) : null}

      {isOpen && (
        <div className="space-y-4 rounded-lg border border-border bg-white p-4 shadow-line">
          <div className="border-b border-border pb-3">
            <h2 className="text-sm font-bold text-institutional-ink">Registar nova despesa</h2>
            <p className="mt-1 text-sm text-muted-foreground">A despesa entra no fluxo financeiro com estado inicial controlado pelo backend.</p>
          </div>
          <div className="grid gap-4 lg:grid-cols-2">
          {isAdmin ? (
            <div>
              <Label>Unidade Orcamental</Label>
              <select
                value={formData.idInst}
                onChange={(e) => setFormData({ ...formData, idInst: e.target.value })}
                className={selectClassName}
              >
                <option value="">Selecione uma UO</option>
                {instituicoes?.content.map((inst) => (
                  <option key={inst.id} value={inst.id}>
                    {inst.codigo} - {inst.nome}
                  </option>
                ))}
              </select>
            </div>
          ) : null}

          <div className={isAdmin ? "" : "lg:col-span-2"}>
            <Label>Descrição</Label>
            <Input
              placeholder="Descrição da despesa"
              value={formData.descricao}
              onChange={(e) => setFormData({ ...formData, descricao: e.target.value })}
              className="mt-1"
            />
          </div>

          <div>
            <Label>Data de Registro</Label>
            <Input
              type="date"
              value={formData.dataRegistro}
              onChange={(e) => setFormData({ ...formData, dataRegistro: e.target.value })}
              className="mt-1"
            />
          </div>

          <div>
            <Label>Valor Bruto (AOA)</Label>
            <Input
              type="number"
              placeholder="0.00"
              step="0.01"
              value={formData.valorBruto}
              onChange={(e) => setFormData({ ...formData, valorBruto: e.target.value })}
              className="mt-1"
            />
          </div>

          <div>
            <Label>Classificacao Economica</Label>
            <select
              value={formData.idClasse}
              onChange={(e) => setFormData({ ...formData, idClasse: e.target.value })}
              className={selectClassName}
            >
              <option value="">Selecione uma classificação</option>
              {classificacoes?.content.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.codigo} - {c.descricao}
                </option>
              ))}
            </select>
          </div>
          </div>

          <div className="flex gap-2">
            <Button onClick={() => save()} disabled={isPending} size="sm">
              <Save className="h-4 w-4" />
              {isPending ? "Salvando..." : "Criar Despesa"}
            </Button>
            <Button onClick={() => setIsOpen(false)} variant="secondary" size="sm">
              <X className="h-4 w-4" />
              Cancelar
            </Button>
          </div>
        </div>
      )}
    </div>
  );
}
