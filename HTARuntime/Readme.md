# HTA Runtime

Runtime moderno en Rust que ejecuta archivos `.hta` y `.htax` usando WebView2 (wry),
reemplazando el motor MSHTML clásico por Edge Chromium.

## Características

- Renderiza HTML5 / CSS3 / JavaScript ES2024+ en WebView2
- Soporte para formato `.hta` (HTML plano) y `.htax` (ZIP empaquetado)
- Bridge nativo vía HTTP RPC: `window.System.*`
- Diálogos nativos del sistema (abrir/guardar archivo, seleccionar carpeta)
- Ejecución de comandos shell
- Compatibilidad ActiveX parcial: `FileSystemObject`, `WScript.Shell`, `TextStream`, `WinHttpRequest`
- Parseo de etiqueta `<HTA:APPLICATION>` (APPLICATIONNAME, BORDER, CAPTION, WINDOWSTATE, etc.)

## Stack Tecnológico

| Componente | Librería |
|---|---|
| WebView | `wry` |
| Event Loop / Ventana | `winit` |
| Servidor HTTP embebido | `tiny_http` |
| Diálogos nativos | `rfd` |
| Extracción ZIP | `zip` |
| Parsing | `regex` |

## API — `window.System`

```javascript
System.info(msg)                          // alert(msg)
System.error(msg)                         // alert("[Error] " + msg)
System.confirm(msg)                       // confirm(msg) → bool
System.quit()                             // Cierra la aplicación
System.getConfig()                        // HtaConfig parseada

System.fs.readFile(path)                  // Lee archivo

System.exec(command)                      // Ejecuta comando shell

System.dialogs.openFile(opts)             // Diálogo abrir archivo
System.dialogs.saveFile(opts)             // Diálogo guardar archivo
System.dialogs.openDir(opts)              // Diálogo seleccionar carpeta

new ActiveXObject("Scripting.FileSystemObject")  // FSO (FileExists, CopyFile, etc.)
new ActiveXObject("WScript.Shell")               // Shell (Exec)
new ActiveXObject("WinHttp.WinHttpRequest.5.1")  // HTTP (polyfill nativo)
```

## Requisitos

- Rust Stable
- Cargo
- WebView2 Runtime
- Windows 10 o superior (Linux/macOS: experimental con WebKitGTK)

## Uso

```cmd
cargo run -- examples/demo.hta
cargo run -- examples/test_maximized.hta
hta-runtime.exe app.htax
```

## Estructura del proyecto

```text
hta-runtime
├── src/
│   ├── main.rs              — Orquestador, CLI, event loop, RPC dispatch
│   ├── server.rs            — HTTP server, static serving, MIME types
│   ├── hta_parser.rs        — Parsea <HTA:APPLICATION> con regex
│   ├── hta_loader.rs        — Valida extensión, extrae .htax (zip)
│   └── bridge/
│       ├── mod.rs           — Re-exports
│       ├── fs.rs            — read_file
│       ├── dialogs.rs       — open_file, save_file, open_dir
│       ├── exec.rs          — exec_command
│       └── activex.rs       — ActiveX bridge (FSO, WshShell, TextStream)
├── runtime/
│   ├── hta.js               — Bridge JS: window.System + ActiveXObject
│   └── index.html           — Página standalone
├── examples/                — 6 ejemplos .hta
├── gen/schemas/             — Schemas de capabilities
├── Cargo.toml
└── spec.md                  — Especificación técnica detallada
```

## Licencia

MIT License
