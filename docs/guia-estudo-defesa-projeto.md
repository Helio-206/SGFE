# SGFE - Guia de Estudo Completo para Defesa

Data de referencia: 10 de maio de 2026

## 1) Como explicar o projeto em 60 segundos

O SGFE e uma plataforma full stack para gestao financeira publica, cobrindo instituicoes, orcamentos, receitas RUPE, despesas, auditoria e relatorios.

Arquitetura:

- Frontend: Next.js 15 + React 19 + TypeScript + Tailwind + TanStack Query/Table + Recharts.
- Backend: Spring Boot 3.3.7 (Java 21) + Spring Security + JWT + refresh token + JPA + Flyway.
- Base de dados: MySQL 8 com schema versionado por migrations.

Ponto-chave de produto: controle institucional com rastreabilidade completa (audit log) e autorizacao por papel (ADMIN, GESTOR, AUDITOR).

## 2) Stack tecnica e por que foi usada

## 2.1 Backend

- spring-boot-starter-web: API REST.
- spring-boot-starter-security: autenticacao/autorizacao e cadeia de filtros.
- spring-boot-starter-data-jpa: persistencia e mapeamento objeto-relacional.
- flyway-mysql: versionamento de schema e dados de referencia.
- jjwt (api/impl/jackson): emissao e validacao de JWT.
- openpdf: geracao de relatorios PDF.
- poi-ooxml: geracao de Excel (XLSX).

Decisoes:

- Hibernate com ddl-auto=validate para impedir drift entre entidades e banco.
- open-in-view=false para evitar acesso lazy fora da camada transacional.

## 2.2 Frontend

- Next.js App Router: roteamento por pasta, SSR/CSR hibrido.
- React Query: cache, refetch e estado async.
- React Hook Form + Zod: formularios com validacao declarativa.
- Recharts: visualizacao de KPIs/graficos.
- Tailwind + componentes UI: produtividade e consistencia visual.

Decisoes:

- Sessao em cookies HttpOnly (nao localStorage), reduzindo superficie XSS.
- Camada unica de fetch com renovacao automatica de token no 401.

## 3) Arquitetura de alto nivel

Fluxo de requisicao:

1. Browser chama frontend (rotas Next).
2. Frontend chama API em /api/... com credentials=include.
3. Backend valida JWT via filtro e monta contexto de seguranca.
4. Controller aplica RBAC via @PreAuthorize.
5. Service aplica regras de negocio e persiste via Repository.
6. Eventos criticos sao gravados em audit_logs.
7. Response volta para frontend; React Query atualiza tela.

## 4) Modelo de dominio e dados

Tabelas principais:

- instituicoes
- users
- classificacoes_economicas
- orcamentos
- transacoes_receitas
- transacoes_despesas
- audit_logs
- refresh_tokens
- password_reset_tokens

Relacionamentos centrais:

- Instituicao 1:N Users, Orcamentos, Receitas, Despesas, AuditLogs.
- Classificacao 1:N Receitas e 1:N Despesas.
- User 1:N RefreshTokens.

Migrations Flyway:

- V1__create_sgfe_core_schema.sql
- V2__seed_reference_data.sql
- V3__auth_profile_and_indexes.sql

## 5) Backend em detalhes

## 5.1 Modulos

- auth: login, refresh, logout, forgot/reset, cookies de autenticacao.
- users: CRUD de utilizadores e perfil proprio.
- instituicoes: CRUD institucional.
- orcamentos: teto por UO e ano fiscal.
- classificacoes: catalogo economico.
- receitas: registro de arrecadacao RUPE.
- despesas: cabimentacao -> liquidacao -> pagamento.
- dashboard: consolidacao de indicadores.
- relatorios: exportacao PDF e XLSX.
- auditoria: leitura filtrada de logs.

## 5.2 Seguranca backend

Configuracao principal:

- Stateless session (SessionCreationPolicy.STATELESS).
- Rotas publicas: /api/auth/login, /api/auth/refresh, /api/auth/forgot-password, /api/auth/reset-password, /actuator/health.
- Demais rotas exigem autenticacao.
- CORS com origins configuraveis por variavel de ambiente.

JWT + cookies:

