const width = 40, height = 28;
let currentEntity = "ENTITY_NONE";
let tabs = [];
let activeTab = null;

const grid = document.getElementById("grid");
const tabsContainer = document.getElementById("tabs");
const output = document.getElementById("output");


// --- Utilidades ---
function createEmptyMap() {
    return Array(height).fill().map(() => Array(width).fill(entityToId("ENTITY_NONE")));
}

function renderGrid(map) {
    grid.innerHTML = "";
    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const cell = document.createElement("div");
            cell.className = "cell " + idToEntity(map[y][x]);
            cell.dataset.x = x;
            cell.dataset.y = y;
            grid.appendChild(cell);
        }
    }
}

function refreshTabs() {
    tabsContainer.innerHTML = "";
    tabs.forEach((tab, i) => {
        const tabEl = document.createElement("div");
        tabEl.className = "tab" + (i === activeTab ? " active" : "");
        tabEl.textContent = tab.name;

        const trash = document.createElement("span");
        trash.textContent = "🗑";
        tabEl.appendChild(trash);
        tabEl.addEventListener("click", (e) => {
            switchTab(i)
            if (e.target.tagName.toUpperCase() == "SPAN") {
                if (confirm('Borrar mapa actual?')) {
                    deleteActive();
                }
            }
        });
        tabsContainer.appendChild(tabEl);
        //        tabEl.addEventListener("contextmenu", e => openContextMenu(e, i));
    });
}

function switchTab(index) {
    activeTab = index;
    refreshTabs();
    renderGrid(tabs[index].map);
}
function deleteActive() {
    if (activeTab < 0) return;
    tabs.splice(activeTab, 1);
    refreshTabs()

}
function addTabs(name) {
    tabs.push({ name, map: createEmptyMap() });
    activeTab = tabs.length - 1;
    refreshTabs();
    renderGrid(tabs[activeTab].map);
}

function entityToId(e) {
    switch (e) {
        case 'ENTITY_SNAKE': return 1;
        case 'ENTITY_FOOD': return 2;
        case 'ENTITY_PLATFORM': return 3;
        case 'ENTITY_ROCK': return 4;
        case 'ENTITY_SPIKE': return 5;
        case 'ENTITY_EXIT': return 6;
        default: return 0;
    }
}
function idToEntity(id) {
    switch (id) {
        case 1: return 'ENTITY_SNAKE';
        case 2: return 'ENTITY_FOOD';
        case 3: return 'ENTITY_PLATFORM';
        case 4: return 'ENTITY_ROCK';
        case 5: return 'ENTITY_SPIKE';
        case 6: return 'ENTITY_EXIT';
        default: return 'ENTITY_NONE';
    }
}

// --- Crear pestaña ---
document.getElementById("add-tab").addEventListener("click", () => {
    const name = prompt("Nombre del mapa:", `level${tabs.length + 1}`);
    if (!name) return;
    addTabs(name)
});
// --- Selecionar EntityType
document.getElementById("entityType").addEventListener("click", e => {
    if (e.target.tagName.toUpperCase() == "INPUT") {
        currentEntity = e.target.value;
    }
});
// --- Limpiar grid
document.getElementById("clearBtn").addEventListener("click", () => {
    if (activeTab === null) return;
    tabs[activeTab].map = createEmptyMap();
    renderGrid(tabs[activeTab].map);
});
// -- colorear Grid
grid.addEventListener("click", (e) => {
    debugger
    const cell = e.target;
    if (cell.className.split(" ").some(any => any === "cell")) {
        const { x, y } = e.target.dataset
        tabs[activeTab].map[y][x] = entityToId(currentEntity);
        cell.className = "cell " + currentEntity
    }
});
// --- Exportación ---
function generateEntityDraw(map) {
    const used = Array(height).fill().map(() => Array(width).fill(false));
    const entities = [];

    // Líneas horizontales
    for (let y = 0; y < height; y++) {
        let x = 0;
        while (x < width) {
            const e = map[y][x];
            if (e === 0) { x++; continue; }
            let len = 1;
            while (x + len < width && map[y][x + len] === e) len++;
            if (len > 1) {
                entities.push({ draw: "MAP_LINE_HORIZONTAL", entity: idToEntity(e), x, y, length: len });
                for (let i = 0; i < len; i++) used[y][x + i] = true;
                x += len;
            } else x++;
        }
    }

    // Líneas verticales
    for (let x = 0; x < width; x++) {
        let y = 0;
        while (y < height) {
            const e = map[y][x];
            if (e === 0 || used[y][x]) { y++; continue; }
            let len = 1;
            while (y + len < height && map[y + len][x] === e && !used[y + len][x]) len++;
            if (len > 1) {
                entities.push({ draw: "MAP_LINE_VERTICAL", entity: idToEntity(e), x, y, length: len });
                for (let i = 0; i < len; i++) used[y + i][x] = true;
                y += len;
            } else y++;
        }
    }

    // Puntos individuales
    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const e = map[y][x];
            if (e !== 0 && !used[y][x]) {
                entities.push({ draw: "MAP_POINT", entity: idToEntity(e), x, y, length: 1 });
                used[y][x] = true;
            }
        }
    }

    return entities;
}

