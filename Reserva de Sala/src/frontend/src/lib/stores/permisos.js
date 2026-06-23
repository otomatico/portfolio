import { writable, derived } from 'svelte/store';

function createPermisosStore() {
  const { subscribe, set } = writable([]);

  return {
    subscribe,
    set,
    async cargar() {
      try {
        const user = localStorage.getItem('user');
        if (!user) return;

        const { rol } = JSON.parse(user);
        const token = localStorage.getItem('token');

        const response = await fetch(`/api/permisos/${rol}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (response.ok) {
          const data = await response.json();
          set(data);
        }
      } catch (e) {
        console.error('Error al cargar permisos:', e);
      }
    },
    tienePermiso(componente, metodo = 'GET') {
      let result = false;
      const unsubscribe = subscribe(permisos => {
        const permiso = permisos.find(p => p.componente === componente);
        if (permiso) {
          switch (metodo) {
            case 'GET': result = permiso.permiso_lectura; break;
            case 'POST': result = permiso.permiso_creacion; break;
            case 'PUT': result = permiso.permiso_actualizacion; break;
            case 'DELETE': result = permiso.permiso_eliminacion; break;
          }
        }
      });
      unsubscribe();
      return result;
    },
  };
}

export const permisos = createPermisosStore();
export const permisosList = derived(permisos, $permisos => $permisos);