- Access token cookie: SGFE_ACCESS_TOKEN, path=/.
- Refresh token cookie: SGFE_REFRESH_TOKEN, path=/api/auth.
- Ambos HttpOnly, SameSite configuravel, Secure configuravel.

Rotacao de refresh token:

- Cada refresh revoga token anterior e emite novo refresh token.
- Token armazenado em hash SHA-256 em refresh_tokens.

RBAC por anotacao:

- Exemplo: @PreAuthorize("hasRole('ADMIN')") para criacao de users.
- Exemplo: auditoria apenas ADMIN/AUDITOR.

Tratamento de erros:

- 422 para regra de negocio.
- 403 para acesso negado.
- 400 para validacao de DTO.
- 409 para conflito/integridade.

## 5.3 Regras de negocio importantes

Orcamento:

- Uma UO nao pode ter mais de um orcamento por ano.
- Orcamento nao pode ser reduzido abaixo do comprometido.

Despesa:

- Criacao exige saldo disponivel (cabimentacao).
- Transicao valida:
  - PENDENTE_CABIMENTADA -> LIQUIDADA_APROVADA -> PAGA
- Pagamento sem liquidacao e bloqueado.

Receita:

- Gera codigo RUPE automaticamente.
- Data deve estar no ano fiscal corrente.

Users:

- Email e username unicos.
- "Delete" e logico via status INATIVO.

## 5.4 Auditoria tecnica

AuditService grava, em transacao separada (REQUIRES_NEW):

- utilizador
- instituicao
- acao
- entidade e entidadeId
- resultado e severidade
- ip, user-agent, correlation-id
- contexto JSON

Isso garante rastreabilidade mesmo quando a transacao de negocio falha.

## 5.5 Relatorios

- Resumo financeiro PDF (contexto nacional ou por UO).
- Despesa por natureza PDF.
- Receitas RUPE XLSX (com filtro de periodo).

As exportacoes tambem geram eventos de auditoria.

## 6) Frontend em detalhes

## 6.1 Estrutura de rotas

Publicas:

- /
- /login
- /recuperar-senha

Protegidas:

