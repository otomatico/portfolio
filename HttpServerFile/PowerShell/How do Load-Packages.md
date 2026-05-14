# Guía: Cómo usar `Load-Packages` en el Servidor HTTP de PowerShell

---

## 📌 Introducción

La función `**Load-Packages**` en tu script de PowerShell permite cargar **librerías .dll** (ensamblados de .NET) desde un directorio específico. Esto es útil para:

- Añadir funcionalidades adicionales al servidor HTTP.
- Reutilizar código de otros proyectos.
- Manejar formatos como JSON, XML, o bases de datos con librerías especializadas.

---

## 📂 Estructura de directorios

Para usar `Load-Packages`, organiza tus archivos de la siguiente manera:

```
.
├── SimpleHttpServer.ps1
└── Packages/
    ├── Newtonsoft.Json.dll
    ├── System.Net.Http.dll
    └── MiLibreriaPersonal.dll
```

- `**Packages/**`: Directorio donde se almacenan las librerías `.dll`.
- `**SimpleHttpServer.ps1**`: Tu script del servidor HTTP.

---

## 🔧 Cómo cargar librerías

### 1. Llamar a `Load-Packages`

La función ya está integrada en tu script y se ejecuta automáticamente al inicio. Por defecto, busca las `.dll` en el directorio `**Packages**`.

Si quieres cargar librerías desde otro directorio, modifica la llamada:

```powershell
Load-Packages -directory "Ruta\personalizada\con\dll"
```

---

### 2. Usar las librerías cargadas

Una vez cargadas las `.dll`, puedes usar sus clases y métodos en cualquier parte del script.

#### Ejemplo: Usar `Newtonsoft.Json`

Si cargaste `Newtonsoft.Json.dll`, puedes manejar objetos JSON fácilmente:

```powershell
$json = @'
{
    "nombre": "Otavio",
    "edad": 30
}
'@

$objetoJson = [Newtonsoft.Json.Linq.JObject]::Parse($json)
Write-Host "Nombre: $($objetoJson.nombre)"
```

---

## 🌟 Ejemplo práctico: Servidor HTTP con soporte JSON

### Modificar el script para manejar JSON

Si quieres que tu servidor responda con JSON dinámico, puedes integrar `Newtonsoft.Json` de la siguiente manera:

```powershell
# Cargar las librerías al inicio del script
Load-Packages -directory "Packages"

# ... (resto del código del servidor)

# Añadir una ruta para responder con JSON
if ($context.Request.RawUrl -eq "/api/data") {
    $data = @{
        mensaje = "Hola, $($env:USERNAME)"
        timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    }

    # Convertir el objeto a JSON
    $jsonResponse = [Newtonsoft.Json.JsonConvert]::SerializeObject($data)

    # Configurar la respuesta
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($jsonResponse)
    $response.ContentLength64 = $bytes.Length
    $response.ContentType = "application/json"
    $response.OutputStream.Write($bytes, 0, $bytes.Length)
}
```

---

## ⚠️ Notas importantes

### 1. Compatibilidad

- Las `.dll` deben ser compatibles con **.NET Framework 4.x** (la versión que usa PowerShell por defecto).
- Si usas librerías de **.NET Core** o **.NET 5+**, asegúrate de que sean compatibles con PowerShell.

### 2. Permisos

- Asegúrate de que el script tenga permisos para acceder al directorio donde están las `.dll`.
- Si las `.dll` están bloqueadas (descargadas de Internet), desbloquéalas:
  1. Haz clic derecho en el archivo `.dll`.
  2. Selecciona **Propiedades**.
  3. Haz clic en **Desbloquear** (si el botón aparece).

### 3. Depuración

Si una `.dll` no se carga:

- Verifica que el archivo exista en el directorio especificado.
- Usa herramientas como **Dependency Walker** para diagnosticar dependencias faltantes.

---

## 📌 Casos de uso comunes


| Caso de uso              | Librería recomendada        | Ejemplo de uso                        |
| ------------------------ | --------------------------- | ------------------------------------- |
| Manejar JSON             | `Newtonsoft.Json.dll`       | Serializar/deserializar objetos JSON. |
| Acceder a bases de datos | `System.Data.SqlClient.dll` | Conectar a SQL Server.                |
| Hacer peticiones HTTP    | `System.Net.Http.dll`       | Consumir APIs externas.               |
| Manejar archivos ZIP     | `SharpCompress.dll`         | Comprimir/descomprimir archivos.      |


---

## 🤝 Contribuciones

Si tienes sugerencias para mejorar esta guía o el script, ¡no dudes en compartirlas! 😊