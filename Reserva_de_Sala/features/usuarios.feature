@usuarios @modulo-usuarios
Feature: Gestión de Usuarios
  Como administrador
  Quiero gestionar los usuarios del sistema
  Para controlar el acceso y asignar roles y sucursales

  Background:
    Given el sistema tiene una sucursal "Sucursal Centro"
    Given el sistema tiene una sucursal "Sucursal Norte"
    And el sistema tiene un usuario administrador con email "admin@example.com"
    And el sistema tiene un usuario coordinador con email "coord1@example.com" asociado a "Sucursal Centro"

  @admin @F-USU-001
  Scenario: Administrador lista todos los usuarios
    Given un administrador autenticado
    When el administrador solicita el listado de usuarios
    Then el sistema devuelve todos los usuarios del sistema incluyendo su rol y sucursal

  @admin @F-USU-002
  Scenario: Administrador ve detalle de un usuario
    Given un administrador autenticado
    When el administrador solicita el detalle del usuario con email "coord1@example.com"
    Then el sistema devuelve los datos completos del usuario incluyendo nombre, email, rol y sucursal

  @admin @F-USU-003
  Scenario: Administrador crea un nuevo administrador
    Given un administrador autenticado
    When el administrador crea un usuario con nombre "Admin 2", email "admin2@example.com", contraseña "Password123" y rol "admin" sin sucursal asociada
    Then el sistema registra el usuario exitosamente
    And el nuevo usuario tiene rol "admin"
    And el nuevo usuario no tiene sucursal asociada

  @admin @F-USU-004
  Scenario: Administrador crea un nuevo coordinador con sucursal asociada
    Given un administrador autenticado
    When el administrador crea un usuario con nombre "Coord 2", email "coord2@example.com", contraseña "Password123", rol "coordinador" y sucursal "Sucursal Norte"
    Then el sistema registra el usuario exitosamente
    And el nuevo usuario tiene rol "coordinador"
    And el nuevo usuario está asociado a "Sucursal Norte"

  @admin @F-USU-005
  Scenario: Administrador intenta crear coordinador sin sucursal
    Given un administrador autenticado
    When el administrador intenta crear un usuario con rol "coordinador" sin especificar sucursal
    Then el sistema rechaza la operación
    And el sistema muestra un error indicando que el coordinador debe estar asociado a una sucursal

  @admin @F-USU-006
  Scenario: Administrador intenta crear usuario con email duplicado
    Given un administrador autenticado
    When el administrador intenta crear un usuario con email "admin@example.com"
    Then el sistema rechaza la operación
    And el sistema muestra un error indicando que el email ya está registrado

  @admin @F-USU-007
  Scenario: Administrador edita un usuario existente
    Given un administrador autenticado
    When el administrador edita el usuario con email "coord1@example.com" cambiando el nombre a "Coordinador Nuevo"
    Then el sistema actualiza los datos del usuario exitosamente

  @admin @F-USU-008
  Scenario: Administrador elimina un usuario
    Given un administrador autenticado
    When el administrador elimina el usuario con email "coord1@example.com"
    Then el sistema elimina el usuario exitosamente
    And el usuario ya no aparece en el listado

  @coordinador @F-USU-009
  Scenario: Coordinador no puede acceder al módulo de usuarios
    Given un coordinador autenticado
    When el coordinador intenta acceder al módulo "usuarios"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"
