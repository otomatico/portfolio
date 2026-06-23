import { describe, it, expect, vi, beforeEach } from 'vitest';
import { listar, obtener, crear, cancelar, disponibilidad } from '../../../src/frontend/src/lib/api/reservas';

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

describe('reservas API module', () => {
  beforeEach(() => {
    mockFetch.mockReset();
    localStorageMock.clear();
  });

  it('listar hace GET a /api/reservas sin filtros', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, sala_nombre: 'Sala A' }],
    });

    const result = await listar();

    expect(mockFetch).toHaveBeenCalledWith('/api/reservas', {
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result).toEqual([{ id: 1, sala_nombre: 'Sala A' }]);
  });

  it('listar construye query params con filtros', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1 }],
    });

    await listar({
      sala_id: '3',
      estado: 'confirmada',
      fecha_desde: '2026-07-01',
    });

    const url = mockFetch.mock.calls[0][0];
    expect(url).toContain('/api/reservas?');
    expect(url).toContain('sala_id=3');
    expect(url).toContain('estado=confirmada');
    expect(url).toContain('fecha_desde=2026-07-01');
  });

  it('obtener hace GET a /api/reservas/:id', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 5, sala_nombre: 'Sala B', estado: 'confirmada' }),
    });

    const result = await obtener(5);

    expect(mockFetch).toHaveBeenCalledWith('/api/reservas/5', {
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result.estado).toBe('confirmada');
  });

  it('crear hace POST a /api/reservas con los datos', async () => {
    const data = { sala_id: 1, fecha_inicio: '2026-07-10 09:00', fecha_fin: '2026-07-10 11:00' };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 10, ...data }),
    });

    const result = await crear(data);

    expect(mockFetch).toHaveBeenCalledWith('/api/reservas', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    expect(result.sala_id).toBe(1);
  });

  it('cancelar hace PUT a /api/reservas/:id/cancelar', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 1, estado: 'cancelada' }),
    });

    const result = await cancelar(1);

    expect(mockFetch).toHaveBeenCalledWith('/api/reservas/1/cancelar', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result.estado).toBe('cancelada');
  });

  it('disponibilidad hace GET a /api/salas/:id/disponibilidad?fecha=', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({
        ocupados: [{ hora_inicio: '09:00', hora_fin: '11:00' }],
        slots: [],
      }),
    });

    const result = await disponibilidad(1, '2026-07-10');

    expect(mockFetch).toHaveBeenCalledWith('/api/salas/1/disponibilidad?fecha=2026-07-10', {
      headers: { 'Content-Type': 'application/json' },
    });
    expect(result.ocupados).toHaveLength(1);
  });

  it('lanza error cuando la respuesta no es ok', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      json: async () => ({ error: 'Error en la petición' }),
    });

    await expect(listar()).rejects.toThrow('Error en la petición');
  });
});
