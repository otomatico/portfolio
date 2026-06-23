import { writable } from 'svelte/store';

export const sidebarOpen = writable(true);
export const loading = writable(false);

export function toggleSidebar() {
  sidebarOpen.update(v => !v);
}
