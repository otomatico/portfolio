# Estructura de Directorios — Sistema de Gestión de Salas de Formación

> **Versión:** 2.0  
> **Nota:** Todo el código del proyecto (excepto tests) está dentro de `src/`.

---

## Árbol Completo

```
/
├── project-spec.md                   # Especificación del proyecto
├── README.md                         # Documento principal
├── opencode.json                     # Configuración de OpenCode
│
├── src/                              # CÓDIGO FUENTE (todo aquí)
│   │
│   ├── backend/                      # PHP 8 API REST
│   │   ├── public/
│   │   │   └── index.php             # Front Controller / Entry point
│   │   ├── Config/
│   │   │   ├── Database.php          # Conexión SQLite (PDO)
│   │   │   └── app.php               # Config. general (CORS, JWT secret, etc.)
│   │   ├── Routes/
│   │   │   └── api.php               # Definición de rutas
│   │   ├── Middleware/
│   │   │   ├── JwtMiddleware.php         # Validación y parseo de JWT
│   │   │   ├── PermissionMiddleware.php  # Verifica permisos CRUD por rol+componente
│   │   │   └── CorsMiddleware.php        # Cabeceras CORS
│   │   ├── Controllers/
│   │   │   ├── AuthController.php    # Login, logout, me
│   │   │   ├── SucursalController.php
│   │   │   ├── SalaController.php
│   │   │   ├── RecursoController.php
│   │   │   ├── ReservaController.php
│   │   │   ├── UsuarioController.php
│   │   │   ├── MaestroController.php    # CRUD grupos de datos maestros
│   │   │   └── PermisoController.php    # Gestión de matriz de permisos
│   │   ├── Services/
│   │   │   ├── AuthService.php       # Login, verificación, generación JWT
│   │   │   ├── SucursalService.php   # CRUD sucursales
│   │   │   ├── SalaService.php       # CRUD salas + asignación recursos + asignación sucursal
│   │   │   ├── RecursoService.php    # CRUD recursos
│   │   │   ├── ReservaService.php    # CRUD reservas + validación disponibilidad
│   │   │   ├── UsuarioService.php    # CRUD usuarios
│   │   │   ├── MaestroService.php    # CRUD maestros + opciones
│   │   │   └── PermisoService.php    # Consulta y actualización de permisos
│   │   ├── Models/
│   │   │   ├── Sucursal.php          # Entidad Sucursal
│   │   │   ├── Sala.php              # Entidad Sala (FK → Sucursal)
│   │   │   ├── Recurso.php           # Entidad Recurso
│   │   │   ├── SalaRecurso.php       # Entidad pivote Sala-Recurso
│   │   │   ├── Reserva.php           # Entidad Reserva
│   │   │   ├── Usuario.php           # Entidad Usuario
│   │   │   ├── Maestro.php           # Entidad grupo de datos maestros (PK = codigo)
│   │   │   ├── OpcionMaestro.php     # Entidad valor de dato maestro (FK = maestro_codigo)
│   │   │   └── Permiso.php           # Entidad permiso CRUD
│   │   ├── Repositories/
│   │   │   ├── SucursalRepository.php
│   │   │   ├── SalaRepository.php
│   │   │   ├── RecursoRepository.php
│   │   │   ├── ReservaRepository.php
│   │   │   ├── UsuarioRepository.php
│   │   │   ├── MaestroRepository.php
│   │   │   └── PermisoRepository.php
│   │   ├── Database/
│   │   │   └── MigrationManager.php  # Gestor de migraciones (auto-ejecución al arrancar)
│   │   └── Log/
│   │       └── Logger.php            # Sistema de logging (app.log + error.log)
│   │
│   └── frontend/                     # Svelte SPA
│       ├── package.json
│       ├── vite.config.js
│       ├── svelte.config.js
│       ├── static/
│       │   ├── favicon.ico
│       │   └── global.css
│       └── src/
│           ├── main.js               # Punto de entrada Svelte
│           ├── App.svelte            # Componente raíz + router
│           ├── lib/
│           │   ├── api/
│           │   │   ├── auth.js       # Llamadas a /api/auth/*
│           │   │   ├── sucursales.js # Llamadas a /api/sucursales/*
│           │   │   ├── salas.js      # Llamadas a /api/salas/*
│           │   │   ├── recursos.js   # Llamadas a /api/recursos/*
│           │   │   ├── reservas.js   # Llamadas a /api/reservas/*
│           │   │   └── usuarios.js   # Llamadas a /api/usuarios/*
│           │   ├── components/       # Componentes reutilizables
│           │   │   ├── DataTable.svelte     # Tabla genérica con sort/filtros
│           │   │   ├── FormField.svelte     # Campo de formulario
│           │   │   ├── ConfirmModal.svelte  # Modal de confirmación
│           │   │   ├── Pagination.svelte    # Paginación
│           │   │   └── LoadingSpinner.svelte
│           │   └── layouts/
│           │       ├── AppShell.svelte      # Layout principal (sidebar + header + content)
│           │       ├── Sidebar.svelte       # Menú de navegación lateral
│           │       └── Header.svelte        # Barra superior (usuario, logout)
│           ├── routes/               # Páginas / vistas (cada una es un CRUD)
│           │   ├── LoginPage.svelte
│           │   ├── DashboardPage.svelte
│           │   ├── SucursalesPage.svelte
│           │   ├── SalasPage.svelte
│           │   ├── RecursosPage.svelte
│           │   ├── ReservasPage.svelte
│           │   ├── UsuariosPage.svelte
│           │   ├── MaestrosPage.svelte     # CRUD maestros + opciones
│           │   └── PermisosPage.svelte     # Matriz de permisos por rol
│           └── stores/               # Stores Svelte reactivas
│               ├── auth.js           # Token JWT, usuario actual, rol
│               ├── permisos.js       # Permisos del usuario autenticado (carga sidebar)
│               └── ui.js             # Estado global (sidebar abierta, loading, etc.)
│
├── tests/                            # Tests automatizados
│   ├── backend/
│   │   ├── Unit/
│   │   │   ├── Services/
│   │   │   └── Models/
│   │   ├── Integration/
│   │   │   ├── Controllers/
│   │   │   └── Repositories/
│   │   └── test_db.sqlite             # BD separada para testing (aislada de desarrollo)
│   └── frontend/
│       └── components/
│
├── features/                         # Especificaciones Gherkin
│   ├── auth.feature
│   ├── sucursales.feature
│   ├── salas.feature
│   ├── recursos.feature
│   ├── usuarios.feature
│   └── reservas.feature
│
├── docs/                             # Documentación del sistema
│   ├── SPEC.md                       # Especificación funcional
│   ├── ARCHITECTURE.md               # Arquitectura del sistema
│   ├── DOMAIN.md                     # Modelo de dominio
│   └── AGENTS.md                     # Agentes del pipeline
│
├── database/                         # Archivos de base de datos
│   ├── migrations/                   # Migraciones SQL (ejecutadas por MigrationManager)
│   │   ├── 001_create_sucursales.sql
│   │   ├── 002_create_salas.sql
│   │   ├── 003_create_recursos.sql
│   │   ├── 004_create_sala_recursos.sql
│   │   ├── 005_create_usuarios.sql
│   │   ├── 006_create_reservas.sql
│   │   ├── 007_create_maestros.sql
│   │   ├── 008_create_opciones_maestro.sql
│   │   ├── 009_create_permisos.sql
│   │   └── 010_seed_master_data.sql           # Datos iniciales (roles, estados, permisos base)
│   ├── seeds/                        # Datos de ejemplo
│   │   └── seed.sql
│   └── database.sqlite               # Archivo de BD (se crea automáticamente si no existe)
│
├── logs/                             # Archivos de log de la aplicación
│   ├── app.log                       # Log general (todos los niveles)
│   └── error.log                     # Solo errores
│
├── .opencode/                        # Configuración de agentes OpenCode
│   ├── agents/
│   │   ├── orchestrator.md
│   │   ├── documentation-architect.md
│   │   ├── functional.md
│   │   ├── developer.md
│   │   ├── testing.md
│   │   └── reviewer.md
│   └── node_modules/
│
├── .gitignore
└── folder_structure.md               # Este archivo
```
