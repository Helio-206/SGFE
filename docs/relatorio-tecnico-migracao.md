# SGFE — Relatório técnico de migração

Data da análise: 2026-05-09  
Projeto analisado: SGFE — Sistema de Gestão das Finanças do Estado  
Domínio: execução do Orçamento Geral do Estado de Angola, Unidade Orçamental, cabimentação, liquidação, pagamento, RUPE, relatórios e auditoria.

Este relatório foi produzido antes de escrever código novo de aplicação. O objetivo é registrar o que existe no projeto PHP/Laravel atual, extrair as regras de negócio relevantes e definir a arquitetura alvo para uma migração segura para Next.js + Spring Boot + MySQL 8.

## 1. Resumo executivo

O projeto atual é um monólito Laravel localizado em `backend/`, com autenticação por sessão, views Blade, Tailwind/Vite, migrations Laravel, seeders e controllers MVC. O sistema já contém os módulos centrais do SGFE: Unidades Orçamentais, tectos orçamentais, classificações económicas, receitas RUPE, despesas com fluxo NCD -> NLD -> pagamento, dashboards, relatórios PDF/Excel e painéis de administração/auditoria.

A migração deve separar a aplicação em:

- `/frontend`: Next.js, TypeScript, Tailwind CSS, shadcn/ui, React Hook Form, Zod, TanStack Query, TanStack Table e Recharts.
- `/backend`: Java 21, Spring Boot, Spring Security, Spring Data JPA, MySQL 8, Flyway, JWT + refresh token, RBAC e audit logs persistentes.
- `/docs`: documentação técnica, decisões arquiteturais, matriz de permissões, contrato de API e plano de migração.
- `/database`: scripts SQL auxiliares, dumps controlados, validações de integridade e artefatos de migração quando necessário.

Não deve haver migração para PostgreSQL, MongoDB ou SQLite em produção. O banco-alvo deve ser MySQL 8, com InnoDB, foreign keys, transações e migrations versionadas com Flyway.

## 2. Stack atual identificada

### Backend atual

- PHP `^8.2`
- Laravel `^12.0`
- Laravel Breeze para autenticação web
- Blade templates
- Eloquent ORM
- Laravel migrations e seeders
- DomPDF (`barryvdh/laravel-dompdf`) para exportação PDF
- Maatwebsite Excel para exportação Excel
- MySQL configurado no `.env`
- Configuração Laravel ainda contém conexões para SQLite, PostgreSQL, SQL Server e MariaDB, herdadas do skeleton.

### Frontend atual

- Blade + Tailwind CSS
- Vite
- Alpine.js
- ApexCharts via CDN
- Formulários server-rendered com validação no Laravel

### Observações de segurança atuais

- A autenticação é baseada em sessão web, não JWT.
- Há bloqueio de login para utilizadores inativos.
- Há rate limit no login via `LoginRequest`.
- O arquivo `UTILIZADORES.md` contém credenciais de teste e deve ficar fora de qualquer entrega produtiva.
- O `.env` contém credenciais locais de banco e deve ser tratado como segredo em ambiente real.
- O cadastro público cria automaticamente utilizadores `gestor` ativos; para produção, isso deve virar fluxo administrativo ou fluxo com aprovação.

## 3. Estrutura funcional atual

### Rotas e áreas

| Área | Rotas atuais | Perfis atuais | Observações |
| --- | --- | --- | --- |
| Autenticação | `/login`, `/register`, reset, verificação de email, logout | público/autenticado | Sessão Laravel; registro cria gestor ativo. |
| Dashboard | `/dashboard`, `/dashboard/live` | autenticado e verificado | KPIs, séries mensais e dados operacionais. |
| Perfil | `/profile` | autenticado | Editar perfil, senha e apagar conta. |
| Administração | `/admin/painel`, `/admin/instituicoes`, `/admin/orcamentos`, `/admin/classificacoes` | `admin` | Gestão de UO, tectos e classificações. |
| Gestão | `/gestao/tecto`, `/gestao/receitas`, `/gestao/despesas` | `admin`, `gestor` | Operações institucionais; middleware de escopo. |
| Relatórios | `/relatorios/*` | `admin`, `gestor`, `auditor` | Listagens, JSON, PDF e Excel. |
| Auditoria | `/auditor/painel` | `admin`, `auditor` | Painel read-only com riscos e logs. |

