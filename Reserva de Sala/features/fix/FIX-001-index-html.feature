@FIX-001
Feature: Archivo index.html faltante en frontend
  Como desarrollador
  Quiero que exista un archivo index.html en src/frontend/
  Para que Vite pueda compilar y servir la SPA de Svelte correctamente

  Scenario: Build de producción exitoso con index.html presente
    Given el proyecto frontend en src/frontend/
    And existe el archivo src/frontend/index.html con un <div id="app">
    When ejecuto "npm run build"
    Then el build se completa sin errores
    And se genera el directorio dist/
    And dist/index.html contiene la aplicación renderizada
    And dist/assets/ contiene los archivos JS/CSS compilados

  Scenario: Servidor de desarrollo sirve la SPA correctamente
    Given el proyecto frontend en src/frontend/
    And existe el archivo src/frontend/index.html con un <div id="app">
    When ejecuto "npm run dev"
    Then Vite inicia el servidor en http://localhost:5173
    And al acceder a "/" se recibe un documento HTML válido
    And el HTML contiene <div id="app"></div> como punto de montaje
    And el HTML incluye la etiqueta <script type="module" src="/src/main.js">
