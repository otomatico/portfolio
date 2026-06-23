import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import ReservasPage from '../../../src/frontend/src/routes/ReservasPage.svelte';

// Mock stores
vi.mock('../../../src/frontend/src/stores/auth', () => ({
  auth: {
    subscribe: (fn) => {
      fn({ user: { rol: 'admin', id: 1, email: 'admin@test.com' }, token: 'token', isLoggedIn: true });
      return () => {};
    },
  },
}));

vi.mock('../../../src/frontend/src/stores/ui', () => ({
  showNotification: vi.fn(),
  notification: { subscribe: () => () => {} },
}));

// Mock API modules
vi.mock('../../../src/frontend/src/lib/api/reservas', () => ({
  listar: vi.fn().mockResolvedValue([
    { id: 1, sala_nombre: 'Sala A', sucursal_nombre: 'Centro', fecha_inicio: '2026-07-10 09:00', fecha_fin: '2026-07-10 11:00', usuario_nombre: 'Admin', estado: 'confirmada' },
  ]),
  crear: vi.fn(),
  obtener: vi.fn(),
  cancelar: vi.fn(),
}));

vi.mock('../../../src/frontend/src/lib/api/salas', () => ({
  listar: vi.fn().mockResolvedValue([
    { id: 1, nombre: 'Sala A', sucursal_nombre: 'Centro' },
  ]),
}));

vi.mock('../../../src/frontend/src/lib/api/sucursales', () => ({
  listar: vi.fn().mockResolvedValue([
    { id: 1, nombre: 'Centro' },
  ]),
}));

// Mock child components
vi.mock('../../../src/frontend/src/lib/components/DataTable.svelte', () => ({
  default: { render: () => {} },
}));
vi.mock('../../../src/frontend/src/lib/components/LoadingSpinner.svelte', () => ({
  default: { render: () => {} },
}));
vi.mock('../../../src/frontend/src/lib/components/ConfirmModal.svelte', () => ({
  default: { render: () => {} },
}));

describe('ReservasPage component', () => {
  it('renders page title and description', () => {
    render(ReservasPage);

    expect(screen.getByText('Reservas')).toBeTruthy();
    expect(screen.getByText('Gestión de reservas de salas')).toBeTruthy();
  });

  it('renders create reservation button', () => {
    render(ReservasPage);

    const createButton = screen.getByText('+ Nueva Reserva');
    expect(createButton).toBeTruthy();
  });

  it('renders filter selects for admin', () => {
    render(ReservasPage);

    expect(screen.getByText('Todas las salas')).toBeTruthy();
    expect(screen.getByText('Todas las sucursales')).toBeTruthy();
    expect(screen.getByText('Todos los estados')).toBeTruthy();
  });

  it('renders filter options for estados', () => {
    render(ReservasPage);

    expect(screen.getByText('Confirmada')).toBeTruthy();
    expect(screen.getByText('Cancelada')).toBeTruthy();
  });
});