### Páginas/telas atuais

| Tela Blade | Função | Destino no Next.js |
| --- | --- | --- |
| `dashboard.blade.php` | KPIs, gráficos, últimas transações, top UOs | `/dashboard` com Recharts e TanStack Query |
| `admin/painel.blade.php` | Métricas administrativas e logs recentes | `/admin` |
| `admin/instituicoes/*` | CRUD de UO | `/admin/instituicoes` |
| `admin/orcamentos/*` | Atribuição e manutenção de tectos | `/admin/orcamentos` |
| `admin/classificacoes/*` | CRUD de classificações económicas | `/admin/classificacoes` |
| `gestao/orcamento/index.blade.php` | Consulta do tecto da UO | `/gestao/tecto` |
| `gestao/receitas/*` | Listagem e criação de receitas RUPE | `/gestao/receitas` |
| `gestao/despesas/*` | Listagem, NCD, liquidação e pagamento | `/gestao/despesas` |
| `relatorios/index.blade.php` | Filtros, receitas, despesas, exports | `/relatorios` |
| `auditor/painel.blade.php` | Riscos, execução, audit logs | `/auditoria` |
| `auth/*` | Login, registro e recuperação | `/login`, `/recuperar-senha`, fluxos de auth |

## 4. Modelo de dados atual

### `instituicoes`

Representa Unidade Orçamental ou entidade institucional.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id_inst` | PK | Identificador da instituição |
| `nome` | string(150) | Obrigatório |
| `tipo` | string(50) | Ministério, Governo Provincial, Administração Municipal, Instituto Público, Empresa Pública etc. |
| `codigo` | string(20), unique | Código da UO |
| `responsavel` | string(100) | Obrigatório |
| timestamps | datetime | Auditoria técnica básica |

Relações: possui utilizadores, orçamentos, receitas e despesas.

### `users`

Utilizadores do SGFE.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id_user` | PK | Identificador do utilizador |
| `nome` | string(100) | Obrigatório |
| `username` | string(50), unique | Obrigatório |
| `email` | string(100), unique | Obrigatório |
| `password` | hash | Obrigatório |
| `role` | enum `admin`, `gestor`, `auditor` | RBAC básico |
| `status` | enum `ativo`, `inativo` | Login bloqueado se inativo |
| `id_inst` | FK -> `instituicoes.id_inst` | Vínculo institucional obrigatório |
| `email_verified_at`, `remember_token`, timestamps | diversos | Herdado do Laravel |

### `orcamentos`

Tecto orçamental por instituição e ano fiscal.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id_orcamento` | PK | Identificador do orçamento |
| `id_user` | FK -> `users.id_user` | Utilizador que atribuiu |
| `id_inst` | FK -> `instituicoes.id_inst` | UO beneficiária |
| `valor_total` | decimal(15,2) | Tecto orçamental |
| `ano_fiscal` | year | Ano fiscal corrente |
| unique `id_inst`, `ano_fiscal` | constraint | Um orçamento por UO por ano |

### `classificacoes_economicas`

Catálogo de rubricas/classificações económicas.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id_classe` | PK | Identificador |
| `descricao` | string(100) | Obrigatório |
| `cod_classe` | string(20), unique | Código da classificação |
| `tipo_receita` | string(50) | Usado tanto para receitas quanto despesas |

Observação: o nome `tipo_receita` é estreito para a função real. Na migração, recomenda-se renomear semanticamente para `tipo` ou `natureza`, mantendo compatibilidade de dados via migration.

### `transacoes_receitas`

