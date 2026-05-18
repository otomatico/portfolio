# API Professional Client - C# Edition

**Un cliente gráfico para probar APIs REST/GraphQL, desarrollado en C# con Windows Forms y Newtonsoft.Json.**

---

## 📌 Descripción

Este proyecto es una **aplicación de escritorio** escrita en **C#** que permite enviar peticiones HTTP (GET, POST, PUT, DELETE) a APIs REST o GraphQL. Incluye una interfaz gráfica intuitiva para configurar **URLs, métodos HTTP, headers y el cuerpo de la petición (body)**, además de permitir guardar y cargar configuraciones en archivos JSON.

Es ideal para desarrolladores que necesitan probar APIs de manera rápida y visual, sin depender de herramientas externas como Postman.

---

## ✨ Características

✅ **Interfaz gráfica con Windows Forms** (compatible con .NET Framework 4.0+).  
✅ **Soporte para métodos HTTP**: GET, POST, PUT, DELETE.  
✅ **Gestión dinámica de headers**: Añadir/eliminar pares clave-valor.  
✅ **Cuerpo de la petición (Body)**: Soporte para JSON (con formateo automático en la respuesta).  
✅ **Guardar y cargar configuraciones** en archivos `.json`.  
✅ **Visualización de respuestas** con colores:

- **Éxito**: Texto en blanco (JSON formateado).
- **Error**: Texto en rosa con detalles del error.  
✅ **Manejo de excepciones** para errores de conexión, tiempo de espera, y respuestas HTTP no exitosas.  
✅ **Soporte para TLS 1.2** (seguridad mejorada).  
✅ **Dependencia mínima**: Solo requiere `Newtonsoft.Json.dll` para el manejo de JSON.

---

## 🛠 Requisitos

