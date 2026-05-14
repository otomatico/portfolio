// ======================
// INICIALIZACIÓN DE LA APLICACIÓN
// ======================

//Global
const TabManager = new Tab_Manager();

// Cargar los scripts de los módulos (en orden)
document.addEventListener("DOMContentLoaded", () => {
    // Asignar eventos del menú
    document.getElementById("fileMenuButton").addEventListener("click", (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    document.getElementById("openConfig").addEventListener("click", () => {
        FileManager.loadConfig();
        toggleMenu();
    });

    document.getElementById("saveConfig").addEventListener("click", () => {
        const activePane = document.querySelector(".tab-pane.active");
        if (activePane) {
            const tabId = activePane.id.replace("pane-", "tab-");
            FileManager.saveConfig(tabId);
        }
        toggleMenu();
    });

    // Botón para añadir pestañas
    document.getElementById("addTabButton").addEventListener("click", () => {
        TabManager.addNewTab();
    });

    // Añadir la primera pestaña
    TabManager.addNewTab("Petición 1");
});

// Función para alternar el menú (global)
function toggleMenu() {
    const menu = document.getElementById("fileMenu");
    menu.classList.toggle("show");
}

// Cerrar el menú si se hace clic fuera de él
document.addEventListener('click', (event) => {
    if (!event.target.classList.contains('menu-button')) {
        document.getElementById("fileMenu").classList.remove("show");
    }
});

// Exponer objetos globales para el HTML
//window.TabManager = TabManager;
window.FileManager = FileManager;
window.RequestManager = RequestManager;