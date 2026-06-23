@salas @modulo-salas
Feature: Gestión de Salas de Formación
  Como administrador o coordinador
  Quiero gestionar y consultar las salas de formación con sus recursos asociados
  Para organizar las reservas en cada sucursal

  Background:
    Given el sistema tiene una sucursal "Sucursal Centro"
    And el sistema tiene una sucursal "Sucursal Norte"
    And el sistema tiene un recurso "Proyector"
    And el sistema tiene un recurso "Pizarra"
    And el sistema tiene una sala "Sala A" con aforo 20, perteneciente a "Sucursal Centro"
    And el sistema tiene una sala "Sala B" con aforo 15, perteneciente a "Sucursal Norte"

  @admin @F-SAL-001
  Scenario: Administrador crea una sala asociada a una sucursal
    Given un administrador autenticado
    When el administrador crea una sala con nombre "Sala C", aforo 25, descripción "Planta baja" y sucursal "Sucursal Centro"
    Then el sistema registra la sala exitosamente
    And la sala aparece asociada a "Sucursal Centro" en el listado

  @admin @F-SAL-002
  Scenario: Administrador lista todas las salas
    Given un administrador autenticado
    When el administrador solicita el listado de salas
    Then el sistema devuelve todas las salas con su sucursal y recursos asociados

  @admin @coordinador @F-SAL-003
  Scenario: Filtrar salas por sucursal
    Given un administrador autenticado
    When el usuario solicita las salas filtrando por sucursal "Sucursal Centro"
    Then el sistema devuelve solo las salas que pertenecen a "Sucursal Centro"

  @admin @coordinador @F-SAL-004
  Scenario: Ver detalle de una sala con sus recursos
    Given un administrador autenticado
    When el usuario solicita el detalle de la sala "Sala A"
    Then el sistema devuelve los datos de la sala incluyendo nombre, aforo, descripción, sucursal y recursos asignados

  @admin @F-SAL-005
  Scenario: Administrador edita una sala
    Given un administrador autenticado
    And la sala "Sala A" existe en el sistema
    When el administrador edita la sala "Sala A" cambiando el aforo a 30
    Then el sistema actualiza los datos de la sala exitosamente

  @admin @F-SAL-006
  Scenario: Administrador elimina una sala
    Given un administrador autenticado
    And la sala "Sala B" existe en el sistema
    When el administrador elimina la sala "Sala B"
    Then el sistema elimina la sala exitosamente
    And la sala ya no aparece en el listado

  @admin @F-SAL-007
  Scenario: Administrador asigna un recurso a una sala
    Given un administrador autenticado
    And la sala "Sala A" no tiene el recurso "Proyector" asignado
    When el administrador asigna el recurso "Proyector" con cantidad 1 a la sala "Sala A"
    Then el sistema registra la asignación exitosamente
    And el recurso "Proyector" aparece en los recursos de la sala "Sala A"

  @admin @F-SAL-008
  Scenario: Administrador desasigna un recurso de una sala
    Given un administrador autenticado
    And la sala "Sala A" tiene el recurso "Proyector" asignado
    When el administrador desasigna el recurso "Proyector" de la sala "Sala A"
    Then el sistema elimina la asignación exitosamente
    And el recurso "Proyector" ya no aparece en los recursos de la sala "Sala A"

  @admin @F-SAL-009
  Scenario: Administrador intenta crear sala sin especificar sucursal
    Given un administrador autenticado
    When el administrador intenta crear una sala con nombre "Sala X" sin asociar a una sucursal
    Then el sistema rechaza la operación
    And el sistema muestra un error indicando que la sucursal es obligatoria

  @admin @F-SAL-010
  Scenario: Administrador intenta crear sala sin nombre
    Given un administrador autenticado
    When el administrador intenta crear una sala sin nombre
    Then el sistema rechaza la operación
    And el sistema muestra un error indicando que el nombre es obligatorio

  @coordinador @F-SAL-011
  Scenario: Coordinador lista salas con sucursal y recursos
    Given un coordinador autenticado
    When el coordinador solicita el listado de salas
    Then el sistema devuelve todas las salas con sucursal y recursos asociados

  @coordinador @F-SAL-012
  Scenario: Coordinador ve detalle de una sala
    Given un coordinador autenticado
    When el coordinador solicita el detalle de la sala "Sala A"
    Then el sistema devuelve los datos de la sala incluyendo sus recursos

  @coordinador @F-SAL-013
  Scenario: Coordinador no puede crear una sala
    Given un coordinador autenticado
    When el coordinador intenta crear una nueva sala
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @coordinador @F-SAL-014
  Scenario: Coordinador no puede editar una sala
    Given un coordinador autenticado
    When el coordinador intenta editar la sala "Sala A"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @coordinador @F-SAL-015
  Scenario: Coordinador no puede eliminar una sala
    Given un coordinador autenticado
    When el coordinador intenta eliminar la sala "Sala A"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"
