#!/usr/bin/env bash
set -e

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
BACKEND_DIR="$ROOT_DIR/backend"
FRONTEND_DIR="$ROOT_DIR/frontend"
MODE="${1:-both}"

cleanup() {
    echo ""
    echo "Deteniendo servicios..."
    [ -n "$BACKEND_PID" ] && kill "$BACKEND_PID" 2>/dev/null
    [ -n "$FRONTEND_PID" ] && kill "$FRONTEND_PID" 2>/dev/null
    wait 2>/dev/null
    echo "Servicios detenidos."
}

trap cleanup EXIT INT TERM

case "$MODE" in
    backend)
        echo "Iniciando backend (http://localhost:8080)..."
        (cd "$BACKEND_DIR" && dotnet run) &
        BACKEND_PID=$!
        ;;
    frontend)
        echo "Iniciando frontend (http://localhost:5173)..."
        (cd "$FRONTEND_DIR" && npm run dev) &
        FRONTEND_PID=$!
        ;;
    both)
        echo "Iniciando backend (http://localhost:8080)..."
        (cd "$BACKEND_DIR" && dotnet run) &
        BACKEND_PID=$!

        sleep 2

        echo "Iniciando frontend (http://localhost:5173)..."
        (cd "$FRONTEND_DIR" && npm run dev) &
        FRONTEND_PID=$!
        ;;
    *)
        echo "Uso: $0 [backend|frontend|both]"
        exit 1
        ;;
esac

echo ""
[ -n "$BACKEND_PID" ] && echo "Backend:  http://localhost:8080"
[ -n "$FRONTEND_PID" ] && echo "Frontend: http://localhost:5173"
echo "Presiona Ctrl+C para detener."
echo ""

wait
