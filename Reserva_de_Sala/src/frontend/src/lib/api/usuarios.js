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

export async function listar() {
  const response = await fetch(`${API_BASE}/usuarios`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function obtener(id) {
  const response = await fetch(`${API_BASE}/usuarios/${id}`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function crear(data) {
  const response = await fetch(`${API_BASE}/usuarios`, {
    method: 'POST', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function actualizar(id, data) {
  const response = await fetch(`${API_BASE}/usuarios/${id}`, {
    method: 'PUT', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function eliminar(id) {
  const response = await fetch(`${API_BASE}/usuarios/${id}`, {
    method: 'DELETE', headers: getHeaders(),
  });
  return handleResponse(response);
}