- **Sistema operativo**: Windows (7, 8, 10, 11).
- **.NET Framework 4.0 o superior** (el código está escrito para C# 5.0).
- **Newtonsoft.Json**: Se requiere el ensamblado `Newtonsoft.Json.dll` para compilar y ejecutar el proyecto.
  - Puedes descargarlo desde [NuGet](https://www.nuget.org/packages/Newtonsoft.Json/) o incluirlo manualmente en el proyecto.

---

## 📥 Instalación y Compilación

### 1. **Descargar el código**

Guarda el código en un archivo llamado `Program.cs`.

### 2. **Descargar Newtonsoft.Json**

- Descarga `Newtonsoft.Json.dll` desde [NuGet](https://www.nuget.org/packages/Newtonsoft.Json/) o usa el Administrador de Paquetes de Visual Studio.
- Coloca el archivo `Newtonsoft.Json.dll` en la misma carpeta que `Program.cs`.

### 3. **Compilar el proyecto**

Ejecuta el siguiente comando en la **terminal de desarrollador de Visual Studio** (o en una ventana de CMD con el compilador de C# en el PATH):

```sh
C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe /target:winexe /reference:Newtonsoft.Json.dll /out:GraphQLClient.exe Program.cs
```

> **Nota**:
>
> - Asegúrate de que la ruta a `csc.exe` sea correcta para tu versión de .NET Framework.
> - Si usas **Visual Studio**, puedes crear un proyecto de **Windows Forms** y añadir el código directamente.

### 4. **Ejecutar la aplicación**

- Tras compilar, se generará un archivo `GraphQLClient.exe`.
- Ejecútalo haciendo doble clic.

---

## 🎯 Uso

### 1. **Configurar la petición**

- **Método**: Selecciona el método HTTP (GET, POST, PUT, DELETE) desde el menú desplegable.
- **URL**: Introduce la URL de la API (ej: `http://localhost:8080/api/graphql`).
- **Headers**: Añade headers personalizados haciendo clic en **+ Añadir**. Por defecto, no se incluye `Content-Type` (se maneja automáticamente para POST/PUT).
- **Body**: Introduce el cuerpo de la petición en formato JSON (para POST/PUT). Ejemplo:
  ```json
  {
    "query": "{ user(id: 1) { name email } }"
  }
  ```

### 2. **Enviar la petición**

Haz clic en el botón **"Enviar"**. La respuesta aparecerá en el panel inferior:

- **Éxito**: Respuesta JSON formateada en blanco.
- **Error**: Mensaje de error en rosa (incluye código HTTP y detalles).

### 3. **Guardar/Cargar configuraciones**

- **Guardar**: Usa `Archivo > Guardar Configuración` para exportar la configuración actual a un archivo `.json`.
- **Cargar**: Usa `Archivo > Abrir Configuración` para importar una configuración guardada.

---

## 📂 Ejemplo de archivo de configuración (JSON)

```json
{
  "url": "http://localhost:8080/api/graphql",
  "method": "POST",
  "body": "{\"query\": \"{ user(id: 1) { name email } }\"}",
  "headers": [
    { "key": "Authorization", "val": "Bearer your_token_here" }
  ]
}
```

---

## 🔧 Personalización

### Cambiar el estilo visual

Modifica las propiedades de los controles en el método `InitializeComponent()`:

- **Colores**: Cambia los valores hexadecimales o usa `Color.FromArgb()`.
  ```csharp
  btnSend.BackColor = ColorTranslator.FromHtml("#0078d4"); // Azul
  ```
- **Fuentes**: Ajusta el `Font` de los controles.
  ```csharp
  this.Font = new Font("Segoe UI", 10);
  ```
- **Tamaño de la ventana**: Modifica `this.Size` en el constructor.

### Añadir más métodos HTTP

Edita el `ComboBox` en `InitializeComponent()`:

```csharp
comboMethod.Items.AddRange(new object[] { "POST", "GET", "PUT", "DELETE", "PATCH" });
```

### Manejo avanzado de errores

El código ya maneja excepciones básicas (`WebException` y `Exception`). Puedes extenderlo para manejar casos específicos, como:

- Timeouts.
- Errores de autenticación.
- Respuestas con códigos HTTP específicos (ej: 404, 500).

---

## ⚠️ Limitaciones

- **Solo Windows**: No compatible con Linux/macOS (depende de `System.Windows.Forms`).
- **.NET Framework**: Requiere .NET Framework 4.0 o superior.
- **Newtonsoft.Json**: La aplicación depende de esta librería para el manejo de JSON.
- **Sin autenticación avanzada**: No soporta OAuth2 o certificados cliente (solo headers básicos).
- **Síncrono**: Las peticiones HTTP son síncronas (puede bloquear la UI en peticiones lentas).

---

## 🛡 Seguridad

- **TLS 1.2**: El código está preparado para usar TLS 1.2 (comentado en el código original). Si necesitas habilitarlo, descomenta la línea:
  ```csharp
  ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
  ```
- **Validación de JSON**: La aplicación usa `JValue.Parse` para formatear la respuesta. Asegúrate de que la API devuelva JSON válido.

---

## 🤝 Contribuciones

¡Las sugerencias y mejoras son bienvenidas! Puedes:

- Reportar errores o solicitar funcionalidades.
- Modificar el código y compartir tus mejoras (ej: añadir soporte para async/await, autenticación OAuth2, etc.).

---

## 📜 Licencia

## Este proyecto es de **código abierto** bajo la licencia [MIT](LICENSE). Puedes usarlo, modificarlo y distribuirlo libremente.

## 📌 Notas técnicas

- **Clase `HeaderRow**`: Se usa para almacenar referencias a los controles de cada fila de headers (Panel, Key, Val).
- **Manejo de `Content-Type**`: Se añade automáticamente para POST/PUT si hay un cuerpo. Los headers personalizados con clave `Content-Type` son ignorados para evitar conflictos.
- **Formateo de JSON**: La respuesta se formatea usando `JValue.Parse(responseText).ToString(Formatting.Indented)`.