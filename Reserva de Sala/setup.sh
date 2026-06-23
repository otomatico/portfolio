#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
FRONTEND_DIR="$ROOT_DIR/src/frontend"

REQUIRED_PHP="8.0"
REQUIRED_NODE="18"

info()  { printf "\033[1;34m[INFO]\033[0m %s\n" "$*"; }
ok()    { printf "\033[1;32m[OK]\033[0m   %s\n" "$*"; }
warn()  { printf "\033[1;33m[WARN]\033[0m %s\n" "$*"; }
err()   { printf "\033[1;31m[ERR]\033[0m  %s\n" "$*"; }

ver_ge() {
    [ "$(printf '%s\n' "$1" "$2" | sort -V | head -n1)" = "$2" ]
}

detect_pkg_manager() {
    if command -v apt &>/dev/null; then
        echo "apt"
    elif command -v dnf &>/dev/null; then
        echo "dnf"
    elif command -v pacman &>/dev/null; then
        echo "pacman"
    elif command -v brew &>/dev/null; then
        echo "brew"
    else
        echo ""
    fi
}

check_php() {
    if ! command -v php &>/dev/null; then
        return 1
    fi
    local version
    version=$(php -r 'echo PHP_VERSION;')
    if ver_ge "$version" "$REQUIRED_PHP"; then
        return 0
    fi
    return 1
}

check_php_ext() {
    php -m | grep -qi "$1"
}

check_node() {
    if ! command -v node &>/dev/null; then
        return 1
    fi
    local version
    version=$(node --version | sed 's/^v//')
    if ver_ge "$version" "$REQUIRED_NODE"; then
        return 0
    fi
    return 1
}

check_npm() {
    command -v npm &>/dev/null
}

check_sqlite3() {
    command -v sqlite3 &>/dev/null
}

install_php_apt() {
    info "Instalando PHP $REQUIRED_PHP+ vía apt..."
    sudo apt update
    sudo apt install -y php php-cli php-sqlite3 php-json
}

install_php_dnf() {
    info "Instalando PHP $REQUIRED_PHP+ vía dnf..."
    sudo dnf install -y php php-cli php-pdo php-sqlite3 php-json
}

install_php_pacman() {
    info "Instalando PHP $REQUIRED_PHP+ vía pacman..."
    sudo pacman -S --noconfirm php php-sqlite
}

install_php_brew() {
    info "Instalando PHP $REQUIRED_PHP+ vía brew..."
    brew install php
}

install_node_apt() {
    info "Instalando Node.js $REQUIRED_NODE+ vía apt..."
    if ! command -v curl &>/dev/null; then
        sudo apt install -y curl
    fi
    curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
    sudo apt install -y nodejs
}

install_node_dnf() {
    info "Instalando Node.js $REQUIRED_NODE+ vía dnf..."
    if ! command -v curl &>/dev/null; then
        sudo dnf install -y curl
    fi
    curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo -E bash -
    sudo dnf install -y nodejs
}

install_node_pacman() {
    info "Instalando Node.js $REQUIRED_NODE+ vía pacman..."
    sudo pacman -S --noconfirm nodejs npm
}

install_node_brew() {
    info "Instalando Node.js $REQUIRED_NODE+ vía brew..."
    brew install node
}

install_sqlite_apt() {
    sudo apt install -y sqlite3
}

install_sqlite_dnf() {
    sudo dnf install -y sqlite
}

install_sqlite_pacman() {
    sudo pacman -S --noconfirm sqlite
}

install_sqlite_brew() {
    brew install sqlite
}

echo "========================================"
echo "  Instalación — Salas de Formación"
echo "========================================"
echo ""

PKG_MANAGER=$(detect_pkg_manager)
if [ -z "$PKG_MANAGER" ]; then
    err "No se pudo detectar un gestor de paquetes compatible."
    err "Este script soporta: apt, dnf, pacman, brew."
    exit 1
fi
info "Gestor detectado: $PKG_MANAGER"
echo ""

# ── PHP ──
info "Verificando PHP..."
if check_php; then
    php_ver=$(php -r 'echo PHP_VERSION;')
    ok "PHP $php_ver encontrado"
else
    warn "PHP $REQUIRED_PHP+ no encontrado. Instalando..."
    "install_php_$PKG_MANAGER"
fi

if ! check_php_ext "pdo_sqlite"; then
    warn "Extensión PHP pdo_sqlite no encontrada. Instalando..."
    "install_php_$PKG_MANAGER"
fi
ok "Extensión PHP pdo_sqlite OK"

if ! check_php_ext "json"; then
    warn "Extensión PHP json no encontrada. Instalando..."
    "install_php_$PKG_MANAGER"
fi
ok "Extensión PHP json OK"
echo ""

# ── SQLite ──
info "Verificando SQLite3..."
if check_sqlite3; then
    ok "SQLite3 $(sqlite3 --version) encontrado"
else
    warn "SQLite3 no encontrado. Instalando..."
    "install_sqlite_$PKG_MANAGER"
fi
echo ""

# ── Node.js ──
info "Verificando Node.js..."
if check_node; then
    node_ver=$(node --version | sed 's/^v//')
    ok "Node.js v$node_ver encontrado"
else
    warn "Node.js $REQUIRED_NODE+ no encontrado. Instalando..."
    "install_node_$PKG_MANAGER"
fi

if check_npm; then
    npm_ver=$(npm --version)
    ok "npm v$npm_ver encontrado"
else
    warn "npm no encontrado. Instalando Node.js (incluye npm)..."
    "install_node_$PKG_MANAGER"
fi
echo ""

# ── Frontend ──
info "Instalando dependencias del frontend..."
if [ -f "$FRONTEND_DIR/package.json" ]; then
    (cd "$FRONTEND_DIR" && npm install)
    ok "Dependencias del frontend instaladas"
else
    warn "No se encontró $FRONTEND_DIR/package.json"
fi
echo ""

echo "========================================"
ok "  Instalación completada"
echo ""
echo "  Para iniciar el backend:"
echo "    cd src/backend && php -S localhost:5000 -t public/"
echo ""
echo "  Para iniciar el frontend:"
echo "    cd src/frontend && npm run dev"
echo "========================================"
