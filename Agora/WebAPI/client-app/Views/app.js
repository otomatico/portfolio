//Base Framework
//import * as $global from '/global.js';
Object.prototype.replaceEntries = (sourceText, O) => {
    let entriesReplace = Object.entries(O)
    entriesReplace.forEach((item) => {
        const [property, value] = item
        sourceText = sourceText.replaceAll(`\${${property}}`, value===undefined?'':value)
    })
    return sourceText
}
window.importeComponent = (file) => {
    return new Promise((resolve, reject) => {
        fetch(file)
            .then(result => {
                return result.text();
            })
            .then(txt => {
                const parser = new DOMParser();
                const fragment = parser.parseFromString(txt, 'text/html');
                const originalTemplate = fragment.getElementsByTagName('TEMPLATE')[0];
                let template;
                if (originalTemplate) {
                    template = document.createElement('template');
                    template.innerHTML = originalTemplate.innerHTML;
                    template.id = originalTemplate.id;
                    document.body.appendChild(template);
                }
                const originalScript = fragment.getElementsByTagName('SCRIPT')[0];
                let script;
                if (originalScript) {
                    script = document.createElement('script');
                    script.innerHTML = originalScript.innerHTML;
                    script.type = originalScript.type || 'text/javascript';
                    document.body.appendChild(script);
                }
                resolve({ template, script });
            })
            .catch(reject);
    });
}

window.addEventListener('DOMContentLoaded', () => {
    [
        './components/core/data-table.html',
        './components/core/json-values.html',
        './components/core/tree-list.html',
        './components/ui-menu.html',
        './components/dialog-master.html',
        './components/dialog-master-option.html',
        './components/dialog-template.html',
        './components/dialog-template-field.html',
        './components/dialog-template-children.html',
        './components/node-edit.html',
        './pages/configure/view-template.html',
        './pages/configure/view-master.html',
        './components/dialog-node-root.html',
        './pages/edit/view-node-root.html',
        './pages/edit/view-node.html'
    ].forEach((element) => {
        importeComponent(element);
    });
})

