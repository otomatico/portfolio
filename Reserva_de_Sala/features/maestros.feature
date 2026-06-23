@maestros @modulo-maestros
Feature: Gestión de Datos Maestros (Maestros y Opciones)
  Como administrador
  Quiero gestionar los grupos de datos maestros y sus opciones
  Para configurar valores estáticos del sistema sin modificar código

  Background:
    Given el sistema tiene un grupo maestro "user_role" con nombre "Roles de Usuario"
    Given el sistema tiene un grupo maestro "reserva_estado" con nombre "Estados de Reserva"
    And el grupo "user_role" tiene opciones: "admin" ("Administrador"), "coordinador" ("Coordinador")
    And el grupo "reserva_estado" tiene opciones: "confirmada" ("Confirmada"), "cancelada" ("Cancelada")

  # ── CRUD Grupos Maestros ──

  @admin @F-MAE-001
  Scenario: Administrador lista todos los grupos maestros
    Given un administrador autenticado
    When el administrador solicita el listado de grupos maestros
    Then el sistema devuelve todos los grupos registrados

  @admin @F-MAE-002
  Scenario: Administrador crea un nuevo grupo maestro
    Given un administrador autenticado
    When el administrador crea un grupo maestro con código "tipo_recurso" y nombre "Tipos de Recurso"
    Then el sistema registra el grupo exitosamente
    And el nuevo grupo aparece en el listado de maestros

  @admin @F-MAE-003
  Scenario: Administrador edita un grupo maestro existente
    Given un administrador autenticado
    When el administrador edita el grupo "user_role" cambiando el nombre a "Roles del Sistema"
    Then el sistema actualiza el grupo exitosamente

  @admin @F-MAE-004
  Scenario: Administrador elimina un grupo maestro sin opciones asociadas
    Given un administrador autenticado
    And el sistema tiene un grupo maestro "canal_formacion" sin opciones asociadas
    When el administrador elimina el grupo "canal_formacion"
    Then el sistema elimina el grupo exitosamente
    And el grupo ya no aparece en el listado

  @admin @F-MAE-005
  Scenario: Administrador no puede eliminar un grupo maestro con opciones asociadas
    Given un administrador autenticado
    When el administrador intenta eliminar el grupo "user_role" que tiene opciones asociadas
    Then el sistema rechaza la operación
    And el sistema muestra un error "No se puede eliminar un grupo que tiene opciones asociadas"

  @admin @F-MAE-006
  Scenario: Administrador intenta crear grupo con código duplicado
    Given un administrador autenticado
    When el administrador intenta crear un grupo maestro con código "user_role"
    Then el sistema rechaza la operación
    And el sistema muestra un error indicando que el código ya existe

  # ── CRUD Opciones ──

  @admin @F-MAE-007
  Scenario: Administrador lista opciones de un grupo maestro
    Given un administrador autenticado
    When el administrador solicita las opciones del grupo "user_role"
    Then el sistema devuelve todas las opciones del grupo

  @admin @F-MAE-008
  Scenario: Administrador crea una nueva opción en un grupo maestro
    Given un administrador autenticado
    When el administrador crea una opción en el grupo "user_role" con código "supervisor", nombre "Supervisor", orden 3 y activo true
    Then el sistema registra la opción exitosamente
    And la nueva opción aparece en el listado de opciones del grupo "user_role"

  @admin @F-MAE-009
  Scenario: Administrador edita una opción existente
    Given un administrador autenticado
    When el administrador edita la opción "coordinador" del grupo "user_role" cambiando el nombre a "Coordinador de Sucursal"
    Then el sistema actualiza la opción exitosamente

  @admin @F-MAE-010
  Scenario: Administrador elimina una opción de un grupo maestro
    Given un administrador autenticado
    When el administrador elimina la opción "cancelada" del grupo "reserva_estado"
    Then el sistema elimina la opción exitosamente
    And la opción ya no aparece en el listado

  # ── Opciones desactivadas (RN-13) ──

  @admin @F-MAE-011
  Scenario: Opción desactivada no se muestra en dropdowns del sistema
    Given un administrador autenticado
    And la opción "cancelada" del grupo "reserva_estado" está desactivada (activo=false)
    When el sistema carga las opciones del grupo "reserva_estado" para un dropdown
    Then la opción "cancelada" no aparece en el resultado
    And la opción "confirmada" sí aparece en el resultado

  @admin @F-MAE-012
  Scenario: Opción desactivada sigue siendo válida en registros existentes
    Given un administrador autenticado
    And la opción "cancelada" del grupo "reserva_estado" está desactivada (activo=false)
    And existe una reserva con estado "cancelada"
    When el administrador consulta la reserva
    Then la reserva muestra correctamente el estado "cancelada"

  # ── Coordinador no accede ──

  @coordinador @F-MAE-013
  Scenario: Coordinador no puede acceder al módulo de maestros
    Given un coordinador autenticado
    When el coordinador intenta acceder al módulo "maestros"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"
