# Servidores HTTP de Archivos

Este documento resume las **características comunes** entre los tres servidores HTTP de archivos implementados en:

1. **C# (HttpListener)** --> CSharp5
2. **PowerShell (HttpListener)**
3. **C# (HttpFileServer con hilos)** --> CSharp11

---

## 🔍 **Características Comunes**

### 1. **Propósito Principal**

Todos los servidores tienen el mismo objetivo:

- **Servir archivos estáticos** (HTML, CSS, JS, imágenes, etc.) desde un directorio local.
- Actuar como un **servidor HTTP local** para desarrollo, pruebas o compartir archivos en una red.

---

### 2. **Configuración Básica**

Los tres servidores permiten configurar:

- **Puerto**: Para definir en qué puerto escuchará el servidor (ej. `8080`, `54321`).
- **Directorio raíz**: Ruta al directorio que contiene los archivos estáticos a servir (ej. `./Views`, `C:\MiSitioWeb`).

---

### 3. **Manejo de Solicitudes HTTP**

- **Ruta por defecto**: Si se solicita la raíz (`/`), todos los servidores sirven el archivo `index.html`.
- **Respuesta a solicitudes**:
  - Leen el archivo solicitado desde el sistema de archivos.
  - Si el archivo existe, lo envían como respuesta con el **Content-Type** adecuado.
  - Si el archivo no existe, devuelven un código de error (ej. `400` o `404`).

---

### 4. **Detención del Servidor**

- **C# (HttpListener)**: Se detiene al acceder a la ruta `/quit`.
- **PowerShell (HttpListener)**: Se detiene al presionar `**CTRL + C**` en la consola.
- **C# (HttpFileServer)**: Se detiene al presionar `**q` + Enter** en la consola.

---

### 5. **Manejo de Tipos MIME**

Todos los servidores intentan determinar el **tipo MIME** de los archivos para establecer el `Content-Type` correcto en la respuesta:

- **C# (HttpListener)**: Usa el registro de Windows para obtener el tipo MIME.
- **PowerShell (HttpListener)**: Usa el registro de Windows para obtener el tipo MIME.
- **C# (HttpFileServer)**: No se muestra en el código, pero se asume que la clase `HttpFileServer` implementa esta funcionalidad.

---

### 6. **Salida en Consola**

Los tres servidores muestran información en la consola:

- **URL del servidor** (ej. `http://localhost:8080/`).
- **Directorio raíz** desde el que se sirven los archivos.
- **Solicitudes recibidas** (URL solicitada y código de respuesta).

---

### 7. **Uso de Argumentos de Línea de Comandos**

Todos los servidores aceptan argumentos para configurar:

- **Puerto** (ej. `-port 8080`).
- **Directorio raíz** (ej. `-path "./Views"`).
- **Ayuda** (ej. `-help`, `-h`, `-?`).

---

### 8. **Entorno de Ejecución**

- **Sistema operativo**: Todos están diseñados para ejecutarse en **Windows**.
- **Dependencias**:
  - **C#**: Requiere .NET Framework o .NET Core.
  - **PowerShell**: Requiere PowerShell 5.1 o superior.

---

### 9. **Casos de Uso**

Los tres servidores son ideales para:

- **Desarrollo local**: Probar sitios web o aplicaciones estáticas.
- **Compartir archivos**: Compartir archivos en una red local de manera temporal.
- **Pruebas rápidas**: Verificar el comportamiento de un sitio web sin necesidad de un servidor completo como Apache o Nginx.

---

## 📌 **Diferencias Clave**


| Característica            | C# (HttpListener) | PowerShell (HttpListener) | C# (HttpFileServer) |
| ------------------------- | ----------------- | ------------------------- | ------------------- |
| **Lenguaje**              | C#                | PowerShell                | C#                  |
| **Hilos**                 | No usa hilos      | No usa hilos              | Usa hilos           |
| **Detención**             | Ruta `/quit`      | `CTRL + C`                | `q` + Enter         |
| **Apertura automática**   | Abre Chrome       | No abre navegador         | No abre navegador   |
| **Dependencias externas** | No                | No                        | Posiblemente sí     |
| **Compilación**           | `csc.exe`         | No requiere compilación   | `dotnet publish`    |


---

## 💡 **Conclusión**

Los tres servidores comparten una **arquitectura y funcionalidad similares**, pero difieren en:

- **Lenguaje de implementación** (C# vs. PowerShell).
- **Mecanismo de detención** (ruta `/quit`, `CTRL + C`, o `q` + Enter).
- **Uso de hilos** (solo el servidor `HttpFileServer` usa hilos para separar la lógica del servidor de la interacción con el usuario).

Todos son **herramientas útiles para desarrollo local** y pueden adaptarse según las necesidades específicas del usuario.