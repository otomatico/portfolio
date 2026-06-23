use std::process::{Command, Stdio};

pub fn exec_command(command: &str) -> Result<String, String> {
    let (program, args) = resolve_command(command);
    let output = Command::new(program)
        .args(&args)
        .stdout(Stdio::piped())
        .stderr(Stdio::piped())
        .output()
        .map_err(|e| format!("Error al ejecutar comando: {e}"))?;
    if output.status.success() {
        Ok(String::from_utf8_lossy(&output.stdout).to_string())
    } else {
        Err(String::from_utf8_lossy(&output.stderr).to_string())
    }
}

#[cfg(target_os = "windows")]
fn resolve_command(command: &str) -> (&str, Vec<&str>) {
    let trimmed = command.trim();
    if let Some(rest) = trimmed.strip_prefix("powershell ") {
        let cmd = rest.trim().trim_matches('"');
        ("powershell.exe", vec!["-NoProfile", "-Command", cmd])
    } else if let Some(rest) = trimmed.strip_prefix("pwsh ") {
        let cmd = rest.trim().trim_matches('"');
        ("pwsh.exe", vec!["-NoProfile", "-Command", cmd])
    } else {
        ("cmd", vec!["/C", command])
    }
}

#[cfg(not(target_os = "windows"))]
fn resolve_command(command: &str) -> (&str, Vec<&str>) {
    ("sh", vec!["-c", command])
}
