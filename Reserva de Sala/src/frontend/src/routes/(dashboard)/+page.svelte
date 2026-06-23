<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth';
  import { showNotification } from '$lib/stores/notification';
  import * as reservasApi from '$lib/api/reservas';
  import * as salasApi from '$lib/api/salas';
  import * as sucursalesApi from '$lib/api/sucursales';

  let loading = $state(true);
  let stats = $state({
    totalReservas: 0, reservasHoy: 0, totalSalas: 0, totalSucursales: 0,
  });
  let recentReservas = $state([]);

  onMount(async () => {
    try {
      const [reservas, salas, sucursales] = await Promise.all([
        reservasApi.listar(), salasApi.listar(), sucursalesApi.listar(),
      ]);
      const today = new Date().toISOString().split('T')[0];
      const reservasHoy = reservas.filter(r => r.fecha_inicio.startsWith(today) && r.estado === 'confirmada');
      stats = { totalReservas: reservas.length, reservasHoy: reservasHoy.length, totalSalas: salas.length, totalSucursales: sucursales.length };
      recentReservas = reservas.sort((a, b) => b.id - a.id).slice(0, 5);
    } catch (e) {
      showNotification('Error al cargar dashboard', 'error');
    } finally {
      loading = false;
    }
  });
</script>

<header class="mb-6">
  <h2 class="h2 m-0">Dashboard</h2>
  <p class="opacity-60 text-sm mt-1">Bienvenido, {$auth.user?.nombre || 'Usuario'}</p>
</header>

{#if loading}
  <div class="flex flex-col items-center justify-center py-16">
    <progress class="progress w-48" indeterminate></progress>
  </div>
{:else}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    {#each [
      { value: stats.totalReservas, label: 'Total Reservas', icon: '📅' },
      { value: stats.reservasHoy, label: 'Reservas Hoy', icon: '⏰' },
      { value: stats.totalSalas, label: 'Salas', icon: '🚪' },
      { value: stats.totalSucursales, label: 'Sucursales', icon: '🏢' },
    ] as stat}
      <div class="card p-5 flex items-center gap-4">
        <span class="text-3xl">{stat.icon}</span>
        <div>
          <p class="h2 m-0">{stat.value}</p>
          <p class="text-sm opacity-60 m-0">{stat.label}</p>
        </div>
      </div>
    {/each}
  </div>

  <div class="card p-5">
    <h3 class="h3 m-0 mb-4">Últimas Reservas</h3>
    {#if recentReservas.length === 0}
      <p class="text-center opacity-40 py-8">No hay reservas recientes</p>
    {:else}
      <table class="table w-full">
        <thead>
          <tr>
            <th>Sala</th><th>Inicio</th><th>Fin</th><th>Estado</th>
          </tr>
        </thead>
        <tbody>
          {#each recentReservas as r}
            <tr>
              <td>{r.sala_nombre || '-'}</td>
              <td>{r.fecha_inicio}</td>
              <td>{r.fecha_fin}</td>
              <td>
                <span class="badge uppercase" class:variant-filled-success={r.estado === 'confirmada'} class:variant-filled-error={r.estado === 'cancelada'} class:variant-filled-warning={r.estado === 'pendiente'}>
                  {r.estado}
                </span>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    {/if}
  </div>
{/if}
