"use client";

import { useEffect, useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { PlusCircle, Save, X } from "lucide-react";
import { apiFetch, type Classificacao } from "@/lib/api";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

interface ClassificacaoFormProps {
  classificacao?: Classificacao | null;
  onCancel?: () => void;
  onSuccess?: () => void;
}

const emptyForm = {
  codigo: "",
  descricao: "",
  tipo: ""
};

const selectClassName = "focus-ring mt-1 h-10 w-full rounded-md border border-input bg-white px-3 text-sm text-institutional-ink shadow-line hover:border-institutional-blue/35";

export function ClassificacaoForm({ classificacao, onCancel, onSuccess }: ClassificacaoFormProps) {
  const queryClient = useQueryClient();
  const isEditing = Boolean(classificacao);
  const [isOpen, setIsOpen] = useState(Boolean(classificacao));
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);
  const [formData, setFormData] = useState({
    codigo: classificacao?.codigo || "",
    descricao: classificacao?.descricao || "",
    tipo: classificacao?.tipo || ""
  });

  useEffect(() => {
    if (!classificacao) {
      return;
    }

    setIsOpen(true);
    setFormData({
      codigo: classificacao.codigo,
      descricao: classificacao.descricao,
      tipo: classificacao.tipo
    });
  }, [classificacao]);

  const { mutate: save, isPending } = useMutation({
    mutationFn: async () => {
      setMessage(null);
      const method = classificacao ? "PUT" : "POST";
      const url = classificacao ? `/classificacoes/${classificacao.id}` : "/classificacoes";
      return apiFetch<Classificacao>(url, { method, body: JSON.stringify(formData) });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["classificacoes"] });
      setMessage({ type: "success", text: classificacao ? "Classificacao atualizada com sucesso." : "Classificacao criada com sucesso." });
      setIsOpen(false);
      setFormData(emptyForm);
      onSuccess?.();
    },
    onError: (error: Error) => {
      setMessage({ type: "error", text: error.message || "Nao foi possivel guardar a classificacao." });
    }
  });

  const handleCancel = () => {
    setIsOpen(false);
    setFormData(emptyForm);
    onCancel?.();
  };

  return (
    <div className="space-y-4">
      {!isEditing && (
        <Button onClick={() => setIsOpen(!isOpen)} variant="default" size="sm">
          <PlusCircle className="h-4 w-4" />
          Nova Classificacao
        </Button>
      )}

      {message ? (
        <p className={message.type === "success" ? "rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" : "rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red"}>
          {message.text}
        </p>
      ) : null}

      {isOpen && (
        <div className="space-y-4 rounded-lg border border-border bg-white p-4 shadow-line">
          <div className="border-b border-border pb-3">
            <h2 className="text-sm font-bold text-institutional-ink">{isEditing ? "Editar classificacao economica" : "Nova classificacao economica"}</h2>
            <p className="mt-1 text-sm text-muted-foreground">Codigos consistentes facilitam o enquadramento de receitas e despesas.</p>
          </div>

          <div className="grid gap-4 lg:grid-cols-[180px_1fr_180px]">
          <div>
            <Label>Código</Label>
            <Input
              placeholder="Ex: 0101"
              value={formData.codigo}
              onChange={(e) => setFormData({ ...formData, codigo: e.target.value })}
              className="mt-1"
            />
          </div>

          <div>
            <Label>Descrição</Label>
            <Input
              placeholder="Descrição da classificação"
              value={formData.descricao}
              onChange={(e) => setFormData({ ...formData, descricao: e.target.value })}
              className="mt-1"
            />
          </div>

          <div>
            <Label>Tipo</Label>
            <select
              value={formData.tipo}
              onChange={(e) => setFormData({ ...formData, tipo: e.target.value })}
              className={selectClassName}
            >
              <option value="">Selecione o tipo</option>
              <option value="RECEITA">Receita</option>
              <option value="DESPESA">Despesa</option>
            </select>
          </div>
          </div>

          <div className="flex gap-2">
            <Button onClick={() => save()} disabled={isPending} size="sm">
              <Save className="h-4 w-4" />
              {isPending ? "Salvando..." : isEditing ? "Atualizar" : "Guardar"}
            </Button>
            <Button onClick={handleCancel} variant="secondary" size="sm">
              <X className="h-4 w-4" />
              Cancelar
            </Button>
          </div>
        </div>
      )}
    </div>
  );
}
