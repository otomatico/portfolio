class API_Fetch {
  #header
  constructor() {
    this.#header = {
      method: '', // *GET, POST, PUT, DELETE, etc.
      mode: 'cors', // no-cors, *cors, same-origin
      cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
      credentials: 'same-origin', // include, *same-origin, omit
      headers: {
        'Content-Type': '',
        // 'Content-Type': 'application/json', 'application/x-www-form-urlencoded', 'multipart/form-data' => new FormData(), or 'text/plain'
        authorization: ''
      },
      redirect: 'follow', // manual, *follow, error
      referrerPolicy: 'no-referrer' // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
      // body: ''// body data type must match "Content-Type" header only case PUT/POST
    }
  }

  #getHeader(method) {
    const cloneHeader = { ...this.#header }
    cloneHeader.headers.authorization =
      window.localStorage.getItem('auth') || ''
    cloneHeader.method = method
    let contentType = ''
    if (['GET', 'DELETE'].includes(method)) {
      contentType = 'application/x-www-form-urlencoded'
    }
    if (['POST', 'PUT'].includes(method)) {
      contentType = 'application/json'
    }
    cloneHeader.headers['Content-Type'] = contentType
    return cloneHeader
  }
  async get(url) {
    const header = this.#getHeader('GET')
    const response = await fetch(url, header)
    return response
  }
  async delete(url) {
    const header = this.#getHeader('DELETE')
    const response = await fetch(url, header)
    return response
  }
  async post(url, data) {
    const header = this.#getHeader('POST')
    header.body = data || ''
    const response = await fetch(url, header)
    return response
  }
  async put(url, data) {
    const header = this.#getHeader('PUT')
    header.body = data || ''
    const response = await fetch(url, header)
    return response
  }
}
const API = new API_Fetch();
export default API; 
//window.API = new API_Fetch()
