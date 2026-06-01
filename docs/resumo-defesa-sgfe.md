# SGFE - Resumo de Defesa

## 1. Ideia central

O SGFE e uma plataforma de gestao financeira publica para controlar Unidades Orcamentais, utilizadores, orcamentos, receitas RUPE, despesas, relatorios e auditoria.

Frase curta:

> O sistema organiza a execucao financeira do Estado com seguranca por perfil, rastreabilidade por auditoria e regras de negocio no backend.

Arquitetura:

- Frontend: Next.js + React.
- Backend: Spring Boot.
- Base de dados: MySQL.
- Persistencia: JPA/Hibernate.
- Migracoes: Flyway.
- Seguranca: JWT em cookies HttpOnly + RBAC.

## 2. Papeis do sistema

ADMIN:

- cria e edita UO;
- cria utilizadores e senhas;
- cria orcamentos;
- cria receitas/despesas com escopo administrativo;
- pode usar autorizacao retroativa aprovada.

GESTOR:

- opera a sua propria UO;
- cria receitas do dia;
- cria e acompanha despesas da sua UO;
- nao deve criar UO nem gerir utilizadores.

AUDITOR:

- consulta dados;
- ve logs de auditoria;
- autoriza receitas retroativas;
- gera PDF da autorizacao.

Resposta curta:

> O RBAC separa responsabilidades: Admin administra, Gestor executa, Auditor fiscaliza.

## 3. Seguranca

Autenticacao:

- login valida email e palavra-passe no backend;
- senha guardada com BCrypt;
- backend gera access token JWT e refresh token;
- tokens sao enviados por cookies HttpOnly;
- access cookie: `SGFE_ACCESS_TOKEN`;
- refresh cookie: `SGFE_REFRESH_TOKEN`.

Por que HttpOnly?

> Porque JavaScript do browser nao consegue ler o token diretamente, reduzindo risco em caso de XSS.

Refresh token:

- e gerado de forma segura;
- no banco fica apenas o hash SHA-256;
- ao renovar sessao, o refresh antigo e revogado e outro e criado.

Autorizacao:

- backend usa `@PreAuthorize`;
- frontend tambem filtra rotas e menus;
- a decisao real fica no backend.

Headers de seguranca:

- CSP restritiva;
- `X-Frame-Options: DENY`;
- `Referrer-Policy: NO_REFERRER`;
- `Permissions-Policy` bloqueia camera, microfone e geolocalizacao.

CORS:

- aceita apenas origens configuradas;
- usa credenciais porque a sessao esta em cookies.

Auditoria:

- acoes importantes geram log;
- log guarda utilizador, UO, acao, entidade, resultado, severidade, IP, user-agent e contexto JSON.

Resposta para pergunta dificil:

> Mesmo que o frontend esconda um botao, a regra critica esta no backend. Se alguem tentar chamar a API manualmente, o Spring Security bloqueia pelo papel do utilizador.

## 4. Base de dados

Tabelas principais:

- `instituicoes`: Unidades Orcamentais.
- `users`: utilizadores ligados a uma UO.
- `refresh_tokens`: tokens de renovacao de sessao.
- `classificacoes_economicas`: rubricas/classificacoes.
- `orcamentos`: tecto orcamental por UO e ano fiscal.
- `transacoes_receitas`: receitas RUPE.
- `transacoes_despesas`: despesas.
- `audit_logs`: historico de auditoria.
- `autorizacoes_receitas_retroativas`: autorizacoes para criar receita com data anterior.

Relacionamentos essenciais:

- Uma `instituicao` tem muitos `users`.
- Uma `instituicao` tem muitos `orcamentos`.
- Uma `instituicao` tem muitas `receitas`.
- Uma `instituicao` tem muitas `despesas`.
- Uma `classificacao_economica` pode estar em muitas receitas e despesas.
- Uma `receita retroativa` pertence a uma UO, tem solicitante, pode ter auditor e pode apontar para a receita criada.
- `audit_logs` pode apontar para utilizador e instituicao, mas usa `ON DELETE SET NULL` para preservar historico.

Regras importantes no banco:

- `instituicoes.codigo` e unico.
- `users.email` e `users.username` sao unicos.
- `orcamentos` tem unicidade por UO + ano fiscal.
- `transacoes_receitas.codigo_rupe` e unico.
- RUPE deve ter 20 digitos numericos.
- valores de receita/despesa devem ser maiores que zero.
- FKs usam `ON DELETE RESTRICT` onde apagar quebraria historico financeiro.

Resposta curta:

> A base e relacional porque os dados financeiros dependem muito de integridade: UO, utilizador, orcamento, receita, despesa e auditoria precisam estar ligados por chaves estrangeiras.

