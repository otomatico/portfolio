class Tab_Manager {
    constructor() {
        this.tabCounter = 0;
        this.headerManager = new TabHeader_Manager();
        this.contentManager = new TabContent_Manager();
        this.setupEventListeners();
    }

    // Configura los event listeners
    setupEventListeners() {
        // Delegación de eventos para los botones de pestañas
        const tabsHeader = document.querySelector(".tabs-header");
        tabsHeader.addEventListener("click", (event) => {
            const tabButton = event.target.closest(".tab-button");
            if (tabButton) {
                const tabId = tabButton.id;
                const paneId = `pane-${tabId.replace("tab-", "")}`;
                this.switchTab(tabId, paneId);
            }

            // Manejar el cierre de pestañas
            const closeButton = event.target.closest(".tab-close");
            if (closeButton) {
                event.stopPropagation();
                const tabButton = closeButton.parentElement;
                const tabId = tabButton.id;
                const paneId = `pane-${tabId.replace("tab-", "")}`;
                this.closeTab(tabId, paneId);
            }
        });
    }

    // Añade una nueva pestaña
    addNewTab(tabText = null) {
        this.tabCounter++;
        const tabId = `tab-${this.tabCounter}`;
        const paneId = `pane-${this.tabCounter}`;
        if (tabText == null) {
            tabText = prompt("Nombre de la Nueva Pestaña", `Peticion ${this.tabCounter}`);
        }
        this.headerManager.createTabHeader(tabId, tabText);
        this.contentManager.createTabPane(tabId, paneId);
        this.switchTab(tabId, paneId); // Activar la nueva pestaña
    }

    // Cierra una pestaña
    closeTab(tabId, paneId) {
        if (document.querySelectorAll(".tab-button").length <= 1) {
            alert("No puedes cerrar la última pestaña.");
            return;
        }

        this.headerManager.removeTab(tabId);
        this.contentManager.removePane(paneId);

        // Activar la primera pestaña restante
        const firstTabButton = document.querySelector(".tab-button");
        if (firstTabButton) {
            const firstTabId = firstTabButton.id;
            const firstPaneId = `pane-${firstTabId.replace("tab-", "")}`;
            this.switchTab(firstTabId, firstPaneId);
        }
    }

    // Cambia entre pestañas
    switchTab(tabId, paneId) {
        // Desactivar todas las pestañas y paneles
        document.querySelectorAll(".tab-button").forEach(tab => {
            tab.classList.remove("active");
        });
        document.querySelectorAll(".tab-pane").forEach(pane => {
            pane.classList.remove("active");
        });

        // Activar la pestaña y panel seleccionados
        document.getElementById(tabId).classList.add("active");
        document.getElementById(paneId).classList.add("active");
    }

    // Obtiene la configuración de una pestaña
    getTabConfig(tabId) {
        return this.contentManager.getTabConfig(tabId);
    }

    // Carga la configuración en una pestaña
    loadConfigToTab(tabId, data) {
        this.contentManager.loadConfigToTab(tabId, data);
    }
}

class TabHeader_Manager {
    constructor() {
        this.tabsHeader = document.querySelector(".tabs-header");
    }

    // Añade una nueva pestaña
    createTabHeader(tabId, tabText) {
        const tabButton = this.createTabButton(tabId, tabText);
        this.tabsHeader.insertBefore(tabButton, this.tabsHeader.lastChild);
    }

    // Crea el botón de una pestaña
    createTabButton(tabId, tabText) {
        const tabButton = document.createElement("div");
        tabButton.id = tabId;
        tabButton.className = "tab-button";
        tabButton.innerHTML = `${tabText}<span class="tab-close">x</span>`;
        return tabButton;
    }

    // Elimina un botón de pestaña
    removeTab(tabId) {
        const tabButton = document.getElementById(tabId);
        if (tabButton) tabButton.remove();
    }
}

class TabContent_Manager {
    constructor() {
        this.tabsContent = document.querySelector(".tabs-content");
    }