function exportMap(name, map) {
    const entities = generateEntityDraw(map);
    let code = `#ifndef ${name.toUpperCase()}_H\n#define ${name.toUpperCase()}_H\n\n#include "TileMap.h"\n\nstatic EntityDrawMap ${name}_tiles[] = {\n`;
    debugger
    for (const e of entities) {
        code += `    { ${e.draw}, ${e.entity}, ${e.x}, ${e.y}, ${e.length} },\n`;
    }

    code += `};\n\nstatic const EntityMap ${name} = { ${entities.length}, ${name}_tiles };\n\n#endif // ${name.toUpperCase()}_H\n`;
    return code;
}

document.getElementById("exportBtn").addEventListener("click", () => {
    if (activeTab === null) return;
    const { name, map } = tabs[activeTab];
    output.value = exportMap(name, map);
});

document.getElementById("exportAllBtn").addEventListener("click", () => {
    if (!tabs.length) return;
    let allCode = `#ifndef ALL_MAPS_H\n#define ALL_MAPS_H\n\n#include "TileMap.h"\n\n`;
    const names = [];

    for (const { name, map } of tabs) {
        const entities = generateEntityDraw(map);
        names.push(name);
        allCode += `static EntityDrawMap ${name}_tiles[] = {\n`;
        for (const e of entities) {
            allCode += `    { ${e.draw}, ${e.entity}, ${e.x}, ${e.y}, ${e.length} },\n`;
        }
        allCode += `};\n\nstatic const EntityMap ${name} = { ${entities.length}, ${name}_tiles };\n\n`;
    }

    allCode += `#define MAX_TILES ${tabs.length}\n`;
    allCode += `const EntityMap *Tiles[MAX_TILES] = { ${names.map(n => "&" + n).join(", ")} };\n\n`;
    allCode += `#endif // ALL_MAPS_H\n`;

    output.value = allCode;
    downloadFile("all_maps.h", allCode);
});

function downloadFile(filename, content) {
    const blob = new Blob([content], { type: "text/plain" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

// --- Guardar/Cargar JSON ---
document.getElementById("saveJsonBtn").addEventListener("click", () => {
    const jsonData = JSON.stringify(tabs, null, 2);
    downloadFile("maps.json", jsonData);
});

document.getElementById("loadJsonBtn").addEventListener("click", () => {
    document.getElementById("fileInput").click();
});
const navbar = document.querySelector(".navbar")
navbar.addEventListener("click", (e) => {
    const summary = e.target;
    if (summary.tagName.toUpperCase() == "SUMMARY" && summary.parentNode.hasAttribute("open")) {
        return;
    }
    navbar.querySelectorAll("[open]").forEach(detail => {
        if (detail.hasAttribute("open")) {
            detail.removeAttribute("open")
        }
    });
})
document.getElementById("fileInput").addEventListener("change", e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = event => {
        try {
            debugger
            tabs = JSON.parse(event.target.result);
            if (!Array.isArray(tabs) || !tabs.length) throw new Error();
            activeTab = 0;
            refreshTabs();
            renderGrid(tabs[0].map);
        } catch {
            alert("Archivo JSON no válido.");
        }
    };
    reader.readAsText(file);
});
// --- Inicialización ---
//addTabs("level1");