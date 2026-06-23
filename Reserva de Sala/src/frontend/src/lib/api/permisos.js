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

export async function listarTodos() {
  const response = await fetch(`${API_BASE}/permisos`, { headers: getHeaders() });
  return handleResponse(response);
}

export async function actualizar(rol, componente, data) {
  const response = await fetch(`${API_BASE}/permisos/${rol}/${componente}`, {
    method: 'PUT', headers: getHeaders(), body: JSON.stringify(data),
  });
  return handleResponse(response);
}
