# SGFE — Base de dados

O backend migrado usa exclusivamente MySQL 8 em produção, com InnoDB, foreign keys, transações e migrations Flyway.

As migrations principais estão em:

- `backend/src/main/resources/db/migration/V1__create_sgfe_core_schema.sql`
- `backend/src/main/resources/db/migration/V2__seed_reference_data.sql`
- `backend/src/main/resources/db/migration/V3__auth_profile_and_indexes.sql`

Scripts auxiliares:

- `database/migrate_legacy_laravel_to_spring.sql`
- `database/validate_migration.sql`

## Base local de desenvolvimento

A base local preparada para a nova stack é `sgfe`, acessível com o utilizador `sgfe_user`.

O schema antigo foi preservado num dump local em `database/backups/sgfe_legacy_before_spring_20260509.sql`; a pasta `database/backups` fica ignorada pelo Git por poder conter dados sensíveis.

## Regras corrigidas na migração

- O saldo disponível passa a subtrair todas as despesas que comprometem o tecto: `PENDENTE_CABIMENTADA`, `LIQUIDADA_APROVADA` e `PAGA`.
- RUPE passa a ser validado como código único de 20 dígitos.
- Operações críticas passam a gerar registo persistente em `audit_logs`.
- Refresh tokens são persistidos apenas como hash SHA-256 e podem ser revogados/rotacionados.
- Valores monetários são `DECIMAL(15,2)` no banco e `BigDecimal` no Java.

## Migração de dados Laravel -> Spring Boot

Ao importar dados existentes do Laravel, transformar:

- `users.role`: `admin`, `gestor`, `auditor` -> `ADMIN`, `GESTOR`, `AUDITOR`
- `users.status`: `ativo`, `inativo` -> `ATIVO`, `INATIVO`
- `transacoes_receitas.font_receita`: `Petrolífera`, `Não Petrolífera`, `Patrimonial` -> `PETROLIFERA`, `NAO_PETROLIFERA`, `PATRIMONIAL`
- `classificacoes_economicas.tipo_receita` -> `classificacoes_economicas.tipo`

Os hashes bcrypt do Laravel podem ser reaproveitados pelo Spring Security BCrypt, desde que permaneçam na coluna `users.password`.
