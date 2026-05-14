//Base Framework
Object.prototype.replaceEntries = (sourceText, O) => {
    let entriesReplace = Object.entries(O)
    entriesReplace.forEach((item) => {
        const [property, value] = item
        sourceText = sourceText.replaceAll(`\${${property}}`, value === undefined ? '' : value)
    })
    return sourceText
}

Object.prototype.groupBy = (values, keyFinder) => {
    return values.reduce((a, b) => {
        const key = typeof keyFinder === 'function' ? keyFinder(b) : b[keyFinder]
        if (!a[key]) {
            a[key] = [b]
        } else {
            a[key] = [...a[key], b]
        }
        return a
    }, {})
}

window.importeComponent = (file) => {
    return new Promise((resolve, reject) => {
        fetch(file)
            .then(result => {
                return result.text()
            })
            .then(txt => {
                const parser = new DOMParser()
                const fragment = parser.parseFromString(txt, 'text/html')
                const originalTemplate = fragment.getElementsByTagName('TEMPLATE')[0]
                let template, style, script
                if (originalTemplate) {
                    template = document.createElement('template')
                    template.innerHTML = originalTemplate.innerHTML
                    template.id = originalTemplate.id
                    document.body.appendChild(template)
                }
                const originalStyle = fragment.getElementsByTagName('STYLE')[0]
                if (originalStyle) {
                    style = document.createElement('style')
                    style.innerHTML = originalStyle.innerHTML
                    style.dataset.file = file
                    document.getElementsByTagName("head")[0].appendChild(style)
                }
                const originalScript = fragment.getElementsByTagName('SCRIPT')[0]
                if (originalScript) {
                    script = document.createElement('script')
                    script.innerHTML = originalScript.innerHTML
                    script.type = originalScript.type || 'text/javascript'
                    document.body.appendChild(script)
                }
                resolve({ style, template, script })
            })
            .catch(reject)
    })
}
export default async function () {

    const list = [
        './ui/menuStrip.html',
        './ui/dialogNewSprite.html',
        './ui/dialogExportAS.html',
        './ui/panelProperty.html',
        './ui/panelDrawer.html',
        './ui/panelToolBox.html'
    ]
    for (let element of list) {
        await importeComponent(element)
    }
}
