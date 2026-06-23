@FIX-002
Feature: Puerto inconsistente entre README y Vite proxy
  Como desarrollador
  Quiero que el README.md y vite.config.js usen el mismo puerto
  Para que el frontend pueda comunicarse con el backend sin errores de conexión

  Scenario: README indica el puerto correcto para iniciar el backend
    Given el archivo README.md en la raíz del proyecto
    When un desarrollador lee la sección de inicio del backend
    Then el comando indicado es "cd src/backend && php -S localhost:5000 -t public/"
    And el puerto especificado es 5000

  Scenario: Vite proxy configura el puerto del backend correctamente
    Given el archivo vite.config.js en src/frontend/
    When se inspecciona la configuración del proxy
    Then el proxy para "/api/*" apunta a "http://localhost:5000"
    And el puerto 5000 coincide con el indicado en README.md

  Scenario: Frontend en desarrollo se comunica con el backend
    Given el backend corriendo en localhost:5000
    And el frontend corriendo en localhost:5173
    When el frontend hace una petición a "/api/auth/login"
    Then Vite redirige la petición al backend en localhost:5000
    And el backend responde correctamente
