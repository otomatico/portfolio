import { describe, it, expect, beforeEach, vi } from 'vitest';
import { get } from 'svelte/store';
import { permisos, permisosList } from '../../../src/frontend/src/stores/permisos';

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

// Mock fetch
global.fetch = vi.fn();

describe('PermisosStore', () => {
  beforeEach(() => {
    localStorageMock.clear();
    permisos.set([]);
  });

  it('initial state is empty array', () => {
    expect(get(permisos)).toEqual([]);
  });

  it('tienePermiso returns false for empty permissions', () => {
    expect(permisos.tienePermiso('usuarios', 'GET')).toBe(false);
  });

  it('tienePermiso returns correct value for lectura', () => {
    permisos.set([
      { componente: 'usuarios', permiso_lectura: true, permiso_creacion: false },
    ]);

    expect(permisos.tienePermiso('usuarios', 'GET')).toBe(true);
    expect(permisos.tienePermiso('usuarios', 'POST')).toBe(false);
  });

  it('tienePermiso returns correct value for creacion', () => {
    permisos.set([
      { componente: 'reservas', permiso_lectura: true, permiso_creacion: true },
    ]);

    expect(permisos.tienePermiso('reservas', 'POST')).toBe(true);
  });

  it('tienePermiso returns correct value for actualizacion', () => {
    permisos.set([
      { componente: 'salas', permiso_actualizacion: true },
    ]);

    expect(permisos.tienePermiso('salas', 'PUT')).toBe(true);
  });

  it('tienePermiso returns correct value for eliminacion', () => {
    permisos.set([
      { componente: 'reservas', permiso_eliminacion: true },
    ]);

    expect(permisos.tienePermiso('reservas', 'DELETE')).toBe(true);
  });

  it('tienePermiso returns false for unknown method', () => {
    permisos.set([
      { componente: 'test', permiso_lectura: true },
    ]);

    expect(permisos.tienePermiso('test', 'PATCH')).toBe(false);
  });

  it('derived store permisosList returns all permissions', () => {
    const data = [
      { componente: 'sucursales', permiso_lectura: true },
      { componente: 'salas', permiso_lectura: true },
    ];
    permisos.set(data);

    expect(get(permisosList)).toEqual(data);
  });

  it('cargar fetches permissions from API', async () => {
    const userData = JSON.stringify({ rol: 'admin' });
    localStorageMock.getItem.mockImplementation((key) => {
      if (key === 'user') return userData;
      if (key === 'token') return 'test-token';
      return null;
    });

    const mockPermisos = [
      { componente: 'sucursales', permiso_lectura: true },
      { componente: 'usuarios', permiso_lectura: true },
    ];

    global.fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => mockPermisos,
    });

    await permisos.cargar();

    expect(get(permisos)).toEqual(mockPermisos);
  });

  it('cargar does nothing if no user in localStorage', async () => {
    localStorageMock.getItem.mockReturnValue(null);

    await permisos.cargar();

    expect(get(permisos)).toEqual([]);
  });
});
