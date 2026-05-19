"use client";

import { motion } from "framer-motion";
import { ArrowRight, CheckCircle2, FileCheck2, Landmark, Layers3, ShieldCheck } from "lucide-react";
import Link from "next/link";
import { PublicHeader } from "@/components/layout/public-header";
import { Button } from "@/components/ui/button";

const modules = [
  { title: "Unidades Orcamentais", text: "Identificacao, responsabilizacao e operacao distribuida por UO." },
  { title: "Orcamento e tectos", text: "Controlo do limite, saldo disponivel e compromisso acumulado." },
  { title: "Receitas RUPE", text: "Arrecadacao integrada com classificacao e leitura consolidada." },
  { title: "Despesas", text: "Cabimentacao, liquidacao e pagamento com rastreabilidade completa." },
  { title: "Auditoria", text: "Linha temporal de eventos, severidade e contexto operacional." },
  { title: "Relatorios", text: "Exportacoes operacionais e executivas com controlo de acesso." }
];

const flow = [
  { step: "01", title: "Planeamento", text: "A UO recebe tecto, enquadramento e classificacao economica validada." },
  { step: "02", title: "Execucao", text: "Receitas e despesas seguem estados formais com verificacoes antes de cada transicao." },
  { step: "03", title: "Supervisao", text: "Perfis autorizados acompanham risco, desvios e exposicao por entidade." },
  { step: "04", title: "Prestacao", text: "Auditoria e relatorios fecham o ciclo com memoria institucional persistente." }
];

const partners = [
  "Ministerio das Financas",
  "Tesouro Nacional",
  "Receitas Publicas do Estado",
  "Unidades Orcamentais",
  "Inspeccao e Auditoria",
  "Conta Geral do Estado"
];

