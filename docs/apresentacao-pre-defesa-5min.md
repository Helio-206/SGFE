# SGFE - Apresentacao Pre-Defesa (ate 5 min)

## 1) Abertura (20-30s)

Boa tarde. Este projeto chama-se SGFE, Sistema de Gestao das Financas do Estado.
O objetivo foi construir uma plataforma unica para controlar instituicoes, orcamentos, receitas, despesas, auditoria e relatorios, com seguranca e rastreabilidade.

## 2) Problema e proposta (35-45s)

Antes, o processo financeiro tende a ficar fragmentado: dados separados, pouca visibilidade de execucao e dificuldade de auditoria.
Com o SGFE, centralizamos o ciclo financeiro numa arquitetura web moderna, com regras de negocio no backend, controlo por perfis e trilha de auditoria persistente.

## 3) O que foi implementado (60s)

Entregamos um sistema full stack com:

- autenticacao com JWT e refresh token;
- controlo de acesso por papel: ADMIN, GESTOR e AUDITOR;
- modulos de instituicoes, orcamentos, classificacoes, receitas RUPE e despesas;
- transicoes de despesa (cabimentada, liquidada e paga) com validacao de saldo;
- dashboard com KPIs financeiros e nivel de risco;
- auditoria de operacoes criticas;
- exportacao de relatorios PDF e Excel.

## 4) Arquitetura tecnica (55-65s)

No frontend usamos Next.js com TypeScript, React Query para dados e componentes de interface para operacao diaria.
No backend usamos Java 21 com Spring Boot, Spring Security e JPA.
A base de dados e MySQL com migrations Flyway para garantir versao e consistencia do schema.

Fluxo resumido:

1. Utilizador autentica no frontend.
2. Frontend consome a API com cookies de sessao.
3. Backend valida token, permissao e regra de negocio.
4. Operacao e gravada e, quando critica, auditada em log.

## 5) Seguranca e conformidade (45-55s)

Os principais pontos de seguranca sao:

- cookies HttpOnly para reduzir exposicao de tokens;
- refresh token com rotacao e revogacao;
- RBAC no backend por endpoint e por acao;
- validacoes de DTO e tratamento padrao de erros;
- audit logs com utilizador, entidade, resultado, severidade e IP.

Isso foi pensado para um contexto de financas publicas, onde rastreabilidade e controlo sao obrigatorios.

## 6) Demonstração curta sugerida (45-55s)

Durante a demo, o fluxo recomendado e:

1. Login como gestor.
2. Criar receita e despesa.
3. Liquidar e pagar despesa.
4. Mostrar atualizacao do dashboard.
5. Abrir auditoria e provar que as acoes ficaram registadas.

Esse fluxo mostra valor funcional, regra de negocio e governanca em poucos minutos.

## 7) Encerramento (20-30s)

Em resumo, o SGFE entrega controlo financeiro institucional, seguranca de acesso, rastreabilidade e base tecnica escalavel.
Como proximo passo, a evolucao natural e ampliar as mutacoes administrativas diretamente na interface, mantendo as regras ja consolidadas na API.

Obrigado.

---

## Versao ultra curta (30s, se faltar tempo)

O SGFE e uma plataforma full stack para gestao das financas publicas com foco em controlo, seguranca e auditoria.
Implementamos autenticacao segura, RBAC, modulos financeiros principais, dashboard de execucao e relatorios oficiais.
O diferencial e unir operacao e rastreabilidade: toda acao critica fica registada para supervisao e prestacao de contas.