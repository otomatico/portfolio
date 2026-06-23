@sucursales @modulo-sucursales
Feature: Gestión de Sucursales
  Como administrador o coordinador
  Quiero gestionar y consultar las sucursales del call center
  Para organizar las salas de formación por ubicación

  Background:
    Given el sistema tiene una sucursal registrada con nombre "Sucursal Centro" y dirección "Av. Principal 123"
    And el sistema tiene una sucursal registrada con nombre "Sucursal Norte" y dirección "Calle Secundaria 456"

  @admin @F-SUC-001
  Scenario: Administrador crea una nueva sucursal exitosamente
    Given un administrador autenticado
    When el administrador crea una sucursal con nombre "Sucursal Sur" y dirección "Av. del Sur 789"
    Then el sistema registra la sucursal exitosamente
    And la nueva sucursal aparece en el listado de sucursales

  @admin @F-SUC-002
  Scenario: Administrador lista todas las sucursales
    Given un administrador autenticado
    When el administrador solicita el listado de sucursales
    Then el sistema devuelve todas las sucursales registradas

  @admin @F-SUC-003
  Scenario: Administrador ve detalle de una sucursal
    Given un administrador autenticado
    When el administrador solicita el detalle de la sucursal "Sucursal Centro"
    Then el sistema devuelve los datos completos de la sucursal incluyendo nombre y dirección

  @admin @F-SUC-004
  Scenario: Administrador edita una sucursal existente
    Given un administrador autenticado
    When el administrador edita la sucursal "Sucursal Centro" cambiando el nombre a "Sucursal Centro Renovada"
    Then el sistema actualiza los datos de la sucursal exitosamente
    And los cambios se reflejan en el listado de sucursales

  @admin @F-SUC-005
  Scenario: Administrador elimina una sucursal
    Given un administrador autenticado
    When el administrador elimina la sucursal "Sucursal Norte"
    Then el sistema elimina la sucursal exitosamente
    And la sucursal ya no aparece en el listado

  @admin @F-SUC-006
  Scenario: Administrador intenta crear sucursal sin especificar nombre
    Given un administrador autenticado
    When el administrador intenta crear una sucursal sin nombre
    Then el sistema rechaza la operación
    And el sistema muestra un error indicando que el nombre es obligatorio

  @coordinador @F-SUC-007
  Scenario: Coordinador lista todas las sucursales
    Given un coordinador autenticado
    When el coordinador solicita el listado de sucursales
    Then el sistema devuelve todas las sucursales registradas

  @coordinador @F-SUC-008
  Scenario: Coordinador ve detalle de una sucursal
    Given un coordinador autenticado
    When el coordinador solicita el detalle de la sucursal "Sucursal Centro"
    Then el sistema devuelve los datos completos de la sucursal

  @coordinador @F-SUC-009
  Scenario: Coordinador no puede crear una sucursal
    Given un coordinador autenticado
    When el coordinador intenta crear una nueva sucursal
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @coordinador @F-SUC-010
  Scenario: Coordinador no puede editar una sucursal
    Given un coordinador autenticado
    When el coordinador intenta editar la sucursal "Sucursal Centro"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @coordinador @F-SUC-011
  Scenario: Coordinador no puede eliminar una sucursal
    Given un coordinador autenticado
    When el coordinador intenta eliminar la sucursal "Sucursal Centro"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"
