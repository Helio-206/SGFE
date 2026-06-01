"use client";

import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { PlusCircle, Save, X } from "lucide-react";
import { apiFetch, type Instituicao, type Orcamento, type PageResponse } from "@/lib/api";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

interface OrcamentoFormProps {
  orcamento?: Orcamento;
  onSuccess?: () => void;
}

const selectClassName = "focus-ring mt-1 h-10 w-full rounded-md border border-input bg-surface-strong/90 px-3 text-sm text-institutional-ink shadow-line hover:border-institutional-blue/40";

export function OrcamentoForm({ orcamento, onSuccess }: OrcamentoFormProps) {
  const queryClient = useQueryClient();
  const [isOpen, setIsOpen] = useState(false);
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);
  const [formData, setFormData] = useState({
    idInst: orcamento?.idInst || "",
    valorTotal: orcamento?.valorTotal || "",
  });

  const { data: instituicoes } = useQuery({
    queryKey: ["instituicoes"],
    queryFn: () => apiFetch<PageResponse<Instituicao>>("/instituicoes?size=100"),
  });

  const { mutate: save, isPending } = useMutation({
    mutationFn: async () => {
      setMessage(null);
      const method = orcamento ? "PUT" : "POST";
      const url = orcamento ? `/orcamentos/${orcamento.id}` : "/orcamentos";
      return apiFetch<Orcamento>(url, { method, body: JSON.stringify(formData) });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["orcamentos"] });
      setMessage({ type: "success", text: orcamento ? "Tecto atualizado com sucesso." : "Tecto criado com sucesso." });
      setIsOpen(false);
      setFormData({ idInst: "", valorTotal: "" });
      onSuccess?.();
    },
    onError: (error: Error) => {
      setMessage({ type: "error", text: error.message || "Nao foi possivel guardar o tecto." });
    },
  });

  return (
    <div className="space-y-4">
      <Button onClick={() => setIsOpen(!isOpen)} variant="default" size="sm">
        <PlusCircle className="h-4 w-4" />
        {orcamento ? "Editar Tecto" : "Novo Tecto"}
      </Button>

      {message ? (
        <p className={message.type === "success" ? "rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700" : "rounded-md border border-institutional-red/30 bg-institutional-red/5 px-3 py-2 text-sm font-medium text-institutional-red"}>
          {message.text}
        </p>
      ) : null}

      {isOpen && (
        <div className="space-y-4 rounded-lg border border-border/80 bg-surface/95 p-4 shadow-quiet">
          <div className="border-b border-border/75 pb-3">
            <h2 className="text-sm font-bold text-institutional-ink">{orcamento ? "Editar tecto orcamental" : "Novo tecto orcamental"}</h2>
            <p className="mt-1 text-sm text-muted-foreground">O valor definido alimenta o controlo de saldo e execucao da UO.</p>
          </div>
          <div className="grid gap-4 lg:grid-cols-2">
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

          <div>
            <Label>Valor Total (AOA)</Label>
            <Input
              type="number"
              placeholder="0.00"
              step="0.01"
              value={formData.valorTotal}
              onChange={(e) => setFormData({ ...formData, valorTotal: e.target.value })}
              className="mt-1"
            />
          </div>
          </div>

          <div className="flex gap-2">
            <Button onClick={() => save()} disabled={isPending} size="sm">
              <Save className="h-4 w-4" />
              {isPending ? "Salvando..." : "Salvar"}
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
