use std::collections::HashMap;
use std::fs;
use std::path::Path;
use std::process::{Command, Stdio};
use std::sync::atomic::{AtomicU64, Ordering};
use std::sync::Mutex;
use std::time::{SystemTime, UNIX_EPOCH};

static AX_INSTANCES: std::sync::OnceLock<Mutex<HashMap<u64, AxInstance>>> =
    std::sync::OnceLock::new();
static AX_NEXT_ID: AtomicU64 = AtomicU64::new(1);

struct AxInstance {
    prog_id: String,
    text_stream: Option<TextStreamState>,
    wsh_exec: Option<WshScriptExec>,
}

struct WshScriptExec {
    stdout_stream_id: u64,
    stderr_stream_id: u64,
    exit_code: i32,
}

struct TextStreamState {
    path: String,
    mode: u32,
    buffer: String,
    position: usize,
    closed: bool,
}

fn ax_instances() -> &'static Mutex<HashMap<u64, AxInstance>> {
    AX_INSTANCES.get_or_init(|| Mutex::new(HashMap::new()))
}

fn arg_str(args: &[serde_json::Value], idx: usize) -> Result<String, String> {
    args.get(idx)
        .and_then(|v| v.as_str())
        .map(|s| s.to_string())
        .ok_or_else(|| format!("Argument {idx} missing or not a string"))
}

fn arg_bool(args: &[serde_json::Value], idx: usize, default: bool) -> bool {
    args.get(idx)
        .and_then(|v| v.as_bool())
        .unwrap_or(default)
}

fn arg_i64(args: &[serde_json::Value], idx: usize, default: i64) -> i64 {
    args.get(idx)
        .and_then(|v| v.as_i64())
        .unwrap_or(default)
}

fn ok_value(val: serde_json::Value) -> serde_json::Value {
    serde_json::json!({"ok": true, "result": val})
}

fn ok_instance(id: u64, prog_id: &str) -> serde_json::Value {
    serde_json::json!({"ok": true, "result": id, "resultType": "instance", "progID": prog_id})
}

pub fn ax_create(prog_id: &str) -> Result<serde_json::Value, String> {
    let id = AX_NEXT_ID.fetch_add(1, Ordering::SeqCst);
    let mut map = ax_instances().lock().unwrap();

    match prog_id.to_uppercase().as_str() {
        "SCRIPTING.FILESYSTEMOBJECT" | "SCRIPTING.FILESYSTEMOBJECT.1" => {
            map.insert(
                id,
                AxInstance { prog_id: "Scripting.FileSystemObject".into(), text_stream: None, wsh_exec: None },
            );
            Ok(serde_json::json!({"ok": true, "result": id}))
        }
        "WSCRIPT.SHELL" | "WSCRIPT.SHELL.1" => {
            map.insert(
                id,
                AxInstance { prog_id: "WScript.Shell".into(), text_stream: None, wsh_exec: None },
            );
            Ok(serde_json::json!({"ok": true, "result": id}))
        }
        _ => Err(format!("ActiveX no soportado: {prog_id}")),
    }
}

pub fn ax_call(
    id: u64,
    name: &str,
    args: &[serde_json::Value],
) -> Result<serde_json::Value, String> {
    let mut map = ax_instances().lock().unwrap();
    let inst = map
        .get_mut(&id)
        .ok_or_else(|| format!("Invalid ActiveX instance: {id}"))?;

    match inst.prog_id.to_uppercase().as_str() {
        "SCRIPTING.FILESYSTEMOBJECT" => fso_call(name, args, &mut map),
        "TEXTSTREAM" => {
            if let Some(ref mut ts) = inst.text_stream {
                textstream_call(ts, name, args)
            } else {
                Err("TextStream state not initialized".into())
            }
        }
        "WSCRIPT.SHELL" => wshshell_call(name, args, &mut map),
        _ => Err(format!("Unknown instance type: {}", inst.prog_id)),
    }
}

pub fn ax_get(id: u64, prop: &str) -> Result<serde_json::Value, String> {
    let map = ax_instances().lock().unwrap();
    let inst = map
        .get(&id)
        .ok_or_else(|| format!("Invalid ActiveX instance: {id}"))?;

    match inst.prog_id.to_uppercase().as_str() {
        "TEXTSTREAM" => {
            if let Some(ref ts) = inst.text_stream {
                textstream_get(ts, prop)
            } else {
                Err("TextStream state not initialized".into())
            }
        }
        "WSHSCRIPTEXEC" => wsh_exec_get(inst, prop),
        "WSCRIPT.SHELL" => match prop {
            "CurrentDirectory" => Ok(ok_value(serde_json::Value::String(
                std::env::current_dir()
                    .unwrap_or_default()
                    .to_string_lossy()
                    .to_string(),
            ))),
            _ => Err(format!("WshShell property not supported: {prop}")),
        },
        _ => Err(format!("Properties not supported for: {}", inst.prog_id)),
    }
}

