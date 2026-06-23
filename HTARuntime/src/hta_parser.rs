use regex::Regex;
use serde::Serialize;
use std::sync::LazyLock;

#[derive(Debug, Clone, PartialEq, Serialize)]
#[serde(rename_all = "snake_case")]
pub enum BorderStyle {
    Thick,
    Dialog,
    None,
    Thin,
}

impl Default for BorderStyle {
    fn default() -> Self {
        BorderStyle::Thick
    }
}

#[derive(Debug, Clone, PartialEq, Serialize)]
#[serde(rename_all = "snake_case")]
pub enum WindowState {
    Normal,
    Minimize,
    Maximize,
}

impl Default for WindowState {
    fn default() -> Self {
        WindowState::Normal
    }
}

#[derive(Debug, Clone, Serialize)]
pub struct HtaConfig {
    pub application_name: String,
    pub border: BorderStyle,
    pub caption: bool,
    pub show_in_taskbar: bool,
    pub single_instance: bool,
    pub window_state: WindowState,
}

impl Default for HtaConfig {
    fn default() -> Self {
        HtaConfig {
            application_name: String::new(),
            border: BorderStyle::default(),
            caption: true,
            show_in_taskbar: true,
            single_instance: false,
            window_state: WindowState::default(),
        }
    }
}

fn parse_bool(val: &str) -> bool {
    matches!(val.to_lowercase().as_str(), "yes" | "true" | "1")
}

fn parse_border(val: &str) -> BorderStyle {
    match val.to_lowercase().as_str() {
        "dialog" => BorderStyle::Dialog,
        "none" => BorderStyle::None,
        "thin" => BorderStyle::Thin,
        _ => BorderStyle::Thick,
    }
}

fn parse_window_state(val: &str) -> WindowState {
    match val.to_lowercase().as_str() {
        "minimize" => WindowState::Minimize,
        "maximize" => WindowState::Maximize,
        _ => WindowState::Normal,
    }
}

static TAG_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"(?is)<HTA:APPLICATION\s+([^>]*)>").unwrap());
static ATTR_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r#"(?i)(\w+)\s*=\s*"([^"]*)"#).unwrap());

pub fn parse_config(content: &str) -> HtaConfig {
    let mut cfg = HtaConfig::default();

    if let Some(caps) = TAG_RE.captures(content) {
        let attrs_str = caps.get(1).map_or("", |m| m.as_str());

        for attr_cap in ATTR_RE.captures_iter(attrs_str) {
            let key = attr_cap.get(1).map_or("", |m| m.as_str());
            let val = attr_cap.get(2).map_or("", |m| m.as_str());

            match key.to_uppercase().as_str() {
                "APPLICATIONNAME" => cfg.application_name = val.to_string(),
                "BORDER" => cfg.border = parse_border(val),
                "CAPTION" => cfg.caption = parse_bool(val),
                "SHOWINTASKBAR" => cfg.show_in_taskbar = parse_bool(val),
                "SINGLEINSTANCE" => cfg.single_instance = parse_bool(val),
                "WINDOWSTATE" => cfg.window_state = parse_window_state(val),
                _ => {}
            }
        }
    }

    cfg
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn parse_all_attributes() {
        let html = r#"<html>
        <HTA:APPLICATION
            APPLICATIONNAME="Mi App"
            BORDER="dialog"
            CAPTION="no"
            SHOWINTASKBAR="false"
            SINGLEINSTANCE="yes"
            WINDOWSTATE="maximize"
        >
        </head>
        <body>Hola</body>
        </html>"#;
        let cfg = parse_config(html);
        assert_eq!(cfg.application_name, "Mi App");
        assert_eq!(cfg.border, BorderStyle::Dialog);
        assert!(!cfg.caption);
        assert!(!cfg.show_in_taskbar);
        assert!(cfg.single_instance);
        assert_eq!(cfg.window_state, WindowState::Maximize);
    }

    #[test]
    fn parse_minimal() {
        let html = r#"<html><HTA:APPLICATION APPLICATIONNAME="Test"></html>"#;
        let cfg = parse_config(html);
        assert_eq!(cfg.application_name, "Test");
        assert_eq!(cfg.border, BorderStyle::Thick);
        assert!(cfg.caption);
    }

    #[test]
    fn parse_no_tag() {
        let html = "<html><body>No HTA tag</body></html>";
        let cfg = parse_config(html);
        assert_eq!(cfg.application_name, "");
        assert!(cfg.caption);
    }

    #[test]
    fn parse_case_insensitive() {
        let html = r#"<html><hta:application border="NONE" windowstate="MINIMIZE"></html>"#;
        let cfg = parse_config(html);
        assert_eq!(cfg.border, BorderStyle::None);
        assert_eq!(cfg.window_state, WindowState::Minimize);
    }
}