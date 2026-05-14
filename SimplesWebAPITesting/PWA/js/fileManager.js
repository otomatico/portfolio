// ======================
// OBJETO PARA MANEJAR ARCHIVOS (Configuración)
// ======================
const FileManager = {
    // Abre el diálogo para seleccionar un archivo (open/save)
    async selectFile(mode) {
        try {
            const options = {
                types: [
                    {
                        description: "Archivos JSON",
                        accept: { "application/json": [".json"] },
                    },
                ],
                excludeAcceptAllOption: true,
            };

            if (mode === "save") {
                const fileHandle = await window.showSaveFilePicker(options);
                return fileHandle;
            } else {
                const [fileHandle] = await window.showOpenFilePicker(options);
                return fileHandle;
            }
        } catch (e) {
            console.error("Error al seleccionar archivo:", e);
            return null;
        }
    },

    // Guarda la configuración de una pestaña en un archivo JSON
    async saveConfig(tabId) {
        const fileHandle = await this.selectFile("save");
        if (!fileHandle) return false;

        try {
            const config = TabManager.getTabConfig(tabId);
            const writable = await fileHandle.createWritable();
            await writable.write(JSON.stringify(config, null, 2));
            await writable.close();
            alert("Archivo guardado con éxito.");
            return true;
        } catch (e) {
            alert(`Error al guardar el archivo: ${e.message}`);
            return false;
        }
    },

    // Carga la configuración desde un archivo JSON a una pestaña
    async loadConfig() {
        const fileHandle = await this.selectFile("open");
        if (!fileHandle) return false;

        try {
            const file = await fileHandle.getFile();
            const contents = await file.text();
            const data = JSON.parse(contents);

            const activePane = document.querySelector(".tab-pane.active");
            if (!activePane) return false;

            const tabId = activePane.id.replace("pane-", "tab-");
            TabManager.loadConfigToTab(tabId, data);
            return true;
        } catch (e) {
            alert(`Error al cargar el archivo JSON: ${e.message}`);
            return false;
        }
    },
};