- /dashboard
- /admin/*
- /gestao/*
- /relatorios
- /auditoria
- /perfil

Middleware:

- Se nao ha cookie de access token e rota protegida: redireciona para /login?next=...
- Se ha sessao e usuario tenta /login: redireciona para /dashboard.

## 6.2 Estado e dados

QueryProvider configura React Query com:

- staleTime de 30s
- sem refetch automatico no foco da janela

apiFetch centraliza:

- headers padrao
- credentials=include
- retry com refresh em 401 (uma vez)
- padronizacao de mensagens de erro

## 6.3 Navegacao funcional

Menu principal:

- Dashboard
- Administracao
- Unidades Orcamentais
- Orcamentos
- Classificacoes
- Receitas RUPE
- Despesas
- Relatorios
- Auditoria
- Perfil

## 6.4 O que esta implementado na UI vs somente API

UI com CRUD parcial:

- Instituicoes: create + read na UI.
- Perfil proprio: update nome/email + update senha.
- Relatorios: download na UI.
- Auditoria: leitura e filtro textual.

Disponivel apenas via API (na versao atual da UI):

- Create/update de classificacoes.
- Create/update de orcamentos.
- Create/update/inativacao de utilizadores administrativos.
- Create de receitas e despesas.
- Acoes de liquidar/pagar (botoes aparecem, mas ainda sem mutacao ligada na tela).

## 7) Inventario de API (visao de banca)

Auth:

- POST /api/auth/login
- POST /api/auth/refresh
- POST /api/auth/logout
- POST /api/auth/forgot-password
- POST /api/auth/reset-password
- GET /api/auth/me

Users:

- GET /api/users
- POST /api/users
- PUT /api/users/{id}
- PATCH /api/users/{id}/role-status
- GET /api/users/me
- PUT /api/users/me
- PATCH /api/users/me/password

Instituicoes:

- GET /api/instituicoes
- POST /api/instituicoes
- PUT /api/instituicoes/{id}

Orcamentos:

- GET /api/orcamentos
- GET /api/orcamentos/meu-tecto
- POST /api/orcamentos
- PUT /api/orcamentos/{id}

Classificacoes:

- GET /api/classificacoes
- POST /api/classificacoes
- PUT /api/classificacoes/{id}

Receitas:

- GET /api/receitas
- POST /api/receitas

Despesas:

- GET /api/despesas
- POST /api/despesas
- POST /api/despesas/{id}/liquidar
- POST /api/despesas/{id}/pagar

Auditoria:

- GET /api/auditoria/logs

Relatorios:

- GET /api/relatorios/exportar/resumo-financeiro.pdf
- GET /api/relatorios/exportar/despesa-por-natureza.pdf
- GET /api/relatorios/exportar/receitas-rupe.xlsx

Dashboard:

- GET /api/dashboard

## 8) CRUD/CRUID consolidado

No projeto atual, o "D" e majoritariamente logico:

- users: status INATIVO via PATCH.

Para as demais entidades principais, o padrao e C/R/U sem delete exposto, focando integridade historica e rastreabilidade.

## 9) KPIs e logica do dashboard

Metricas:

- tectoTotal
- valorComprometido
- valorPago
- totalReceita
- saldoDisponivel = max(tectoTotal - valorComprometido, 0)
- percentualExecucao = (valorComprometido / tectoTotal) * 100

Classificacao de risco:

- >= 95: CRITICO
- >= 80: ALTO
- >= 60: MODERADO
- < 60: CONTROLADO

Visoes:

- Gestor: apenas sua UO.
- Admin/Auditor: consolidado nacional + top UOs.

## 10) Seguranca: como defender na banca

Camadas de seguranca aplicadas:

1. Autenticacao JWT com expirada curta para access token.
2. Refresh token com rotacao e revogacao.
3. Tokens em cookies HttpOnly.
4. RBAC por role no backend.
5. Rotas protegidas no frontend (middleware).
6. Headers de seguranca no backend (CSP, Referrer-Policy, Permissions-Policy).
7. Auditoria persistente para operacoes sensiveis.

Limites conhecidos (importante falar com maturidade):

- CSRF esta desativado porque o modelo e stateless e as chamadas sao same-origin de app controlada; em producao, e recomendavel avaliar token anti-CSRF para endpoints mutaveis.
- Algumas operacoes ainda estao apenas na API e nao na UI.

## 11) Perguntas dificeis e respostas curtas

"Por que usar refresh token em cookie separado?"

- Para reduzir exposicao do token de longa duracao e limitar escopo por path (/api/auth), diminuindo risco.

"Como evitam comprometimento de saldo?"

- O service de despesa calcula comprometido por estados validos e bloqueia cabimentacao acima do saldo.

"Como garantem rastreabilidade confiavel?"

- Toda acao critica passa por AuditService em transacao separada, com usuario, entidade, resultado, severidade e contexto.

"Por que nao existe delete fisico para tudo?"

- Dominio financeiro exige historico e auditabilidade; por isso priorizamos inativacao logica e bloqueios por integridade referencial.

"Como o frontend lida com sessao expirada?"

- apiFetch tenta refresh automaticamente no 401 e repete a chamada uma unica vez.

## 12) Roteiro de estudo (para parecer autor do sistema)

Ordem recomendada:

1. Ler README raiz e este guia.
2. Entender auth + security (backend e middleware).
3. Estudar services de negocio (orcamentos, despesas, receitas, users).
4. Revisar auditoria e relatorios.
5. Revisar app-shell, api.ts e paginas principais no frontend.
6. Executar o fluxo completo de teste (guia CRUD/fluxo) e observar logs.

Tempo sugerido (2h30):

- 30 min: arquitetura e stack
- 45 min: backend (security + regras de negocio)
- 35 min: frontend (rotas + fetch + estado)
- 25 min: API endpoints e CRUD
- 15 min: simulacao oral de perguntas

## 13) Resumo final para decorar

- O SGFE e um sistema financeiro institucional, auditavel, orientado a regras de negocio e RBAC.
- A API concentra a logica critica (saldo, estados de despesa, perfis e validacoes).
- O frontend organiza a operacao e consome API com sessao por cookies HttpOnly.
- Auditoria e relatorios sao parte central do produto, nao acessorio.
- O projeto esta pronto para evoluir UI de mutacoes que hoje ja existem na API.
