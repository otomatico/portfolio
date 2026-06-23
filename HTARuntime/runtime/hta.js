;(function() {

// ── Async RPC via fetch ─────────────────────────────────────────────────────

function callRust(cmd, data) {
    return fetch("./rpc", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cmd: cmd, data: data || {} }),
    }).then(function(resp) {
        return resp.json();
    });
}

// ── Sync RPC via XMLHttpRequest (for ActiveX) ───────────────────────────────

var AX_GETTERS = {
    TEXTSTREAM: {
        AtEndOfStream: true,
        Line: true,
        Column: true,
    },
    WSHSCRIPTEXEC: {
        StdOut: true,
        StdErr: true,
        ExitCode: true,
    },
    "WSCRIPT.SHELL": {
        CurrentDirectory: true,
    },
};
function callRustSync(cmd, data) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "./rpc", false);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify({ cmd: cmd, data: data || {} }));
    var resp = JSON.parse(xhr.responseText);
    return resp;
}

function createAxProxy(id, progID) {
    var getters = AX_GETTERS[progID.toUpperCase()] || {};
    return new Proxy({ __ax_id: id, __progid: progID }, {
        get: function(target, prop) {
            if (typeof prop === "string" && prop.startsWith("__")) {
                return target[prop];
            }
            if (typeof prop === "string" && getters[prop]) {
                var resp = callRustSync("ax_get", { id: id, prop: prop });
                if (!resp.ok) throw new Error(resp.error || "ActiveX get error");
                if (resp.resultType === "instance") {
                    return createAxProxy(resp.result, resp.progID);
                }
                return resp.result;
            }
            if (typeof prop === "string" && prop !== "then") {
                return function() {
                    var args = Array.prototype.slice.call(arguments);
                    var resp = callRustSync("ax_call", { id: id, name: prop, args: args });
                    if (!resp.ok) throw new Error(resp.error || "ActiveX error");
                    if (resp.resultType === "instance") {
                        return createAxProxy(resp.result, resp.progID);
                    }
                    return resp.result;
                };
            }
            return target[prop];
        },
    });
}

// ── WinHttpRequest polyfill (native XMLHttpRequest) ──────────────────────────

var WINHTTP_PROGIDS = ["WINHTTP.WINHTTPREQUEST", "WINHTTP.WINHTTPREQUEST.5.1"];

function createWinHttpRequest() {
    var xhr = new XMLHttpRequest();
    return {
        Open: function(method, url, asyncFlag) {
            xhr.open(method, url, asyncFlag !== false);
        },
        SetRequestHeader: function(name, value) {
            xhr.setRequestHeader(name, value);
        },
        Send: function(body) {
            xhr.send(body !== undefined ? body : null);
        },
        WaitForResponse: function() {
            return xhr.readyState === 4;
        },
        get Status() { return xhr.status; },
        get StatusText() { return xhr.statusText; },
        get ResponseText() { return xhr.responseText; },
        Option: function() {},
        Abort: function() { xhr.abort(); },
        GetAllResponseHeaders: function() { return xhr.getAllResponseHeaders(); },
        GetResponseHeader: function(header) { return xhr.getResponseHeader(header); },
    };
}

window.ActiveXObject = function(progID) {
    var upper = progID.toUpperCase();
    if (WINHTTP_PROGIDS.indexOf(upper) !== -1) {
        return createWinHttpRequest();
    }
    var resp = callRustSync("ax_create", { progID: progID });
    if (!resp.ok) throw new Error("ActiveXObject: " + (resp.error || "create failed"));
    return createAxProxy(resp.result, progID);
};

// ── Public API ───────────────────────────────────────────────────────────────

window.System = {};

window.System.info = function(msg) { alert(msg); };
window.System.error = function(msg) { alert("[Error] " + msg); };
window.System.confirm = function(msg) { return confirm(msg); };

window.System.quit = function() {
    return callRust("quit");
};

window.System.getConfig = function() {
    return callRust("get_config");
};

window.System.fs = {};
window.System.fs.readFile = function(path) {
    return callRust("read_file", { path: path });
};

window.System.exec = function(command) {
    return callRust("exec_command", { command: command });
};

window.System.dialogs = {};

window.System.dialogs.openFile = function(opts) {
    return callRust("open_file", opts || {});
};

window.System.dialogs.saveFile = function(opts) {
    return callRust("save_file", opts || {});
};

window.System.dialogs.openDir = function(opts) {
    return callRust("open_dir", opts || {});
};

})();
