# SGFE — Base de Dados, Tabelas e Relacionamentos

Data: 10 de maio de 2026

## 1. Objetivo

Este documento descreve a estrutura actual da base de dados do SGFE, incluindo tabelas, colunas, chaves primárias, chaves estrangeiras, constraints, índices, regras de integridade e relacionamentos entre entidades.

A fonte principal deste documento é o esquema oficial definido nas migrations Flyway:

- `V1__create_sgfe_core_schema.sql`
- `V2__seed_reference_data.sql`
- `V3__auth_profile_and_indexes.sql`

## 2. Visão Geral do Modelo

O SGFE usa MySQL 8 com engine InnoDB, charset `utf8mb4` e collation `utf8mb4_unicode_ci`.

O modelo está organizado em três blocos principais:

- núcleo institucional e financeiro;
- autenticação e segurança;
- auditoria e rastreabilidade.

## 3. Tabelas do Sistema

As tabelas actualmente definidas são:

1. `instituicoes`
2. `users`
3. `refresh_tokens`
4. `password_reset_tokens`
5. `classificacoes_economicas`
6. `orcamentos`
7. `transacoes_receitas`
8. `transacoes_despesas`
9. `audit_logs`

## 4. Diagrama Lógico Simplificado

```text
instituicoes
  ├── users
  │     ├── refresh_tokens
  │     ├── orcamentos
  │     ├── transacoes_despesas
  │     └── audit_logs
  ├── orcamentos
  ├── transacoes_receitas
  ├── transacoes_despesas
  └── audit_logs

classificacoes_economicas
  ├── transacoes_receitas
  └── transacoes_despesas

password_reset_tokens
  └── tabela auxiliar sem FK física para users
```

## 5. Descrição Detalhada das Tabelas

### 5.1 `instituicoes`

Tabela central de cadastro institucional. Representa a Unidade Orçamental ou entidade pública dentro do SGFE.

#### Finalidade

- identificar a instituição;
- organizar a vinculação institucional de utilizadores;
- servir de base para orçamento, receitas, despesas e auditoria.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id_inst` | `BIGINT` | não | chave primária, auto incremento |
| `nome` | `VARCHAR(150)` | não | nome institucional |
| `tipo` | `VARCHAR(50)` | não | tipo da instituição |
| `codigo` | `VARCHAR(20)` | não | código institucional único |
| `responsavel` | `VARCHAR(100)` | não | responsável pela instituição |
| `status` | `VARCHAR(20)` | não | default `ATIVA` |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |
| `updated_at` | `TIMESTAMP(6)` | não | atualização automática |

#### Constraints

- `PRIMARY KEY (id_inst)`
- `UNIQUE (codigo)`

#### Relacionamentos

- `1:N` com `users`
- `1:N` com `orcamentos`
- `1:N` com `transacoes_receitas`
- `1:N` com `transacoes_despesas`
- `1:N` com `audit_logs`

#### Comportamento de integridade

As referências para `instituicoes` são maioritariamente `ON DELETE RESTRICT`, o que impede apagar uma instituição ainda ligada a utilizadores, orçamentos, receitas ou despesas. Em `audit_logs`, o comportamento é `ON DELETE SET NULL`.

### 5.2 `users`

Tabela de utilizadores do sistema.

#### Finalidade

- autenticação;
- autorização por perfil;
- associação institucional;
- rastreabilidade de operações.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id_user` | `BIGINT` | não | chave primária, auto incremento |
| `nome` | `VARCHAR(100)` | não | nome completo |
| `username` | `VARCHAR(50)` | não | único |
| `email` | `VARCHAR(100)` | não | único |
| `email_verified_at` | `TIMESTAMP(6)` | sim | data de verificação de email |
| `password` | `VARCHAR(255)` | não | hash da palavra-passe |
| `role` | `ENUM('ADMIN','GESTOR','AUDITOR')` | não | default `GESTOR` |
| `status` | `ENUM('ATIVO','INATIVO')` | não | default `ATIVO` |
| `id_inst` | `BIGINT` | não | FK para instituição |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |
| `updated_at` | `TIMESTAMP(6)` | não | atualização automática |

#### Constraints

- `PRIMARY KEY (id_user)`
- `UNIQUE (username)`
- `UNIQUE (email)`
- `FOREIGN KEY (id_inst) REFERENCES instituicoes(id_inst) ON DELETE RESTRICT`

#### Relacionamentos

