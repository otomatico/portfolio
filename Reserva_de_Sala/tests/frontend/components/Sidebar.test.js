import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import Sidebar from '../../../src/frontend/src/lib/layouts/Sidebar.svelte';

// Mock stores
vi.mock('../../../src/frontend/src/stores/auth', () => ({
  auth: {
    subscribe: (fn) => {
      fn({
        user: { rol: 'admin' },
        token: 'token123',
        isLoggedIn: true,
      });
      return () => {};
    },
  },
}));

vi.mock('../../../src/frontend/src/stores/permisos', () => ({
  permisos: {
    subscribe: (fn) => {
      fn([
        { componente: 'sucursales', permiso_lectura: true },
        { componente: 'salas', permiso_lectura: true },
        { componente: 'recursos', permiso_lectura: true },
        { componente: 'reservas', permiso_lectura: true },
        { componente: 'usuarios', permiso_lectura: true },
        { componente: 'maestros', permiso_lectura: true },
        { componente: 'permisos', permiso_lectura: true },
      ]);
      return () => {};
    },
  },
  permisosList: {
    subscribe: (fn) => {
      fn([]);
      return () => {};
    },
  },
}));

vi.mock('../../../src/frontend/src/stores/ui', () => ({
  sidebarOpen: {
    subscribe: (fn) => {
      fn(true);
      return () => {};
    },
  },
}));

// Mock svelte-spa-router
vi.mock('svelte-spa-router', () => ({
  link: {},
}));

// Mock LoadingSpinner
vi.mock('../../../src/frontend/src/lib/components/LoadingSpinner.svelte', () => ({
  default: {},
}));

describe('Sidebar component', () => {
  // ─── F-PER-010: Admin ve todas las opciones ───

  /**
   * @test
   * @F-PER-010
   */
  it('shows all menu items for admin', () => {
    render(Sidebar);

    expect(screen.getByText('Dashboard')).toBeTruthy();
    expect(screen.getByText('Sucursales')).toBeTruthy();
    expect(screen.getByText('Salas')).toBeTruthy();
    expect(screen.getByText('Recursos')).toBeTruthy();
    expect(screen.getByText('Reservas')).toBeTruthy();
    expect(screen.getByText('Usuarios')).toBeTruthy();
    expect(screen.getByText('Maestros')).toBeTruthy();
    expect(screen.getByText('Permisos')).toBeTruthy();
  });

  // ─── F-PER-011: Coordinador no ve opciones restringidas ───

  /**
   * @test
   * @F-PER-011
   */
  it('hides restricted items for coordinador', () => {
    // Sobrescribir mock para coordinador con permisos limitados
    const authModule = require('../../../src/frontend/src/stores/auth');
    authModule.auth.subscribe = (fn) => {
      fn({
        user: { rol: 'coordinador' },
        token: 'token123',
        isLoggedIn: true,
      });
      return () => {};
    };

    const permisosModule = require('../../../src/frontend/src/stores/permisos');
    permisosModule.permisos.subscribe = (fn) => {
      fn([
        { componente: 'sucursales', permiso_lectura: true },
        { componente: 'salas', permiso_lectura: true },
        { componente: 'recursos', permiso_lectura: true },
        { componente: 'reservas', permiso_lectura: true },
        { componente: 'usuarios', permiso_lectura: false },
        { componente: 'maestros', permiso_lectura: false },
        { componente: 'permisos', permiso_lectura: false },
      ]);
      return () => {};
    };

    render(Sidebar);

    // Elementos visibles
    expect(screen.getByText('Dashboard')).toBeTruthy();
    expect(screen.getByText('Sucursales')).toBeTruthy();
    expect(screen.getByText('Salas')).toBeTruthy();
    expect(screen.getByText('Recursos')).toBeTruthy();
    expect(screen.getByText('Reservas')).toBeTruthy();

    // Elementos que no deben aparecer para coordinador
    expect(screen.queryByText('Usuarios')).toBeNull();
    expect(screen.queryByText('Maestros')).toBeNull();
    expect(screen.queryByText('Permisos')).toBeNull();
  });
});
