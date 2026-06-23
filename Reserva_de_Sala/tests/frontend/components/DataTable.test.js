import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import DataTable from '../../../src/frontend/src/lib/components/DataTable.svelte';

describe('DataTable component', () => {
  const columns = [
    { key: 'nombre', label: 'Nombre' },
    { key: 'email', label: 'Email' },
    { key: 'rol', label: 'Rol' },
  ];

  const rows = [
    { nombre: 'Admin', email: 'admin@test.com', rol: 'admin' },
    { nombre: 'Coord', email: 'coord@test.com', rol: 'coordinador' },
  ];

  it('renders column headers', () => {
    render(DataTable, { props: { columns, rows } });

    expect(screen.getByText('Nombre')).toBeTruthy();
    expect(screen.getByText('Email')).toBeTruthy();
    expect(screen.getByText('Rol')).toBeTruthy();
  });

  it('renders row data', () => {
    render(DataTable, { props: { columns, rows } });

    expect(screen.getByText('Admin')).toBeTruthy();
    expect(screen.getByText('admin@test.com')).toBeTruthy();
    expect(screen.getByText('coord@test.com')).toBeTruthy();
  });

  it('shows empty state when no rows', () => {
    render(DataTable, { props: { columns, rows: [], emptyText: 'No hay datos' } });

    expect(screen.getByText('No hay datos')).toBeTruthy();
  });

  it('shows loading spinner when loading', () => {
    render(DataTable, { props: { columns, rows: [], loading: true } });

    expect(screen.getByText('Cargando...')).toBeTruthy();
  });

  it('hides columns marked as not visible', () => {
    const colsWithHidden = [
      { key: 'nombre', label: 'Nombre' },
      { key: 'email', label: 'Email', visible: false },
    ];
    render(DataTable, { props: { columns: colsWithHidden, rows } });

    expect(screen.getByText('Nombre')).toBeTruthy();
    expect(screen.queryByText('Email')).toBeNull();
  });

  it('renders custom cell render function', () => {
    const colsWithRender = [
      {
        key: 'nombre',
        label: 'Nombre',
        render: (row) => `**${row.nombre}**`,
      },
    ];
    render(DataTable, { props: { columns: colsWithRender, rows } });

    expect(screen.getByText('**Admin**')).toBeTruthy();
  });

  it('shows dash for missing cell values', () => {
    const rowsWithMissing = [{ nombre: 'Test' }];
    render(DataTable, { props: { columns, rows: rowsWithMissing } });

    const dashes = screen.getAllByText('-');
    expect(dashes.length).toBeGreaterThan(0);
  });
});
