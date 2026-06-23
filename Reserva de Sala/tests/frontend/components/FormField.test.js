import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import FormField from '../../../src/frontend/src/lib/components/FormField.svelte';

describe('FormField component', () => {
  it('renders label', () => {
    render(FormField, { props: { label: 'Nombre', value: '' } });
    expect(screen.getByText('Nombre')).toBeTruthy();
  });

  it('renders input field', () => {
    render(FormField, { props: { label: 'Email', type: 'email', value: 'test@test.com' } });
    const input = screen.getByLabelText('Email');
    expect(input).toBeTruthy();
    expect(input.value).toBe('test@test.com');
  });

  it('shows required asterisk', () => {
    render(FormField, { props: { label: 'Nombre', required: true, value: '' } });
    expect(screen.getByText('*')).toBeTruthy();
  });

  it('shows error message', () => {
    render(FormField, { props: { label: 'Email', error: 'Email inválido', value: '' } });
    expect(screen.getByText('Email inválido')).toBeTruthy();
  });

  it('renders select with options', () => {
    const options = [
      { id: 1, nombre: 'Opción A' },
      { id: 2, nombre: 'Opción B' },
    ];
    render(FormField, {
      props: {
        label: 'Selección',
        type: 'select',
        options,
        value: '',
      },
    });

    expect(screen.getByText('Opción A')).toBeTruthy();
    expect(screen.getByText('Opción B')).toBeTruthy();
  });

  it('renders textarea', () => {
    render(FormField, {
      props: {
        label: 'Descripción',
        type: 'textarea',
        value: 'Texto largo',
      },
    });
    const textarea = screen.getByLabelText('Descripción');
    expect(textarea).toBeTruthy();
    expect(textarea.value).toBe('Texto largo');
  });

  it('disables input when disabled prop is true', () => {
    render(FormField, {
      props: {
        label: 'Nombre',
        disabled: true,
        value: 'Test',
      },
    });
    const input = screen.getByLabelText('Nombre');
    expect(input.disabled).toBe(true);
  });

  it('does not render label when not provided', () => {
    const { container } = render(FormField, { props: { value: 'test' } });
    const labels = container.querySelectorAll('label');
    expect(labels.length).toBe(0);
  });
});
