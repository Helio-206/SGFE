#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FRONTEND_HOST="${FRONTEND_HOST:-127.0.0.1}"
FRONTEND_PORT="${FRONTEND_PORT:-39117}"

build_cors_origins() {
  local port="$1"
  local primary_host="$2"
  local origins=()

  origins+=("http://${primary_host}:${port}")

  if [[ "$primary_host" != "127.0.0.1" ]]; then
    origins+=("http://127.0.0.1:${port}")
  fi

  if [[ "$primary_host" != "localhost" ]]; then
    origins+=("http://localhost:${port}")
  fi

  local joined=""
  local origin
  for origin in "${origins[@]}"; do
    if [[ -n "$joined" ]]; then
      joined+=","
    fi
    joined+="$origin"
  done

  printf '%s' "$joined"
}

port_is_busy() {
  local port="$1"

  if command -v ss >/dev/null 2>&1; then
    ss -H -ltn "( sport = :$port )" | grep -q .
    return
  fi

  if command -v lsof >/dev/null 2>&1; then
    lsof -iTCP:"$port" -sTCP:LISTEN -P -n >/dev/null 2>&1
    return
  fi

  return 1
}

resolve_available_port() {
  local candidate="$1"

  while port_is_busy "$candidate"; do
    candidate=$((candidate + 1))
  done

  printf '%s' "$candidate"
}

BACKEND_PORT_REQUESTED="${SGFE_PORT:-8080}"
SGFE_PORT="$(resolve_available_port "$BACKEND_PORT_REQUESTED")"

FRONTEND_PORT_REQUESTED="$FRONTEND_PORT"
FRONTEND_PORT="$(resolve_available_port "$FRONTEND_PORT")"

export SGFE_DB_URL="${SGFE_DB_URL:-jdbc:mysql://localhost:3306/sgfe?useUnicode=true&characterEncoding=utf8&serverTimezone=UTC}"
export SGFE_DB_USERNAME="${SGFE_DB_USERNAME:-sgfe_user}"
export SGFE_DB_PASSWORD="${SGFE_DB_PASSWORD:-sgfe_pass}"
export SGFE_JWT_SECRET="${SGFE_JWT_SECRET:-change-this-development-secret-change-this-development-secret}"
export SGFE_ACCESS_TOKEN_MINUTES="${SGFE_ACCESS_TOKEN_MINUTES:-15}"
export SGFE_REFRESH_TOKEN_DAYS="${SGFE_REFRESH_TOKEN_DAYS:-7}"
export SGFE_CORS_ORIGINS="${SGFE_CORS_ORIGINS:-$(build_cors_origins "$FRONTEND_PORT" "$FRONTEND_HOST")}"
export SGFE_PORT
export NEXT_PUBLIC_API_BASE_URL="${NEXT_PUBLIC_API_BASE_URL:-http://localhost:${SGFE_PORT}/api}"

cleanup() {
  jobs -pr | xargs -r kill
}

trap cleanup EXIT INT TERM

if [[ "$FRONTEND_PORT" != "$FRONTEND_PORT_REQUESTED" ]]; then
  echo "Porta $FRONTEND_PORT_REQUESTED ocupada para o frontend; usando $FRONTEND_PORT."
fi

if [[ "$SGFE_PORT" != "$BACKEND_PORT_REQUESTED" ]]; then
  echo "Porta $BACKEND_PORT_REQUESTED ocupada para o backend; usando $SGFE_PORT."
fi

echo "SGFE backend:  http://localhost:${SGFE_PORT}"
echo "SGFE frontend: http://${FRONTEND_HOST}:${FRONTEND_PORT}"

(
  cd "$ROOT_DIR/backend"
  mvn spring-boot:run
) &

(
  cd "$ROOT_DIR/frontend"
  npm run dev -- --hostname "$FRONTEND_HOST" --port "$FRONTEND_PORT"
) &

wait -n
