pub fn open_file(data: &serde_json::Value) -> Result<String, String> {
    let mut dialog = rfd::FileDialog::new();
    if let Some(title) = data.get("title").and_then(|v| v.as_str()) {
        dialog = dialog.set_title(title);
    }
    dialog = parse_dialog_filters(dialog, data);
    dialog
        .pick_file()
        .map(|p| p.to_string_lossy().to_string())
        .ok_or_else(|| "User cancelled".into())
}

pub fn save_file(data: &serde_json::Value) -> Result<String, String> {
    let mut dialog = rfd::FileDialog::new();
    if let Some(title) = data.get("title").and_then(|v| v.as_str()) {
        dialog = dialog.set_title(title);
    }
    dialog = parse_dialog_filters(dialog, data);
    dialog
        .save_file()
        .map(|p| p.to_string_lossy().to_string())
        .ok_or_else(|| "User cancelled".into())
}

pub fn open_dir(data: &serde_json::Value) -> Result<String, String> {
    let mut dialog = rfd::FileDialog::new();
    if let Some(title) = data.get("title").and_then(|v| v.as_str()) {
        dialog = dialog.set_title(title);
    }
    dialog
        .pick_folder()
        .map(|p| p.to_string_lossy().to_string())
        .ok_or_else(|| "User cancelled".into())
}

fn parse_dialog_filters(
    mut dialog: rfd::FileDialog,
    data: &serde_json::Value,
) -> rfd::FileDialog {
    if let Some(filters) = data.get("filters").and_then(|v| v.as_array()) {
        for f in filters {
            let name = f.get("name").and_then(|v| v.as_str()).unwrap_or("");
            let exts: Vec<&str> = f
                .get("extensions")
                .and_then(|v| v.as_array())
                .map(|a| a.iter().filter_map(|e| e.as_str()).collect())
                .unwrap_or_default();
            if !exts.is_empty() {
                dialog = dialog.add_filter(name, &exts);
            }
        }
    }
    dialog
}
