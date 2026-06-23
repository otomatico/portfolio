# Project Specification — Sistema de Gestión de Salas de Formación

> **Versión:** 2.0  
> **Estado:** Aprobado  
> **Fecha:** 2026-06-09

---

## 1. Resumen Ejecutivo

Sistema web para la gestión de reservas de salas de formación en un call center con múltiples sucursales. Permite a los coordinadores reservar salas evitando conflictos de horario, y visualizar los recursos (proyector, pizarra, etc.) disponibles en cada sala. Los administradores tienen visión completa del sistema.

---

## 2. Problema a Resolver

Actualmente las reservas de salas de formación se gestionan de forma manual, generando:
- Conflictos de horario (dobles reservas)
- Desconocimiento de los recursos disponibles en cada sala
- Falta de visibilidad centralizada entre sucursales

---

## 3. Usuarios del Sistema

| Perfil | Descripción | Alcance |
|---|---|---|
| **Administrador** | Visión completa del sistema | CRUD global de todos los elementos |
| **Coordinador** | Usuario operativo por sucursal | Reservar salas, consultar disponibilidad |

---

## 4. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| **Backend** | PHP 8.x |
| **Frontend** | Svelte (SPA) |
| **Base de Datos** | SQLite |
| **Autenticación** | JWT (JSON Web Tokens) |

---

## 5. Arquitectura del Sistema

### Backend — MVC + Repository Pattern

| Capa | Responsabilidad |
|---|---|
| **Controllers** | Recibir peticiones HTTP, delegar en Services, devolver respuestas |
| **Services** | Resolver casos de uso, orquestar lógica de negocio |
| **Models** | Entidades del dominio de la aplicación |
| **Repositories** | Acceso a base de datos (patrón Repository) |
| **Middleware** | Validación de JWT, autorización por roles |

### Frontend — SPA con AppShell Layout

- Layout tipo **App Shell** con sidebar navegable
- Cada elemento del menú del sidebar corresponde a un CRUD del sistema
- Comunicación con backend mediante API REST
- Almacenamiento de JWT en memoria/localStorage

### Componentes Transversales del Backend

| Componente | Ubicación | Responsabilidad |
|---|---|---|
| **MigrationManager** | `src/backend/Database/MigrationManager.php` | Ejecuta migraciones SQL al arrancar: crea BD si no existe, ejecuta archivos .sql pendientes, registra resultados y errores |
| **Logger** | `src/backend/Log/Logger.php` | Sistema de logging con niveles (debug/info/warning/error), escribe en `logs/app.log` y `logs/error.log` |
| **PermissionMiddleware** | `src/backend/Middleware/PermissionMiddleware.php` | Verifica permisos CRUD (GET/POST/PUT/DELETE) por rol y componente del sistema |

### Sistema de Datos Maestros (Master Data)

Se implementa mediante dos tablas que permiten gestionar elementos estáticos pero configurables sin tocar código:

| Tabla | Propósito | Ejemplos |
|---|---|---|
| `maestros` | Grupos de datos maestros | `user_role`, `reserva_estado`, `tipo_recurso` |
| `opciones_maestro` | Valores concretos de cada grupo | `admin`, `coordinador`, `confirmada`, `cancelada` |

### Sistema de Permisos (Basado en Componentes)

Matriz de permisos que asocia a cada **rol** y **componente** del sistema (Sucursal, Sala, Reserva, etc.) los permisos específicos sobre GET (lectura), POST (creación), PUT (actualización), DELETE (eliminación).

- **Backend:** `PermissionMiddleware` intercepta cada petición y verifica contra la tabla `permisos`
- **Frontend:** El sidebar del AppShell se renderiza dinámicamente según los permisos del rol autenticado

---

## 6. Estructura de Directorios

```
/
├── src/
│   ├── backend/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   ├── Middleware/
│   │   ├── Database/
│   │   │   └── MigrationManager.php
│   │   ├── Log/
│   │   │   └── Logger.php
│   │   ├── Config/
│   │   ├── Routes/
│   │   └── public/
│   │       └── index.php
│   └── frontend/
│       ├── src/
│       │   ├── lib/
│       │   │   ├── components/
│       │   │   ├── layouts/
│       │   │   └── api/
│       │   ├── routes/
│       │   ├── stores/
│       │   └── App.svelte
│       └── static/
├── tests/
├── features/
├── docs/
├── database/
│   ├── migrations/
│   │   ├── 001_create_sucursales.sql
│   │   ├── 002_create_salas.sql
│   │   ├── 003_create_recursos.sql
│   │   ├── 004_create_sala_recursos.sql
│   │   ├── 005_create_usuarios.sql
│   │   ├── 006_create_reservas.sql
│   │   ├── 007_create_maestros.sql
│   │   ├── 008_create_opciones_maestro.sql
│   │   ├── 009_create_permisos.sql
│   │   └── 010_seed_master_data.sql
│   ├── seeds/
│   │   └── seed.sql
│   └── database.sqlite
├── logs/
│   ├── app.log
│   └── error.log
└── project-spec.md
```

> **Nota:** Todo el código del proyecto (excepto tests) está dentro de `src/`, dividido entre `backend/` y `frontend/`.

---

## 7. Entidades del Dominio

### 7.1 Maestro (Master Group)
- `codigo` (string, PK) — ej: `user_role`, `reserva_estado`, `tipo_recurso`
- `nombre` (string)
- `created_at`, `updated_at`

### 7.2 OpcionMaestro (Master Option)
- `id` (int, PK)
- `maestro_codigo` (string, FK → Maestro.codigo)
- `codigo` (string) — ej: `admin`, `coordinador`, `confirmada`
- `nombre` (string)
- `orden` (int, default 0)
- `activo` (bool, default true)
- `created_at`, `updated_at`