Registos de receitas arrecadadas com RUPE.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id_receita` | PK | Identificador |
| `font_receita` | string(150) | Valores aceitos: `Petrolífera`, `Não Petrolífera`, `Patrimonial` |
| `codigo_rupe` | string(40), unique | Gerado automaticamente |
| `data_registro` | date | Ano fiscal corrente, até hoje |
| `valor_arrecadado` | decimal(15,2) | Maior que zero |
| `id_classe` | FK -> `classificacoes_economicas.id_classe` | Obrigatório |
| `id_inst` | FK -> `instituicoes.id_inst` | Sempre vem do utilizador autenticado |

### `transacoes_despesas`

Registos de execução da despesa.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id_despesa` | PK | Identificador |
| `estado` | enum | `PENDENTE_CABIMENTADA`, `LIQUIDADA_APROVADA`, `PAGA` |
| `descricao` | string(150) | Obrigatório |
| `valor_bruto` | decimal(15,2) | Maior que zero |
| `data_registro` | date | Ano fiscal corrente, até hoje |
| `id_inst` | FK -> `instituicoes.id_inst` | UO responsável |
| `id_user` | FK -> `users.id_user` | Utilizador que registrou |
| `id_classe` | FK nullable -> `classificacoes_economicas.id_classe` | Opcional; `SET NULL` no delete |

### `audit_logs`

Tabela de logs de auditoria.

| Campo | Tipo atual | Regra |
| --- | --- | --- |
| `id` | PK | Identificador |
| `id_user` | FK nullable -> `users.id_user` | Autor da ação |
| `acao` | string(120) | Nome do evento |
| `ip_address` | string(45) | IP |
| `contexto` | JSON | Metadados |
| timestamps | datetime | Data/hora |

Observação crítica: atualmente apenas algumas exportações escrevem na tabela `audit_logs`. A alteração de despesa para `PAGA` é registrada no log textual da aplicação via observer, não na tabela `audit_logs`. Na migração, todos os eventos críticos devem persistir em `audit_logs`.

## 5. Regras de negócio extraídas

### Ano fiscal

- O ano fiscal é calculado como o ano civil corrente (`date('Y')`/`now()->year`).
- Receitas e despesas só podem ser registradas entre 1 de janeiro do ano corrente e a data atual.
- Orçamentos são cadastrados para o ano fiscal corrente.

Recomendação: no Spring Boot, centralizar em um `FiscalYearService`, porque no futuro Angola pode exigir regras específicas de exercício, fecho, reabertura ou períodos especiais.

### Unidade Orçamental

- Apenas `admin` pode gerir instituições.
- Código da UO é único e aceita letras, números e hífens, entre 3 e 20 caracteres.
- Não é permitido eliminar instituição com utilizadores ou despesas.
- O banco também restringe remoções por FKs de orçamentos, receitas e outros vínculos.

Recomendação: evitar hard delete em produção; preferir estado `ATIVA/INATIVA/ARQUIVADA` para preservar histórico.

### Orçamento/tecto

- Apenas `admin` atribui ou edita tecto orçamental.
- Há no máximo um orçamento por UO por ano fiscal.
- O valor é decimal(15,2), mínimo zero.
- Ao editar, o valor não pode ficar abaixo de despesas já executadas.
- Orçamento com despesas no ano fiscal não pode ser eliminado.

Lacuna encontrada: há inconsistência nos cálculos de saldo/execução:

- `OrcamentoConsultaController` e `DashboardController` consideram despesas `PENDENTE_CABIMENTADA`, `LIQUIDADA_APROVADA` e `PAGA` como comprometidas.
- `TransacaoDespesaController::saldoAtualInstituicao` subtrai apenas despesas `PENDENTE_CABIMENTADA`.
- `OrcamentoController` usa em alguns pontos somente `LIQUIDADA_APROVADA` e `PAGA`.

Risco: a regra de cabimentação pode liberar saldo indevidamente depois que uma despesa avança para liquidação ou pagamento. Na migração, a regra oficial deve ser: toda despesa não cancelada compromete o tecto desde a cabimentação até o pagamento. Como não há estado de cancelamento hoje, os três estados atuais devem contar como comprometidos.

### Receita e RUPE

- `gestor` registra receitas apenas para sua própria UO.
- `admin` também tem acesso às rotas de gestão, mas a implementação atual grava usando a UO do utilizador autenticado.
- Fonte da receita permitida: `Petrolífera`, `Não Petrolífera`, `Patrimonial`.
- Classificação económica é obrigatória.
- Valor arrecadado deve ser maior que zero e até `999999999999.99`.
- RUPE é gerado automaticamente como código numérico de 20 dígitos e garantido por unique constraint.