export default function LandingPage() {
  return (
    <main className="bg-[#f6f7f4] text-institutional-ink">
      <section className="institutional-hero relative min-h-[100vh] overflow-hidden text-white">
        <PublicHeader />
        <div className="mx-auto flex min-h-[100vh] max-w-7xl items-center px-5 pb-16 pt-32 lg:pt-36">
          <motion.div
            initial={{ opacity: 0, y: 18 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="max-w-4xl"
          >
            <p className="text-sm font-semibold uppercase text-institutional-gold">Infraestrutura operacional do Estado</p>
            <h1 className="mt-5 max-w-4xl text-5xl font-bold leading-[1.02] md:text-7xl">
              Execucao financeira publica com leitura nacional, controlo institucional e prova auditavel.
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-100/92">
              O SGFE organiza orcamento, receita, despesa e auditoria num unico percurso, reduz a friccao operacional e reforca a confianca na informacao financeira do Estado.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Button asChild variant="gold" size="lg">
                <Link href="/login">
                  Aceder ao sistema
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </Button>
              <Button asChild variant="secondary" size="lg">
                <Link href="#plataforma">Ver plataforma</Link>
              </Button>
            </div>
            <div className="mt-10 grid gap-4 sm:grid-cols-3">
              {[
                { value: "360", label: "visao operacional", detail: "tecto, receita e despesa num mesmo quadro" },
                { value: "RBAC", label: "governanca", detail: "permissoes por papel e por contexto" },
                { value: "24/7", label: "rastreabilidade", detail: "eventos criticos persistidos para auditoria" }
              ].map((item) => (
                <div key={item.label} className="rounded-lg border border-white/10 bg-white/8 p-5 backdrop-blur-md">
                  <div className="text-3xl font-bold text-white">{item.value}</div>
                  <div className="mt-3 text-sm font-semibold uppercase text-institutional-gold">{item.label}</div>
                  <p className="mt-2 text-sm leading-6 text-slate-200">{item.detail}</p>
                </div>
              ))}
            </div>
          </motion.div>
        </div>
      </section>

      <section className="border-b border-black/5 bg-[#ebece7] py-16">
        <div className="mx-auto grid max-w-7xl gap-8 px-5 md:grid-cols-[0.8fr_1.2fr]">
          <div>
            <p className="text-xs font-bold uppercase text-institutional-gold">Desafio operacional</p>
            <h2 className="mt-3 text-3xl font-bold text-institutional-ink">Menos dispersao, mais prova institucional</h2>
          </div>
          <p className="text-lg leading-8 text-slate-700">
            Quando a informacao financeira fica repartida por silos, a decisao atrasa, a validacao enfraquece e a consolidacao nacional perde confianca. O SGFE reconfigura esse percurso com regras unificadas e leitura continua do estado de cada operacao.
          </p>
        </div>
      </section>

      <section id="fluxo" className="fine-grid py-20">
        <div className="mx-auto max-w-7xl px-5">
          <div className="max-w-3xl">
            <p className="text-xs font-bold uppercase text-institutional-gold">Fluxo leve e rastreavel</p>
            <h2 className="mt-3 text-4xl font-bold">O ciclo financeiro cabe num percurso legivel, nao num emaranhado de ecras.</h2>
          </div>
          <div className="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            {flow.map((item) => (
              <div key={item.step} className="rounded-lg border border-black/5 bg-white/80 p-6 shadow-[0_18px_55px_rgba(8,26,45,0.08)]">
                <div className="text-sm font-semibold uppercase text-institutional-gold">Etapa {item.step}</div>
                <h3 className="mt-4 text-2xl font-bold text-institutional-ink">{item.title}</h3>
                <p className="mt-3 leading-7 text-slate-700">{item.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="plataforma" className="border-y border-black/5 bg-white py-20">
        <div className="mx-auto max-w-7xl px-5">
          <div className="flex items-end justify-between gap-5">
            <div>
              <p className="text-xs font-bold uppercase text-institutional-gold">Plataforma</p>
              <h2 className="mt-3 text-4xl font-bold">Blocos funcionais desenhados para a administracao publica</h2>
            </div>
          </div>
          <div className="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {modules.map((module) => (
              <div key={module.title} className="rounded-lg border border-black/5 bg-[#fbfaf7] p-6 shadow-[0_20px_65px_rgba(8,26,45,0.06)]">
                <CheckCircle2 className="h-5 w-5 text-institutional-gold" />
                <div className="mt-4 text-xl font-semibold">{module.title}</div>
                <p className="mt-3 leading-7 text-slate-700">{module.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-institutional-deep py-20 text-white">
        <div className="mx-auto grid max-w-7xl gap-6 px-5 md:grid-cols-3">
          {[
            { icon: ShieldCheck, title: "Transparencia operacional", text: "Estados, severidades e registos criticos ficam legiveis para escrutinio institucional." },
            { icon: Layers3, title: "Consolidacao", text: "Receitas, despesas e tectos sobem para leitura nacional sem perder o detalhe da entidade." },
            { icon: FileCheck2, title: "Seguranca aplicada", text: "Sessao por cookies HttpOnly, RBAC, refresh controlado e auditoria persistente." }
          ].map((item) => (
            <div key={item.title} className="rounded-lg border border-white/10 bg-white/5 p-6">
              <item.icon className="h-6 w-6 text-institutional-gold" />
              <h3 className="mt-5 text-lg font-bold">{item.title}</h3>
              <p className="mt-3 leading-7 text-slate-300">{item.text}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="parceiros" className="py-20">
        <div className="mx-auto max-w-7xl px-5">
          <div className="max-w-3xl">
            <p className="text-xs font-bold uppercase text-institutional-gold">Parceiros institucionais</p>
            <h2 className="mt-3 text-4xl font-bold">Entidades centrais do ecossistema financeiro do Estado</h2>
          </div>
          <div className="mt-10 overflow-x-auto pb-3">
            <div className="flex min-w-max gap-3">
              {partners.map((partner) => (
                <span
                  key={partner}
                  className="rounded-full border border-black/10 bg-white px-5 py-3 text-sm font-semibold text-institutional-ink shadow-[0_10px_30px_rgba(8,26,45,0.06)]"
                >
                  {partner}
                </span>
              ))}
            </div>
          </div>
          <p className="mt-5 text-sm leading-7 text-slate-600">
            Os nomes acima resumem as entidades com papel directo no circuito financeiro e de supervisao do SGFE.
          </p>
        </div>
      </section>

      <section className="border-t border-black/5 py-16">
        <div className="mx-auto grid max-w-7xl gap-8 px-5 md:grid-cols-2">
          <div className="flex gap-4">
            <Landmark className="mt-1 h-6 w-6 text-institutional-gold" />
            <div>
              <h2 className="text-2xl font-bold">Beneficios institucionais</h2>
              <p className="mt-3 leading-8 text-slate-700">Maior controlo, rastreabilidade da despesa publica, consolidacao financeira e suporte efectivo a auditoria do Estado.</p>
            </div>
          </div>
          <div className="flex gap-4">
            <ShieldCheck className="mt-1 h-6 w-6 text-institutional-gold" />
            <div>
              <h2 className="text-2xl font-bold">Seguranca e auditoria</h2>
              <p className="mt-3 leading-8 text-slate-700">Toda operacao critica deve ficar registada com utilizador, entidade, IP, resultado e contexto operacional, sem expor o material da sessao ao browser.</p>
            </div>
          </div>
        </div>
      </section>

      <footer className="border-t border-black/5 bg-slate-50 py-8">
        <div className="mx-auto flex max-w-7xl flex-col gap-2 px-5 text-sm text-muted-foreground md:flex-row md:items-center md:justify-between">
          <span>SGFE - Sistema de Gestao das Financas do Estado</span>
          <span>Republica de Angola | Ministerio das Financas</span>
        </div>
      </footer>
    </main>
  );
}
