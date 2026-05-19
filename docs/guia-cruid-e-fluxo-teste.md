# Guia CRUD e Teste de Fluxo Completo (SGFE)

## 1) Objectivo

Este guia cobre:

- CRUID completo por modulo (Create, Read, Update, Delete).
- Fluxo ponta a ponta: login, criacao de dados, gestao de utilizadores, auditoria, relatorios e leitura do dashboard/graficos.
- Validacoes de seguranca (RBAC) e regras de negocio.

Nota sobre "CRUID": no SGFE, a parte de "D" e normalmente tratada como inactivacao (soft delete), sobretudo em utilizadores. Nao ha endpoint de DELETE exposto nos modulos principais.

## 2) Pre-requisitos

- MySQL activo com base sgfe disponivel.
- Java 21, Maven e Node/NPM instalados.
- Porta backend livre (por defeito 8080).

## 3) Arranque para testes repetiveis

Para subir o sistema com massa de testes padrao:

```bash
cd /home/helio/Documentos/projetos/SGFE
SGFE_BOOTSTRAP_TEST_DATA=true ./scripts/dev.sh
```

Com SGFE_BOOTSTRAP_TEST_DATA=true, o backend limpa dados operacionais e recria:

- 4 instituicoes
- 7 utilizadores
- 4 orcamentos

Credenciais padrao:

- Admin: admin.sistema@sgfe.gov.ao / Admin@SGFE2026
- Gestor (exemplo): gestor.minfin@sgfe.gov.ao / Gestor@SGFE2026
- Auditor (exemplo): auditor.minfin@sgfe.gov.ao / Auditor@SGFE2026

## 4) Matriz CRUD por modulo

### 4.1 Autenticacao e sessao

- C: POST /api/auth/login
- R: GET /api/auth/me
- U: POST /api/auth/refresh, POST /api/auth/reset-password
- D: POST /api/auth/logout

Permissao: publico para login/refresh/forgot/reset; autenticado para /me/logout.

### 4.2 Instituicoes

- C: POST /api/instituicoes (ADMIN)
- R: GET /api/instituicoes (ADMIN, AUDITOR)
- U: PUT /api/instituicoes/{id} (ADMIN)
- D: nao exposto

UI actual:

- Criacao: sim (form em Admin > Unidades Orcamentais)
- Leitura: sim
- Edicao: nao exposta na UI (somente API)

### 4.3 Orcamentos

- C: POST /api/orcamentos (ADMIN)
- R: GET /api/orcamentos (ADMIN, AUDITOR), GET /api/orcamentos/meu-tecto (ADMIN, GESTOR)
- U: PUT /api/orcamentos/{id} (ADMIN)
- D: nao exposto

UI actual:

- Leitura: sim
- Criacao/edicao: nao exposta (somente API)

### 4.4 Classificacoes economicas

- C: POST /api/classificacoes (ADMIN)
- R: GET /api/classificacoes (ADMIN, GESTOR, AUDITOR)
- U: PUT /api/classificacoes/{id} (ADMIN)
- D: nao exposto

UI actual:

- Leitura: sim
- Criacao/edicao: nao exposta (somente API)

### 4.5 Utilizadores (funcionarios)

- C: POST /api/users (ADMIN)
- R: GET /api/users (ADMIN), GET /api/users/me (autenticado)
- U: PUT /api/users/{id} (ADMIN), PUT /api/users/me (autenticado), PATCH /api/users/me/password (autenticado), PATCH /api/users/{id}/role-status (ADMIN)
- D: inactivacao via PATCH /api/users/{id}/role-status com status=INATIVO

UI actual:

- Lista de utilizadores: sim
- Gestao de perfil proprio: sim (nome/email/senha)
- Criacao/edicao/inactivacao de outros utilizadores: nao exposta (somente API)

### 4.6 Receitas RUPE

- C: POST /api/receitas (ADMIN, GESTOR)
- R: GET /api/receitas (ADMIN, GESTOR, AUDITOR)
- U: nao exposto
- D: nao exposto

UI actual:

- Leitura: sim
- Criacao: nao exposta (somente API)

### 4.7 Despesas

- C: POST /api/despesas (ADMIN, GESTOR)
- R: GET /api/despesas (ADMIN, GESTOR, AUDITOR)
- U: POST /api/despesas/{id}/liquidar e POST /api/despesas/{id}/pagar (ADMIN, GESTOR)
- D: nao exposto

UI actual:

- Leitura: sim
- Botoes Liquidar/Pagar: visiveis, mas sem chamada API ligada na pagina actual
- Criacao/liquidacao/pagamento operacionais: somente API neste momento