## 5. Fluxos principais

### Login

1. Utilizador envia email e senha.
2. Backend autentica.
3. Gera access token e refresh token.
4. Escreve cookies HttpOnly.
5. Frontend usa sessao para mostrar menus conforme papel.

### Criar UO

1. Admin informa dados da UO.
2. Admin informa email e senha inicial do responsavel.
3. Backend cria a UO.
4. Backend cria o utilizador responsavel como GESTOR.
5. Backend audita a operacao.

Pergunta:

> Por que criar o utilizador junto com a UO?

Resposta:

> Para evitar funcionarios criados manualmente sem controlo. So o Admin cria e gere utilizadores e senhas.

### Criar orcamento

1. Admin escolhe UO.
2. Define valor total do ano fiscal.
3. Backend impede duplicidade UO + ano.
4. O orcamento vira o tecto usado no controlo de despesas.

### Criar receita RUPE normal

1. Admin ou Gestor cria receita.
2. Data fica cravada no dia atual.
3. Fonte deve ser uma das permitidas: `PETROLIFERA`, `NAO_PETROLIFERA`, `PATRIMONIAL`.
4. Backend gera RUPE automaticamente.
5. Receita e auditada.

RUPE:

- codigo numerico de 20 digitos;
- gerado com `SecureRandom`;
- backend verifica se ja existe;
- banco tambem garante unicidade.

### Receita retroativa

1. Admin/Gestor pede autorizacao para data anterior.
2. Pedido fica como `PENDENTE`.
3. Auditor autoriza e gera PDF.
4. Pedido passa para `AUTORIZADA`.
5. Admin usa a autorizacao para criar a receita naquela data.
6. Autorizacao passa para `UTILIZADA`.

Resposta curta:

> A autorizacao retroativa e de uso unico. Depois que cria a receita, fecha automaticamente.

### Despesa

1. Admin/Gestor cria despesa.
2. Backend verifica UO e classificacao.
3. Backend valida saldo/tecto.
4. Despesa passa por estados financeiros.

Estados:

- `PENDENTE_CABIMENTADA`;
- `LIQUIDADA_APROVADA`;
- `PAGA`;
- `REJEITADA`;
- `CANCELADA`;
- `EM_ANALISE`.

Estados que comprometem tecto:

- `PENDENTE_CABIMENTADA`;
- `LIQUIDADA_APROVADA`;
- `PAGA`.

## 6. Backend

Camadas:

- Controller: recebe HTTP e aplica autorizacao.
- DTO: define entrada/saida da API.
- Service: contem regra de negocio.
- Repository: acesso ao banco.
- Entity: mapeamento JPA das tabelas.

Por que regra no Service?

> Porque a regra nao deve depender da interface. Assim, mesmo se outro cliente chamar a API, as validacoes continuam iguais.

Exemplos de services:

- `AuthService`: login, refresh, logout, reset de senha.
- `InstituicaoService`: UO e utilizador responsavel.
- `ReceitaService`: criacao de receita, RUPE, datas e autorizacao retroativa.
- `DespesaService`: ciclo da despesa e controlo de tecto.
- `OrcamentoService`: tecto anual por UO.
- `AuditService`: persistencia de logs.
- `RelatorioService`: PDF/Excel.

Tratamento de erros:

- regras de negocio lancam `RegraNegocioException`;
- erros viram resposta JSON com status e mensagem;
- validacoes usam Bean Validation;
- erros de integridade viram resposta controlada.

Transacoes:

- operacoes criticas usam `@Transactional`;
- evita gravar metade de uma operacao;
- exemplo: criar receita e marcar autorizacao como utilizada deve ser consistente.

## 7. Perguntas e respostas rapidas

### Por que Spring Boot?

Porque oferece seguranca, validacao, JPA, transacoes e APIs REST de forma robusta para sistema institucional.

### Por que MySQL?

Porque o dominio e altamente relacional e precisa de integridade por FK, unicidade e consultas financeiras.

### Por que Flyway?

Para versionar a base de dados. Cada migration documenta como o schema evoluiu.

### Por que RBAC?

Porque cada perfil tem responsabilidades diferentes. Isso reduz risco operacional.

### O que e UO?

Unidade Orcamental. E a entidade institucional que recebe orcamento, registra receitas e executa despesas.

### O que e RUPE?

E o codigo unico da receita. No sistema e gerado automaticamente com 20 digitos numericos e guardado como unico.

### O que impede um gestor de criar UO?

Frontend nao mostra a opcao e backend exige `ADMIN` no endpoint de criacao/edicao/remocao.

### O que impede alterar receita para data passada?

Backend exige data corrente. Para data passada precisa de autorizacao retroativa aprovada pelo Auditor.

