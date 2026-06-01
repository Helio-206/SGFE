# SGFE Backend

Backend migrado para Java 21 + Spring Boot.

## Stack

- Java 21
- Spring Boot
- Spring Security
- Spring Data JPA
- MySQL 8
- Flyway
- JWT + refresh token
- RBAC
- Audit logs

## Execução local

Configure as variáveis esperadas em `src/main/resources/application.yml` ou no ambiente:

- `SGFE_DB_URL`
- `SGFE_DB_USERNAME`
- `SGFE_DB_PASSWORD`
- `SGFE_JWT_SECRET`
- `SGFE_CORS_ORIGINS`
- `SGFE_PORT`

Depois execute:

```bash
mvn spring-boot:run
```

Na raiz do projeto também pode usar:

```bash
./scripts/dev.sh
```

## Validação

```bash
mvn -q test
```

## Migrations

As migrations oficiais ficam em `src/main/resources/db/migration` e são executadas pelo Flyway na inicialização.
