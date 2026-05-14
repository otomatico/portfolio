export function paint(ctx,{sprite,palette,size={x:1,y:1}}){
    if(ctx){
        sprite?.forEach((row, y) => {
            row.forEach((col, x) => {
                ctx.fillStyle = palette[col]??col
                ctx.fillRect(x * size.x, y * size.y, size.x, size.y)
            })
        })
    }
}
const styleDialog=`dialog.app p{
    margin: 2px 6px;
    padding:5px;
}
dialog.app input{
    margin: 2px 5px;
    padding:5px;
    width: calc(100% - 10px);
}
dialog.app form .window-body>p:last-child{
    text-align:right
}
dialog.app {
    min-width:250px
}`

const htmlTemplate = `<dialog class="window app">
<form method="dialog">
    <div class="title-bar">
        <div class="title-bar-text">Edit Pixel</div>
        <div class="title-bar-controls">
            <button type="reset" aria-label="Close"></button>
        </div>
    </div>
    <div class="window-body"></div>
</form>
</dialog>`
document.head.insertAdjacentHTML("beforeend",`<style>${styleDialog}</style>`)
document.body.insertAdjacentHTML("beforeend",`<template id="dialogSystem">${htmlTemplate}</style>`)
const template = document.body.querySelector("TEMPLATE#dialogSystem").content

export function prompt(message,value){
    document.body.appendChild(template.cloneNode(true))
    const dialog =  document.body.querySelector("dialog.window.app")
    const htmlDialog = `
    <p>${message}</p>
    <input type="text" value="${value}"/>
    <p>
        <button type="submit">Aceptar</button>&nbsp;
        <button type="reset">Cancelar</button>
    </p>`

    dialog.querySelector(".window-body").insertAdjacentHTML("beforeend",htmlDialog)
    dialog.addEventListener("submit",(event)=>{
        const {parentNode} = event.target
        parentNode.close(parentNode.querySelector("input[type=text]").value)
    })
    dialog.addEventListener("reset",(event)=>{
        event.target.parentNode.close("")
    }) 
    
    dialog.showModal()
    return new Promise((resolve,reject)=>{
        dialog.addEventListener("close",(event)=>{
            const data = event.target.returnValue
            document.body.removeChild(event.target)
            resolve(data)
        })
    })
}

export function alert(message){
    document.body.appendChild(template.cloneNode(true))
    const dialog =  document.body.querySelector("dialog.window.app")

    const htmlDialog = `
    <p>${message}</p>
    <p> <button type="submit">Aceptar</button> </p>`
    dialog.querySelector(".window-body").insertAdjacentHTML("beforeend",htmlDialog)
    dialog.showModal()
    const onClick = (event)=>event.target.parentNode.close("")
    dialog.addEventListener("submit",onClick)
    dialog.addEventListener("reset",onClick)

    return new Promise((resolve,reject)=>{
        dialog.addEventListener("close",(event)=>{
            document.body.removeChild(event.target)
            resolve(null)
        })
    })
}

export function confirm(message){
    document.body.appendChild(template.cloneNode(true))
    const dialog =  document.body.querySelector("dialog.window.app")

    const htmlDialog = ` <p>${message}</p>
    <p style="text-align:right;">
    <button type="submit">Aceptar</button>&nbsp;<button type="reset">Cancelar</button>
    </p>`

    dialog.querySelector(".window-body").insertAdjacentHTML("beforeend",htmlDialog)
    const onClick = (event)=>event.target.parentNode.close(event.type==="submit"||'')
    dialog.addEventListener("submit",onClick)
    dialog.addEventListener("reset",onClick)
    
    dialog.showModal()
    return new Promise((resolve,reject)=>{
        dialog.addEventListener("close",(event)=>{
            const data = event.target.returnValue
            document.body.removeChild(event.target)
            resolve(data?true:false)
        })
    })
}

export function ExportAs({fileName, data, mimeType='text/plain;charset=utf-8'}){
    const link = document.createElement('a')
    link.href = `data:${mimeType},${encodeURIComponent(data)}`
    link.download = fileName;
    link.click();
}
export function OpenAs(){
    return new Promise((resolve,reject)=>{
        let input = document.createElement('input')
        input.type = 'file'
        input.addEventListener("change", (event)=>{
            const [file] = event.target.files
            const reader = new FileReader();
            reader.addEventListener("load",(event)=>{
                resolve(event.target.result)
            })
            if (file) {
                reader.readAsText(file);
            }
        })
        input.click();
    })
}