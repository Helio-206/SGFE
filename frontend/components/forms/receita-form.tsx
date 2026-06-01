"use client";

import { useEffect, useMemo, useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { FileCheck2, PlusCircle, Save, Send, X } from "lucide-react";
import { apiFetch, type AutorizacaoReceitaRetroativa, type Classificacao, type Instituicao, type PageResponse, type Receita } from "@/lib/api";
import type { Role } from "@/lib/rbac";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

interface ReceitaFormProps {
  receita?: Receita;
  onSuccess?: () => void;
}

type FonteReceita = Receita["fonteReceita"];

type SessionUser = {
  role: Role;
};

const fonteOptions: Array<{ value: FonteReceita; label: string }> = [
  { value: "PETROLIFERA", label: "Petrolifera" },
  { value: "NAO_PETROLIFERA", label: "Nao petrolifera" },
  { value: "PATRIMONIAL", label: "Patrimonial" }
];

const selectClassName = "focus-ring mt-1 h-10 w-full rounded-md border border-input bg-surface-strong/90 px-3 text-sm text-institutional-ink shadow-line hover:border-institutional-blue/40 disabled:cursor-not-allowed disabled:bg-surface-muted disabled:text-muted-foreground";

function todayInputValue() {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  return now.toISOString().slice(0, 10);
}

function yesterdayInputValue() {
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  yesterday.setMinutes(yesterday.getMinutes() - yesterday.getTimezoneOffset());
  return yesterday.toISOString().slice(0, 10);
}

export function ReceitaForm({ receita, onSuccess }: ReceitaFormProps) {
  const queryClient = useQueryClient();
  const today = todayInputValue();
  const yesterday = yesterdayInputValue();
  const [isOpen, setIsOpen] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [selectedAutorizacaoId, setSelectedAutorizacaoId] = useState("");
  const [retroRequest, setRetroRequest] = useState({ dataRegistro: "", motivo: "" });
  const [formData, setFormData] = useState({
    idInst: receita?.idInst ? String(receita.idInst) : "",
    fonteReceita: receita?.fonteReceita || "",
    dataRegistro: receita?.dataRegistro || today,
    valorArrecadado: receita?.valorArrecadado ? String(receita.valorArrecadado) : "",
    idClasse: receita?.idClasse ? String(receita.idClasse) : ""
  });

  const { data: user } = useQuery({
    queryKey: ["auth", "me"],
    queryFn: () => apiFetch<SessionUser>("/auth/me"),
    retry: false
  });
  const isAdmin = user?.role === "ADMIN";
  const canRequestRetroAuthorization = user?.role === "ADMIN" || user?.role === "GESTOR";

  const { data: instituicoes } = useQuery({
    queryKey: ["instituicoes"],
    queryFn: () => apiFetch<PageResponse<Instituicao>>("/instituicoes?size=100"),
    enabled: isAdmin
  });

  const { data: classificacoes } = useQuery({
    queryKey: ["classificacoes"],
    queryFn: () => apiFetch<PageResponse<Classificacao>>("/classificacoes?size=100")
  });

  const { data: autorizacoes } = useQuery({
    queryKey: ["receitas", "autorizacoes-retroativas"],
    queryFn: () => apiFetch<PageResponse<AutorizacaoReceitaRetroativa>>("/receitas/autorizacoes-retroativas?size=100&sort=createdAt,desc"),
    enabled: isAdmin,
    retry: false
  });

  const autorizacoesDisponiveis = useMemo(
    () => autorizacoes?.content.filter((item) => item.status === "AUTORIZADA") ?? [],
    [autorizacoes]
  );
  const selectedAutorizacao = autorizacoesDisponiveis.find((item) => String(item.id) === selectedAutorizacaoId);

  useEffect(() => {
    if (selectedAutorizacao) {
      setFormData((current) => ({
        ...current,
        idInst: String(selectedAutorizacao.idInst),
        dataRegistro: selectedAutorizacao.dataRegistro
      }));
      return;
    }
    setFormData((current) => ({ ...current, dataRegistro: today }));
  }, [selectedAutorizacao, today]);

  const { mutate: save, isPending } = useMutation({
    mutationFn: async () => {
      setErrorMessage(null);
      setSuccessMessage(null);
      return apiFetch<Receita>("/receitas", {
        method: "POST",
        body: JSON.stringify({
          idInst: isAdmin ? formData.idInst : undefined,
          fonteReceita: formData.fonteReceita,
          dataRegistro: selectedAutorizacao ? selectedAutorizacao.dataRegistro : today,
          valorArrecadado: formData.valorArrecadado,
          idClasse: formData.idClasse,
          idAutorizacaoRetroativa: selectedAutorizacao?.id
        })
      });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["receitas"] });
      queryClient.invalidateQueries({ queryKey: ["receitas", "autorizacoes-retroativas"] });
      setSuccessMessage("Receita criada com sucesso.");
      setIsOpen(false);
      setSelectedAutorizacaoId("");
      setFormData({
        idInst: "",
        fonteReceita: "",
        dataRegistro: today,
        valorArrecadado: "",
        idClasse: ""
      });
      onSuccess?.();
    },
    onError: (error: Error) => {
      setErrorMessage(error.message || "Erro ao criar receita.");
    }
  });

  const { mutate: solicitarAutorizacao, isPending: isRequestingAuthorization } = useMutation({
    mutationFn: async () => {
      setErrorMessage(null);
      setSuccessMessage(null);
      return apiFetch<AutorizacaoReceitaRetroativa>("/receitas/autorizacoes-retroativas", {
        method: "POST",
        body: JSON.stringify({
          idInst: isAdmin ? formData.idInst : undefined,
          dataRegistro: retroRequest.dataRegistro,
          motivo: retroRequest.motivo
        })
      });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["receitas", "autorizacoes-retroativas"] });
      setSuccessMessage("Pedido enviado ao Auditor.");
      setRetroRequest({ dataRegistro: "", motivo: "" });
    },
    onError: (error: Error) => {
      setErrorMessage(error.message || "Nao foi possivel pedir autorizacao.");
    }
  });

  function toggleForm() {
    setErrorMessage(null);
    setSuccessMessage(null);
    setIsOpen((current) => !current);
    setFormData((current) => ({ ...current, dataRegistro: selectedAutorizacao?.dataRegistro ?? today }));
  }

  return (
    <div className="space-y-4">
      <Button onClick={toggleForm} variant="default" size="sm">
        <PlusCircle className="h-4 w-4" />
        Nova Receita RUPE
      </Button>

      {successMessage ? (
        <p className="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
          {successMessage}
        </p>
      ) : null}

      {isOpen && (
        <div className="space-y-4 rounded-lg border border-border/80 bg-surface/95 p-4 shadow-quiet">
          <div className="border-b border-border/75 pb-3">
            <h2 className="text-sm font-bold text-institutional-ink">Registar receita RUPE</h2>
            <p className="mt-1 text-sm text-muted-foreground">A data corrente e assumida automaticamente; registos retroativos exigem autorizacao.</p>
          </div>
          <div className="grid gap-4 lg:grid-cols-2">
          {isAdmin ? (
            <div>
              <Label>Unidade Orcamental</Label>
              <select
                value={formData.idInst}
                onChange={(e) => setFormData({ ...formData, idInst: e.target.value })}
                className={selectClassName}
                disabled={Boolean(selectedAutorizacao)}
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

          <div>
            <Label>Fonte de Receita</Label>
            <select
              value={formData.fonteReceita}
              onChange={(e) => setFormData({ ...formData, fonteReceita: e.target.value })}
              className={selectClassName}
            >
              <option value="">Selecione uma fonte</option>
              {fonteOptions.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>

          <div>
            <Label>Data de Registro</Label>
            <Input type="date" value={formData.dataRegistro} min={formData.dataRegistro} max={formData.dataRegistro} disabled className="mt-1" />
          </div>

          <div>
            <Label>Valor Arrecadado (AOA)</Label>
            <Input
              type="number"
              placeholder="0.00"
              step="0.01"
              value={formData.valorArrecadado}
              onChange={(e) => setFormData({ ...formData, valorArrecadado: e.target.value })}
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
              <option value="">Selecione uma classificacao</option>
              {classificacoes?.content.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.codigo} - {c.descricao}
                </option>
              ))}
            </select>
          </div>
          </div>

          {canRequestRetroAuthorization ? (
            <div className="space-y-3 rounded-md border border-border/80 bg-surface-strong/70 p-3">
              <div className="flex items-start gap-3 border-b border-border/75 pb-3">
                <div className="rounded-md bg-institutional-mist p-2 text-institutional-blue">
                  <FileCheck2 className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="text-sm font-bold text-institutional-ink">Receita retroativa</h3>
                  <p className="mt-1 text-sm text-muted-foreground">
                    {isAdmin
                      ? "Use apenas autorizacoes aprovadas ou envie uma justificacao para auditoria."
                      : "Envie uma justificacao para auditoria; a criacao retroativa sera concluida pelo Admin apos aprovacao."}
                  </p>
                </div>
              </div>
              {isAdmin ? (
                <div>
                  <Label>Autorizacao retroativa aprovada</Label>
                  <select
                    value={selectedAutorizacaoId}
                    onChange={(e) => setSelectedAutorizacaoId(e.target.value)}
                    className={selectClassName}
                  >
                    <option value="">Sem autorizacao retroativa</option>
                    {autorizacoesDisponiveis.map((autorizacao) => (
                      <option key={autorizacao.id} value={autorizacao.id}>
                        #{autorizacao.id} - {autorizacao.codigoUo} - {autorizacao.dataRegistro} ({autorizacao.diasAtraso} dias)
                      </option>
                    ))}
                  </select>
                </div>
              ) : null}

              <div className="grid gap-3 lg:grid-cols-[220px_1fr_auto]">
                <div>
                  <Label>Data em atraso</Label>
                  <Input
                    type="date"
                    value={retroRequest.dataRegistro}
                    max={yesterday}
                    onChange={(e) => setRetroRequest({ ...retroRequest, dataRegistro: e.target.value })}
                    className="mt-1"
                  />
                </div>
                <div>
                  <Label>Motivo</Label>
                  <Input
                    value={retroRequest.motivo}
                    onChange={(e) => setRetroRequest({ ...retroRequest, motivo: e.target.value })}
                    placeholder="Justifique o atraso"
                    className="mt-1"
                  />
                </div>
                <div className="flex items-end">
                  <Button onClick={() => solicitarAutorizacao()} disabled={isRequestingAuthorization} type="button" variant="secondary">
                    <Send className="h-4 w-4" />
                    {isRequestingAuthorization ? "Enviando..." : "Pedir autorizacao"}
                  </Button>
                </div>
              </div>
            </div>
          ) : null}

          {errorMessage ? (
            <p className="rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red">
              {errorMessage}
            </p>
          ) : null}

          <div className="flex gap-2">
            <Button onClick={() => save()} disabled={isPending} size="sm">
              <Save className="h-4 w-4" />
              {isPending ? "Salvando..." : "Criar Receita"}
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
