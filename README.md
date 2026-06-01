# SGFE — Sistema de Gestão das Finanças do Estado

O SGFE é uma solução tecnológica para apoiar a gestão, execução, monitorização e auditoria das finanças públicas, com foco na Unidade Orçamental, nas fases da despesa pública e na integração da arrecadação via RUPE.

## Arquitetura

- `frontend`: Next.js, TypeScript, Tailwind CSS, shadcn/ui, TanStack Query/Table, React Hook Form, Zod e Recharts.
- `backend`: Java 21, Spring Boot, Spring Security, Spring Data JPA, Flyway, JWT, refresh token, RBAC e audit logs.
- `database`: scripts auxiliares de migração e validação MySQL.
- `docs`: documentação técnica, relatório de migração e registo de cutover.

## Domínio

O sistema apoia a execução do Orçamento Geral do Estado de Angola, cobrindo Unidade Orçamental, OGE, Cabimentação, Liquidação, Pagamento, RUPE, relatórios e auditoria.

## Validação

```bash
cd backend && mvn -q test
cd ../frontend && npm run typecheck && npm run build && npm audit --omit=dev
```

## Execução local

Com MySQL ativo e a base `sgfe` criada, execute tudo com:

```bash
./scripts/dev.sh
```

O backend fica em `http://localhost:8080` e o frontend arranca por omissao em `http://127.0.0.1:39117`.
Se essa porta ja estiver ocupada, o script sobe automaticamente para a proxima porta livre.
O backend aceita por defeito tanto `127.0.0.1` como `localhost` para a porta escolhida do frontend.

Se a porta `8080` ja estiver ocupada nesta maquina:

```bash
SGFE_PORT=18080 ./scripts/dev.sh
```

Se quiser fixar manualmente uma porta diferente para o frontend:

```bash
FRONTEND_PORT=43173 ./scripts/dev.sh
```

Credencial inicial criada na base local:

- Email: `admin@sgfe.gov.ao`
- Senha: definida localmente durante esta preparação.

Para uma base nova, crie o primeiro admin no arranque assim:

```bash
SGFE_BOOTSTRAP_ADMIN_PASSWORD='uma-senha-forte' ./scripts/dev.sh
```
