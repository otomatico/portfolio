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

export async function listar(sucursalId = null) {
  const params = sucursalId ? `?sucursal_id=${sucursalId}` : '';
  const response = await fetch(`${API_BASE}/salas${params}`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function obtener(id) {
  const response = await fetch(`${API_BASE}/salas/${id}`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function crear(data) {
  const response = await fetch(`${API_BASE}/salas`, {
    method: 'POST', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function actualizar(id, data) {
  const response = await fetch(`${API_BASE}/salas/${id}`, {
    method: 'PUT', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}

export async function eliminar(id) {
  const response = await fetch(`${API_BASE}/salas/${id}`, {
    method: 'DELETE', headers: getHeaders(),
  });
  return handleResponse(response);
}

export async function asignarRecurso(salaId, recursoId, cantidad = 1) {
  const response = await fetch(`${API_BASE}/salas/${salaId}/recursos`, {
    method: 'POST', headers: getHeaders(),
    body: JSON.stringify({ recurso_id: recursoId, cantidad }),
  });
  return handleResponse(response);
}

export async function desasignarRecurso(salaId, recursoId) {
  const response = await fetch(`${API_BASE}/salas/${salaId}/recursos/${recursoId}`, {
    method: 'DELETE', headers: getHeaders(),
  });
  return handleResponse(response);
}