- `N:1` com `instituicoes`
- `1:N` com `refresh_tokens`
- `1:N` com `orcamentos`
- `1:N` com `transacoes_despesas`
- `1:N` com `audit_logs`

#### Observação

A entidade JPA `User` mapeia diretamente `password`, `role`, `status` e `id_inst`. A coluna `email_verified_at` existe na base, embora não apareça no mapeamento principal da entidade actual.

### 5.3 `refresh_tokens`

Tabela auxiliar de autenticação para gestão de sessões e rotação de refresh token.

#### Finalidade

- persistir refresh tokens;
- permitir revogação;
- suportar logout e rotação de sessão.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id` | `BIGINT` | não | chave primária, auto incremento |
| `id_user` | `BIGINT` | não | FK para utilizador |
| `token_hash` | `CHAR(64)` | não | hash único do token |
| `expires_at` | `TIMESTAMP(6)` | não | expiração |
| `revoked_at` | `TIMESTAMP(6)` | sim | revogação |
| `ip_address` | `VARCHAR(45)` | sim | IP de origem |
| `user_agent` | `VARCHAR(255)` | sim | agente do cliente |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |

#### Constraints e índices

- `PRIMARY KEY (id)`
- `UNIQUE (token_hash)`
- `FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE`
- índice `idx_refresh_tokens_user_active (id_user, revoked_at, expires_at)`

#### Relacionamento

- `N:1` com `users`

#### Comportamento de integridade

Se um utilizador for eliminado, os seus refresh tokens são apagados automaticamente por `ON DELETE CASCADE`.

### 5.4 `password_reset_tokens`

Tabela auxiliar para recuperação de palavra-passe.

#### Finalidade

- armazenar token de recuperação por email;
- controlar expiração e uso do token.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `email` | `VARCHAR(100)` | não | chave primária |
| `token_hash` | `CHAR(64)` | não | hash do token |
| `expires_at` | `TIMESTAMP(6)` | não | expiração |
| `used_at` | `TIMESTAMP(6)` | sim | data de utilização |
| `ip_address` | `VARCHAR(45)` | sim | IP de origem |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |

#### Constraints e índices

- `PRIMARY KEY (email)`
- índice `idx_password_reset_token_hash (token_hash)`
- índice `idx_password_reset_expires (expires_at)`

#### Relacionamento

- não existe FK física para `users`

#### Observação

Esta tabela é ligada logicamente à tabela `users` por `email`, mas o esquema actual não impõe chave estrangeira. Isso dá flexibilidade operacional, mas reduz integridade referencial ao nível do banco nesta parte específica.

### 5.5 `classificacoes_economicas`

Catálogo de classificações económicas usadas por receitas e despesas.

#### Finalidade

- normalizar rubricas financeiras;
- permitir enquadramento económico das transações;
- suportar filtros, relatórios e controlo contabilístico.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id_classe` | `BIGINT` | não | chave primária, auto incremento |
| `descricao` | `VARCHAR(100)` | não | descrição da rubrica |
| `cod_classe` | `VARCHAR(20)` | não | código único |
| `tipo` | `VARCHAR(80)` | não | natureza ou agrupamento |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |
| `updated_at` | `TIMESTAMP(6)` | não | atualização automática |

#### Constraints

- `PRIMARY KEY (id_classe)`
- `UNIQUE (cod_classe)`

#### Relacionamentos

- `1:N` com `transacoes_receitas`
- `1:N` com `transacoes_despesas`

#### Comportamento de integridade

- em `transacoes_receitas`, `ON DELETE RESTRICT`
- em `transacoes_despesas`, `ON DELETE SET NULL`

### 5.6 `orcamentos`

Tabela de tectos orçamentais por instituição e exercício fiscal.

#### Finalidade