Recomendação: gerar RUPE dentro de transação, com retry em colisão de chave única. Definir se o padrão final será apenas numérico ou prefixado, pois os seeders massivos usam códigos `RUPE-YYYYMMDD-XXXX`, enquanto o controller atual gera 20 dígitos.

### Despesa pública

Fluxo atual:

1. NCD/Cabimentação: cria despesa em `PENDENTE_CABIMENTADA`.
2. NLD/Liquidação: somente despesa cabimentada pode passar para `LIQUIDADA_APROVADA`.
3. Pagamento: somente despesa liquidada pode passar para `PAGA`.

Regras:

- Valor deve ser maior que zero e até `999999999999.99`.
- Data deve pertencer ao ano fiscal corrente e não pode ser futura.
- Gestor não pode operar despesa de outra instituição.
- Não é permitido pagar uma despesa sem liquidar.
- Não é permitido liquidar uma despesa já paga.
- A classificação económica da despesa é opcional no código atual, mas o painel do auditor sinaliza despesas sem classificação.

Recomendação: tornar classificação económica obrigatória para novas despesas no sistema migrado, ou exigir justificativa auditável quando ficar em branco.

### Relatórios

Funcionalidades atuais:

- Filtros por UO, classificação, valor mínimo/máximo e intervalo de datas.
- Admin/auditor podem filtrar por UO.
- Gestor vê apenas sua própria UO.
- JSON de gastos consolidados por instituição.
- JSON de evolução mensal de receitas.
- PDF de resumo financeiro institucional.
- PDF de despesa por natureza.
- Excel de receitas RUPE com intervalo de datas.

Recomendação: manter exports no backend Spring Boot, não no frontend, para preservar controle de acesso, audit log e consistência de dados.

### Auditoria

Eventos críticos identificados no domínio:

- Login, logout, refresh token e falhas de autenticação relevantes.
- Criação, edição e desativação de utilizadores.
- Alteração de role/status.
- Criação, edição e tentativa de eliminação de UO.
- Criação, edição e tentativa de eliminação de orçamento.
- Criação/edição de classificação económica.
- Criação de receita e geração de RUPE.
- Criação de despesa/NCD.
- Liquidação/NLD.
- Pagamento.
- Exportação PDF/Excel.
- Tentativas de acesso negado.
- Exceções de regra financeira, como cabimentação acima do saldo.

A migração deve gerar audit log persistente para todos esses eventos.

## 6. Permissões atuais e matriz alvo

### Perfis atuais

| Perfil | Capacidades atuais |
| --- | --- |
| `admin` | Gestão de UOs, orçamentos, classificações, acesso a relatórios e auditoria; também pode acessar rotas de gestão. |
| `gestor` | Consulta tecto da própria UO, registra receitas e despesas, liquida e paga despesas da própria UO, vê relatórios da própria UO. |
| `auditor` | Acesso read-only a relatórios e painel de auditoria. |

### Matriz recomendada para o Spring Security

| Recurso | Admin | Gestor | Auditor |
| --- | --- | --- | --- |
| Dashboard consolidado nacional | Sim | Não | Sim |
| Dashboard da própria UO | Sim | Sim | Sim, read-only |
| Criar/editar UO | Sim | Não | Não |
| Criar/editar orçamento | Sim | Não | Não |
| Criar/editar classificação | Sim | Não | Não |
| Registrar receita RUPE | Sim, com escopo explícito | Sim, própria UO | Não |
| Criar NCD | Sim, com escopo explícito | Sim, própria UO | Não |
| Liquidar NLD | Sim, com escopo explícito | Sim, própria UO | Não |
| Registrar pagamento | Sim, com escopo explícito | Sim, própria UO | Não |
| Ver relatórios | Sim | Sim, própria UO | Sim |
| Exportar relatórios | Sim | Sim, própria UO | Sim |
| Ver audit logs | Sim | Não | Sim |
| Gerir utilizadores | Sim | Não | Não |

Observação: o sistema atual permite que o mesmo `gestor` faça cabimentação, liquidação e pagamento. Para um ambiente governamental mais robusto, recomenda-se introduzir permissões granulares no backend, ainda que inicialmente mapeadas aos três perfis existentes. Exemplo: `DESPESA_CRIAR`, `DESPESA_LIQUIDAR`, `DESPESA_PAGAR`.

