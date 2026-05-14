import Observable from "./Observer.js"
export default class SpriteList extends Observable {
    sprites = []
    map_bg = []
    constructor(data=null) {
        super()
        if(data){
            this.#init(data)
        }
    }
    #init(data=null){
        if(data){
            this.sprites = data.sprite
            this.map_bg = data.map_bg
        }
        this.notify(this)
    }
    toString(){
        const{sprites, num_sprit,map_bg}=this
        return JSON.stringify({sprites:sprites.map((item)=>item.toJSON()), map_bg})
    }
}