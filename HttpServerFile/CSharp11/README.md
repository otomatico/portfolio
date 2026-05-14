# Servidor HTTP de Archivos en C#

Un **servidor HTTP simple** escrito en **C#** que permite servir archivos estáticos desde un directorio local. Ideal para desarrollo, pruebas o compartir archivos rápidamente en una red local.

---

## 📌 Descripción

Este programa crea un servidor HTTP que:

- Escucha en una **IP y puerto configurables** (por defecto: `localhost:8080`).
- Sirve archivos estáticos (HTML, CSS, JS, imágenes, etc.) desde un **directorio raíz especificado** (por defecto: directorio actual).
- Se ejecuta en un **hilo separado** para permitir la interacción con el usuario.
- Permite **detener el servidor** presionando `q` + `Enter` en la consola.

---

## 🚀 Cómo usar

---

### 1. Compilar el código

Ejecuta el siguiente comando para compilar el proyecto en modo **Release** para Windows x64:

```sh
dotnet publish -c Release -r win-x64 --self-contained false -o ./publish
```

- `**-c Release**`: Compila en modo Release (optimizado).
- `**-r win-x64**`: Especifica que el destino es Windows de 64 bits.
- `**--self-contained false**`: Indica que no se incluirán las dependencias de .NET en el output (requiere .NET Runtime instalado en el sistema destino).
- `**-o ./publish**`: Directorio de salida donde se generarán los archivos compilados.

---

### 2. Ejecutar el servidor

Navega al directorio `publish` y ejecuta el archivo generado:

```sh
cd ./publish
.\HttpFileServer.exe
```

#### Opciones disponibles:


| Opción                | Descripción                                    | Valor por defecto       |
| --------------------- | ---------------------------------------------- | ----------------------- |
| `-ip`                 | Dirección IP del servidor.                     | `localhost`             |
| `-port`               | Puerto del servidor.                           | `8080`                  |
| `-path`               | Ruta al directorio raíz para servir archivos.  | Directorio actual (`.`) |
| `-help` o `-h` o `-?` | Muestra la ayuda con las opciones disponibles. | -                       |


#### Ejemplo de uso:

```sh
.\HttpFileServer.exe -ip 127.0.0.1 -port 8080 -path C:\MiSitioWeb
```

---

### 3. Acceder al servidor

Abre tu navegador y visita:

```
http://{ip}:{puerto}/
```

Por ejemplo, si usaste la IP `127.0.0.1` y el puerto `8080`:

```
http://127.0.0.1:8080/
```

---

### 4. Detener el servidor

Mientras el servidor está en ejecución, presiona `**q` + `Enter**` en la consola para detenerlo. Verás el mensaje:

```
Servicio finalizado por el usuario.
```

---

## 📂 Estructura de archivos recomendada

Asegúrate de que el directorio especificado con `-path` contenga tus archivos estáticos. Ejemplo:

```
C:\MiSitioWeb\
├── index.html
├── styles\
│   └── main.css
└── scripts\
    └── app.js
```

---

## 🔧 Dependencias

- **.NET 6.0** o superior (requerido para compilar y ejecutar el proyecto).
- **Sistema operativo**: Windows (el comando de compilación está configurado para `win-x64`).

---

## ⚠️ Notas importantes

1. **Permisos de administrador**:
  - En Windows, ejecutar servidores HTTP en puertos bajos (ej. `80` o `443`) **requiere permisos de administrador**.
  - Si usas un puerto alto (ej. `8080`, `54321`), no necesitarás permisos elevados.
2. **Ruta del directorio**:
  - Asegúrate de que la ruta especificada con `-path` sea **absoluta** (ej. `C:\MiSitioWeb\`). Si usas rutas relativas, el servidor puede no encontrar los archivos.
3. **Seguridad**:
  - Este servidor es **solo para desarrollo local o pruebas en redes de confianza**.
  - **No lo uses en producción** sin implementar medidas de seguridad adicionales (autenticación, HTTPS, etc.).
4. **Hilos**:
  - El servidor se ejecuta en un **hilo separado** para permitir la interacción con el usuario en la consola principal.

---

## 📜 Licencia

Este proyecto es de código abierto y puede usarse libremente. No incluye licencia explícita.

---

## 🤝 Contribuciones

Si encuentras errores o quieres mejorar el código, ¡siéntete libre de contribuir! 😊