### 4.8 Auditoria

- R: GET /api/auditoria/logs (ADMIN, AUDITOR)
- Filtros: idUser, acao, entidade, ip, inicio, fim

UI actual:

- Consulta e filtro textual: sim

### 4.9 Relatorios

- R (download):
  - GET /api/relatorios/exportar/resumo-financeiro.pdf
  - GET /api/relatorios/exportar/despesa-por-natureza.pdf
  - GET /api/relatorios/exportar/receitas-rupe.xlsx

Permissao: ADMIN, GESTOR, AUDITOR.

UI actual:

- Download dos 3 relatorios: sim

### 4.10 Dashboard e graficos

- R: GET /api/dashboard (autenticado)
- Contexto:
  - Gestor: visao da sua UO
  - Admin/Auditor: visao nacional + top UOs

## 5) Passo a passo para testar o fluxo completo

### 5.1 Preparar sessao de testes por API

Abra outro terminal para chamadas HTTP:

```bash
cd /home/helio/Documentos/projetos/SGFE
rm -f /tmp/sgfe.cookies
```

Login ADMIN e guardar cookies:

```bash
curl -i -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"email":"admin.sistema@sgfe.gov.ao","password":"Admin@SGFE2026"}' \
  http://localhost:8080/api/auth/login
```

Esperado: HTTP 200 e cookies SGFE_ACCESS_TOKEN/SGFE_REFRESH_TOKEN.

### 5.2 Testar login e proteccao de rotas (UI)

1. Entrar em /login com admin.
2. Confirmar redireccao para /dashboard.
3. Abrir /admin, /gestao, /auditoria, /relatorios, /perfil sem novo login.
4. Fazer logout e tentar abrir /dashboard.

Esperado:

- Sem sessao, rotas protegidas redireccionam para /login?next=...
- Com sessao, navegacao livre conforme perfil.

### 5.3 Criar base minima de operacao (ADMIN)

1. UI: Admin > Unidades Orcamentais > criar 1 nova UO pelo formulario.
2. API: criar classificacao economica.

```bash
curl -s -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"descricao":"Aquisicao de bens correntes","codigo":"CE-9001","tipo":"DESPESA_CORRENTE"}' \
  http://localhost:8080/api/classificacoes
```

3. API: listar classificacoes e guardar id da criada.

```bash
curl -s -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  "http://localhost:8080/api/classificacoes?size=50"
```

4. API: criar orcamento para uma instituicao (usar idInst existente).

```bash
curl -s -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"idInst":1,"valorTotal":250000000.00}' \
  http://localhost:8080/api/orcamentos
```

5. API: criar utilizador gestor (funcionario).

```bash
curl -s -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "nome":"Gestor de Teste",
    "username":"gestor.teste",
    "email":"gestor.teste@sgfe.gov.ao",
    "password":"Gestor@SGFE2026",
    "role":"GESTOR",
    "status":"ATIVO",
    "idInst":1
  }' \
  http://localhost:8080/api/users
```

6. UI: abrir Admin > Utilizadores e validar que o novo funcionario aparece.

### 5.4 Testar receitas, despesas e transicoes de estado (GESTOR)

1. Fazer login com gestor.minfin@sgfe.gov.ao / Gestor@SGFE2026 no browser.
2. No dashboard de gestao, registar os valores actuais de KPI:
   - Receita RUPE
   - Comprometido
   - Pago
   - Percentual de execucao
3. No terminal, autenticar como gestor para API:

```bash
curl -i -c /tmp/sgfe.gestor.cookies -b /tmp/sgfe.gestor.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"email":"gestor.minfin@sgfe.gov.ao","password":"Gestor@SGFE2026"}' \
  http://localhost:8080/api/auth/login
```

4. Criar receita RUPE:

```bash
curl -s -c /tmp/sgfe.gestor.cookies -b /tmp/sgfe.gestor.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "fonteReceita":"NAO_PETROLIFERA",
    "dataRegistro":"2026-05-10",
    "valorArrecadado":1500000.00,
    "idClasse":1
  }' \
  http://localhost:8080/api/receitas
```

5. Criar despesa cabimentada:

```bash
curl -s -c /tmp/sgfe.gestor.cookies -b /tmp/sgfe.gestor.cookies \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "descricao":"Compra de material de escritorio",
    "valorBruto":400000.00,
    "dataRegistro":"2026-05-10",
    "idClasse":1
  }' \
  http://localhost:8080/api/despesas
```

