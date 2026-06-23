use std::fs;
use std::io::Cursor;
use std::path::PathBuf;

use tiny_http::{Response, StatusCode};

pub fn start_server(serve_dir: PathBuf) -> Result<String, wry::Error> {
    let server = tiny_http::Server::http("127.0.0.1:0").unwrap_or_else(|e| {
        eprintln!("[ERROR] No se pudo iniciar el servidor HTTP: {e}");
        std::process::exit(1);
    });
    let port = server.server_addr().to_ip().unwrap().port();
    let url = format!("http://127.0.0.1:{port}/index.html");

    let _server_thread = std::thread::spawn(move || {
        for mut request in server.incoming_requests() {
            let url_path = request.url().to_string();
            let is_post = request.method() == &tiny_http::Method::Post;

            let response = if is_post && url_path == "/rpc" {
                serve_rpc_request(&mut request)
            } else {
                serve_static_file(&serve_dir, &url_path)
            };

            if let Err(e) = request.respond(response) {
                eprintln!("[HTTP] Error al responder: {e}");
            }
        }
    });

    Ok(url)
}

fn serve_rpc_request(
    request: &mut tiny_http::Request,
) -> tiny_http::Response<Cursor<Vec<u8>>> {
    let mut body = String::new();
    request
        .as_reader()
        .read_to_string(&mut body)
        .unwrap_or_default();

    let result = if let Ok(msg) = serde_json::from_str::<serde_json::Value>(&body) {
        let cmd = msg["cmd"].as_str().unwrap_or("").to_string();
        let data = msg.get("data").cloned().unwrap_or(serde_json::Value::Null);
        eprintln!("[HTTP] RPC cmd={:?} data={:?}", cmd, data);
        let res = super::handle_rpc(&cmd, &data);
        eprintln!("[HTTP] RPC response={:?}", res);
        res
    } else {
        eprintln!("[HTTP] RPC invalid json: {:?}", body);
        serde_json::json!({"ok": false, "error": "invalid json"})
    };

    let json = serde_json::to_string(&result).unwrap_or_default();
    let data = json.into_bytes();
    let len = data.len();

    Response::new(
        StatusCode(200),
        vec![
            "Content-Type: application/json"
                .parse::<tiny_http::Header>()
                .unwrap(),
        ],
        Cursor::new(data),
        Some(len),
        None,
    )
}

fn serve_static_file(
    serve_dir: &PathBuf,
    url_path: &str,
) -> tiny_http::Response<Cursor<Vec<u8>>> {
    let clean_path = url_path.trim_start_matches('/');
    let relative = if clean_path.is_empty() || clean_path == "/" {
        "index.html"
    } else {
        clean_path
    };

    let file_path = serve_dir.join(relative);

    if !file_path.starts_with(serve_dir) || file_path.is_dir() {
        return not_found();
    }

    match fs::read(&file_path) {
        Ok(data) => {
            let ext = file_path
                .extension()
                .and_then(|e| e.to_str())
                .unwrap_or("");
            let mime = mime_type(ext);
            let len = data.len();

            let headers = vec![
                format!("Content-Type: {mime}")
                    .parse::<tiny_http::Header>()
                    .unwrap(),
                "Cache-Control: no-cache"
                    .parse::<tiny_http::Header>()
                    .unwrap(),
            ];

            Response::new(StatusCode(200), headers, Cursor::new(data), Some(len), None)
        }
        Err(_) => not_found(),
    }
}

fn not_found() -> tiny_http::Response<Cursor<Vec<u8>>> {
    let data = b"404 Not Found".to_vec();
    let len = data.len();
    Response::new(
        StatusCode(404),
        vec![
            "Content-Type: text/plain"
                .parse::<tiny_http::Header>()
                .unwrap(),
        ],
        Cursor::new(data),
        Some(len),
        None,
    )
}

fn mime_type(ext: &str) -> &'static str {
    match ext.to_lowercase().as_str() {
        "html" | "htm" => "text/html; charset=utf-8",
        "css" => "text/css; charset=utf-8",
        "js" => "application/javascript; charset=utf-8",
        "json" => "application/json",
        "png" => "image/png",
        "jpg" | "jpeg" => "image/jpeg",
        "gif" => "image/gif",
        "svg" | "svgz" => "image/svg+xml",
        "ico" => "image/x-icon",
        "webp" => "image/webp",
        "woff" => "font/woff",
        "woff2" => "font/woff2",
        "ttf" => "font/ttf",
        "eot" => "application/vnd.ms-fontobject",
        "otf" => "font/otf",
        "pdf" => "application/pdf",
        "zip" => "application/zip",
        "txt" => "text/plain; charset=utf-8",
        "xml" => "application/xml",
        "wasm" => "application/wasm",
        _ => "application/octet-stream",
    }
}
