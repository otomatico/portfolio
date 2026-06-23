@FIX-004
Feature: Captura global de excepciones en el front controller
  Como desarrollador
  Quiero que el front controller capture todas las excepciones no contempladas
  Para que el backend devuelva un JSON con información de diagnóstico en lugar de una respuesta vacía

  Scenario: Controlador lanza una excepción no contemplada
    Given el backend recibe una petición a una ruta existente
    And el controlador correspondiente lanza una excepción inesperada (ej: error de PDO)
    When el front controller ejecuta el bloque del router
    Then la respuesta HTTP tiene código 500
    And el cuerpo de la respuesta es un JSON con los campos "error", "message", "file" y "line"

  Scenario: Petición exitosa no se ve afectada por el try-catch global
    Given el backend recibe una petición a una ruta existente
    And el controlador correspondiente se ejecuta sin errores
    When el front controller ejecuta el bloque del router
    Then la respuesta se procesa normalmente
    And el try-catch global no altera el contenido de la respuesta

  Scenario: Excepción en ruta no existente
    Given el backend recibe una petición a una ruta que no existe
    When el front controller ejecuta el bloque del router
    Then la respuesta HTTP tiene código 404
    And el sistema no debería lanzar una excepción 500 por falta de ruta