pub fn ax_release(id: u64) {
    let mut map = ax_instances().lock().unwrap();
    map.remove(&id);
}

fn fso_call(
    method: &str,
    args: &[serde_json::Value],
    instances: &mut HashMap<u64, AxInstance>,
) -> Result<serde_json::Value, String> {
    match method {
        "FileExists" => {
            let path = arg_str(args, 0)?;
            Ok(ok_value(serde_json::Value::Bool(Path::new(&path).exists())))
        }
        "FolderExists" => {
            let path = arg_str(args, 0)?;
            Ok(ok_value(serde_json::Value::Bool(Path::new(&path).is_dir())))
        }
        "DriveExists" => {
            let path = arg_str(args, 0)?;
            Ok(ok_value(serde_json::Value::Bool(Path::new(&path).exists())))
        }
        "CopyFile" => {
            let src = arg_str(args, 0)?;
            let dst = arg_str(args, 1)?;
            let overwrite = arg_bool(args, 2, true);
            if !overwrite && Path::new(&dst).exists() {
                return Err("File already exists".into());
            }
            fs::copy(&src, &dst).map_err(|e| format!("CopyFile: {e}"))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "CopyFolder" => {
            let src = arg_str(args, 0)?;
            let dst = arg_str(args, 1)?;
            copy_dir_recursive(Path::new(&src), Path::new(&dst))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "DeleteFile" => {
            let path = arg_str(args, 0)?;
            fs::remove_file(&path).map_err(|e| format!("DeleteFile: {e}"))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "DeleteFolder" => {
            let path = arg_str(args, 0)?;
            fs::remove_dir_all(&path).map_err(|e| format!("DeleteFolder: {e}"))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "MoveFile" => {
            let src = arg_str(args, 0)?;
            let dst = arg_str(args, 1)?;
            fs::rename(&src, &dst).map_err(|e| format!("MoveFile: {e}"))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "MoveFolder" => {
            let src = arg_str(args, 0)?;
            let dst = arg_str(args, 1)?;
            fs::rename(&src, &dst).map_err(|e| format!("MoveFolder: {e}"))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "CreateFolder" => {
            let path = arg_str(args, 0)?;
            fs::create_dir_all(&path).map_err(|e| format!("CreateFolder: {e}"))?;
            Ok(serde_json::json!({"ok": true}))
        }
        "GetTempName" => {
            let ts = SystemTime::now()
                .duration_since(UNIX_EPOCH)
                .unwrap_or_default()
                .as_nanos();
            let name = format!("HTA_{}_{}.tmp", std::process::id(), ts);
            Ok(ok_value(serde_json::Value::String(name)))
        }
        "GetAbsolutePathName" => {
            let path = arg_str(args, 0)?;
            let cwd = std::env::current_dir().unwrap_or_default();
            let abs = cwd.join(&path);
            Ok(ok_value(serde_json::Value::String(
                abs.to_string_lossy().to_string(),
            )))
        }
        "GetFileName" => {
            let path = arg_str(args, 0)?;
            let name = Path::new(&path)
                .file_name()
                .map(|s| s.to_string_lossy().to_string())
                .unwrap_or_default();
            Ok(ok_value(serde_json::Value::String(name)))
        }
        "GetExtensionName" => {
            let path = arg_str(args, 0)?;
            let ext = Path::new(&path)
                .extension()
                .map(|s| s.to_string_lossy().to_string())
                .unwrap_or_default();
            Ok(ok_value(serde_json::Value::String(ext)))
        }
        "GetBaseName" => {
            let path = arg_str(args, 0)?;
            let stem = Path::new(&path)
                .file_stem()
                .map(|s| s.to_string_lossy().to_string())
                .unwrap_or_default();
            Ok(ok_value(serde_json::Value::String(stem)))
        }
        "GetParentFolderName" => {
            let path = arg_str(args, 0)?;
            let parent = Path::new(&path)
                .parent()
                .map(|p| p.to_string_lossy().to_string())
                .unwrap_or_default();
            Ok(ok_value(serde_json::Value::String(parent)))
        }
        "BuildPath" => {
            let path = arg_str(args, 0)?;
            let name = arg_str(args, 1)?;
            let combined = Path::new(&path).join(&name);
            Ok(ok_value(serde_json::Value::String(
                combined.to_string_lossy().to_string(),
            )))
        }
        "GetSpecialFolder" => {
            let id = arg_i64(args, 0, 0);
            let p = match id {
                0 => std::env::current_dir().unwrap_or_default(),
                1 => Path::new("/usr/share").to_path_buf(),
                2 => std::env::temp_dir(),
                _ => return Err("Invalid SpecialFolder ID".into()),
            };
            Ok(ok_value(serde_json::Value::String(
                p.to_string_lossy().to_string(),
            )))
        }
        "GetFile" => {
            let path = arg_str(args, 0)?;
            let meta = fs::metadata(&path).map_err(|e| format!("GetFile: {e}"))?;
            let name = Path::new(&path)
                .file_name()
                .map(|s| s.to_string_lossy().to_string())
                .unwrap_or_default();
            let file_type = if meta.is_dir() { "Folder" } else { "File" };
            Ok(ok_value(serde_json::json!({
                "Path": path,
                "Name": name,
                "Size": meta.len(),
                "Type": file_type,
                "DateCreated": meta.created().ok().map(|t| format!("{:?}", t)),
                "DateLastModified": meta.modified().ok().map(|t| format!("{:?}", t)),
                "DateLastAccessed": meta.accessed().ok().map(|t| format!("{:?}", t)),
                "Attributes": meta.permissions().readonly() as i64
            })))
        }
        "GetFolder" => {
            let path = arg_str(args, 0)?;
            let meta = fs::metadata(&path).map_err(|e| format!("GetFolder: {e}"))?;
            let name = Path::new(&path)
                .file_name()
                .map(|s| s.to_string_lossy().to_string())
                .unwrap_or_default();
            Ok(ok_value(serde_json::json!({
                "Path": path,
                "Name": name,
                "Size": meta.len(),
                "Type": "Folder",
                "DateCreated": meta.created().ok().map(|t| format!("{:?}", t)),
                "DateLastModified": meta.modified().ok().map(|t| format!("{:?}", t)),
                "IsRootFolder": Path::new(&path).parent().is_none(),
            })))
        }
        "GetDrive" => {
            let name = arg_str(args, 0)?;
            Ok(ok_value(serde_json::json!({
                "DriveLetter": name,
                "Path": name,
                "IsReady": true,
                "DriveType": 2,
            })))
        }
        "GetDriveName" => {
            let path = arg_str(args, 0)?;
            let drive = Path::new(&path)
                .components()
                .next()
                .map(|c| format!("{:?}", c))
                .unwrap_or_default();
            Ok(ok_value(serde_json::Value::String(drive)))
        }
        "CreateTextFile" => {
            let path = arg_str(args, 0)?;
            let overwrite = arg_bool(args, 1, false);
            let _unicode = arg_bool(args, 2, false);

            if !overwrite && Path::new(&path).exists() {
                return Err("File already exists".into());
            }

            if let Some(parent) = Path::new(&path).parent() {
                fs::create_dir_all(parent).ok();
            }

            let id = AX_NEXT_ID.fetch_add(1, Ordering::SeqCst);
            let stream = TextStreamState {
                path: path.clone(),
                mode: 2,
                buffer: String::new(),
                position: 0,
                closed: false,
            };
            instances.insert(
                id,
                AxInstance { prog_id: "TextStream".into(), text_stream: Some(stream), wsh_exec: None },
            );

            Ok(ok_instance(id, "TextStream"))
        }
        "OpenTextFile" => {
            let path = arg_str(args, 0)?;
            let mode = arg_i64(args, 1, 1) as u32;
            let create = arg_bool(args, 2, false);
            let _format_val = arg_i64(args, 3, -1);

            if !Path::new(&path).exists() {
                if create {
                    if let Some(parent) = Path::new(&path).parent() {
                        fs::create_dir_all(parent).ok();
                    }
                    fs::write(&path, "").map_err(|e| format!("OpenTextFile: {e}"))?;
                } else {
                    return Err("File not found".into());
                }
            }

            let buffer = if mode == 2 {
                String::new()
            } else {
                fs::read_to_string(&path).unwrap_or_default()
            };

            let id = AX_NEXT_ID.fetch_add(1, Ordering::SeqCst);
            let stream = TextStreamState {
                path: path.clone(),
                mode,
                buffer,
                position: 0,
                closed: false,
            };
            instances.insert(
                id,
                AxInstance { prog_id: "TextStream".into(), text_stream: Some(stream), wsh_exec: None },
            );

            Ok(ok_instance(id, "TextStream"))
        }
        _ => Err(format!("FSO method not implemented: {method}")),
    }
}

fn extract_powershell_script(rest: &str) -> String {
    let mut s = rest.trim();
    if let Some(tail) = s.strip_prefix("-NoProfile") {
        s = tail.trim();
    }
    if let Some(tail) = s.strip_prefix("-Command") {
        s = tail.trim();
    }
    if let Some(tail) = s.strip_prefix("-c") {
        s = tail.trim();
    }
    s.trim_matches('"').to_string()
}

fn wshshell_call(
    method: &str,
    args: &[serde_json::Value],
    instances: &mut HashMap<u64, AxInstance>,
) -> Result<serde_json::Value, String> {
    match method {
        "Exec" => {
            let cmd = arg_str(args, 0)?;
            let trimmed = cmd.trim();
            let output = if cfg!(target_os = "windows") {
                if let Some(rest) = trimmed.strip_prefix("powershell ") {
                    let script = extract_powershell_script(rest);
                    Command::new("powershell.exe")
                        .args(["-NoProfile", "-Command", &script])
                        .stdout(Stdio::piped())
                        .stderr(Stdio::piped())
                        .output()
                } else if let Some(rest) = trimmed.strip_prefix("pwsh ") {
                    let script = extract_powershell_script(rest);
                    Command::new("pwsh.exe")
                        .args(["-NoProfile", "-Command", &script])
                        .stdout(Stdio::piped())
                        .stderr(Stdio::piped())
                        .output()
                } else {
                    Command::new("cmd")
                        .args(["/C", &cmd])
                        .stdout(Stdio::piped())
                        .stderr(Stdio::piped())
                        .output()
                }
            } else {
                Command::new("sh")
                    .args(["-c", &cmd])
                    .stdout(Stdio::piped())
                    .stderr(Stdio::piped())
                    .output()
            };
            let output = output.map_err(|e| format!("Exec: {e}"))?;

            let stdout = String::from_utf8_lossy(&output.stdout).to_string();
            let stderr = String::from_utf8_lossy(&output.stderr).to_string();

            let stdout_id = AX_NEXT_ID.fetch_add(1, Ordering::SeqCst);
            instances.insert(
                stdout_id,
                AxInstance {
                    prog_id: "TextStream".into(),
                    text_stream: Some(TextStreamState {
                        path: String::new(),
                        mode: 1,
                        buffer: stdout,
                        position: 0,
                        closed: false,
                    }),
                    wsh_exec: None,
                },
            );

            let stderr_id = AX_NEXT_ID.fetch_add(1, Ordering::SeqCst);
            instances.insert(
                stderr_id,
                AxInstance {
                    prog_id: "TextStream".into(),
                    text_stream: Some(TextStreamState {
                        path: String::new(),
                        mode: 1,
                        buffer: stderr,
                        position: 0,
                        closed: false,
                    }),
                    wsh_exec: None,
                },
            );

            let exec_id = AX_NEXT_ID.fetch_add(1, Ordering::SeqCst);
            instances.insert(
                exec_id,
                AxInstance {
                    prog_id: "WshScriptExec".into(),
                    text_stream: None,
                    wsh_exec: Some(WshScriptExec {
                        stdout_stream_id: stdout_id,
                        stderr_stream_id: stderr_id,
                        exit_code: output.status.code().unwrap_or(-1),
                    }),
                },
            );

            Ok(ok_instance(exec_id, "WshScriptExec"))
        }
        _ => Err(format!("WshShell method not implemented: {method}")),
    }
}

fn wsh_exec_get(inst: &AxInstance, prop: &str) -> Result<serde_json::Value, String> {
    let exec = inst
        .wsh_exec
        .as_ref()
        .ok_or("WshScriptExec state not initialized")?;
    match prop {
        "StdOut" => Ok(ok_instance(exec.stdout_stream_id, "TextStream")),
        "StdErr" => Ok(ok_instance(exec.stderr_stream_id, "TextStream")),
        "ExitCode" => Ok(ok_value(serde_json::Value::Number(
            serde_json::Number::from(exec.exit_code),
        ))),
        _ => Err(format!("WshScriptExec property not supported: {prop}")),
    }
}

fn textstream_call(
    ts: &mut TextStreamState,
    method: &str,
    args: &[serde_json::Value],
) -> Result<serde_json::Value, String> {
    if ts.closed && method != "Close" {
        return Err("TextStream is closed".into());
    }

    match method {
        "Read" => {
            let n = arg_i64(args, 0, 0) as usize;
            let result: String = ts.buffer.chars().skip(ts.position).take(n).collect();
            ts.position += result.len();
            Ok(ok_value(serde_json::Value::String(result)))
        }
        "ReadAll" => {
            let result = ts.buffer[ts.position..].to_string();
            ts.position = ts.buffer.len();
            Ok(ok_value(serde_json::Value::String(result)))
        }
        "ReadLine" => {
            let rest = &ts.buffer[ts.position..];
            if let Some(pos) = rest.find('\n') {
                let line = rest[..pos].trim_end_matches('\r').to_string();
                ts.position += pos + 1;
                Ok(ok_value(serde_json::Value::String(line)))
            } else {
                let line = rest.to_string();
                ts.position = ts.buffer.len();
                Ok(ok_value(serde_json::Value::String(line)))
            }
        }
        "Write" => {
            let text = arg_str(args, 0)?;
            ts.buffer.push_str(&text);
            Ok(serde_json::json!({"ok": true}))
        }
        "WriteLine" => {
            let text = args.get(0).and_then(|v| v.as_str()).unwrap_or("");
            ts.buffer.push_str(text);
            ts.buffer.push('\n');
            Ok(serde_json::json!({"ok": true}))
        }
        "WriteBlankLines" => {
            let n = arg_i64(args, 0, 0);
            for _ in 0..n {
                ts.buffer.push('\n');
            }
            Ok(serde_json::json!({"ok": true}))
        }
        "Skip" => {
            let n = arg_i64(args, 0, 0) as usize;
            ts.position = std::cmp::min(ts.position + n, ts.buffer.len());
            Ok(serde_json::json!({"ok": true}))
        }
        "SkipLine" => {
            let rest = &ts.buffer[ts.position..];
            if let Some(pos) = rest.find('\n') {
                ts.position += pos + 1;
            } else {
                ts.position = ts.buffer.len();
            }
            Ok(serde_json::json!({"ok": true}))
        }
        "Close" => {
            if !ts.closed && ts.mode != 1 {
                if let Some(parent) = Path::new(&ts.path).parent() {
                    fs::create_dir_all(parent).ok();
                }
                fs::write(&ts.path, &ts.buffer)
                    .map_err(|e| format!("TextStream.Close: {e}"))?;
            }
            ts.closed = true;
            Ok(serde_json::json!({"ok": true}))
        }
        _ => Err(format!("TextStream method not implemented: {method}")),
    }
}

fn textstream_get(ts: &TextStreamState, prop: &str) -> Result<serde_json::Value, String> {
    match prop {
        "AtEndOfStream" => Ok(ok_value(serde_json::Value::Bool(
            ts.position >= ts.buffer.len(),
        ))),
        "Line" => {
            let line_count = ts.buffer[..ts.position]
                .chars()
                .filter(|&c| c == '\n')
                .count()
                + 1;
            Ok(ok_value(serde_json::Value::Number(
                serde_json::Number::from(line_count as u64),
            )))
        }
        "Column" => {
            let last_newline = ts.buffer[..ts.position].rfind('\n').unwrap_or(0);
            let col = ts.position - last_newline;
            Ok(ok_value(serde_json::Value::Number(
                serde_json::Number::from(col as u64),
            )))
        }
        _ => Err(format!("TextStream property not found: {prop}")),
    }
}

fn copy_dir_recursive(src: &Path, dst: &Path) -> Result<(), String> {
    if src.is_dir() {
        fs::create_dir_all(dst).map_err(|e| format!("CopyFolder: {e}"))?;
        for entry in fs::read_dir(src).map_err(|e| format!("CopyFolder: {e}"))? {
            let entry = entry.map_err(|e| format!("CopyFolder: {e}"))?;
            let file_type = entry.file_type().map_err(|e| format!("CopyFolder: {e}"))?;
            let src_path = entry.path();
            let dst_path = dst.join(entry.file_name());
            if file_type.is_dir() {
                copy_dir_recursive(&src_path, &dst_path)?;
            } else {
                fs::copy(&src_path, &dst_path)
                    .map_err(|e| format!("CopyFolder: {e}"))?;
            }
        }
    }
    Ok(())
}