- definir o valor total autorizado para uma instituição num ano fiscal;
- registar quem fez a atribuição do orçamento;
- suportar controlo de saldo e execução.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id_orcamento` | `BIGINT` | não | chave primária, auto incremento |
| `id_user` | `BIGINT` | não | utilizador que atribuiu |
| `id_inst` | `BIGINT` | não | instituição beneficiária |
| `valor_total` | `DECIMAL(15,2)` | não | deve ser maior ou igual a zero |
| `ano_fiscal` | `SMALLINT` | não | ano fiscal |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |
| `updated_at` | `TIMESTAMP(6)` | não | atualização automática |

#### Constraints e índices

- `PRIMARY KEY (id_orcamento)`
- `FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE RESTRICT`
- `FOREIGN KEY (id_inst) REFERENCES instituicoes(id_inst) ON DELETE RESTRICT`
- `UNIQUE (id_inst, ano_fiscal)`
- `CHECK (valor_total >= 0)`
- índice `idx_orcamentos_ano (ano_fiscal)`

#### Relacionamentos

- `N:1` com `users`
- `N:1` com `instituicoes`

#### Regra funcional relevante

Uma instituição só pode ter um orçamento por ano fiscal.

### 5.7 `transacoes_receitas`

Tabela de receitas arrecadadas.

#### Finalidade

- registar entradas financeiras;
- associar a arrecadação à classificação económica;
- vincular a operação à instituição;
- suportar RUPE.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id_receita` | `BIGINT` | não | chave primária, auto incremento |
| `font_receita` | `ENUM('PETROLIFERA','NAO_PETROLIFERA','PATRIMONIAL')` | não | fonte da receita |
| `codigo_rupe` | `VARCHAR(40)` | não | único, validado como 20 dígitos |
| `data_registro` | `DATE` | não | data do registo |
| `valor_arrecadado` | `DECIMAL(15,2)` | não | valor positivo |
| `id_classe` | `BIGINT` | não | classificação económica |
| `id_inst` | `BIGINT` | não | instituição |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |
| `updated_at` | `TIMESTAMP(6)` | não | atualização automática |

#### Constraints e índices

- `PRIMARY KEY (id_receita)`
- `UNIQUE (codigo_rupe)`
- `FOREIGN KEY (id_classe) REFERENCES classificacoes_economicas(id_classe) ON DELETE RESTRICT`
- `FOREIGN KEY (id_inst) REFERENCES instituicoes(id_inst) ON DELETE RESTRICT`
- `CHECK (valor_arrecadado > 0)`
- `CHECK (codigo_rupe REGEXP '^[0-9]{20}$')`
- índice `idx_receitas_inst_data (id_inst, data_registro)`
- índice `idx_receitas_data (data_registro)`

#### Relacionamentos

- `N:1` com `classificacoes_economicas`
- `N:1` com `instituicoes`

### 5.8 `transacoes_despesas`

Tabela de execução da despesa pública.

#### Finalidade

