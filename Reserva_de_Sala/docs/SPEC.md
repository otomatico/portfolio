# Especificación Funcional — Sistema de Gestión de Salas de Formación

> **Versión:** 1.1  
> **Basado en:** project-spec.md  
> **Destinado a:** Functional Analyst, Developer, Testing Engineer

---

## 1. Actores del Sistema

| Actor | Descripción |
|---|---|
| **Administrador** | Usuario con rol `admin`. Visión completa del sistema. Gestiona sucursales, salas, recursos, usuarios, reservas, datos maestros y permisos. |
| **Coordinador** | Usuario con rol `coordinador`. Asignado a una sucursal. Crea y gestiona sus propias reservas. Visualiza catálogo de salas y recursos. |
| **Visitante** | Usuario no autenticado. Solo puede acceder a la página de login. |

---

## 2. Requisitos Funcionales

### Módulo Autenticación (RF-01)

| ID | Requisito | Actor |
|---|---|---|
| RF-01.1 | Iniciar sesión con email y contraseña | Todos |
| RF-01.2 | Recibir token JWT al autenticarse | Sistema |
| RF-01.3 | Cerrar sesión (invalidar token) | Todos |
| RF-01.4 | Acceder solo a rutas según rol (admin/coordinador) | Sistema |

### Módulo Sucursales (RF-02)

| ID | Requisito | Actor |
|---|---|---|
| RF-02.1 | Listar todas las sucursales | Admin, Coordinador |
| RF-02.2 | Ver detalle de una sucursal | Admin, Coordinador |
| RF-02.3 | Crear una nueva sucursal | Admin |
| RF-02.4 | Editar una sucursal existente | Admin |
| RF-02.5 | Eliminar una sucursal | Admin |

### Módulo Salas (RF-03)

| ID | Requisito | Actor |
|---|---|---|
| RF-03.1 | Listar todas las salas (con sucursal y recursos) | Admin, Coordinador |
| RF-03.2 | Ver detalle de una sala (incluyendo recursos asignados) | Admin, Coordinador |
| RF-03.3 | Filtrar salas por sucursal | Admin, Coordinador |
| RF-03.4 | Crear una nueva sala asociada a una sucursal | Admin |
| RF-03.5 | Editar una sala (nombre, aforo, descripción) | Admin |
| RF-03.6 | Eliminar una sala | Admin |

### Módulo Recursos (RF-04)

| ID | Requisito | Actor |
|---|---|---|
| RF-04.1 | Listar todos los recursos disponibles | Admin, Coordinador |
| RF-04.2 | Ver detalle de un recurso | Admin, Coordinador |
| RF-04.3 | Crear un nuevo recurso (ej: proyector, pizarra) | Admin |
| RF-04.4 | Editar un recurso | Admin |
| RF-04.5 | Eliminar un recurso | Admin |
| RF-04.6 | Asignar recurso a una sala con cantidad | Admin |
| RF-04.7 | Desasignar recurso de una sala | Admin |

### Módulo Usuarios (RF-05)

| ID | Requisito | Actor |
|---|---|---|
| RF-05.1 | Listar todos los usuarios del sistema | Admin |
| RF-05.2 | Ver detalle de un usuario | Admin |
| RF-05.3 | Crear usuario (email, contraseña, rol, sucursal) | Admin |
| RF-05.4 | Editar datos de un usuario | Admin |
| RF-05.5 | Eliminar un usuario | Admin |
| RF-05.6 | Un coordinador debe estar asociado a una sucursal | Sistema |

### Módulo Reservas (RF-06)

| ID | Requisito | Actor |
|---|---|---|
| RF-06.1 | Listar reservas (admin: todas; coordinador: propias) | Admin, Coordinador |
| RF-06.2 | Ver detalle de una reserva | Admin, Coordinador |
| RF-06.3 | Crear una reserva seleccionando sala, fecha y hora | Coordinador, Admin |
| RF-06.4 | Validar que no exista conflicto de horario en la misma sala | Sistema |
| RF-06.5 | Cancelar una reserva futura | Coordinador (propias), Admin (cualquiera) |
| RF-06.6 | Filtrar reservas por sala, sucursal, fecha, estado | Admin |
| RF-06.7 | Visualizar los recursos de la sala antes de reservar | Coordinador, Admin |
| RF-06.8 | No permitir reservar en el pasado | Sistema |
| RF-06.9 | Validar solapamiento por fecha+hora: una sala puede reservarse varias veces al día pero nunca en horarios superpuestos | Sistema |

### Módulo Maestros y Opciones (RF-07)

