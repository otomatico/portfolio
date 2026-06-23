@permisos @modulo-permisos
Feature: Gestión de Permisos por Rol y Componente
  Como administrador
  Quiero gestionar la matriz de permisos del sistema
  Para controlar qué operaciones CRUD puede realizar cada rol sobre cada componente

  Background:
    Given el sistema tiene roles configurados: "admin" y "coordinador"
    Given el sistema tiene componentes: "sucursales", "salas", "recursos", "reservas", "usuarios", "maestros", "permisos"
    And el sistema tiene permisos por defecto para admin y coordinador según la matriz establecida
    And el sistema tiene un administrador con email "admin@example.com"
    And el sistema tiene un coordinador con email "coord1@example.com"

  # ── Visualizar matriz ──

  @admin @F-PER-001
  Scenario: Administrador visualiza la matriz completa de permisos
    Given un administrador autenticado
    When el administrador solicita la matriz de permisos
    Then el sistema devuelve todos los permisos organizados por rol y componente
    And cada permiso muestra los valores de lectura, creación, actualización y eliminación

  @admin @F-PER-002
  Scenario: Administrador visualiza permisos filtrados por un rol específico
    Given un administrador autenticado
    When el administrador solicita los permisos del rol "coordinador"
    Then el sistema devuelve solo los permisos correspondientes al rol "coordinador"

  # ── Actualizar permisos ──

  @admin @F-PER-003
  Scenario: Administrador actualiza un permiso de un rol sobre un componente
    Given un administrador autenticado
    And el rol "coordinador" actualmente no tiene permiso de creación sobre el componente "salas"
    When el administrador actualiza el permiso del rol "coordinador" sobre el componente "salas" estableciendo permiso_creacion en true
    Then el sistema actualiza el permiso exitosamente
    And el rol "coordinador" ahora tiene permiso de creación sobre "salas"

  @admin @F-PER-004
  Scenario: Administrador actualiza múltiples permisos de un rol
    Given un administrador autenticado
    When el administrador actualiza el permiso del rol "coordinador" sobre el componente "reservas" estableciendo permiso_lectura=true, permiso_creacion=true, permiso_actualizacion=false, permiso_eliminacion=true
    Then el sistema actualiza los permisos exitosamente

  # ── Middleware deniega acceso (RN-15) ──

  @auth @F-PER-005
  Scenario: Middleware deniega acceso cuando no existe permiso para el rol y componente
    Given el sistema no tiene un permiso registrado para el rol "coordinador" sobre el componente "maestros"
    And un coordinador autenticado con email "coord1@example.com"
    When el coordinador intenta acceder al componente "maestros"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @auth @F-PER-006
  Scenario: Middleware deniega acceso a operación no permitida aunque exista permiso de lectura
    Given el rol "coordinador" tiene permiso_lectura=true pero permiso_eliminacion=false sobre el componente "reservas"
    And un coordinador autenticado con email "coord1@example.com"
    When el coordinador intenta eliminar una reserva que no es de su propiedad mediante DELETE
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @admin @F-PER-007
  Scenario: Administrador siempre tiene permiso de lectura en todos los componentes
    Given un administrador autenticado
    When el administrador solicita sus permisos
    Then el sistema muestra que el rol "admin" tiene permiso_lectura=true en todos los componentes

  @auth @F-PER-008
  Scenario: Acceso denegado queda registrado en el log del sistema
    Given un coordinador autenticado con email "coord1@example.com"
    When el coordinador intenta acceder al componente "usuarios" sin permiso
    Then el sistema registra un evento de advertencia en el log indicando "Acceso denegado" con el rol, componente y método

  # ── Coordinador no accede ──

  @coordinador @F-PER-009
  Scenario: Coordinador no puede acceder al módulo de permisos
    Given un coordinador autenticado
    When el coordinador intenta acceder al módulo "permisos"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  # ── Sidebar reactivo ──

  @admin @F-PER-010
  Scenario: Frontend oculta opciones del sidebar según permisos del rol
    Given un administrador autenticado
    When el sistema carga el menú de navegación
    Then el sidebar muestra todas las opciones del sistema

  @coordinador @F-PER-011
  Scenario: Frontend oculta opciones no permitidas para coordinador
    Given un coordinador autenticado
    When el sistema carga el menú de navegación
    Then el sidebar no muestra las opciones "Usuarios", "Maestros" ni "Permisos"
