import SpriteList from "./Entity/SpriteList.js"
import Current from "./Entity/Current.js"
import settings from "./settings.js"

window.addEventListener('DOMContentLoaded', () => {
    window.model = new SpriteList()
    window.current = new Current()
    settings().then(()=>{
        const menustrip = document.querySelector("menu-strip")
        const panelTool = document.querySelector("panel-toolbox")
        const treeListCanvas = document.querySelector("[is=tree-list-picture]")
        const panelProperty = document.querySelector("panel-property")
        const panelDrawer = document.querySelector("panel-drawer")
        
        model.subscribe(treeListCanvas)
        model.subscribe(panelDrawer)
        model.subscribe(panelTool)
        model.subscribe(menustrip)

        current.subscribe(treeListCanvas)
        current.subscribe(panelDrawer)
        current.subscribe(panelTool)
        current.subscribe(panelProperty)
        current.subscribe(menustrip)
    })
})