# Contabilidad

Aplicación de contabilidad de doble entrada (partida doble). Backend .NET + Frontend Vue 3 con PrimeVue.

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | .NET 8+, ASP.NET Core, Entity Framework Core + SQLite, JWT auth |
| Frontend | Vue 3 + Vite + PrimeVue, Pinia (state management) |
| Servidor dev | `dotnet run` + `vite dev` (proxy `/api` → backend) |

## Requisitos

- .NET 8 SDK
- Node.js 18+
- Git (opcional)

## Inicio rápido

```bash
# 1. Arrancar backend (puerto 8080)
cd backend && dotnet run

# 2. Arrancar frontend (puerto 5173, proxy /api → :8080)
cd frontend && npm install && npm run dev
```

Abrir `http://localhost:5173`. Usuario por defecto: `admin` / `admin`.

## Estructura

```
├── backend/
│   ├── Controllers/            # AuthController, FondoController, AsientoController,
│   │                           # BalanceController, UserController, WorkspaceController,
│   │                           # TipofondoController
│   ├── Models/                 # User, Fondo, Asiento, TipoFondo, Workspace
│   ├── Data/
│   │   ├── AppDbContext.cs     # DbContext con DbSets y configuración de entidades
│   │   ├── Migrations/         # Migraciones de Entity Framework
│   │   └── app.db              # Base de datos SQLite (se crea automáticamente)
│   ├── Middleware/
│   │   └── AuthMiddleware.cs   # Validación JWT + workspace access check
│   ├── Services/               # JwtService, RateLimiterService, ValidatorService
│   ├── Program.cs              # Entry point: builder, servicios, middleware pipeline
│   ├── appsettings.json        # JWT Secret, ConnectionStrings, configuración
│   └── backend.csproj          # Proyecto .NET
├── frontend/
│   ├── src/
│   │   ├── views/              # Páginas: LoginView, DashboardView, AccountsView,
│   │   │                       # JournalView, BalanceView, UsersView, WorkspaceManagerView
│   │   ├── components/         # Reutilizables: Navbar, FormAsiento, FormCuenta
│   │   ├── composables/        # useApi.js (cliente HTTP), useAuth.js
│   │   ├── stores/             # Pinia stores: userStore, fondosStore, asientosStore,
│   │   │                       # tiposFondoStore, workspaceStore, uiStore
│   │   ├── router/             # Vue Router (login, dashboard, accounts, journal, etc.)
│   │   ├── App.vue             # Root component con layout y router-view
│   │   └── main.js             # Punto de entrada: createApp, PrimeVue, Pinia, Router
│   ├── public/
│   │   └── index.html
│   └── package.json
├── tasks_dotnet.json            # Plan de implementación
```

## API endpoints

Todas requieren `Authorization: Bearer <token>` excepto login.

### Auth
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/auth/login` | Login, devuelve JWT + user + workspaces |
| GET | `/api/auth/me` | Datos del usuario actual |

### Fondos (cuentas)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/fondo` | Listar cuentas (filtradas por workspace activo) |
| GET | `/api/fondo/{id}` | Una cuenta |
| POST | `/api/fondo` | Crear cuenta |
| PUT | `/api/fondo/{id}` | Actualizar cuenta |
| DELETE | `/api/fondo/{id}` | Eliminar cuenta |

### Asientos (transacciones)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/asiento` | Todos los asientos (con paginación opcional: `?page=1&perPage=50`) |
| GET | `/api/asiento/{id}` | Un asiento |
| GET | `/api/asiento/query/{acountId}/{dateFrom}/{dateTo}` | Filtrar asientos |
| POST | `/api/asiento` | Crear asiento |
| PUT | `/api/asiento/{id}` | Actualizar asiento |
| DELETE | `/api/asiento/{id}` | Eliminar asiento |

### Balance
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/balance` | Balance (solo cuentas Ingreso/Gasto). Filtros: `?acountId=X&dateFrom=YYYY-MM-DD&dateTo=YYYY-MM-DD` |

### Tipos de fondo
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/tipofondo` | Listar tipos (globales) |
| POST | `/api/tipofondo` | Crear tipo |
| DELETE | `/api/tipofondo/{id}` | Eliminar tipo |

