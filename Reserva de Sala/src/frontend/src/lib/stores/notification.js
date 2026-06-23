import { writable } from 'svelte/store';

export const notification = writable(null);

export function showNotification(message, type = 'success') {
  notification.set({ message, type });
  setTimeout(() => notification.set(null), 4000);
}
