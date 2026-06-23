import { describe, it, expect, beforeEach, vi } from 'vitest';
import { get } from 'svelte/store';
import { auth, user, token, isLoggedIn } from '../../../src/frontend/src/stores/auth';

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

describe('AuthStore', () => {
  beforeEach(() => {
    localStorageMock.clear();
    // Reset store to initial state
    auth.logout();
  });

  it('initial state is logged out', () => {
    expect(get(auth).user).toBeNull();
    expect(get(auth).token).toBeNull();
    expect(get(auth).isLoggedIn).toBe(false);
  });

  it('login updates user, token, and isLoggedIn', () => {
    const userData = { id: 1, email: 'admin@test.com', rol: 'admin' };
    auth.login(userData, 'test-token-123');

    expect(get(auth).user).toEqual(userData);
    expect(get(auth).token).toBe('test-token-123');
    expect(get(auth).isLoggedIn).toBe(true);
  });

  it('login stores data in localStorage', () => {
    const userData = { id: 1, email: 'admin@test.com', rol: 'admin' };
    auth.login(userData, 'test-token-123');

    expect(localStorageMock.setItem).toHaveBeenCalledWith('user', JSON.stringify(userData));
    expect(localStorageMock.setItem).toHaveBeenCalledWith('token', 'test-token-123');
  });

  it('logout clears user, token, and isLoggedIn', () => {
    const userData = { id: 1, email: 'admin@test.com', rol: 'admin' };
    auth.login(userData, 'test-token-123');
    auth.logout();

    expect(get(auth).user).toBeNull();
    expect(get(auth).token).toBeNull();
    expect(get(auth).isLoggedIn).toBe(false);
  });

  it('logout removes data from localStorage', () => {
    auth.logout();

    expect(localStorageMock.removeItem).toHaveBeenCalledWith('user');
    expect(localStorageMock.removeItem).toHaveBeenCalledWith('token');
  });

  it('updateUser updates user in store', () => {
    const userData = { id: 1, email: 'admin@test.com', rol: 'admin', nombre: 'Admin' };
    auth.login(userData, 'token');
    
    auth.updateUser({ ...userData, nombre: 'Admin Actualizado' });

    expect(get(auth).user.nombre).toBe('Admin Actualizado');
  });

  it('derived store user returns correct user', () => {
    const userData = { id: 1, email: 'test@test.com', rol: 'admin' };
    auth.login(userData, 'token');

    expect(get(user)).toEqual(userData);
  });

  it('derived store token returns correct token', () => {
    auth.login({ id: 1 }, 'my-token');

    expect(get(token)).toBe('my-token');
  });

  it('derived store isLoggedIn returns correct status', () => {
    expect(get(isLoggedIn)).toBe(false);

    auth.login({ id: 1 }, 'token');
    expect(get(isLoggedIn)).toBe(true);
  });
});
