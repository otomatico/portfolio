@recursos @modulo-recursos
Feature: Gestión de Recursos
  Como administrador o coordinador
  Quiero gestionar y consultar los recursos físicos disponibles
  Para asignarlos a las salas de formación

  Background:
    Given el sistema tiene un recurso "Proyector" con descripción "Proyector HD 1080p"
    And el sistema tiene un recurso "Pizarra" con descripción "Pizarra blanca 2x1m"
    And el sistema tiene un recurso "TV" con descripción "TV 55 pulgadas"

  @admin @F-REC-001
  Scenario: Administrador crea un nuevo recurso
    Given un administrador autenticado
    When el administrador crea un recurso con nombre "Equipo de Audio" y descripción "Sistema de sonido profesional"
    Then el sistema registra el recurso exitosamente
    And el nuevo recurso aparece en el listado de recursos

  @admin @F-REC-002
  Scenario: Administrador lista todos los recursos
    Given un administrador autenticado
    When el administrador solicita el listado de recursos
    Then el sistema devuelve todos los recursos disponibles

  @admin @F-REC-003
  Scenario: Administrador ve detalle de un recurso
    Given un administrador autenticado
    When el administrador solicita el detalle del recurso "Proyector"
    Then el sistema devuelve los datos completos del recurso

  @admin @F-REC-004
  Scenario: Administrador edita un recurso existente
    Given un administrador autenticado
    When el administrador edita el recurso "Proyector" cambiando la descripción a "Proyector 4K"
    Then el sistema actualiza el recurso exitosamente

  @admin @F-REC-005
  Scenario: Administrador elimina un recurso
    Given un administrador autenticado
    When el administrador elimina el recurso "TV"
    Then el sistema elimina el recurso exitosamente
    And el recurso ya no aparece en el listado

  @coordinador @F-REC-006
  Scenario: Coordinador lista todos los recursos
    Given un coordinador autenticado
    When el coordinador solicita el listado de recursos
    Then el sistema devuelve todos los recursos disponibles

  @coordinador @F-REC-007
  Scenario: Coordinador ve detalle de un recurso
    Given un coordinador autenticado
    When el coordinador solicita el detalle del recurso "Proyector"
    Then el sistema devuelve los datos completos del recurso

  @coordinador @F-REC-008
  Scenario: Coordinador no puede crear un recurso
    Given un coordinador autenticado
    When el coordinador intenta crear un nuevo recurso
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @coordinador @F-REC-009
  Scenario: Coordinador no puede editar un recurso
    Given un coordinador autenticado
    When el coordinador intenta editar el recurso "Proyector"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @coordinador @F-REC-010
  Scenario: Coordinador no puede eliminar un recurso
    Given un coordinador autenticado
    When el coordinador intenta eliminar el recurso "Proyector"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"
