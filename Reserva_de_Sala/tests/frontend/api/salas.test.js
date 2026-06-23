import { describe, it, expect, vi, beforeEach } from 'vitest';
import { listar, obtener, crear, actualizar, eliminar, getRecursos, asignarRecurso, desasignarRecurso } from '../../../src/frontend/src/lib/api/salas';

// Mock global fetch
const mockFetch = vi.fn();
global.fetch = mockFetch;

// Mock localStorage
const localStorageMock = (() => {
  let store = {};
  return {
    getItem: vi.fn((key) => store[key] || null),
    setItem: vi.fn((key, value) => { store[key] = value; }),
    removeItem: vi.fn((key) => { delete store[key]; }),
    clear: vi.fn(() => { store = {}; }),
  };
})();
Object.defineProperty(global, 'localStorage', { value: localStorageMock });

describe('salas API module', () => {
  beforeEach(() => {
    mockFetch.mockReset();
    localStorageMock.clear();
  });

  it('listar hace GET a /api/salas sin filtro', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, nombre: 'Sala A' }],
    });

    const result = await listar();

    expect(mockFetch).toHaveBeenCalledWith('/api/salas', {
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result).toEqual([{ id: 1, nombre: 'Sala A' }]);
  });

  it('listar incluye sucursal_id cuando se proporciona', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, nombre: 'Sala A', sucursal_id: 5 }],
    });

    await listar(5);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas?sucursal_id=5', {
      headers: { 'Content-Type': 'application/json' },
    });
  });

  it('obtener hace GET a /api/salas/:id', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 1, nombre: 'Sala A' }),
    });

    const result = await obtener(1);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1', {
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result.nombre).toBe('Sala A');
  });

  it('crear hace POST a /api/salas con los datos', async () => {
    const data = { nombre: 'Sala Nueva', aforo: 20, sucursal_id: 1 };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 3, ...data }),
    });

    const result = await crear(data);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    expect(result.nombre).toBe('Sala Nueva');
  });

  it('actualizar hace PUT a /api/salas/:id', async () => {
    const data = { aforo: 30 };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 1, nombre: 'Sala A', aforo: 30 }),
    });

    const result = await actualizar(1, data);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    expect(result.aforo).toBe(30);
  });

  it('eliminar hace DELETE a /api/salas/:id', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ message: 'Sala eliminada exitosamente' }),
    });

    const result = await eliminar(1);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result.message).toBe('Sala eliminada exitosamente');
  });

  it('getRecursos hace GET a /api/salas/:id/recursos', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, nombre: 'Proyector', cantidad: 2 }],
    });

    const result = await getRecursos(1);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1/recursos', {
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result).toHaveLength(1);
    expect(result[0].nombre).toBe('Proyector');
  });

  it('asignarRecurso hace POST a /api/salas/:id/recursos', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, nombre: 'Proyector', cantidad: 1 }],
    });

    const result = await asignarRecurso(1, 5, 3);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1/recursos', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ recurso_id: 5, cantidad: 3 }),
    });
    expect(result).toHaveLength(1);
  });

  it('desasignarRecurso hace DELETE a /api/salas/:id/recursos/:recursoId', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [],
    });

    const result = await desasignarRecurso(1, 5);

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1/recursos/5', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result).toEqual([]);
  });

  it('incluye Authorization header cuando hay token', async () => {
    localStorageMock.getItem.mockImplementation((key) => {
      if (key === 'token') return 'test-token-123';
      return null;
    });

    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1 }],
    });

    await listar();

    expect(mockFetch).toHaveBeenCalledWith('/api/salas', {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer test-token-123',
      },
    });
  });

  it('lanza error cuando la respuesta no es ok', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      json: async () => ({ error: 'Error al obtener salas' }),
    });

    await expect(listar()).rejects.toThrow('Error al obtener salas');
  });
});
