# API Professional Client - PowerShell Edition

**Un cliente gráfico para probar APIs REST directamente desde PowerShell.**

---

## 📌 Descripción

Este script de PowerShell crea una **interfaz gráfica (GUI)** para enviar peticiones HTTP (GET, POST, PUT, DELETE) a APIs REST. Permite:

- Configurar **URL**, **método HTTP**, **headers** y **body** (en formato JSON).
- Guardar y cargar configuraciones en archivos `.json`.
- Visualizar respuestas en formato JSON con sintaxis resaltada.

---

## 🚀 Características

✅ **Interfaz intuitiva** con campos para URL, método, headers y body.  
✅ **Soporte para todos los métodos HTTP** (GET, POST, PUT, DELETE).  
✅ **Gestión dinámica de headers**: Añadir/eliminar pares clave-valor.  
✅ **Guardar y cargar configuraciones** en archivos JSON.  
✅ **Visualización de respuestas** con colores (éxito en verde, errores en rosa).  
✅ **Soporte para TLS 1.2** (seguridad mejorada).

---

## 🛠 Requisitos

- **PowerShell 5.1 o superior** (recomendado PowerShell 7+).
- **Sistema operativo**: Windows (por el uso de `System.Windows.Forms`).
- **Módulos**: Ninguno adicional (usa ensamblados de .NET integrados).

---

## 📥 Instalación

1. **Descargar el script**:
  Guarda el código en un archivo, por ejemplo: `API_Client.ps1`.
2. **Ejecutar el script**:
  Abre PowerShell y ejecuta:
  > **Nota**: Si bloquea la ejecución por políticas de seguridad, ejecuta primero:
  >
  > ```powershell
  > Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
  > ```

---

## 🎯 Uso

### 1. **Configurar la petición**

- **Método**: Selecciona el método HTTP (GET, POST, PUT, DELETE).
- **URL**: Introduce la URL de la API (ej: `https://api.example.com/data`).
- **Headers**: Añade headers personalizados (clic en "+ Añadir Header").
- **Body**: Introduce el cuerpo de la petición en formato JSON (solo para POST/PUT).

### 2. **Enviar la petición**

Haz clic en el botón **"Enviar"**. La respuesta aparecerá en el cuadro de salida:

- **Éxito**: Texto en verde (JSON formateado).
- **Error**: Texto en rosa con el mensaje de error.

### 3. **Guardar/Cargar configuraciones**

- **Guardar**: Usa `Archivo > Guardar Configuración` para exportar la configuración actual a un archivo `.json`.
- **Cargar**: Usa `Archivo > Abrir Configuración` para importar una configuración guardada.

---

## 📂 Ejemplo de archivo de configuración (JSON)

```json
{
  "url": "https://api.example.com/users",
  "method": "POST",
  "body": "{\"name\": \"John Doe\", \"age\": 30}",
  "headers": [
    { "key": "Content-Type", "val": "application/json" },
    { "key": "Authorization", "val": "Bearer token123" }
  ]
}
```

---

## 🔧 Personalización

- **Tema de colores**: Modifica los valores hexadecimales en el script (ej: `#0078d4` para el botón "Enviar").
- **Tamaño de la ventana**: Ajusta `$form.Size` en el script.
- **Fuentes**: Cambia `$form.Font` o `$outputBox.Font`.

---

## ⚠️ Limitaciones

- **Solo Windows**: No compatible con Linux/macOS (depende de `System.Windows.Forms`).
- **Sin autenticación avanzada**: No soporta OAuth2 o certificados cliente (solo headers básicos).
- **JSON simple**: El body debe ser JSON válido (no hay validación automática).

---

## 🤝 Contribuciones

¡Las sugerencias y mejoras son bienvenidas! Abre un *issue* o envía un *pull request* si encuentras errores o quieres añadir funcionalidades.

---

## 📜 Licencia

Este proyecto es de **código abierto** bajo la licencia [MIT](LICENSE).  
</canvaentity