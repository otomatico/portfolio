<script>
  import { onMount } from 'svelte';
  import { showNotification } from '$lib/stores/notification';
  import * as api from '$lib/api/permisos';

  let loading = $state(true);
  let saving = $state(false);
  let roles = $state(['admin', 'coordinador']);
  let componentes = $state(['sucursales', 'salas', 'recursos', 'reservas', 'usuarios', 'maestros', 'permisos']);
  let permisosMatrix = $state({});

  const permisoLabels = {
    permiso_lectura: 'Leer',
    permiso_creacion: 'Crear',
    permiso_actualizacion: 'Editar',
    permiso_eliminacion: 'Borrar',
  };

  onMount(loadData);

  async function loadData() {
    loading = true;
    try {
      const allPermisos = await api.listarTodos();
      permisosMatrix = {};
      for (const rol of roles) {
        permisosMatrix[rol] = {};
        for (const comp of componentes) {
          permisosMatrix[rol][comp] = { permiso_lectura: false, permiso_creacion: false, permiso_actualizacion: false, permiso_eliminacion: false };
        }
      }
      for (const p of allPermisos) {
        if (permisosMatrix[p.rol] && permisosMatrix[p.rol][p.componente]) {
          permisosMatrix[p.rol][p.componente] = {
            permiso_lectura: !!p.permiso_lectura,
            permiso_creacion: !!p.permiso_creacion,
            permiso_actualizacion: !!p.permiso_actualizacion,
            permiso_eliminacion: !!p.permiso_eliminacion,
          };
        }
      }
    } catch (e) { showNotification('Error al cargar permisos', 'error'); }
    finally { loading = false; }
  }

  async function togglePermiso(rol, componente, permiso) {
    const current = permisosMatrix[rol][componente][permiso];
    permisosMatrix[rol][componente][permiso] = !current;
    saving = true;
    try {
      const data = permisosMatrix[rol][componente];
      await api.actualizar(rol, componente, data);
      showNotification(`Permiso actualizado para ${rol}/${componente}`);
    } catch (e) {
      permisosMatrix[rol][componente][permiso] = current;
      showNotification(e.message, 'error');
    } finally { saving = false; }
  }
</script>

<div class="max-w-6xl mx-auto space-y-6">
  <header class="flex justify-between items-start">
    <div>
      <h2 class="h2 m-0">Matriz de Permisos</h2>
      <p class="opacity-60 text-sm mt-1">Gestión de permisos por rol y componente</p>
    </div>
  </header>

  {#if loading}
    <div class="flex flex-col items-center justify-center py-16">
      <progress class="progress w-48" indeterminate />
    </div>
  {:else}
    <div class="card p-5 overflow-x-auto">
      <div class="overflow-x-auto">
        <table class="table w-full text-sm min-w-[600px]">
          <thead>
            <tr>
              <th class="sticky left-0 bg-white z-10 text-left">Componente</th>
              {#each roles as rol}
                <th colspan="4" class="text-center bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-bold capitalize">{rol}</th>
              {/each}
            </tr>
            <tr>
              <th class="sticky left-0 bg-white z-10"></th>
              {#each roles as rol}
                {#each Object.values(permisoLabels) as label}
                  <th class="text-center text-xs font-medium opacity-60">{label}</th>
                {/each}
              {/each}
            </tr>
          </thead>
          <tbody>
            {#each componentes as comp}
              <tr>
                <td class="sticky left-0 bg-white z-10 font-medium capitalize">{comp}</td>
                {#each roles as rol}
                  {#each Object.keys(permisoLabels) as permisoKey}
                    <td class="text-center p-1">
                      <button
                        class="w-8 h-8 rounded-md border-2 transition-all duration-150 flex items-center justify-center font-bold text-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        class:border-[var(--color-primary-500)]!={permisosMatrix[rol]?.[comp]?.[permisoKey]}
                        class:bg-[var(--color-primary-500)]!={permisosMatrix[rol]?.[comp]?.[permisoKey]}
                        class:text-white!={permisosMatrix[rol]?.[comp]?.[permisoKey]}
                        class:border-[var(--color-surface-300)]={!permisosMatrix[rol]?.[comp]?.[permisoKey]}
                        class:bg-white={!permisosMatrix[rol]?.[comp]?.[permisoKey]}
                        class:text-[var(--color-surface-400)]={!permisosMatrix[rol]?.[comp]?.[permisoKey]}
                        onclick={() => togglePermiso(rol, comp, permisoKey)}
                        disabled={saving}
                      >
                        {permisosMatrix[rol]?.[comp]?.[permisoKey] ? '✓' : '-'}
                      </button>
                    </td>
                  {/each}
                {/each}
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>
  {/if}
</div>