## 7. Lacunas e riscos identificados

| Risco | Impacto | Recomendação na migração |
| --- | --- | --- |
| Cálculo inconsistente de saldo comprometido | Pode permitir execução acima do tecto | Centralizar regra no backend: comprometido = soma de todos os estados ativos. |
| Audit log parcial | Perda de rastreabilidade de ações críticas | Criar serviço obrigatório de auditoria transacional. |
| Pagamento logado apenas em arquivo | Auditor não vê evento na tabela `audit_logs` | Persistir eventos de workflow financeiro. |
| Cadastro público cria gestor ativo | Risco de acesso indevido | Cadastro deve ser administrativo ou exigir aprovação. |
| Credenciais documentadas em `UTILIZADORES.md` | Vazamento em produção | Remover do repositório produtivo e usar secrets. |
| `.env` com credenciais locais | Risco de segredo versionado | Usar `.env.example` sem segredos e secret manager. |
| Classificação de despesa opcional | Relatórios por natureza incompletos | Tornar obrigatória ou exigir justificativa auditável. |
| Hard delete de entidades de catálogo | Perda de histórico semântico | Usar status/arquivamento; restringir delete. |
| RUPE com dois formatos nos dados | Reconciliação inconsistente | Definir padrão único e validá-lo no backend. |
| Regras financeiras em controllers | Difícil testar e reutilizar | Migrar para services `@Transactional`. |
| Autorização distribuída entre rotas, middleware e views | Risco de divergência | Centralizar em Spring Security + service-level authorization. |

## 8. Arquitetura alvo

### Backend Spring Boot

Estrutura recomendada:

```text
backend/
  src/main/java/ao/gov/minfin/sgfe/
    auth/
    users/
    instituicoes/
    orcamentos/
    classificacoes/
    receitas/
    despesas/
    relatorios/
    auditoria/
    dashboard/
    common/
```

Responsabilidades:

- `auth`: login, JWT access token, refresh token, logout, rotação/revogação de refresh tokens.
- `users`: utilizadores, perfis, status, vínculo institucional.
- `instituicoes`: Unidades Orçamentais.
- `orcamentos`: tectos, saldos, execução, validações de ano fiscal.
- `classificacoes`: catálogo de classificações económicas.
- `receitas`: arrecadação, RUPE, validações e relatórios de receita.
- `despesas`: workflow NCD -> NLD -> pagamento, saldo e transações.
- `relatorios`: consultas consolidadas e exportações.
- `auditoria`: persistência e consulta de audit logs.
- `dashboard`: KPIs e séries temporais.
- `common`: erros, DTOs comuns, paginação, dinheiro, datas e escopo institucional.

### Princípios obrigatórios no backend

- Todas as alterações financeiras devem ocorrer dentro de `@Transactional`.
- Dinheiro deve usar `BigDecimal`, nunca `double`/`float`.
- Datas de negócio devem usar `LocalDate`; timestamps técnicos devem usar `Instant` ou `OffsetDateTime`.
- Nenhum endpoint sensível deve aceitar dados sem autenticação JWT.
- Autorização deve ser aplicada no endpoint e validada no serviço quando envolver escopo institucional.
- Toda ação crítica deve criar audit log persistente.
- O frontend pode validar com Zod, mas a regra financeira final deve estar no backend.
- Exports devem ser gerados no backend e auditados.

### Banco MySQL 8 + Flyway

Configuração obrigatória:

- MySQL 8
- Engine InnoDB em todas as tabelas
- Charset `utf8mb4`
- FKs explícitas
- Índices para consultas de ano fiscal, UO, estado, RUPE e datas
- Migrations Flyway versionadas
- Sem SQLite, PostgreSQL, MongoDB ou banco alternativo em produção

Tabelas adicionais recomendadas:

- `refresh_tokens`: token hash, utilizador, expiração, revogação, IP, user agent.
- `audit_logs` expandida: ator, ação, entidade, entidade_id, UO, antes/depois, metadata, IP, user agent, correlation id, resultado.
- `permissions` e `role_permissions` se a migração optar por permissões granulares além dos três perfis atuais.

### Frontend Next.js

Estrutura sugerida:

