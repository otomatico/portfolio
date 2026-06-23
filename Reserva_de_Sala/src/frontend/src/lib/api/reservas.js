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

function buildQuery(filtros = {}) {
  const params = new URLSearchParams();
  for (const [key, value] of Object.entries(filtros)) {
    if (value) params.set(key, value);
  }
  const qs = params.toString();
  return qs ? `?${qs}` : '';
}

export async function listar(filtros = {}) {
  const response = await fetch(`${API_BASE}/reservas${buildQuery(filtros)}`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function obtener(id) {
  const response = await fetch(`${API_BASE}/reservas/${id}`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function crear(data) {
  const response = await fetch(`${API_BASE}/reservas`, {
    method: 'POST', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function cancelar(id) {
  const response = await fetch(`${API_BASE}/reservas/${id}/cancelar`, {
    method: 'PUT', headers: getHeaders(),
  });
  return handleResponse(response);
}
