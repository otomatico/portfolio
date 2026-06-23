# AGENTS.md — Contabilidad

## Stack
- **Backend**: Lenguaje del lado servidor, base de datos relacional, autenticación JWT, sin framework.
- **Frontend**: Framework UI declarativo, herramienta de construcción con proxy de desarrollo, SPA en JS vanilla.
- **No hay tests automatizados, ni linter, ni typechecker.**

## Arquitectura

- **Backend entry point**: archivo único que arranca el servidor embebido de desarrollo.
- **Router**: mapea `METHOD /api/{path}` a funciones manejadoras. Soporta parámetros `{id}` en ruta. CORS habilitado para desarrollo.
- **Auth**: JWT manual (HS256). Endpoints: `POST /api/auth/login`, `GET /api/auth/me`. Middleware de autenticación protege todas las rutas excepto login.
- **Database**: archivo único en `backend/database/` (se crea automáticamente al iniciar). Schema en archivo SQL separado. Admin por defecto: `admin`/`admin`.
- **Frontend**: servidor de desarrollo con proxy inverso: `/api` → `http://localhost:8080`.
- **Frontend estructura**: `views/` (páginas como Login, Dashboard), `components/` (reutilizables como DataTable, Modal), `lib/` (cliente API, stores de estado).

## API endpoints

Todas requieren `Authorization: Bearer <token>` excepto login.

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/auth/login` | Login, devuelve JWT |
| GET | `/api/auth/me` | Datos del usuario actual |
| GET | `/api/fondo` | Listar cuentas |
| GET | `/api/fondo/{id}` | Una cuenta |
| POST | `/api/fondo` | Crear cuenta |
| PUT | `/api/fondo/{id}` | Actualizar cuenta |
| DELETE | `/api/fondo/{id}` | Eliminar cuenta |
| GET | `/api/asiento` | Todos los asientos |
| GET | `/api/asiento/{id}` | Un asiento |
| GET | `/api/asiento/query/{acountId}/{dateFrom}/{dateTo}` | Filtrar asientos |
| POST | `/api/asiento` | Crear asiento |
| PUT | `/api/asiento/{id}` | Actualizar asiento |
| DELETE | `/api/asiento/{id}` | Eliminar asiento |
| GET | `/api/balance` | Balance (solo Ingreso/Gasto) |
| GET | `/api/tipofondo` | Listar tipos |
| POST | `/api/tipofondo` | Crear tipo |
| DELETE | `/api/tipofondo/{id}` | Eliminar tipo |
| GET | `/api/user` | Listar usuarios |
| POST | `/api/user` | Crear usuario |
| PUT | `/api/user/{id}` | Cambiar password / habilitar |
| PUT | `/api/user/{id}/workspaces` | Asignar workspaces |
| GET | `/api/user/{id}/workspaces` | Workspaces del usuario |
| GET | `/api/workspace` | Listar workspaces |
| POST | `/api/workspace` | Crear workspace |
| PUT | `/api/workspace/{id}` | Renombrar workspace |
| DELETE | `/api/workspace/{id}` | Eliminar workspace |
| GET | `/api/workspace/{id}/users` | Usuarios del workspace |

## Comandos para desarrollo

```bash
# Arrancar backend (servidor embebido en :8080)
cd backend && dotnet run

# Arrancar frontend (servidor de desarrollo con proxy en :5173)
cd frontend && npm run dev

# O ambos a la vez
./dev.sh
```

## Database
- Base de datos relacional, archivo único.
- Tablas: `users`, `fondo`, `asiento`, `tipofondo`, `workspace`, `user_workspace`.
- Prepared statements para todas las consultas.

## Multi-workspace
- Workspace activo se pasa como `?workspace_id=X` en toda llamada API.
- Tipos de fondo (Ingreso/Gasto/Cliente) son globales, no por workspace.
- La tabla `user_workspace` relaciona usuarios con workspaces (N:M).
- No se permite eliminar el workspace "Principal" (id=1).

## Key quirks
- Secreto JWT en archivo de configuración (cambiar en producción).
- Cuando el filtro de cuenta es 0, el SQL se construye dinámicamente para evitar falsos negativos por comparación de tipos en la base de datos (`0 = '0'` es falso en SQLite y otros motores).
