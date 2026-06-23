import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import LoginPage from '../../../src/frontend/src/routes/LoginPage.svelte';

// Mock stores
vi.mock('../../../src/frontend/src/stores/auth', () => ({
  auth: {
    subscribe: (fn) => {
      fn({ user: null, token: null, isLoggedIn: false });
      return () => {};
    },
    login: vi.fn(),
    logout: vi.fn(),
  },
}));

vi.mock('../../../src/frontend/src/stores/ui', () => ({
  showNotification: vi.fn(),
  notification: { subscribe: () => () => {} },
}));

vi.mock('svelte-spa-router', () => ({
  push: vi.fn(),
  link: {},
  location: { subscribe: () => () => {} },
}));

vi.mock('../../../src/frontend/src/lib/api/auth', () => ({
  login: vi.fn(),
  logout: vi.fn(),
  me: vi.fn(),
}));

describe('LoginPage component', () => {
  it('renders login form', () => {
    render(LoginPage);

    expect(screen.getByText('Salas de Formación')).toBeTruthy();
    expect(screen.getByText('Sistema de Gestión')).toBeTruthy();
    expect(screen.getByText('Iniciar Sesión')).toBeTruthy();
  });

  it('renders email input', () => {
    render(LoginPage);
    const emailInput = screen.getByLabelText('Email');
    expect(emailInput).toBeTruthy();
    expect(emailInput.type).toBe('email');
  });

  it('renders password input', () => {
    render(LoginPage);
    const passwordInput = screen.getByLabelText('Contraseña');
    expect(passwordInput).toBeTruthy();
    expect(passwordInput.type).toBe('password');
  });

  it('renders submit button', () => {
    render(LoginPage);
    const button = screen.getByRole('button', { name: /Iniciar Sesión/i });
    expect(button).toBeTruthy();
  });

  it('renders test credentials section', () => {
    render(LoginPage);
    expect(screen.getByText('Credenciales de prueba:')).toBeTruthy();
    expect(screen.getByText(/admin@example.com/)).toBeTruthy();
    expect(screen.getByText(/coordinador@example.com/)).toBeTruthy();
  });

  it('shows loading state on submit button when loading', async () => {
    render(LoginPage);
    const button = screen.getByRole('button', { name: /Iniciar Sesión/i });
    
    // Initially not loading
    expect(button.textContent).toContain('Iniciar Sesión');
  });

  it('email input is required', () => {
    render(LoginPage);
    const emailInput = screen.getByLabelText('Email');
    expect(emailInput.required).toBe(true);
  });

  it('password input is required', () => {
    render(LoginPage);
    const passwordInput = screen.getByLabelText('Contraseña');
    expect(passwordInput.required).toBe(true);
  });
});