### Usuarios (admin)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/user` | Listar usuarios |
| POST | `/api/user` | Crear usuario |
| PUT | `/api/user/{id}` | Cambiar contraseña / habilitar/deshabilitar |
| PUT | `/api/user/{id}/workspaces` | Asignar workspaces a un usuario |
| GET | `/api/user/{id}/workspaces` | Obtener workspaces de un usuario |

### Workspaces (admin)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/workspace` | Listar workspaces |
| POST | `/api/workspace` | Crear workspace |
| PUT | `/api/workspace/{id}` | Renombrar workspace |
| DELETE | `/api/workspace/{id}` | Eliminar workspace (no permite eliminar el Principal) |
| GET | `/api/workspace/{id}/users` | Usuarios asignados a un workspace |

## Multi-workspace

- Los usuarios pueden tener uno o varios workspaces.
- Los administradores ven todos los workspaces y pueden cambiarse entre ellos desde el sidebar.
- El workspace activo se pasa como query param `?workspace_id=X` en todas las llamadas API.
- Tipos de fondo (Ingreso, Gasto, Cliente) son globales, no por workspace.
- Al crear un usuario, se puede asignar a un workspace desde el panel de Workspaces.

## Base de datos

- SQLite, archivo único: `backend/Data/app.db`
- Entity Framework Core con `Microsoft.EntityFrameworkCore.Sqlite`.
- Las migraciones se generan con `dotnet ef migrations add` y se ejecutan automáticamente al iniciar.
- WAL mode y foreign keys activadas en `OnConfiguring` del DbContext.

### Tablas principales

- `Users` — Id, Username, PasswordHash (bcrypt), Role, Disabled, CreatedAt
- `Workspaces` — Id, Name, Description, CreatedAt
- `UserWorkspaces` — UserId, WorkspaceId, Role (N:M)
- `Fondos` — Id, Descripcion, IdTipoFondo, WorkspaceId
- `Asientos` — Id, IdOrigen, IdDestino, Descripcion, Importe, Fecha, WorkspaceId
- `TipoFondos` — Id (Gasto, Ingreso, Cliente)


## Componentes PrimeVue utilizados

| Componente original | Equivalente PrimeVue |
|---------------------|----------------------|
| Modal | `<Dialog>` |
| DataTable | `<DataTable>` |
| ConfirmDialog | `<ConfirmDialog>` |
| Formulario asiento | `<InputText>`, `<InputNumber>`, `<Calendar>`, `<Dropdown>`, `<SelectButton>` |
| Formulario cuenta | `<InputText>`, `<Dropdown>` |
| Navbar (sidebar) | `<PanelMenu>` + `<Toolbar>` |
| LoadingSpinner | `<ProgressSpinner>` |
| ErrorMessage | `<Message>` / `<Toast>` |
| Tabla balance | `<DataTable>` con `<Column>` y `<ColumnGroup>` para footers |

## Usuario por defecto

- **Usuario**: `admin`
- **Contraseña**: `admin`
- **Rol**: `admin`
- Se crea automáticamente vía migración o seed en `AppDbContext`.

## Convenciones

- Backend: controllers con atributos (`[ApiController]`, `[Route("api/[controller]")]`), inyección de dependencias, DbContext scoped.
- Frontend: Composition API con `<script setup>`, Pinia stores con `defineStore`, Vue Router con lazy loading.
- No hay tests automatizados ni CI.
- JWT secret en `appsettings.json` (cambiar en producción).

## Comandos

```bash
# Backend
cd backend && dotnet run

# Migraciones EF Core
cd backend && dotnet ef migrations add NombreMigracion
cd backend && dotnet ef database update

# Frontend (desarrollo)
cd frontend && npm run dev

# Frontend (build producción)
cd frontend && npm run build
# luego servir frontend/dist/ con cualquier servidor estático

# Ambos a la vez (si npm-run-all está instalado)
npm run dev
```