# HTA Runtime — Especificación Técnica

## Visión General
Runtime moderno en Rust que ejecuta archivos `.hta` y `.htax` usando WebView2 (wry),
reemplazando el motor MSHTML clásico por Edge Chromium.

## Arquitectura

```
HTA Runtime
├── src/
│   ├── main.rs          — Orquestador, CLI args, event loop, HTTP server
│   ├── bridge.rs        — RPC: fs, dialogs, exec, ActiveX (FSO, WshShell, TextStream)
│   ├── hta_parser.rs    — Parsea <HTA:APPLICATION> con regex
│   └── hta_loader.rs    — Valida extensión, extrae .htax (zip), genera temp dir
├── runtime/
│   ├── hta.js           — Bridge JS: window.hta, ActiveXObject polyfill
│   └── index.html       — Página standalone (sin .hta)
├── examples/            — 6 ejemplos .hta de prueba
└── gen/schemas/         — Schemas de capabilities
```

## Stack Tecnológico
| Componente | Librería |
|---|---|
| WebView | `wry 0.50` |
| Event Loop / Ventana | `winit 0.30` |
| Servidor HTTP embebido | `tiny_http 0.12` |
| Diálogos nativos | `rfd 0.15` |
| Extracción ZIP | `zip 2` |
| Parsing | `regex 1.11` |
| Serialización | `serde + serde_json` |

**Nota:** No usa Tauri. La comunicación Rust ↔ JS es vía HTTP POST a `127.0.0.1:{port}/rpc`.

## Flujo de Carga
1. CLI recibe `app.hta` o `app.htax`
2. `hta_loader` valida extensión (`.hta`|`.htax`) y existencia
3. Si es `.htax`, extrae ZIP a directorio temporal
4. Inyecta `<script src="hta.js">` en `</head>`
5. `hta_parser` extrae `HtaConfig` de `<HTA:APPLICATION>`
6. Se inicia servidor HTTP embebido en puerto aleatorio
7. Se crea ventana `winit` + webview `wry` apuntando a `http://127.0.0.1:{port}/index.html`
8. Se aplica configuración de ventana (título, decoración, maximizado/minimizado)

## API — RPC HTTP (POST /rpc)

### Comandos Base
| Comando | Payload | Retorno |
|---|---|---|
| `read_file` | `{ path }` | `{ ok, content }` |
| `exec_command` | `{ command }` | `{ ok, output }` |
| `get_config` | `{}` | `{ ok, config }` |
| `quit` | `{}` | `{ ok }` |

### Diálogos Nativos
| Comando | Payload | Descripción |
|---|---|---|
| `open_file` | `{ title?, filters? }` | Selector de archivo |
| `save_file` | `{ title?, filters? }` | Selector para guardar |
| `open_dir` | `{ title? }` | Selector de carpeta |

### ActiveX Bridge
| Comando | Descripción |
|---|---|
| `ax_create` | Crea instancia ActiveX (FSO, WshShell) |
| `ax_call` | Invoca método en instancia |
| `ax_get` | Lee propiedad de instancia |
| `ax_release` | Libera instancia |

**ActiveX soportados:**
- `Scripting.FileSystemObject` — 20+ métodos (FileExists, CopyFile, CreateTextFile, OpenTextFile, GetFile, GetFolder, etc.)
- `WScript.Shell` — Exec, CurrentDirectory
- `WshScriptExec` — StdOut, StdErr, ExitCode
- `TextStream` — Read, ReadAll, ReadLine, Write, WriteLine, Close, AtEndOfStream, Line, Column
- `WinHttp.WinHttpRequest` (polyfill vía XMLHttpRequest nativo)

## API — JS Bridge (`window.hta`)

| Función | Comportamiento |
|---|---|
| `hta.info(msg)` | `alert(msg)` |
| `hta.error(msg)` | `alert("[Error] " + msg)` |
| `hta.confirm(msg)` | `confirm(msg) → bool` |
| `hta.quit()` | Cierra la aplicación |
| `hta.getConfig()` | Obtiene `HtaConfig` parseada |
| `hta.fs.readFile(path)` | Lee archivo del sistema |
| `hta.exec(command)` | Ejecuta comando shell |
| `hta.dialogs.openFile(opts)` | Diálogo abrir archivo |
| `hta.dialogs.saveFile(opts)` | Diálogo guardar archivo |
| `hta.dialogs.openDir(opts)` | Diálogo seleccionar carpeta |
| `new ActiveXObject(progID)` | Crea objeto ActiveX (FSO, WshShell, WinHttp) |

## HTA:APPLICATION — Atributos Soportados

| Atributo | Valores | Default |
|---|---|---|
| `APPLICATIONNAME` | string | `""` |
| `BORDER` | `thick`, `dialog`, `thin`, `none` | `thick` |
| `CAPTION` | `yes`, `no` | `yes` |
| `SHOWINTASKBAR` | `yes`, `no` | `yes` |
| `SINGLEINSTANCE` | `yes`, `no` | `no` |
| `WINDOWSTATE` | `normal`, `maximize`, `minimize` | `normal` |

## HtaConfig (Rust)

```rust
pub struct HtaConfig {
    pub application_name: String,
    pub border: BorderStyle,       // Thick | Dialog | None | Thin
    pub caption: bool,
    pub show_in_taskbar: bool,
    pub single_instance: bool,
    pub window_state: WindowState, // Normal | Minimize | Maximize
}
```

## Formatos Soportados

- **`.hta`** — HTML plano, se inyecta `hta.js` automáticamente
- **`.htax`** — Archivo ZIP conteniendo la aplicación (debe incluir `index.html`)

## Archivos del Proyecto

```
Cargo.toml              — wry 0.50, winit 0.30, tiny_http 0.12, rfd 0.15, regex 1.11, zip 2
src/main.rs             — 360+ líneas: orquestador, HTTP server, RPC handler, ventana
src/bridge.rs           — 760+ líneas: RPC implementations + ActiveX bridge completo
src/hta_parser.rs       — 156 líneas: parser + 4 unit tests
src/hta_loader.rs       — 119 líneas: loader con soporte .hta/.htax
runtime/hta.js          — 150 líneas: bridge JS con ActiveXObject y RPC via fetch/XHR
runtime/index.html      — Página standalone informativa
examples/               — 6 ejemplos: demo, test_full, test_maximized, test_dialogs, pkill, SimplesWebAPITesting
gen/schemas/            — Schemas de capabilities (4 archivos)
```

## Pruebas

```bash
cargo test              # Ejecuta tests unitarios del parser (4 tests)
cargo build             # Compila el proyecto
cargo run -- examples/demo.hta
```

## Roadmap / Progreso

- [x] Parser HTA:APPLICATION con 4 unit tests
- [x] Loader con soporte .hta y .htax (zip)
- [x] Servidor HTTP embebido (tiny_http)
- [x] Ventana winit + webview wry
- [x] Bridge JS con RPC via POST
- [x] Comandos: read_file, exec_command, get_config, quit
- [x] Diálogos nativos: open_file, save_file, open_dir (rfd)
- [x] ActiveX: FileSystemObject (20+ métodos)
- [x] ActiveX: WScript.Shell (Exec)
- [x] ActiveX: TextStream + WshScriptExec
- [x] WinHttpRequest polyfill vía XMLHttpRequest
- [ ] Single-instance enforcement
- [ ] Empaquetado (.msi / portable)
- [ ] Pruebas multiplataforma (Linux/macOS)