### O que acontece se alguem tentar burlar pelo DevTools?

A API bloqueia, porque as regras de papel, data, UO e autorizacao estao no backend.

### Por que auditoria e importante?

Porque permite responder: quem fez, quando fez, em que UO, com que resultado e em que contexto.

### Por que refresh token e salvo em hash?

Porque se a base vazar, o token real nao fica exposto diretamente.

### Qual a diferenca entre autenticacao e autorizacao?

Autenticacao confirma quem e o utilizador. Autorizacao define o que esse utilizador pode fazer.

## 8. Resposta final para banca

> O SGFE foi desenhado com separacao clara entre interface, API e banco. O frontend facilita o uso, mas as regras criticas ficam no backend. A base de dados garante integridade relacional, o Spring Security garante acesso por perfil e a auditoria garante rastreabilidade das operacoes financeiras.

## 9. Comandos manuais

### Subir tudo com o script do projeto

Na raiz do projeto:

```bash
FRONTEND_PORT=39120 ./scripts/dev.sh
```

O script:

- escolhe porta livre para backend;
- escolhe porta livre para frontend;
- exporta variaveis de ambiente;
- inicia Spring Boot e Next.js.

### Subir backend manualmente

Terminal 1:

```bash
cd backend

export SGFE_DB_URL="jdbc:mysql://localhost:3306/sgfe?useUnicode=true&characterEncoding=utf8&serverTimezone=UTC"
export SGFE_DB_USERNAME="sgfe_user"
export SGFE_DB_PASSWORD="sgfe_pass"
export SGFE_JWT_SECRET="change-this-development-secret-change-this-development-secret"
export SGFE_ACCESS_TOKEN_MINUTES=15
export SGFE_REFRESH_TOKEN_DAYS=7
export SGFE_CORS_ORIGINS="http://localhost:39120,http://127.0.0.1:39120"
export SGFE_PORT=8080

mvn spring-boot:run
```

As migrations Flyway correm automaticamente quando o backend inicia.

### Subir frontend manualmente

Terminal 2:

```bash
cd frontend

export NEXT_PUBLIC_API_BASE_URL="http://localhost:8080/api"

npm install
npm run dev -- --hostname 127.0.0.1 --port 39120
```

Abrir:

```text
http://localhost:39120
```

### Criar base de dados local

Se a base ainda nao existir:

```bash
sudo systemctl start mysql
mysql -u root -p
```

Dentro do MySQL:

```sql
CREATE DATABASE IF NOT EXISTS sgfe
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'sgfe_user'@'localhost'
  IDENTIFIED BY 'sgfe_pass';

GRANT ALL PRIVILEGES ON sgfe.* TO 'sgfe_user'@'localhost';
FLUSH PRIVILEGES;
```

### Verificar se esta a funcionar

Backend:

```bash
curl http://localhost:8080/actuator/health
```

Resposta esperada:

```json
{"status":"UP"}
```

Frontend:

```bash
curl -I http://localhost:39120/login
```

Resposta esperada:

```text
HTTP/1.1 200 OK
```

### Rodar testes

Backend:

```bash
cd backend
mvn test
```

Frontend:

```bash
cd frontend
npm run typecheck
```

Build do frontend:

```bash
cd frontend
npm run build
```

### Limpar portas ocupadas

Ver processos:

```bash
ss -H -ltnp '( sport = :39120 or sport = :8080 )'
```

Encerrar por PID:

```bash
kill <PID>
```

Resposta curta para banca:

> Primeiro inicio o MySQL, depois subo o backend com `mvn spring-boot:run`, que aplica as migrations Flyway, e por fim subo o frontend com `npm run dev`. Para validar, uso `/actuator/health` e abro o frontend no browser.

## 10. Onde demonstrar CRUD completo

Use `Admin > Classificacoes Economicas`.

CRUD:

- Create: clicar em `Nova Classificacao` e guardar.
- Read: ver a tabela e pesquisar pelo codigo/descricao.
- Update: clicar em `Editar`, alterar dados e atualizar.
- Delete: clicar em `Remover`.

Endpoints equivalentes:

- `GET /api/classificacoes`
- `POST /api/classificacoes`
- `PUT /api/classificacoes/{id}`
- `DELETE /api/classificacoes/{id}`

Regra de seguranca:

- `ADMIN` cria, edita e remove.
- `GESTOR` e `AUDITOR` apenas consultam.

Nota para a demonstracao:

> Para apagar, cria uma classificacao nova durante a apresentacao e remove essa mesma classificacao. Se tentar apagar uma classificacao ja usada por receita ou despesa, a base pode bloquear por chave estrangeira para preservar o historico financeiro.
