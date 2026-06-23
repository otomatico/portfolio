mod hta_loader;
mod hta_parser;
mod bridge;
mod server;

use std::path::PathBuf;
use std::sync::OnceLock;

use hta_parser::{HtaConfig, WindowState};
use winit::dpi::LogicalSize;
use winit::event_loop::{EventLoop, EventLoopProxy};
use winit::window::Window;
use wry::{WebViewAttributes, WebViewBuilder};

static HTA_CFG: OnceLock<HtaConfig> = OnceLock::new();
static QUIT_PROXY: OnceLock<EventLoopProxy<HtaEvent>> = OnceLock::new();

enum HtaEvent {
    Quit,
}

fn main() -> wry::Result<()> {
    let args: Vec<String> = std::env::args().collect();

    let event_loop = EventLoop::with_user_event().build().unwrap();
    let proxy = event_loop.create_proxy();
    let _ = QUIT_PROXY.set(proxy);

    let (url, title, decorated, window_state) = if args.len() > 1 {
        let path = &args[1];
        let hta_js = include_str!("../runtime/hta.js");

        match hta_loader::load_hta(path, hta_js) {
            Ok((content, serve_dir)) => {
                let cfg = hta_parser::parse_config(&content);
                let name = if cfg.application_name.is_empty() {
                    "HTA Application"
                } else {
                    &cfg.application_name
                };
                println!("[INFO] Aplicación HTA cargada: {name}");
                let url = server::start_server(serve_dir)?;
                let _ = HTA_CFG.set(cfg.clone());
                (
                    url,
                    name.to_string(),
                    cfg.caption,
                    Some(cfg.window_state),
                )
            }
            Err(e) => {
                eprintln!("[ERROR] {e}");
                std::process::exit(1);
            }
        }
    } else {
        println!("[INFO] No HTA file specified");
        println!("Usage: hta-runtime.exe <file.hta | file.htax>");
        let serve_dir = create_standalone_dir()?;
        let url = server::start_server(serve_dir)?;
        (url, "HTA Runtime".to_string(), true, None)
    };

    run_window(event_loop, &url, &title, decorated, window_state)
}

fn create_standalone_dir() -> Result<PathBuf, wry::Error> {
    let dir = std::env::temp_dir().join(format!("hta-standalone-{}", std::process::id()));
    std::fs::create_dir_all(&dir).ok();
    let index = include_str!("../runtime/index.html");
    let hta_js = include_str!("../runtime/hta.js");
    std::fs::write(dir.join("index.html"), index).ok();
    std::fs::write(dir.join("hta.js"), hta_js).ok();
    Ok(dir)
}

fn run_window(
    event_loop: EventLoop<HtaEvent>,
    url: &str,
    title: &str,
    decorated: bool,
    window_state: Option<WindowState>,
) -> wry::Result<()> {
    let window_attrs = Window::default_attributes()
        .with_title(title)
        .with_inner_size(LogicalSize::new(800.0, 600.0))
        .with_decorations(decorated);
    #[allow(deprecated)]
    let window = event_loop.create_window(window_attrs).unwrap();

    if let Some(state) = window_state {
        match state {
            WindowState::Maximize => window.set_maximized(true),
            WindowState::Minimize => window.set_minimized(true),
            WindowState::Normal => {}
        }
    }

    let mut attrs = WebViewAttributes::default();
    attrs.url = Some(url.into());

    let builder = WebViewBuilder::with_attributes(attrs);
    let _webview = builder.build(&window)?;

    #[allow(deprecated)]
    let _ = event_loop.run(move |event, elwt| {
        elwt.set_control_flow(winit::event_loop::ControlFlow::Wait);
        match event {
            winit::event::Event::UserEvent(HtaEvent::Quit) => elwt.exit(),
            winit::event::Event::WindowEvent {
                event: winit::event::WindowEvent::CloseRequested,
                ..
            } => elwt.exit(),
            _ => {}
        }
    });

    Ok(())
}

pub fn handle_rpc(command: &str, data: &serde_json::Value) -> serde_json::Value {
    match command {
        "quit" => {
            if let Some(proxy) = QUIT_PROXY.get() {
                let _ = proxy.send_event(HtaEvent::Quit);
            }
            serde_json::json!({"ok": true})
        }
        "get_config" => {
            let cfg = HTA_CFG
                .get()
                .map(serde_json::to_value)
                .unwrap_or(Ok(serde_json::Value::Null));
            match cfg {
                Ok(val) => serde_json::json!({"ok": true, "config": val}),
                Err(_) => serde_json::json!({"ok": false, "error": "Serialization failed"}),
            }
        }
        "read_file" => {
            let path = data.get("path").and_then(|v| v.as_str()).unwrap_or("");
            match bridge::read_file(path) {
                Ok(content) => serde_json::json!({"ok": true, "content": content}),
                Err(e) => serde_json::json!({"ok": false, "error": e}),
            }
        }
        "exec_command" => {
            let cmd = data.get("command").and_then(|v| v.as_str()).unwrap_or("");
            match bridge::exec_command(cmd) {
                Ok(output) => serde_json::json!({"ok": true, "output": output}),
                Err(e) => serde_json::json!({"ok": false, "error": e}),
            }
        }
        "open_file" => match bridge::open_file(data) {
            Ok(path) => serde_json::json!({"ok": true, "path": path}),
            Err(e) => serde_json::json!({"ok": false, "error": e}),
        },
        "save_file" => match bridge::save_file(data) {
            Ok(path) => serde_json::json!({"ok": true, "path": path}),
            Err(e) => serde_json::json!({"ok": false, "error": e}),
        },
        "open_dir" => match bridge::open_dir(data) {
            Ok(path) => serde_json::json!({"ok": true, "path": path}),
            Err(e) => serde_json::json!({"ok": false, "error": e}),
        },
        "ax_create" => {
            let prog_id = data.get("progID").and_then(|v| v.as_str()).unwrap_or("");
            bridge::ax_create(prog_id)
                .unwrap_or_else(|e| serde_json::json!({"ok": false, "error": e}))
        }
        "ax_call" => {
            let id = data.get("id").and_then(|v| v.as_u64()).unwrap_or(0);
            let name = data.get("name").and_then(|v| v.as_str()).unwrap_or("");
            let args = data
                .get("args")
                .and_then(|v| v.as_array())
                .cloned()
                .unwrap_or_default();
            bridge::ax_call(id, name, &args)
                .unwrap_or_else(|e| serde_json::json!({"ok": false, "error": e}))
        }
        "ax_get" => {
            let id = data.get("id").and_then(|v| v.as_u64()).unwrap_or(0);
            let prop = data.get("prop").and_then(|v| v.as_str()).unwrap_or("");
            bridge::ax_get(id, prop)
                .unwrap_or_else(|e| serde_json::json!({"ok": false, "error": e}))
        }
        "ax_release" => {
            let id = data.get("id").and_then(|v| v.as_u64()).unwrap_or(0);
            bridge::ax_release(id);
            serde_json::json!({"ok": true})
        }
        _ => serde_json::json!({"ok": false, "error": format!("Unknown command: {command}")}),
    }
}
