const API_BASE = '/api';

function getHeaders() {
  const token = localStorage.getItem('token');
  return {
    'Content-Type': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };
}

async function handleResponse(response) {
  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.error || 'Error en la petición');
  }
  return data;
}

export async function listarGrupos() {
  const response = await fetch(`${API_BASE}/maestros`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function crearGrupo(data) {
  const response = await fetch(`${API_BASE}/maestros`, {
    method: 'POST', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function actualizarGrupo(codigo, data) {
  const response = await fetch(`${API_BASE}/maestros/${codigo}`, {
    method: 'PUT', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function eliminarGrupo(codigo) {
  const response = await fetch(`${API_BASE}/maestros/${codigo}`, {
    method: 'DELETE', headers: getHeaders(),
  });
  return handleResponse(response);
}

export async function listarOpciones(maestroCodigo) {
  const response = await fetch(`${API_BASE}/maestros/${maestroCodigo}/opciones`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function crearOpcion(maestroCodigo, data) {
  const response = await fetch(`${API_BASE}/maestros/${maestroCodigo}/opciones`, {
    method: 'POST', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function actualizarOpcion(id, data) {
  const response = await fetch(`${API_BASE}/maestros/opciones/${id}`, {
    method: 'PUT', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function eliminarOpcion(id) {
  const response = await fetch(`${API_BASE}/maestros/opciones/${id}`, {
    method: 'DELETE', headers: getHeaders(),
  });
  return handleResponse(response);
}
