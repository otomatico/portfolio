export default class Observable {
    constructor() {
        this.observers = []
    }

    // Suscribe una clase notificadora
    subscribe(c) {
        this.observers.push(c)
    }

    // Desuscribe la clase notificadora
    unsubscribe(c) {
        this.observers = this.observers.filter(observer => observer instanceof c !== true)
    }

    // Llama a todos nuestros suscriptores
    notify(model) {
        this.observers.forEach(observer => {
            if (observer?.notify) {
                observer.notify(model)
            }
        })
    }
}