| ID | Requisito | Actor |
|---|---|---|
| RF-07.1 | Listar todos los grupos maestros | Admin |
| RF-07.2 | Crear un nuevo grupo maestro (código + nombre) | Admin |
| RF-07.3 | Editar un grupo maestro | Admin |
| RF-07.4 | Eliminar un grupo maestro (solo si no tiene opciones asociadas) | Admin |
| RF-07.5 | Listar opciones de un grupo maestro | Admin |
| RF-07.6 | Crear una opción dentro de un grupo (código, nombre, orden, activo) | Admin |
| RF-07.7 | Editar una opción | Admin |
| RF-07.8 | Eliminar una opción | Admin |
| RF-07.9 | Las opciones desactivadas (activo=false) no se muestran en dropdowns | Sistema |

### Módulo Permisos (RF-08)

| ID | Requisito | Actor |
|---|---|---|
| RF-08.1 | Visualizar la matriz completa de permisos (rol × componente × CRUD) | Admin |
| RF-08.2 | Filtrar permisos por rol | Admin |
| RF-08.3 | Actualizar permisos de un rol sobre un componente específico | Admin |
| RF-08.4 | El middleware debe denegar (403) si el rol no tiene permiso para la operación | Sistema |
| RF-08.5 | El sidebar del frontend debe ocultar opciones no permitidas | Sistema |
| RF-08.6 | Registrar en log los accesos denegados por falta de permiso | Sistema |

---

## 3. Reglas de Negocio

| ID | Regla |
|---|---|
| RN-01 | Una sala pertenece exactamente a una sucursal |
| RN-02 | Un recurso puede estar asignado a múltiples salas |
| RN-03 | Una sala puede tener múltiples recursos |
| RN-04 | El aforo de una sala es un número entero positivo |
| RN-05 | No puede haber dos reservas con fecha+hora solapadas para la misma sala (validación datetime completo: `nueva.fecha_inicio < existente.fecha_fin AND nueva.fecha_fin > existente.fecha_inicio`) |
| RN-06 | El coordinador solo puede crear reservas para sí mismo |
| RN-07 | Un coordinador debe estar asociado a una sucursal al crearlo |
| RN-08 | Solo se pueden cancelar reservas con fecha futura |
| RN-09 | El administrador puede gestionar cualquier entidad del sistema |
| RN-10 | El coordinador solo ve las sucursales y salas (no las gestiona) |
| RN-11 | Los códigos de grupo de opciones maestro deben ser únicos en el sistema |
| RN-12 | El par (grupo, código) en valores de opciones debe ser único |
| RN-13 | La combinación (rol, componente) en permisos es única |
| RN-14 | Si no existe un permiso para un (rol, componente), se deniega el acceso por defecto |
| RN-15 | El administrador tiene permiso_lectura=1 en todos los componentes por defecto |

---

## 4. Casos de Uso Principales

### CU-01: Iniciar Sesión
1. El usuario ingresa email y contraseña
2. El sistema valida las credenciales
3. El sistema genera un token JWT con rol y datos del usuario
4. El sistema redirige al dashboard según el rol

### CU-02: Reservar Sala
1. El coordinador selecciona una sala del listado
2. El sistema muestra los recursos disponibles de la sala
3. El coordinador selecciona fecha y hora de inicio/fin
4. El sistema valida disponibilidad (sin conflictos)
5. El sistema crea la reserva con estado "confirmada"
6. El sistema notifica al usuario que la reserva fue exitosa

### CU-03: Gestionar Salas (Admin)
1. El administrador accede al módulo de salas
2. El sistema muestra listado con sucursal y recursos
3. El administrador puede crear, editar o eliminar salas
4. Al crear/editar, puede asignar recursos con cantidades

### CU-04: Gestionar Datos Maestros (Admin)
1. El administrador accede al módulo de "Opciones Maestro"
2. El sistema muestra los grupos existentes (Roles, Estados, etc.)
3. El administrador selecciona un grupo y gestiona sus valores
4. Los cambios afectan inmediatamente a los dropdowns del sistema

### CU-05: Configurar Permisos (Admin)
1. El administrador accede al módulo de "Permisos"
2. El sistema muestra una matriz: filas = componentes, columnas = roles × (GET, POST, PUT, DELETE)
3. El administrador activa/desactiva permisos específicos
4. Los cambios son efectivos inmediatamente (próxima request del usuario afectado)

---

## 5. Criterios de Aceptación Generales

- El sistema debe responder en menos de 2 segundos para operaciones CRUD estándar
- La validación de disponibilidad debe ejecutarse correctamente ante reservas simultáneas
- Los tokens JWT deben expirar después de 8 horas
- Las contraseñas deben almacenarse hasheadas (bcrypt)
- La interfaz debe adaptarse a los permisos del rol autenticado
