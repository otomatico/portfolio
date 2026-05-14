# Servidor HTTP Simple en PowerShell

Un servidor HTTP ligero escrito en **PowerShell** que sirve archivos estáticos desde un directorio local. Ideal para desarrollo, pruebas o compartir archivos rápidamente en una red local.

---

## 📌 Descripción

Este script de PowerShell crea un servidor HTTP que:

- Escucha en un **puerto configurable** (por defecto: `4321`).
- Sirve archivos estáticos (HTML, CSS, JS, imágenes, etc.) desde un **directorio especificado**.
- Detecta automáticamente el **tipo MIME** de los archivos usando el registro de Windows.
- Muestra en la consola las solicitudes recibidas y el código de respuesta.

---

## 🚀 Cómo usar

### 1. Guardar el script

Guarda el código en un archivo llamado `**SimpleHttpServer.ps1**`.

---

### 2. Ejecutar el servidor

Abre **PowerShell** y ejecuta el script con los parámetros deseados:

```powershell
.\SimpleHttpServer.ps1 -port 3080 -localPath "$((Get-Location).Path)\Views\"
```

#### Parámetros disponibles:


| Parámetro    | Descripción                                                      | Valor por defecto                       |
| ------------ | ---------------------------------------------------------------- | --------------------------------------- |
| `-port`      | Puerto en el que el servidor escuchará.                          | `4321`                                  |
| `-localPath` | Ruta al directorio que contiene los archivos estáticos a servir. | Directorio actual (`Get-Location`).Path |


---

### 3. Acceder al servidor

Abre tu navegador y visita:

```
http://localhost:{puerto}/
```

Por ejemplo, si usaste el puerto `3080`:

```
http://localhost:3080/
```

---

## 📂 Estructura de archivos recomendada

Asegúrate de que el directorio especificado con `-localPath` contenga tus archivos estáticos. Ejemplo:

```
.
├── SimpleHttpServer.ps1
└── Views/
    ├── index.html
    ├── styles/
    │   └── main.css
    └── scripts/
        └── app.js
```

---

## 🛑 Detener el servidor

Para detener el servidor, presiona `**CTRL + C**` en la terminal donde se está ejecutando el script.

---

## 🔧 Dependencias

- **PowerShell 5.1** o superior (incluido en Windows 10/11 y Windows Server 2016+).
- **Permisos de ejecución**: Si es la primera vez que ejecutas scripts de PowerShell, puede que necesites habilitar la ejecución de scripts con:
  ```powershell
  Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
  ```
  (Ejecuta esto como administrador si es necesario).

---

## ⚠️ Notas importantes

1. **Permisos de administrador**:
  - En Windows, ejecutar servidores HTTP en puertos bajos (ej. `80` o `443`) **requiere permisos de administrador**.
  - Si usas un puerto alto (ej. `3080`, `54321`), no necesitarás permisos elevados.
2. **Tipo MIME**:
  - El servidor intenta detectar el tipo MIME usando el registro de Windows.
  - Si el tipo MIME no está registrado, el navegador puede no interpretar correctamente el archivo.
3. **Seguridad**:
  - Este servidor es **solo para desarrollo local o pruebas en redes de confianza**.
  - **No lo uses en producción** sin implementar medidas de seguridad adicionales (autenticación, HTTPS, etc.).
4. **Ruta absoluta**:
  - Asegúrate de que `-localPath` sea una **ruta absoluta** (ej. `C:\mi_proyecto\Views\`). Si usas rutas relativas, el servidor puede no encontrar los archivos.
5. **Paquetes DLL**:
  - El script incluye una función `Load-Packages` para cargar ensamblados `.dll` desde un directorio `Packages`. Si no necesitas esta funcionalidad, puedes eliminarla.

---

## 📜 Licencia

Este proyecto es de código abierto y puede usarse libremente. No incluye licencia explícita.

---

## 🤝 Contribuciones

Si encuentras errores o quieres mejorar el código, ¡siéntete libre de contribuir!