```text
frontend/
  app/
    login/
    dashboard/
    admin/
      instituicoes/
      orcamentos/
      classificacoes/
    gestao/
      tecto/
      receitas/
      despesas/
    relatorios/
    auditoria/
  components/
  features/
  lib/
```

Uso das bibliotecas obrigatórias:

- React Hook Form: todos os formulários.
- Zod: schemas de validação de formulários e DTOs.
- TanStack Query: cache e chamadas API.
- TanStack Table: listagens de UOs, orçamentos, receitas, despesas, classificações e audit logs.
- Recharts: gráficos do dashboard, execução orçamental, receitas mensais, despesas por estado e riscos.
- shadcn/ui: botões, inputs, selects, dialogs, tables, badges, cards, dropdowns, toasts.
- Tailwind CSS: layout e design system.

## 9. Contrato inicial de API alvo

### Auth

| Método | Endpoint | Permissão | Função |
| --- | --- | --- | --- |
| POST | `/api/auth/login` | público | Autentica e retorna access/refresh token |
| POST | `/api/auth/refresh` | refresh válido | Rotaciona access token |
| POST | `/api/auth/logout` | autenticado | Revoga refresh token atual |
| GET | `/api/auth/me` | autenticado | Retorna utilizador e permissões |

### Administração

| Método | Endpoint | Permissão |
| --- | --- | --- |
| GET/POST | `/api/instituicoes` | `admin` |
| GET/PUT/DELETE | `/api/instituicoes/{id}` | `admin` |
| GET/POST | `/api/orcamentos` | `admin` |
| GET/PUT/DELETE | `/api/orcamentos/{id}` | `admin` |
| GET/POST | `/api/classificacoes` | `admin` |
| GET/PUT/DELETE | `/api/classificacoes/{id}` | `admin` |
| GET/POST | `/api/users` | `admin` |
| PATCH | `/api/users/{id}/status` | `admin` |
| PATCH | `/api/users/{id}/role` | `admin` |

### Gestão financeira

| Método | Endpoint | Permissão |
| --- | --- | --- |
| GET | `/api/gestao/tecto` | `admin`, `gestor` |
| GET/POST | `/api/receitas` | `admin`, `gestor` |
| GET/POST | `/api/despesas` | `admin`, `gestor` |
| POST | `/api/despesas/{id}/liquidar` | `admin`, `gestor` |
| POST | `/api/despesas/{id}/pagar` | `admin`, `gestor` |

### Relatórios e auditoria

| Método | Endpoint | Permissão |
| --- | --- | --- |
| GET | `/api/dashboard` | autenticado |
| GET | `/api/relatorios/receitas` | `admin`, `gestor`, `auditor` |
| GET | `/api/relatorios/despesas` | `admin`, `gestor`, `auditor` |
| GET | `/api/relatorios/consolidado-gastos` | `admin`, `gestor`, `auditor` |
| GET | `/api/relatorios/evolucao-receitas-mensal` | `admin`, `gestor`, `auditor` |
| GET | `/api/relatorios/exportar/resumo-financeiro.pdf` | `admin`, `gestor`, `auditor` |
| GET | `/api/relatorios/exportar/despesa-por-natureza.pdf` | `admin`, `gestor`, `auditor` |
| GET | `/api/relatorios/exportar/receitas-rupe.xlsx` | `admin`, `gestor`, `auditor` |
| GET | `/api/auditoria/painel` | `admin`, `auditor` |
| GET | `/api/auditoria/logs` | `admin`, `auditor` |

## 10. Estratégia de migração de dados

1. Congelar o schema Laravel atual como baseline documental.
2. Criar migrations Flyway equivalentes, com nomes e tipos compatíveis.
3. Decidir se os nomes físicos das tabelas serão preservados para facilitar migração (`instituicoes`, `orcamentos`, `transacoes_receitas`, `transacoes_despesas`) ou se haverá normalização com views de compatibilidade.
4. Exportar dados do MySQL atual em janela controlada.
5. Importar em ambiente staging com migrations Flyway aplicadas.
6. Rodar validações de integridade:
   - contagem por tabela;
   - soma de `valor_total` por ano fiscal;
   - soma de receitas por UO/ano;
   - soma de despesas por UO/ano/estado;
   - verificação de RUPE único;
   - verificação de despesas sem UO, sem utilizador ou sem orçamento.