- registar despesas;
- acompanhar estado do fluxo financeiro;
- vincular despesa à instituição, ao utilizador e, opcionalmente, à classificação económica.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id_despesa` | `BIGINT` | não | chave primária, auto incremento |
| `estado` | `ENUM('PENDENTE_CABIMENTADA','LIQUIDADA_APROVADA','PAGA','REJEITADA','CANCELADA','EM_ANALISE')` | não | default `PENDENTE_CABIMENTADA` |
| `descricao` | `VARCHAR(150)` | não | descrição da despesa |
| `valor_bruto` | `DECIMAL(15,2)` | não | valor positivo |
| `data_registro` | `DATE` | não | data do registo |
| `id_inst` | `BIGINT` | não | instituição |
| `id_user` | `BIGINT` | não | utilizador responsável |
| `id_classe` | `BIGINT` | sim | classificação opcional |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |
| `updated_at` | `TIMESTAMP(6)` | não | atualização automática |

#### Constraints e índices

- `PRIMARY KEY (id_despesa)`
- `FOREIGN KEY (id_inst) REFERENCES instituicoes(id_inst) ON DELETE RESTRICT`
- `FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE RESTRICT`
- `FOREIGN KEY (id_classe) REFERENCES classificacoes_economicas(id_classe) ON DELETE SET NULL`
- `CHECK (valor_bruto > 0)`
- índice `idx_despesas_inst_data_estado (id_inst, data_registro, estado)`
- índice `idx_despesas_data_estado (data_registro, estado)`

#### Relacionamentos

- `N:1` com `instituicoes`
- `N:1` com `users`
- `N:1` opcional com `classificacoes_economicas`

#### Estados actualmente suportados

- `PENDENTE_CABIMENTADA`
- `LIQUIDADA_APROVADA`
- `PAGA`
- `REJEITADA`
- `CANCELADA`
- `EM_ANALISE`

#### Observação funcional

Para o controlo do tecto orçamental, o domínio actual considera como comprometedoras as despesas nos estados `PENDENTE_CABIMENTADA`, `LIQUIDADA_APROVADA` e `PAGA`.

### 5.9 `audit_logs`

Tabela de registo de auditoria.

#### Finalidade

- manter histórico de operações relevantes;
- associar eventos a utilizadores e instituições;
- guardar contexto técnico e funcional da ação.

#### Colunas

| Coluna | Tipo | Nulo | Regra |
| --- | --- | --- | --- |
| `id` | `BIGINT` | não | chave primária, auto incremento |
| `id_user` | `BIGINT` | sim | utilizador relacionado |
| `id_inst` | `BIGINT` | sim | instituição relacionada |
| `acao` | `VARCHAR(120)` | não | nome da ação |
| `entidade` | `VARCHAR(80)` | sim | entidade afectada |
| `entidade_id` | `VARCHAR(80)` | sim | identificador da entidade |
| `resultado` | `ENUM('SUCESSO','FALHA','NEGADO')` | não | default `SUCESSO` |
| `severidade` | `ENUM('INFO','ALERTA','CRITICO')` | não | default `INFO` |
| `ip_address` | `VARCHAR(45)` | sim | IP |
| `user_agent` | `VARCHAR(255)` | sim | agente do cliente |
| `correlation_id` | `VARCHAR(80)` | sim | correlação da requisição |
| `contexto` | `JSON` | sim | metadados da operação |
| `created_at` | `TIMESTAMP(6)` | não | criação automática |

#### Constraints e índices

- `PRIMARY KEY (id)`
- `FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL`
- `FOREIGN KEY (id_inst) REFERENCES instituicoes(id_inst) ON DELETE SET NULL`
- índice `idx_audit_acao_created (acao, created_at)`
- índice `idx_audit_user_created (id_user, created_at)`
- índice `idx_audit_entidade (entidade, entidade_id)`
- índice `idx_audit_inst_created (id_inst, created_at)`
- índice `idx_audit_ip (ip_address)`
- índice adicional `idx_audit_created (created_at)` criado em `V3`

#### Relacionamentos

- `N:1` opcional com `users`
- `N:1` opcional com `instituicoes`

#### Comportamento de integridade

Quando o utilizador ou a instituição associados deixam de existir, o log é preservado e a FK fica `NULL` por `ON DELETE SET NULL`.

## 6. Relacionamentos Entre Tabelas

### 6.1 Relações com `instituicoes`

- uma instituição pode ter vários utilizadores;
- uma instituição pode ter vários orçamentos ao longo de anos diferentes;
- uma instituição pode ter várias receitas;
- uma instituição pode ter várias despesas;
- uma instituição pode aparecer em vários logs de auditoria.

### 6.2 Relações com `users`

- um utilizador pertence a uma única instituição;
- um utilizador pode gerar vários refresh tokens;
- um utilizador pode criar vários orçamentos;
- um utilizador pode registar várias despesas;
- um utilizador pode estar associado a vários logs de auditoria.

### 6.3 Relações com `classificacoes_economicas`

- uma classificação económica pode ser usada em várias receitas;
- uma classificação económica pode ser usada em várias despesas;
- receitas exigem classificação obrigatória;
- despesas aceitam classificação opcional.

### 6.4 Relações auxiliares de segurança

- `refresh_tokens` depende de `users` por FK directa;
- `password_reset_tokens` depende logicamente do email, sem FK física.

## 7. Cardinalidades

| Origem | Destino | Cardinalidade | Observação |
| --- | --- | --- | --- |
| `instituicoes` | `users` | `1:N` | obrigatória no lado de `users` |
| `instituicoes` | `orcamentos` | `1:N` | um por ano fiscal |
| `instituicoes` | `transacoes_receitas` | `1:N` | obrigatória |
| `instituicoes` | `transacoes_despesas` | `1:N` | obrigatória |
| `instituicoes` | `audit_logs` | `1:N` | opcional no lado de log |
| `users` | `refresh_tokens` | `1:N` | cascata no delete |
| `users` | `orcamentos` | `1:N` | utilizador que atribuiu |
| `users` | `transacoes_despesas` | `1:N` | utilizador que registou/operou |
| `users` | `audit_logs` | `1:N` | opcional no lado de log |
| `classificacoes_economicas` | `transacoes_receitas` | `1:N` | obrigatória |
| `classificacoes_economicas` | `transacoes_despesas` | `1:N` | opcional |

## 8. Regras de Integridade Relevantes

### 8.1 Unicidade

- `instituicoes.codigo` é único;
- `users.username` é único;
- `users.email` é único;
- `classificacoes_economicas.cod_classe` é único;
- `transacoes_receitas.codigo_rupe` é único;
- `orcamentos (id_inst, ano_fiscal)` é único;
- `refresh_tokens.token_hash` é único.

### 8.2 Validação de valores

- `orcamentos.valor_total >= 0`;
- `transacoes_receitas.valor_arrecadado > 0`;
- `transacoes_despesas.valor_bruto > 0`.

### 8.3 Integridade referencial

- não se pode apagar uma instituição que ainda esteja ligada a utilizadores, orçamentos, receitas ou despesas;
- não se pode apagar um utilizador se houver referências com `RESTRICT`, como em `orcamentos` ou `transacoes_despesas`;
- apagar um utilizador remove os seus `refresh_tokens`;
- apagar uma classificação não é permitido se existirem receitas ligadas a ela;
- apagar uma classificação ligada a despesas faz `SET NULL` na despesa.

### 8.4 Regras especiais do esquema

- `codigo_rupe` deve obedecer ao padrão de 20 dígitos;
- `audit_logs.contexto` é armazenado em `JSON`;
- `audit_logs` preserva histórico mesmo com remoção da entidade relacionada;
- `password_reset_tokens` não força FK com `users`.

## 9. Índices Principais

Os índices actuais dão suporte sobretudo a consultas operacionais e relatórios.

### 9.1 Índices em `refresh_tokens`

- `idx_refresh_tokens_user_active (id_user, revoked_at, expires_at)`

### 9.2 Índices em `orcamentos`

- `idx_orcamentos_ano (ano_fiscal)`

### 9.3 Índices em `transacoes_receitas`

- `idx_receitas_inst_data (id_inst, data_registro)`
- `idx_receitas_data (data_registro)`

### 9.4 Índices em `transacoes_despesas`

- `idx_despesas_inst_data_estado (id_inst, data_registro, estado)`
- `idx_despesas_data_estado (data_registro, estado)`

### 9.5 Índices em `audit_logs`

- `idx_audit_acao_created`
- `idx_audit_user_created`
- `idx_audit_entidade`
- `idx_audit_inst_created`
- `idx_audit_ip`
- `idx_audit_created`

### 9.6 Índices em `password_reset_tokens`

- `idx_password_reset_token_hash`
- `idx_password_reset_expires`

## 10. Dados de Referência Inicial

As seeds actuais definidas em `V2__seed_reference_data.sql` introduzem dados básicos para arranque do sistema.

### 10.1 Instituição inicial

É inserida uma instituição de referência:

- `Ministerio das Financas`
- tipo `Ministerio`
- código `UO-001`

### 10.2 Classificações económicas iniciais

São inseridas classificações de referência tanto para receitas como para despesas, incluindo exemplos como:

- impostos sobre o rendimento;
- imposto sobre salários;
- imposto industrial;
- IVA;
- taxas de serviços públicos;
- receitas petrolíferas;
- despesas com pessoal;
- bens e serviços correntes;
- investimentos públicos.

## 11. Observações Importantes do Modelo Actual

### 11.1 Modelo orientado ao domínio

O desenho não é genérico. Ele está claramente alinhado ao contexto de finanças públicas, com foco em instituição, orçamento, receita, despesa e auditoria.

### 11.2 Auditoria persistente

O uso de `audit_logs` com `JSON`, severidade, resultado e correlação técnica indica uma preocupação explícita com rastreabilidade institucional.

### 11.3 Autenticação desacoplada da sessão tradicional

O uso de `refresh_tokens` e `password_reset_tokens` mostra que o modelo já foi preparado para autenticação moderna baseada em token.

### 11.4 Integridade forte no núcleo financeiro

O núcleo financeiro usa FKs com `RESTRICT` em quase todas as tabelas principais, o que reduz risco de perda acidental de integridade histórica.

### 11.5 Exceção controlada em `password_reset_tokens`

A única parte que foge à integridade referencial física clássica é `password_reset_tokens`, ligada logicamente a `users` por email.

## 12. Conclusão

A base de dados do SGFE encontra-se estruturada em torno de um núcleo institucional forte, com relacionamentos claros entre instituições, utilizadores, orçamento, receitas, despesas, auditoria e mecanismos de autenticação.

O modelo actual apresenta:

- separação coerente entre domínio financeiro e domínio de autenticação;
- integridade referencial forte nas tabelas centrais;
- unicidade e validações de negócio ao nível do banco;
- suporte a auditoria persistente;
- base pronta para relatórios, controlo e evolução funcional.

Em termos práticos, trata-se de um esquema relacional consistente, orientado ao domínio público-financeiro e adequado para sustentar tanto a operação do sistema como a sua apresentação técnica e institucional.