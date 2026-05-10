-- SGFE migration helper: Laravel schema -> Spring/Flyway schema.
-- Execute this against a staging MySQL database after loading a dump of the
-- Laravel database into schema `sgfe_legacy` and creating the new schema with
-- Flyway in `sgfe`.
--
-- Example:
--   mysql -u root -p sgfe < backend/src/main/resources/db/migration/V1__create_sgfe_core_schema.sql
--   mysql -u root -p < database/migrate_legacy_laravel_to_spring.sql

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE sgfe.audit_logs;
TRUNCATE TABLE sgfe.transacoes_despesas;
TRUNCATE TABLE sgfe.transacoes_receitas;
TRUNCATE TABLE sgfe.orcamentos;
TRUNCATE TABLE sgfe.refresh_tokens;
TRUNCATE TABLE sgfe.password_reset_tokens;
TRUNCATE TABLE sgfe.users;
TRUNCATE TABLE sgfe.classificacoes_economicas;
TRUNCATE TABLE sgfe.instituicoes;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO sgfe.instituicoes (id_inst, nome, tipo, codigo, responsavel, status, created_at, updated_at)
SELECT id_inst, nome, tipo, codigo, responsavel, 'ATIVA', created_at, updated_at
FROM sgfe_legacy.instituicoes;

INSERT INTO sgfe.users (id_user, nome, username, email, email_verified_at, password, role, status, id_inst, created_at, updated_at)
SELECT
    id_user,
    nome,
    username,
    email,
    email_verified_at,
    password,
    CASE LOWER(role)
        WHEN 'admin' THEN 'ADMIN'
        WHEN 'auditor' THEN 'AUDITOR'
        ELSE 'GESTOR'
    END,
    CASE LOWER(status)
        WHEN 'inativo' THEN 'INATIVO'
        ELSE 'ATIVO'
    END,
    id_inst,
    created_at,
    updated_at
FROM sgfe_legacy.users;

INSERT INTO sgfe.classificacoes_economicas (id_classe, descricao, cod_classe, tipo, created_at, updated_at)
SELECT id_classe, descricao, cod_classe, tipo_receita, created_at, updated_at
FROM sgfe_legacy.classificacoes_economicas;

INSERT INTO sgfe.orcamentos (id_orcamento, id_user, id_inst, valor_total, ano_fiscal, created_at, updated_at)
SELECT id_orcamento, id_user, id_inst, valor_total, ano_fiscal, created_at, updated_at
FROM sgfe_legacy.orcamentos;

INSERT INTO sgfe.transacoes_receitas (id_receita, font_receita, codigo_rupe, data_registro, valor_arrecadado, id_classe, id_inst, created_at, updated_at)
SELECT
    id_receita,
    CASE font_receita
        WHEN 'Petrolífera' THEN 'PETROLIFERA'
        WHEN 'Não Petrolífera' THEN 'NAO_PETROLIFERA'
        ELSE 'PATRIMONIAL'
    END,
    CASE
        WHEN codigo_rupe REGEXP '^[0-9]{20}$' THEN codigo_rupe
        ELSE LPAD(CAST(id_receita AS CHAR), 20, '0')
    END,
    data_registro,
    valor_arrecadado,
    id_classe,
    id_inst,
    created_at,
    updated_at
FROM sgfe_legacy.transacoes_receitas;

INSERT INTO sgfe.transacoes_despesas (id_despesa, estado, descricao, valor_bruto, data_registro, id_inst, id_user, id_classe, created_at, updated_at)
SELECT
    id_despesa,
    CASE estado
        WHEN 'cabimentada' THEN 'PENDENTE_CABIMENTADA'
        WHEN 'aprovado' THEN 'LIQUIDADA_APROVADA'
        WHEN 'aprovada' THEN 'LIQUIDADA_APROVADA'
        WHEN 'executada' THEN 'PAGA'
        WHEN 'pago' THEN 'PAGA'
        ELSE estado
    END,
    descricao,
    valor_bruto,
    data_registro,
    id_inst,
    id_user,
    id_classe,
    created_at,
    updated_at
FROM sgfe_legacy.transacoes_despesas;

INSERT INTO sgfe.audit_logs (id, id_user, acao, ip_address, contexto, created_at)
SELECT id, id_user, acao, ip_address, contexto, created_at
FROM sgfe_legacy.audit_logs;
