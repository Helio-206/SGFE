# SGFE - Slides Essenciais para Apresentacao em Grupo

Sugestao: 10 slides, 3 apresentadores, 6 a 8 minutos.

Regra dos slides: pouco texto. O detalhe fica na fala.

---

## Divisao do grupo

### Apresentador 1 - Contexto e arquitetura

- problema;
- objetivo do SGFE;
- tecnologias usadas;
- arquitetura geral.

### Apresentador 2 - Funcionalidades e fluxos

- modulos principais;
- CRUD administrativo;
- receitas RUPE;
- despesas;
- dashboard.

### Apresentador 3 - Seguranca, base de dados e auditoria

- autenticacao;
- permissoes;
- relacoes da base de dados;
- auditoria;
- conclusao.

---

# Slide 1 - Capa

## SGFE

Sistema de Gestao das Financas do Estado

- Plataforma web para gestao financeira publica
- Controlo de orcamentos, receitas, despesas e auditoria
- Projeto full stack: Next.js + Spring Boot + MySQL

Responsaveis:

- Nome 1
- Nome 2
- Nome 3

---

# Slide 2 - Problema

## Problema identificado

- Dados financeiros fragmentados
- Dificuldade de acompanhar execucao orcamental
- Falta de rastreabilidade em operacoes criticas
- Controlo manual de receitas, despesas e responsaveis
- Risco de acessos sem separacao clara de funcoes

Mensagem para falar:

> O problema central e garantir controlo, transparencia e seguranca na execucao financeira.

---

# Slide 3 - Proposta

## Solucao proposta

O SGFE centraliza o ciclo financeiro institucional.

- Gestao de Unidades Orcamentais
- Gestao de utilizadores e perfis
- Registo de orcamentos
- Registo de receitas RUPE
- Controlo de despesas
- Auditoria e relatorios

Mensagem para falar:

> A plataforma junta operacao financeira, regras de negocio e auditoria numa unica solucao.

---

# Slide 4 - Arquitetura

## Arquitetura tecnica

- Frontend: Next.js, React, TypeScript
- Backend: Java 21, Spring Boot
- Seguranca: Spring Security + JWT
- Base de dados: MySQL
- Migrations: Flyway
- Persistencia: JPA/Hibernate

Fluxo:

```text
Browser -> Frontend -> API Backend -> MySQL
```

Mensagem para falar:

> O frontend facilita a operacao, mas as regras criticas ficam no backend.

---

# Slide 5 - Perfis do sistema

## Controlo por perfil

### ADMIN

- cria UO;
- cria utilizadores;
- cria orcamentos;
- administra dados principais.

### GESTOR

- opera a sua propria UO;
- cria receitas do dia;
- cria e acompanha despesas.

### AUDITOR

- consulta dados;
- acompanha logs;
- autoriza receitas retroativas.

Mensagem para falar:

> O sistema usa RBAC: cada utilizador so acessa o que o seu papel permite.

---

# Slide 6 - Base de dados

## Modelo relacional

Tabelas principais:

- `instituicoes`
- `users`
- `orcamentos`
- `classificacoes_economicas`
- `transacoes_receitas`
- `transacoes_despesas`
- `audit_logs`
- `refresh_tokens`
- `autorizacoes_receitas_retroativas`

Relacionamentos:

- uma UO tem muitos utilizadores;
- uma UO tem muitos orcamentos;
- uma UO tem muitas receitas e despesas;
- receitas e despesas usam classificacao economica;
- auditoria liga utilizador, entidade e acao.

Mensagem para falar:

> A base relacional garante integridade por chaves estrangeiras, unicidade e historico.

---

# Slide 7 - Fluxo financeiro

## Receita e despesa

### Receita RUPE

- criada por Admin ou Gestor;
- data normal fica no dia atual;
- RUPE e gerado automaticamente;
- operacao fica auditada.

### Despesa

- criada por Admin ou Gestor;
- passa por estados financeiros;
- compromete saldo conforme o estado;
- pode ser liquidada e paga.

Estados principais:

- `PENDENTE_CABIMENTADA`
- `LIQUIDADA_APROVADA`
- `PAGA`
- `REJEITADA`
- `CANCELADA`

---

# Slide 8 - CRUD para demonstracao

## CRUD completo

Modulo recomendado:

> Admin > Classificacoes Economicas

Operacoes:

- Create: criar nova classificacao
- Read: listar e pesquisar
- Update: editar classificacao
- Delete: remover classificacao criada para teste

Endpoints:

- `GET /api/classificacoes`
- `POST /api/classificacoes`
- `PUT /api/classificacoes/{id}`
- `DELETE /api/classificacoes/{id}`

Mensagem para falar:

> O backend protege as mutacoes: apenas ADMIN pode criar, editar ou remover.

---

# Slide 9 - Seguranca e auditoria

## Seguranca

- Senhas com BCrypt
- JWT em cookies HttpOnly
- Refresh token com hash e revogacao
- RBAC no backend com `@PreAuthorize`
- CORS com origens configuradas
- Headers de seguranca

## Auditoria

- guarda utilizador;
- acao realizada;
- entidade afetada;
- resultado;
- severidade;
- IP;
- data/hora.

Mensagem para falar:

> Mesmo que alguem tente burlar o frontend, o backend valida permissao, regra de negocio e registra a acao.

---

# Slide 10 - Dashboard e relatorios

## Visibilidade gerencial

- KPIs financeiros
- Total orcamentado
- Valor comprometido
- Valor pago
- Receita arrecadada
- Saldo disponivel
- Nivel de risco

Relatorios:

- PDF
- Excel
- dados consolidados para prestacao de contas

Mensagem para falar:

> O dashboard transforma os registros financeiros em informacao para tomada de decisao.

---

# Slide 11 - Conclusao

## Resultado final

O SGFE entrega:

- controlo financeiro institucional;
- separacao de responsabilidades;
- integridade da base de dados;
- seguranca por perfil;
- rastreabilidade por auditoria;
- relatorios para apoio a decisao.

Frase final:

> O SGFE foi construido para tornar a gestao financeira mais organizada, segura e auditavel.

---

# Slide 12 - Perguntas

## Obrigado

Perguntas?

---

## Ordem sugerida da apresentacao

### Apresentador 1

Slides 1 a 4.

Foco:

- contextualizar;
- explicar problema;
- apresentar arquitetura.

### Apresentador 2

Slides 5 a 8.

Foco:

- explicar perfis;
- mostrar funcionalidades;
- demonstrar CRUD e fluxo financeiro.

### Apresentador 3

Slides 9 a 12.

Foco:

- explicar seguranca;
- explicar auditoria;
- mostrar dashboard/relatorios;
- fechar conclusao.

---

## Fala curta para abrir

Boa tarde. O nosso projeto chama-se SGFE, Sistema de Gestao das Financas do Estado. A proposta e centralizar a gestao de unidades orcamentais, utilizadores, orcamentos, receitas, despesas e auditoria numa plataforma web segura e rastreavel.

---

## Fala curta para fechar

Em resumo, o SGFE mostra como uma solucao full stack pode apoiar a gestao financeira publica, combinando operacao, seguranca, integridade dos dados e rastreabilidade. Obrigado.
