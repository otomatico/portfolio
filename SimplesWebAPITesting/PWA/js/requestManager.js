// ======================
// OBJETO PARA MANEJAR PETICIONES HTTP
// ======================
const RequestManager = {
    // Ejecuta una petición HTTP
    async executeRequest(tabId) {
        const outputDiv = document.getElementById(`output-${tabId}`);

        const getVal = (id) => {
            const el = typeof id === "string" ? document.getElementById(id) : id;
            if (!el) return "";
            const placeholder = el.getAttribute("placeholder");
            return el.value === placeholder ? "" : el.value;
        };

        // Recoger Headers
        const keys = document.querySelectorAll(`#headers-container-${tabId} .h-key`);
        const vals = document.querySelectorAll(`#headers-container-${tabId} .h-val`);
        const headers = {};

        for (let i = 0; i < keys.length; i++) {
            const k = getVal(keys[i]);
            const v = getVal(vals[i]);
            if (k !== "") {
                headers[k] = v;
            }
        }

        // Recoger cuerpo, URL y método
        const body = getVal(`body-${tabId}`);
        const url = getVal(`url-${tabId}`);
        const method = getVal(`method-${tabId}`);

        // Mostrar estado de envío
        outputDiv.innerText = "Enviando...";
        outputDiv.style.color = "#80ff80";

        try {
            const response = await fetch(url, {
                method: method,
                headers: headers,
                body: method !== "GET" && body !== "" ? body : undefined,
            });

            if (response.ok) {
                const responseText = await response.text();
                outputDiv.innerText = `RESPUESTA:\n${responseText || "Éxito."}`;
                outputDiv.style.color = "#dcdcdc";
            } else {
                outputDiv.innerText = `ERROR:\nHTTP ${response.status} - ${response.statusText}`;
                outputDiv.style.color = "#ff8080";
            }
        } catch (e) {
            outputDiv.innerText = `Error: ${e.message}`;
            outputDiv.style.color = "#ff8080";
        }
    },
};