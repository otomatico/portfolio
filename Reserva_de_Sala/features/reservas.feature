@reservas @modulo-reservas
Feature: Gestión de Reservas de Salas
  Como administrador o coordinador
  Quiero gestionar las reservas de las salas de formación
  Para evitar conflictos de horario y organizar el uso de las instalaciones

  Background:
    Given el sistema tiene una sucursal "Sucursal Centro"
    Given el sistema tiene una sala "Sala A" con aforo 20, perteneciente a "Sucursal Centro"
    Given el sistema tiene una sala "Sala B" con aforo 15, perteneciente a "Sucursal Centro"
    And el sistema tiene un administrador con email "admin@example.com"
    And el sistema tiene un coordinador con email "coord1@example.com" asociado a "Sucursal Centro"
    And el sistema tiene un coordinador con email "coord2@example.com" asociado a "Sucursal Centro"

  # ── Listar reservas ──

  @admin @F-RES-001
  Scenario: Administrador lista todas las reservas del sistema
    Given un administrador autenticado
    And existen reservas creadas por diferentes usuarios
    When el administrador solicita el listado de reservas
    Then el sistema devuelve todas las reservas de todos los usuarios

  @coordinador @F-RES-002
  Scenario: Coordinador lista solo sus propias reservas
    Given un coordinador autenticado con email "coord1@example.com"
    And el usuario "coord1@example.com" tiene reservas registradas
    And el usuario "coord2@example.com" tiene reservas registradas
    When el coordinador solicita el listado de reservas
    Then el sistema devuelve solo las reservas del coordinador autenticado
    And el sistema no incluye reservas de otros coordinadores

  # ── Crear reserva ──

  @admin @F-RES-003
  Scenario: Administrador crea una reserva exitosamente
    Given un administrador autenticado
    When el administrador crea una reserva para la sala "Sala A" desde "2026-07-10 09:00" hasta "2026-07-10 11:00"
    Then el sistema registra la reserva exitosamente con estado "confirmada"

  @coordinador @F-RES-004
  Scenario: Coordinador crea una reserva exitosamente
    Given un coordinador autenticado con email "coord1@example.com"
    When el coordinador crea una reserva para la sala "Sala A" desde "2026-07-10 14:00" hasta "2026-07-10 16:00"
    Then el sistema registra la reserva exitosamente con estado "confirmada"
    And la reserva queda asociada al coordinador autenticado

  # ── Validación de solapamiento (RN-05) ──

  @admin @coordinador @F-RES-005
  Scenario: No se permite crear reserva con horario solapado en la misma sala
    Given un administrador autenticado
    And existe una reserva confirmada para la sala "Sala A" desde "2026-07-10 09:00" hasta "2026-07-10 11:00"
    When el administrador intenta crear una reserva para la sala "Sala A" desde "2026-07-10 10:00" hasta "2026-07-10 12:00"
    Then el sistema rechaza la operación
    And el sistema muestra un error "La sala no está disponible en el horario solicitado"

  @admin @coordinador @F-RES-006
  Scenario: Se permite crear reserva en horario no solapado en la misma sala
    Given un administrador autenticado
    And existe una reserva confirmada para la sala "Sala A" desde "2026-07-10 09:00" hasta "2026-07-10 11:00"
    When el administrador crea una reserva para la sala "Sala A" desde "2026-07-10 11:00" hasta "2026-07-10 13:00"
    Then el sistema registra la reserva exitosamente
    And la nueva reserva no entra en conflicto con la existente

  @admin @coordinador @F-RES-007
  Scenario: Se permite crear reserva en la misma fecha pero en sala diferente
    Given un administrador autenticado
    And existe una reserva confirmada para la sala "Sala A" desde "2026-07-10 09:00" hasta "2026-07-10 11:00"
    When el administrador crea una reserva para la sala "Sala B" desde "2026-07-10 09:00" hasta "2026-07-10 11:00"
    Then el sistema registra la reserva exitosamente
    And la nueva reserva no entra en conflicto con la existente

  # ── Cancelar reserva (RN-08) ──

  @admin @coordinador @F-RES-008
  Scenario: Cancelar una reserva futura exitosamente
    Given un administrador autenticado
    And existe una reserva confirmada para la sala "Sala A" desde "2026-08-01 09:00" hasta "2026-08-01 11:00"
    When el administrador cancela la reserva
    Then el sistema cambia el estado de la reserva a "cancelada"

  @admin @coordinador @F-RES-009
  Scenario: No se permite cancelar una reserva pasada
    Given un administrador autenticado
    And existe una reserva confirmada para la sala "Sala A" desde "2025-01-01 09:00" hasta "2025-01-01 11:00"
    When el administrador intenta cancelar la reserva
    Then el sistema rechaza la operación
    And el sistema muestra un error "Solo se pueden cancelar reservas futuras"

  @coordinador @F-RES-010
  Scenario: Coordinador no puede cancelar una reserva de otro usuario
    Given un coordinador autenticado con email "coord1@example.com"
    And existe una reserva creada por "coord2@example.com" para la sala "Sala A" desde "2026-08-01 09:00" hasta "2026-08-01 11:00"
    When el coordinador intenta cancelar la reserva de otro usuario
    Then el sistema rechaza la operación
    And el sistema muestra un error "No tienes permiso para cancelar esta reserva"

  @admin @F-RES-011
  Scenario: Administrador cancela una reserva de cualquier usuario
    Given un administrador autenticado
    And existe una reserva creada por "coord1@example.com" para la sala "Sala A" desde "2026-08-01 14:00" hasta "2026-08-01 16:00"
    When el administrador cancela la reserva
    Then el sistema cambia el estado de la reserva a "cancelada"

  # ── Filtros (solo admin) ──

  @admin @F-RES-012
  Scenario: Administrador filtra reservas por sala
    Given un administrador autenticado
    And existen reservas en "Sala A" y "Sala B"
    When el administrador solicita reservas filtrando por sala "Sala A"
    Then el sistema devuelve solo las reservas de "Sala A"

  @admin @F-RES-013
  Scenario: Administrador filtra reservas por sucursal
    Given un administrador autenticado
    And existen reservas en salas de diferentes sucursales
    When el administrador solicita reservas filtrando por sucursal "Sucursal Centro"
    Then el sistema devuelve solo las reservas de salas pertenecientes a "Sucursal Centro"

  @admin @F-RES-014
  Scenario: Administrador filtra reservas por estado
    Given un administrador autenticado
    And existen reservas en estado "confirmada" y "cancelada"
    When el administrador solicita reservas filtrando por estado "cancelada"
    Then el sistema devuelve solo las reservas en estado "cancelada"

  @admin @F-RES-015
  Scenario: Administrador filtra reservas por rango de fechas
    Given un administrador autenticado
    And existen reservas en diferentes fechas
    When el administrador solicita reservas filtrando desde "2026-07-01" hasta "2026-07-31"
    Then el sistema devuelve solo las reservas dentro del rango de fechas especificado

  # ── Disponibilidad ──

  @admin @coordinador @F-RES-016
  Scenario: Consultar disponibilidad de una sala en un rango de fechas
    Given un administrador autenticado
    And existe una reserva confirmada para la sala "Sala A" desde "2026-07-10 09:00" hasta "2026-07-10 11:00"
    When el usuario consulta la disponibilidad de la sala "Sala A" para el día "2026-07-10"
    Then el sistema muestra los horarios ocupados y disponibles de la sala

  # ── Ver detalle ──

  @admin @F-RES-017
  Scenario: Administrador ve detalle de cualquier reserva
    Given un administrador autenticado
    When el administrador solicita el detalle de una reserva existente
    Then el sistema devuelve los datos completos de la reserva incluyendo sala, usuario, fecha, hora y estado

  @coordinador @F-RES-018
  Scenario: Coordinador ve detalle de su propia reserva
    Given un coordinador autenticado con email "coord1@example.com"
    And existe una reserva creada por "coord1@example.com"
    When el coordinador solicita el detalle de su reserva
    Then el sistema devuelve los datos completos de la reserva

  @coordinador @F-RES-019
  Scenario: Coordinador no puede ver detalle de reserva de otro usuario
    Given un coordinador autenticado con email "coord1@example.com"
    And existe una reserva creada por "coord2@example.com"
    When el coordinador intenta ver el detalle de la reserva de otro usuario
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para ver esta reserva"

  @admin @coordinador @F-RES-020
  Scenario: Visualizar recursos de una sala antes de reservar
    Given un administrador autenticado
    And la sala "Sala A" tiene asignado el recurso "Proyector"
    When el usuario consulta los recursos de la sala "Sala A"
    Then el sistema muestra los recursos disponibles en la sala