    // Crea el panel de una pestaña
    createTabPane(tabId, paneId) {
        const tabPane = document.createElement("div");
        tabPane.id = paneId;
        tabPane.className = "tab-pane";

        // Contenido del panel
        tabPane.innerHTML = `
            <div class="main-wrapper">
                <div class="container">
                    <div class="row-table">
                        <div class="cell" style="width:100px;flex:none">
                            <label>Método:</label>
                            <select id="method-${tabId}">
                                <option value="POST">POST</option>
                                <option value="GET">GET</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        <div class="cell" style="width:100%">
                            <label>Endpoint URL:</label>
                            <input type="text" id="url-${tabId}" value="http://localhost:8080/api/graphql" placeholder="URL de la API">
                        </div>
                        <div class="cell" style="flex:none">
                            <button class="btn btn-execute" id="execute-${tabId}">Enviar</button>
                        </div>
                    </div>
                    <label>Headers de la Petición:</label>
                    <div id="headers-container-${tabId}"></div>
                    <button class="btn btn-add" id="add-header-${tabId}">+ Añadir Cabecera</button>
                    <label for="body-${tabId}">Cuerpo de la Petición (Body/Query):</label>
                    <textarea id="body-${tabId}" placeholder='{"query": "{ ... }"}'></textarea>
                </div>
                <div id="output-${tabId}" class="output-panel">
                    Los resultados de la API aparecerán en este panel...
                </div>
            </div>
        `;

        // Evento para ejecutar la petición
        const executeButton = tabPane.querySelector(`#execute-${tabId}`);
        executeButton.addEventListener("click", (event) => {
            event.stopPropagation();
            RequestManager.executeRequest(tabId);
        });

        // Evento para añadir una cabecera
        const addHeaderButton = tabPane.querySelector(`#add-header-${tabId}`);
        addHeaderButton.addEventListener("click", (event) => {
            event.stopPropagation();
            this.addHeaderRow(tabId, "", "");
        });

        this.tabsContent.appendChild(tabPane);
        this.addHeaderRow(tabId, "Content-Type", "application/json");
        return tabPane;
    }

    // Elimina un panel de pestaña
    removePane(paneId) {
        const tabPane = document.getElementById(paneId);
        if (tabPane) tabPane.remove();
    }

    // Añade una fila de header
    addHeaderRow(tabId, key, value) {
        const container = document.getElementById(`headers-container-${tabId}`);
        const row = document.createElement("div");
        row.className = "header-row";

        row.innerHTML = `
            <div class="cell">
                <input type="text" class="h-key" placeholder="Key" value="${key || ""}">
            </div>
            <div class="cell">
                <input type="text" class="h-val" placeholder="Value" value="${value || ""}">
            </div>
            <div class="cell" style="width:35px">
                <button class="btn btn-del">x</button>
            </div>
        `;

        // Evento para eliminar la fila de header
        const deleteButton = row.querySelector(".btn-del");
        deleteButton.addEventListener("click", (event) => {
            event.stopPropagation();
            this.removeHeader(row);
        });

        container.appendChild(row);
    }

    // Elimina una fila de header
    removeHeader(row) {
        if (row) row.remove();
    }

    // Obtiene la configuración de una pestaña
    getTabConfig(tabId) {
        const getVal = (id) => {
            const el = typeof id === "string" ? document.getElementById(id) : id;
            if (!el) return "";
            const placeholder = el.getAttribute("placeholder");
            return el.value === placeholder ? "" : el.value;
        };

        const config = {
            url: getVal(`url-${tabId}`),
            method: getVal(`method-${tabId}`),
            body: getVal(`body-${tabId}`),
            headers: [],
        };

        // Recoger todos los headers dinámicos
        const headerRows = document.querySelectorAll(`#headers-container-${tabId} .header-row`);
        headerRows.forEach(row => {
            const keyInput = row.querySelector('.h-key');
            const valInput = row.querySelector('.h-val');
            const key = getVal(keyInput);
            const val = getVal(valInput);
            if (key !== "") {
                config.headers.push({ key, val });
            }
        });

        return config;
    }

    // Carga la configuración en una pestaña
    loadConfigToTab(tabId, data) {
        document.getElementById(`url-${tabId}`).value = data.url || "";
        document.getElementById(`method-${tabId}`).value = data.method || "POST";
        document.getElementById(`body-${tabId}`).value = data.body || "";

        // Limpiar headers existentes
        document.getElementById(`headers-container-${tabId}`).innerHTML = "";

        // Añadir headers desde el archivo
        if (data.headers && data.headers.length > 0) {
            data.headers.forEach(header => {
                this.addHeaderRow(tabId, header.key, header.val);
            });
        } else {
            // Headers por defecto
            this.addHeaderRow(tabId, "Content-Type", "application/json");
        }
    }
}