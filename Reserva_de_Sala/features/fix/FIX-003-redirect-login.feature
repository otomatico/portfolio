@FIX-003
Feature: Redirección a /login cuando el usuario no está autenticado
  Como usuario no autenticado
  Quiero ser redirigido automáticamente a /login
  Para que no pueda ver contenido protegido del dashboard sin haber iniciado sesión

  Scenario: Usuario no autenticado intenta acceder a ruta raíz
    Given el usuario no tiene sesión iniciada (JWT ausente o inválido)
    When navega a "/"
    Then el sistema redirige automáticamente a "/login"
    And el dashboard no se renderiza

  Scenario: Usuario no autenticado intenta acceder a cualquier ruta protegida
    Given el usuario no tiene sesión iniciada (JWT ausente o inválido)
    When navega a "/salas"
    Then el sistema redirige automáticamente a "/login"

  Scenario: Usuario autenticado intenta acceder a /login
    Given el usuario tiene sesión iniciada (JWT válido en localStorage)
    When navega a "/login"
    Then el sistema redirige automáticamente a "/"
    And se renderiza el dashboard

  Scenario: Usuario autenticado y en ruta protegida permanece en ella
    Given el usuario tiene sesión iniciada (JWT válido en localStorage)
    And está en "/"
    When el sistema verifica la autenticación
    Then no se produce ninguna redirección
    And el dashboard se renderiza con el AppShell completo
