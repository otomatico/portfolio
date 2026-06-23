use std::collections::hash_map::DefaultHasher;
use std::fs;
use std::hash::{Hash, Hasher};
use std::path::{Path, PathBuf};

fn inject_script(content: &str) -> String {
    let tag = "<script src=\"hta.js\"></script>";
    if let Some(pos) = content.find("</head>") {
        let (before, after) = content.split_at(pos);
        format!("{before}{tag}{after}")
    } else {
        format!("{tag}{content}")
    }
}

fn ensure_hta_js(serve_dir: &Path, hta_js: &str) -> Result<(), String> {
    let hta_path = serve_dir.join("hta.js");
    if !hta_path.exists() {
        fs::write(&hta_path, hta_js).map_err(|e| format!("Error escribiendo hta.js: {e}"))?;
    }
    Ok(())
}

pub fn load_hta(path: &str, hta_js: &str) -> Result<(String, PathBuf), String> {
    let p = Path::new(path);
    if !p.exists() {
        return Err(format!("Archivo no encontrado: {path}"));
    }

    let ext = p
        .extension()
        .and_then(|e| e.to_str())
        .map(|e| e.to_lowercase())
        .unwrap_or_default();

    if ext != "hta" && ext != "htax" {
        return Err(format!("Extensión no soportada: .{ext}. Use .hta o .htax"));
    }

    let serve_dir = create_temp_dir(path)?;
    ensure_hta_js(&serve_dir, hta_js)?;

    let (content, index_path) = match ext.as_str() {
        "hta" => {
            let content =
                fs::read_to_string(path).map_err(|e| format!("Error leyendo {path}: {e}"))?;
            (content.clone(), serve_dir.join("index.html"))
        }
        "htax" => {
            extract_zip(path, &serve_dir)?;
            let index_path = serve_dir.join("index.html");
            if !index_path.exists() {
                return Err("El archivo .htax debe contener un index.html".into());
            }
            let content = fs::read_to_string(&index_path)
                .map_err(|e| format!("Error leyendo index.html: {e}"))?;
            (content, index_path)
        }
        _ => unreachable!(),
    };

    let existing = fs::read_to_string(&index_path).unwrap_or_default();
    if !existing.contains("hta.js") {
        let modified = inject_script(&content);
        fs::write(&index_path, &modified)
            .map_err(|e| format!("Error escribiendo index.html: {e}"))?;
    }

    Ok((content, serve_dir))
}

fn create_temp_dir(path: &str) -> Result<PathBuf, String> {
    let mut hasher = DefaultHasher::new();
    path.hash(&mut hasher);
    let hash = format!("{:x}", hasher.finish());

    let dir = std::env::temp_dir().join(format!("hta-{hash}"));
    if dir.exists() {
        fs::remove_dir_all(&dir).ok();
    }
    fs::create_dir_all(&dir).map_err(|e| format!("Error creando directorio temporal: {e}"))?;

    Ok(dir)
}

fn extract_zip(path: &str, dest: &Path) -> Result<(), String> {
    use zip::ZipArchive;

    let file = fs::File::open(path).map_err(|e| format!("Error abriendo .htax: {e}"))?;
    let mut archive =
        ZipArchive::new(file).map_err(|e| format!("Error leyendo .htax: {e}"))?;

    for i in 0..archive.len() {
        let mut entry = archive
            .by_index(i)
            .map_err(|e| format!("Error en entrada {i}: {e}"))?;
        let raw_name = entry.name().to_string();
        let name = raw_name.replace('\\', "/");

        if name.contains("..") || name.starts_with('/') {
            continue;
        }

        let out_path = dest.join(&name);

        if entry.is_dir() {
            fs::create_dir_all(&out_path).ok();
        } else {
            if let Some(parent) = out_path.parent() {
                fs::create_dir_all(parent).ok();
            }
            let mut outfile =
                fs::File::create(&out_path).map_err(|e| format!("Error extrayendo {name}: {e}"))?;
            std::io::copy(&mut entry, &mut outfile)
                .map_err(|e| format!("Error extrayendo {name}: {e}"))?;
        }
    }

    Ok(())
}