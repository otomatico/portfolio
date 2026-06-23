import { writable, derived } from 'svelte/store';

const storedUser = typeof localStorage !== 'undefined' ? localStorage.getItem('user') : null;
const storedToken = typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null;

function createAuthStore() {
  const { subscribe, set, update } = writable({
    user: storedUser ? JSON.parse(storedUser) : null,
    token: storedToken || null,
    isLoggedIn: !!storedToken,
  });

  return {
    subscribe,
    set,
    login(user, token) {
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('token', token);
      set({ user, token, isLoggedIn: true });
    },
    logout() {
      localStorage.removeItem('user');
      localStorage.removeItem('token');
      set({ user: null, token: null, isLoggedIn: false });
    },
    updateUser(user) {
      localStorage.setItem('user', JSON.stringify(user));
      update(s => ({ ...s, user }));
    },
  };
}

export const auth = createAuthStore();
export const user = derived(auth, $auth => $auth.user);
export const token = derived(auth, $auth => $auth.token);
export const isLoggedIn = derived(auth, $auth => $auth.isLoggedIn);
