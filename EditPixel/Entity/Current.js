import Observable from "./Observer.js"
export default class Current extends Observable {
    sprite=[]
    palette=["#e0f8d0", "#88c070", "#346856", "#081820"]
    constructor() {
        super()
    }
    get width(){
        return this.sprite[0]?.length || 0
    }
    get height(){
        return this.sprite?.length || 0
    }
    toJSON(){
        const {sprite,palette} = this
        return {sprite,palette}
    }
}