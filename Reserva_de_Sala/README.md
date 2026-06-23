# Sistema de Gestión de Salas de Formación

Aplicación web para la gestión de reservas de salas de formación en un call center con múltiples sucursales.

## Stack Tecnológico

- **Backend:** PHP 8.x — MVC + Repository Pattern
- **Frontend:** Svelte (SPA) — AppShell Layout
- **Base de Datos:** SQLite
- **Autenticación:** JWT

## Estructura del Proyecto

```
/
├── src/               # Código fuente (backend + frontend)
│   ├── backend/       # API REST en PHP 8
│   └── frontend/      # SPA en Svelte
├── tests/             # Tests automatizados
├── features/          # Especificaciones Gherkin
├── docs/              # Documentación del sistema
├── database/          # Migraciones y seeds
└── project-spec.md    # Especificación del proyecto
```

## Documentación

| Archivo | Descripción |
|---|---|
| [project-spec.md](./project-spec.md) | Especificación completa del proyecto |
| [docs/SPEC.md](./docs/SPEC.md) | Especificación funcional detallada |
| [docs/ARCHITECTURE.md](./docs/ARCHITECTURE.md) | Arquitectura del sistema |
| [docs/DOMAIN.md](./docs/DOMAIN.md) | Modelo de dominio y entidades |
| [docs/AGENTS.md](./docs/AGENTS.md) | Estructura de agentes del pipeline |
| [folder_structure.md](./folder_structure.md) | Estructura de directorios completa |

## Usuarios del Sistema

- **Administrador:** Visión completa, CRUD global de todos los elementos
- **Coordinador:** Reserva salas, consulta disponibilidad y recursos

## Funcionalidades Principales

- CRUD de Sucursales
- CRUD de Salas (con recursos y aforo)
- CRUD de Recursos (proyector, pizarra, etc.)
- Asignación de recursos a salas
- CRUD de Usuarios (solo admin)
- Gestión de Reservas con validación de disponibilidad

## Quick Start

### Requisitos Previos

- PHP 8.0+

- Node.js 18+ y npm
- SQLite3 (incluido con PHP)

### Backend (API PHP)

```bash
# Iniciar servidor de desarrollo (desde la raíz del proyecto)
cd src/backend && php -S localhost:5000 -t public/
```

La API estará disponible en `http://localhost:5000/api`.
Las migraciones se ejecutan automáticamente al arrancar; crean y configuran la base de datos `database/database.sqlite`.

### Frontend (SPA Svelte)

```bash
# 1. Instalar dependencias
cd src/frontend
npm install

# 2. Iniciar servidor de desarrollo
npm run dev
```

El frontend estará disponible en `http://localhost:5173` (por defecto).

> **Nota:** Asegúrate de que el backend esté corriendo para que el frontend pueda consumir la API.

### Credenciales por Defecto

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | `admin@example.com` | `Password123` |
| Coordinador | `coord@example.com` | `Password123` |

### Ejecutar Tests

```bash
# Tests del backend (PHPUnit)
cd src/backend
vendor/bin/phpunit

# Tests del frontend (Vitest)
cd src/frontend
npm run test
```

## Licencia

Proyecto interno — Call Center