### 7.3 Permiso (Permission)
- `id` (int, PK)
- `rol` (string) — ej: `admin`, `coordinador`
- `componente` (string) — ej: `sucursales`, `salas`, `reservas`, `recursos`, `usuarios`, `maestros`, `permisos`
- `permiso_lectura` (bool) — GET
- `permiso_creacion` (bool) — POST
- `permiso_actualizacion` (bool) — PUT
- `permiso_eliminacion` (bool) — DELETE
- `created_at`, `updated_at`

### 7.4 Sucursal (Branch)
- `id` (int, PK)
- `nombre` (string)
- `direccion` (string)
- `created_at`, `updated_at`

### 7.5 Sala (Room)
- `id` (int, PK)
- `nombre` (string)
- `aforo` (int)
- `descripcion` (text, nullable)
- `sucursal_id` (int, FK → Sucursal)
- `created_at`, `updated_at`

### 7.6 Recurso (Resource)
- `id` (int, PK)
- `nombre` (string) — ej: "Proyector", "Pizarra", "TV", "Equipo de audio"
- `descripcion` (text, nullable)
- `created_at`, `updated_at`

### 7.7 Sala_Recurso (Room_Resource) — Tabla pivote
- `sala_id` (int, FK → Sala)
- `recurso_id` (int, FK → Recurso)
- `cantidad` (int, default 1)

### 7.8 Usuario (User)
- `id` (int, PK)
- `nombre` (string)
- `email` (string, unique)
- `password` (string, hasheada)
- `rol` (enum: admin, coordinador)
- `sucursal_id` (int, FK → Sucursal, nullable para admins globales)
- `created_at`, `updated_at`

### 7.9 Reserva (Reservation)
- `id` (int, PK)
- `sala_id` (int, FK → Sala)
- `usuario_id` (int, FK → Usuario)
- `fecha_inicio` (datetime) — fecha y hora de inicio
- `fecha_fin` (datetime) — fecha y hora de fin
- `estado` (enum: pendiente, confirmada, cancelada)
- `created_at`, `updated_at`

---

## 8. Funcionalidades Principales (CRUDs)

| Módulo | Admin | Coordinador |
|---|---|---|
| **Sucursales** | CRUD completo | Solo lectura |
| **Salas** | CRUD completo | Solo lectura |
| **Recursos** | CRUD completo | Solo lectura |
| **Asignación Sala-Recurso** | CRUD completo | Solo lectura |
| **Usuarios** | CRUD completo | No accede |
| **Reservas** | CRUD completo + visión global | CRUD propio (solo sus reservas) |
| **Maestros** | CRUD completo | No accede |
| **Permisos** | Lectura + actualización | No accede |

### Flujo de Reserva
1. Coordinador inicia sesión (JWT)
2. Selecciona una sala disponible
3. Visualiza los recursos de la sala
4. Selecciona fecha y hora
5. Confirma reserva
6. Sistema valida disponibilidad (sin conflicto)
7. Reserva queda registrada

---

## 9. Reglas de Negocio

1. **Disponibilidad:** No puede haber dos reservas que solapen fechas/horas para una misma sala.
2. **Asignación de salas:** Una sala pertenece a una única sucursal.
3. **Recursos por sala:** Una sala puede tener múltiples recursos; un recurso puede estar en múltiples salas.
4. **Roles:** El administrador ve y gestiona todo. El coordinador solo gestiona sus propias reservas y ve información de su sucursal.
5. **Autenticación:** Todas las rutas (excepto login) requieren JWT válido.
6. **Cancelación:** Solo se pueden cancelar reservas futuras (no pasadas).

---

## 10. Decisiones Técnicas Confirmadas

| Decisión | Opción Elegida |
|---|---|
| Stack backend | PHP 8 |
| Stack frontend | Svelte |
| Base de datos | SQLite |
| Arquitectura backend | MVC + Repository Pattern |
| Arquitectura frontend | SPA con AppShell |
| Autenticación | JWT |
| Layout frontend | App Shell con sidebar |
| Ubicación del código | `src/backend/` y `src/frontend/` |
| Sistema de migraciones | MigrationManager — automático al arrancar `index.php` |
| Sistema de logging | Logger — archivos `logs/app.log` y `logs/error.log` |
| Asignación Sala↔Sucursal | FK `sucursal_id` en Sala, dropdown en frontend, filtro por `?sucursal_id=` en API |
| BD de Testing | Independiente: `tests/backend/test_db.sqlite` — aislada de la BD de desarrollo |
| Datos Maestros | Tablas `opciones_maestro` + `opciones_maestro_valores` para datos configurables |
| Sistema de Permisos | Matriz por rol+componente (GET/POST/PUT/DELETE), verificada por `PermissionMiddleware` |

---

## 11. Próximos Pasos (Pipeline)

1. ✅ **Fase 0 — Intake:** Completado
2. ✅ **Fase 1 — Discovery:** Completado (este documento)
3. ✅ **Fase 2 — Especificación Funcional:** Completado — 8 features Gherkin generados (96 escenarios)
4. ✅ **Fase 3 — Implementación:** Completado — 74 archivos generados (42 backend PHP + 32 frontend Svelte)
5. ✅ **Fase 4 — Testing:** Completado — 28 archivos de test (~225 tests, 100% cobertura de IDs)
6. ✅ **Fase 5 — Revisión:** **APROBADO** — 8 issues corregidos en rework, 100% trazabilidad features ↔ src ↔ tests
