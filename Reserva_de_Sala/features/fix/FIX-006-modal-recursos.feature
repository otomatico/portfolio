@FIX-006
Feature: Actualización y orden del modal de recursos de sala
  Como administrador o coordinador
  Quiero que el modal de recursos refleje los cambios inmediatamente y tenga el orden correcto
  Para gestionar los recursos asignados a una sala de forma eficiente y sin confusiones

  Background:
    Given el sistema tiene una sucursal "Sucursal Centro"
    And el sistema tiene una sala "Sala A" con aforo 20, perteneciente a "Sucursal Centro"
    And el sistema tiene un recurso "Proyector"
    And el sistema tiene un recurso "Pizarra"
    And el sistema tiene un recurso "TV"
    And la sala "Sala A" tiene el recurso "Proyector" asignado con cantidad 1
    And la sala "Sala A" tiene el recurso "Pizarra" asignado con cantidad 2

  @F-FIX006-001
  Scenario: La lista de recursos del modal se actualiza tras desasignar sin cerrar el modal
    Given un administrador autenticado visualizando el listado de salas
    And el modal de recursos de la sala "Sala A" está abierto mostrando "Proyector" y "Pizarra"
    When el administrador hace clic en el botón ✕ del recurso "Proyector"
    Then el modal permanece abierto
    And el recurso "Proyector" desaparece de la lista de recursos asignados
    And la lista del modal muestra únicamente "Pizarra"

  @F-FIX006-002
  Scenario: El formulario de asignación aparece antes que la lista de recursos asignados en el modal
    Given un administrador autenticado
    When el administrador abre el modal de recursos de la sala "Sala A"
    Then el primer elemento del modal es el formulario para asignar un nuevo recurso
    And después del formulario aparece la lista de recursos ya asignados
    And la lista muestra "Proyector" y "Pizarra"

  @F-FIX006-003
  Scenario: Administrador asigna y desasigna recursos desde el modal
    Given un administrador autenticado
    And el modal de recursos de la sala "Sala A" está abierto
    When el administrador asigna el recurso "TV" con cantidad 1 a la sala "Sala A" desde el modal
    Then el sistema registra la asignación exitosamente
    And el recurso "TV" aparece en la lista de recursos asignados del modal
    When el administrador desasigna el recurso "TV" de la sala "Sala A" desde el modal
    Then el sistema elimina la asignación exitosamente
    And el recurso "TV" desaparece de la lista de recursos asignados del modal
    And el modal permanece abierto mostrando solo "Proyector" y "Pizarra"

  @F-FIX006-004
  Scenario: Coordinador puede ver la lista de recursos pero no asignar ni desasignar
    Given un coordinador autenticado
    When el coordinador abre el modal de recursos de la sala "Sala A"
    Then el modal muestra la lista de recursos asignados con "Proyector" y "Pizarra"
    And el modal no muestra el formulario para asignar un nuevo recurso
    And cada recurso en la lista no tiene botón ✕ para desasignar
