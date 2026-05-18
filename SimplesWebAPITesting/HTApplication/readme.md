# REST API Client (HTA)

Un cliente gráfico ligero para probar APIs REST/GraphQL desde Windows, construido como una aplicación HTA (HTML Application).

---

## 📌 Descripción

Este proyecto es una **aplicación HTA** (HTML Application) que permite enviar peticiones HTTP (GET, POST, PUT, DELETE) a APIs REST o GraphQL directamente desde Windows, sin necesidad de instalar herramientas externas como Postman o Insomnia. Está diseñado para ser **portátil, ligero y fácil de usar**, con una interfaz similar a un cliente API profesional.

---

## ✨ Características

✅ **Interfaz gráfica intuitiva** con campos para URL, método HTTP, headers y body.  
✅ **Soporte para todos los métodos HTTP**: GET, POST, PUT, DELETE.  
✅ **Gestión dinámica de headers**: Añadir/eliminar pares clave-valor.  
✅ **Guardar y cargar configuraciones** en archivos `.json`.  
✅ **Visualización de respuestas** con colores (éxito en blanco, errores en rosa).  
✅ **Soporte para TLS/SSL** (compatible con APIs seguras).  
✅ **Portátil**: No requiere instalación, solo ejecuta el archivo `.hta`.  
✅ **Compatibilidad con IE9+** (usando JScript y ActiveX).

---

## 🛠 Requisitos

- **Sistema operativo**: Windows (XP, 7, 8, 10, 11).
- **Navegador**: No requiere navegador externo (usa el motor de IE integrado en HTA).
- **Permisos**: Requiere permisos para ejecutar scripts y acceder a `ActiveXObject` (WinHttpRequest y FileSystemObject).
- **Dependencias**: Ninguna. Funciona como un archivo `.hta` autónomo.

---

## 📥 Instalación

1. **Descargar el archivo**:
  Guarda el código HTML en un archivo con extensión `.hta`, por ejemplo: `REST_API_Client.hta`.
2. **Ejecutar el archivo**:
  Haz doble clic en el archivo `.hta`. Windows lo ejecutará automáticamente usando el motor de Internet Explorer.
  > **Nota**: Si aparece un mensaje de seguridad, haz clic en **"Permitir"** o **"Ejecutar"** para continuar.

---

## 🎯 Uso

### 1. **Configurar la petición**

- **Método**: Selecciona el método HTTP (GET, POST, PUT, DELETE) desde el menú desplegable.
- **URL**: Introduce la URL de la API (ej: `https://api.example.com/graphql`).
- **Headers**: Añade headers personalizados (clic en **+ Añadir Cabecera**). Por defecto incluye `Content-Type: application/json`.
- **Body**: Introduce el cuerpo de la petición en formato JSON (para POST/PUT). Ejemplo:
  ```json
  {
    "query": "{ user(id: 1) { name email } }"
  }
  ```

### 2. **Enviar la petición**

Haz clic en el botón **"Enviar"**. La respuesta aparecerá en el panel inferior:

- **Éxito**: Texto en blanco con la respuesta JSON.
- **Error**: Texto en rosa con el código de error HTTP y mensaje.

### 3. **Guardar/Cargar configuraciones**

- **Guardar**: Usa `Archivo > Guardar Configuración` para exportar la configuración actual a un archivo `.json`.
- **Cargar**: Usa `Archivo > Abrir Configuración` para importar una configuración guardada.

### 4. **Opciones adicionales**

- **Reiniciar**: Reinicia la aplicación para limpiar todos los campos.
- **Salir**: Cierra la aplicación.

---

## 📂 Ejemplo de archivo de configuración (JSON)

```json
{
  "url": "https://api.example.com/graphql",
  "method": "POST",
  "body": "{\"query\": \"{ user(id: 1) { name email } }\"}",
  "headers": [
    { "key": "Content-Type", "val": "application/json" },
    { "key": "Authorization", "val": "Bearer your_token_here" }
  ]
}
```

## ⚠️ Limitaciones

- **Solo Windows**: No compatible con Linux/macOS (depende de `ActiveXObject`).
- **Seguridad**: Usa `WinHttpRequest.5.1`, que puede tener limitaciones con APIs modernas (ej: CORS).
- **JSON manual**: La serialización/deserialización de JSON se hace manualmente (no usa `JSON.stringify` nativo).
- **Sin autenticación avanzada**: No soporta OAuth2 o certificados cliente (solo headers básicos).
- **IE9+**: La compatibilidad con CSS/JS moderno es limitada.

---

## 🛡 Seguridad

- **ActiveX**: El archivo `.hta` requiere permisos para ejecutar `ActiveXObject`. Asegúrate de descargarlo de una fuente confiable.
- **Archivos JSON**: Al cargar configuraciones, el archivo se evalúa con `eval`. **No cargues archivos JSON de fuentes no confiables**.

---

## 🤝 Contribuciones

¡Las sugerencias y mejoras son bienvenidas! Puedes:

- Reportar errores o solicitar funcionalidades.
- Modificar el código y compartir tus mejoras.

---

## 📜 Licencia

Este proyecto es de **código abierto** bajo la licencia [MIT](LICENSE). Puedes usarlo, modificarlo y distribuirlo libremente.