7. Corrigir inconsistências antes do cutover.
8. Executar migração final em janela de baixa utilização.

## 11. Testes mínimos para a migração

### Backend

- Testes unitários de `FiscalYearService`, `BudgetService`, `DespesaWorkflowService`, `RupeService` e `AuditService`.
- Testes de integração com MySQL Testcontainers.
- Testes de segurança por perfil e por escopo institucional.
- Testes de concorrência para cabimentação simultânea na mesma UO.
- Testes de exportação com audit log.
- Testes de refresh token, revogação e usuário inativo.

### Frontend

- Testes de formulários com React Hook Form + Zod.
- Testes de renderização condicional por role.
- Testes de tabelas, filtros e paginação.
- Testes E2E dos fluxos:
  - login;
  - criar UO;
  - atribuir orçamento;
  - registrar receita RUPE;
  - criar NCD;
  - liquidar NLD;
  - pagar;
  - exportar relatório;
  - consultar auditoria.

## 12. Plano recomendado de execução

### Fase 0 — Preparação

- Confirmar regras oficiais de execução: quais estados comprometem o tecto, se há cancelamento/anulação, e quem pode liquidar/pagar.
- Remover segredos e credenciais de documentação produtiva.
- Definir padrão RUPE final.
- Definir se classificação de despesa passa a ser obrigatória.

### Fase 1 — Backend base

- Criar `/backend` Spring Boot com Java 21.
- Configurar MySQL 8, Flyway e JPA.
- Criar entidades, migrations e repositórios.
- Implementar autenticação JWT + refresh token.
- Implementar RBAC e escopo institucional.

### Fase 2 — Serviços de domínio

- Implementar UO, utilizadores, orçamentos e classificações.
- Implementar receitas RUPE.
- Implementar despesas com workflow transacional.
- Implementar audit log obrigatório.
- Implementar dashboards e relatórios.

### Fase 3 — Frontend

- Criar `/frontend` Next.js.
- Implementar layout autenticado e guarda por role.
- Migrar telas de administração, gestão, relatórios, dashboard e auditoria.
- Substituir ApexCharts por Recharts.
- Substituir tabelas Blade por TanStack Table.

### Fase 4 — Migração de dados e validação

- Rodar migração em staging.
- Comparar totais entre Laravel e Spring Boot.
- Executar bateria de testes.
- Corrigir divergências de regra.

### Fase 5 — Cutover

- Congelar escrita no sistema antigo.
- Exportar/importar delta final.
- Validar totais.
- Ativar frontend Next.js apontando para API Spring Boot.
- Manter Laravel apenas como fallback temporário read-only, se necessário.

## 13. Decisões técnicas recomendadas

- Manter nomes de tabelas próximos aos atuais na primeira versão para reduzir risco de migração.
- Usar `BigDecimal` com escala 2 para valores monetários.
- Centralizar saldo orçamental em um único serviço.
- Implementar optimistic/pessimistic locking para cabimentação concorrente por UO/ano fiscal.
- Usar refresh token persistido com hash, rotação e revogação.
- Não permitir cadastro público ativo em produção.
- Não apagar fisicamente entidades financeiras; usar status/arquivamento onde possível.
- Usar audit log como parte da transação de negócio sempre que a alteração for crítica.
- Manter exports no backend e registrar evento de exportação.
- Criar documentação OpenAPI para todos os endpoints.

## 14. Próximo passo sugerido

Antes de iniciar código novo, validar com o dono do domínio as decisões abaixo:

1. Estados oficiais da despesa: haverá cancelamento, anulação, estorno ou reversão de pagamento?
2. O mesmo gestor pode cabimentar, liquidar e pagar, ou deve haver segregação de funções?
3. O padrão RUPE deve ser numérico de 20 dígitos ou conter prefixo/data?
4. Despesa sem classificação económica será permitida?
5. Auditores terão visão nacional sempre, ou podem existir auditores por UO?
6. Orçamentos antigos podem ser editados ou somente o ano fiscal corrente?

Com essas decisões fechadas, a implementação pode começar pela criação do backend Spring Boot e das migrations Flyway, mantendo o Laravel como referência de comportamento até a validação final.
