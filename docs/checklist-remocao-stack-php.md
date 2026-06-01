# SGFE — Cutover e remoção da stack PHP

Data da verificação: 2026-05-09

## Decisão

A stack PHP/Laravel pode ser removida do repositório após esta verificação.

A nova arquitetura separada foi criada com `/frontend` em Next.js e `/backend` em Spring Boot, mantendo MySQL 8, Flyway, RBAC, JWT com refresh token, audit logs e regras financeiras no backend.

## Validações concluídas antes da remoção

- `backend`: `mvn -q test` concluído com sucesso.
- `frontend`: `npm run typecheck` concluído com sucesso.
- `frontend`: `npm run build` concluído com sucesso.
- `frontend`: `npm audit --omit=dev` concluído com `found 0 vulnerabilities`.
- Relatórios backend implementados em endpoints autenticados para PDF e XLSX.
- CRUDs principais ligados à API real no frontend Next.js.
- Reset de senha, perfil e gestão de utilizadores migrados para a nova stack.
- Migração de dados Laravel -> Spring documentada em SQL auxiliar.
- Testes unitários adicionados para regras críticas de tecto, RUPE e claims JWT.

## Superfície migrada

- Instituições/Unidades Orçamentais.
- Utilizadores, roles e estados.
- Orçamentos/tectos orçamentais.
- Classificações económicas.
- Receitas com RUPE.
- Despesas com Cabimentação, Liquidação e Pagamento.
- Dashboards nacional, administrativo e de UO.
- Auditoria read-only com filtros.
- Relatórios financeiros e exportações controladas.
- Landing page institucional, login, perfil e recuperação de senha.

## Itens removidos da stack antiga

- Laravel/PHP: `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`, `tests`, `vendor`.
- Entrypoints e configuração PHP: `artisan`, `composer.json`, `composer.lock`, `phpunit.xml`.
- Build antigo do Laravel: `package.json`, `package-lock.json`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js`, `node_modules`.
- `public` antigo do Laravel, após migração dos assets úteis para `frontend/public/assets`.
- `UTILIZADORES.md`, por conter material operacional da stack antiga e credenciais/documentação insegura.

## Observações operacionais

- O backend Spring Boot continua configurado para MySQL 8 via variáveis de ambiente em `application.yml`.
- As migrations Flyway oficiais ficam em `backend/src/main/resources/db/migration`.
- Os scripts auxiliares permanecem em `database/` para migração e validação de dados.
- O cutover em ambiente real ainda deve executar `database/migrate_legacy_laravel_to_spring.sql` e `database/validate_migration.sql` contra um backup/staging antes de apontar produção.