6. Listar despesas e anotar id da despesa criada.

```bash
curl -s -c /tmp/sgfe.gestor.cookies -b /tmp/sgfe.gestor.cookies \
  "http://localhost:8080/api/despesas?size=50&sort=id,desc"
```

7. Liquidar despesa:

```bash
curl -s -c /tmp/sgfe.gestor.cookies -b /tmp/sgfe.gestor.cookies \
  -X POST -H "X-Requested-With: XMLHttpRequest" \
  http://localhost:8080/api/despesas/{ID_DESPESA}/liquidar
```

8. Pagar despesa:

```bash
curl -s -c /tmp/sgfe.gestor.cookies -b /tmp/sgfe.gestor.cookies \
  -X POST -H "X-Requested-With: XMLHttpRequest" \
  http://localhost:8080/api/despesas/{ID_DESPESA}/pagar
```

9. Voltar ao dashboard de gestao e validar variacao dos graficos/KPIs.

Esperado:

- Receita RUPE aumenta apos criacao da receita.
- Comprometido aumenta quando cria despesa cabimentada.
- Pago aumenta depois de pagar.
- Risco orcamental muda conforme percentual de execucao.

### 5.5 Controlar logs de auditoria (ADMIN ou AUDITOR)

1. Login com auditor.minfin@sgfe.gov.ao / Auditor@SGFE2026.
2. Abrir modulo Auditoria.
3. Pesquisar por accoes geradas no fluxo:
   - CRIAR_INSTITUICAO
   - CRIAR_CLASSIFICACAO
   - CRIAR_ORCAMENTO
   - CRIAR_UTILIZADOR
   - CRIAR_RECEITA_RUPE
   - CRIAR_NCD
   - LIQUIDAR_NLD
   - REGISTRAR_PAGAMENTO

Esperado:

- Registos com data, utilizador, accao, entidade e IP.
- Severidade coerente (INFO, ALERTA, CRITICO) por tipo de operacao.

### 5.6 Gerir funcionario (utilizador) - ciclo completo

1. Como ADMIN, editar dados do utilizador via API:

```bash
curl -s -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  -X PUT \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "nome":"Gestor de Teste Actualizado",
    "username":"gestor.teste",
    "email":"gestor.teste@sgfe.gov.ao",
    "role":"GESTOR",
    "status":"ATIVO",
    "idInst":1
  }' \
  http://localhost:8080/api/users/{ID_USER}
```

2. Inactivar utilizador (equivalente ao "D" logico):

```bash
curl -s -c /tmp/sgfe.cookies -b /tmp/sgfe.cookies \
  -X PATCH \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"status":"INATIVO"}' \
  http://localhost:8080/api/users/{ID_USER}/role-status
```

3. Tentar login com esse utilizador.

Esperado:

- Login deve falhar para conta INATIVA.
- Auditoria deve registar ALTERAR_ROLE_STATUS.

### 5.7 Testar perfil proprio

1. Em Perfil, alterar nome/email e gravar.
2. Em Seguranca da conta, alterar palavra-passe.
3. Validar novo login com nova senha.

Esperado:

- Dados pessoais actualizados.
- Senha antiga deixa de funcionar.
- Auditoria com EDITAR_PERFIL e ALTERAR_SENHA.

### 5.8 Testar relatorios

1. Abrir Relatorios.
2. Fazer download dos 3 artefactos.
3. Confirmar ficheiros descarregados com conteudo nao vazio.

Esperado:

- PDF e XLSX gerados com sucesso.
- Operacoes aparecem em auditoria.

## 6) Testes negativos obrigatorios

1. RBAC:
   - Gestor tentar GET /api/auditoria/logs -> deve falhar (403).
   - Auditor tentar POST /api/instituicoes -> deve falhar (403).

2. Regras de negocio:
   - Criar despesa acima do saldo disponivel -> bloqueio com mensagem de cabimentacao.
   - Tentar pagar despesa sem liquidar -> bloqueio.
   - Reduzir orcamento abaixo do comprometido -> bloqueio.

3. Qualidade de dados:
   - Codigo de UO duplicado -> rejeicao.
   - Email duplicado de utilizador -> rejeicao.

## 7) Checklist final de aceite

- Login/logout/refresh operacionais.
- CRUD principal validado nos modulos activos.
- "D" logico validado por inactivacao de utilizador.
- Auditoria completa para accoes criticas.
- Dashboard reflecte operacoes de receita e despesa.
- Relatorios exportam com sucesso.
- Regras de RBAC e negocio passam nos cenarios negativos.
