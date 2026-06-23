@FIX-005
Feature: Navegación SPA en el Sidebar — uso de use:link
  Como usuario autenticado del sistema
  Quiero que los enlaces del sidebar naveguen sin recargar la página
  Para tener una experiencia de navegación fluida y preservar el estado de la aplicación

  Scenario: El sidebar navega sin recargar la página al hacer clic en un enlace
    Given un usuario autenticado visualizando el dashboard
    And el sidebar muestra los enlaces de navegación del menú
    When el usuario hace clic en "Salas" en el sidebar
    Then la URL cambia a "/salas" sin que el navegador recargue la página
    And el contenido de la aplicación se actualiza mostrando la página de Salas

  Scenario: Navegación a la página de Salas desde el sidebar
    Given un usuario autenticado visualizando el dashboard
    When el usuario hace clic en "Salas" en el sidebar
    Then la ruta activa cambia a "/salas"
    And el componente SalasPage se renderiza en el área de contenido principal

  Scenario: Navegación al Dashboard desde el sidebar
    Given un usuario autenticado visualizando la página de Salas
    When el usuario hace clic en "Dashboard" en el sidebar
    Then la ruta activa cambia a "/"
    And el componente DashboardPage se renderiza en el área de contenido principal

  Scenario: El estado de autenticación se preserva durante la navegación SPA
    Given un usuario autenticado visualizando el dashboard
    And el store de autenticación contiene los datos del usuario y el JWT
    When el usuario navega a "/salas" mediante el sidebar
    Then el store de autenticación mantiene el usuario y token en memoria
    And el sidebar continúa mostrando los enlaces correspondientes al rol del usuario
    And el sistema no redirige a "/login"
