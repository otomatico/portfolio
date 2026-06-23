use std::fs;

pub fn read_file(path: &str) -> Result<String, String> {
    fs::read_to_string(path).map_err(|e| format!("Error leyendo {path}: {e}"))
}
