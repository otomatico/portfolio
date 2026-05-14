# Servidor HTTP Local en C# (Express)

Un servidor HTTP simple escrito en **C# 5.0** que sirve archivos estáticos desde un directorio local y abre automáticamente una pestaña en Google Chrome con la URL del servidor.

---

## 📌 Descripción

Este programa crea un servidor HTTP local que:

- Escucha en un puerto configurable (por defecto: `4321`).
- Sirve archivos estáticos (HTML, JS, CSS, imágenes, etc.) desde un directorio especificado.
- Abre automáticamente una pestaña en **Google Chrome** con la URL `http://localhost:{puerto}/index.html`.
- Permite detener el servidor mediante la ruta `/quit`.

---

## 🚀 Cómo usar

### 1. Compilar el código

Ejecuta el siguiente comando en la terminal (asegúrate de tener el compilador de C# instalado):

```sh
C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe /target:exe /out:Express.exe Express.cs
```

### 2. Ejecutar el servidor

```sh
.\Express.exe -port 54321 -path "C:\ruta\a\tu\directorio\Views\"
```

- `**-port**`: Puerto en el que el servidor escuchará (opcional, por defecto: `4321`).
- `**-path**`: Ruta al directorio que contiene los archivos estáticos (opcional, por defecto: directorio actual).

---

## 📂 Estructura de archivos

Asegúrate de que el directorio especificado con `-path` contenga al menos un archivo `index.html`. Ejemplo:

```
Views/
├── index.html
├── styles/
│   └── main.css
└── scripts/
    └── app.js
```

---

## 🛑 Detener el servidor

Accede a la siguiente URL en tu navegador:

```
http://localhost:{puerto}/quit
```

Esto cerrará el servidor y la pestaña de Chrome.

---

## 🔧 Dependencias

- **.NET Framework 4.0** o superior.
- **Google Chrome** instalado en la ruta por defecto (`C:/Program Files/Google/Chrome/Application/chrome_proxy.exe`).
  - Si Chrome está instalado en otra ruta, modifica la variable `chrome` en el método `ExecuteHTA`.

---

## ⚠️ Notas importantes

1. **Permisos de administrador**: En Windows, ejecutar servidores HTTP en puertos bajos (ej. `80` o `443`) puede requerir permisos de administrador.
2. **Ruta de Chrome**: Si usas un sistema operativo diferente o una instalación personalizada de Chrome, actualiza la ruta en el código.
3. **Seguridad**: Este servidor es para desarrollo local. **No lo uses en producción** sin implementar medidas de seguridad adicionales (autenticación, HTTPS, etc.).
4. **Manejo de MIME types**: El servidor intenta detectar el tipo MIME de los archivos usando el registro de Windows. Si falla, usa tipos por defecto para `.js` y `application/octet-stream` para el resto.

---

## 📜 Licencia

Este proyecto es de código abierto y puede usarse libremente. No incluye licencia explícita.

---

## 🤝 Contribuciones

Si encuentras errores o quieres mejorar el código, ¡siéntete libre de contribuir!