<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth';
  import { showNotification } from '$lib/stores/notification';
  import DataTable from '$lib/components/DataTable.svelte';
  import * as api from '$lib/api/reservas';
  import * as salasApi from '$lib/api/salas';
  import * as sucursalesApi from '$lib/api/sucursales';
  import { Plus, X, Ban } from '@lucide/svelte';

  let loading = $state(true);
  let reservas = $state([]);
  let salas = $state([]);
  let sucursales = $state([]);
  let showModal = $state(false);
  let formData = $state({ sala_id: '', fecha_inicio: '', fecha_fin: '' });
  let formErrors = $state({});
  let saving = $state(false);
  let cancelTarget = $state(null);

  let filtroSala = $state('');
  let filtroSucursal = $state('');
  let filtroEstado = $state('');
  let filtroFechaDesde = $state('');
  let filtroFechaHasta = $state('');

  let isAdmin = $derived($auth.user?.rol === 'admin');

  const columns = [
    { key: 'id', label: 'ID', width: '60px' },
    { key: 'sala_nombre', label: 'Sala' },
    { key: 'sucursal_nombre', label: 'Sucursal' },
    { key: 'fecha_inicio', label: 'Inicio' },
    { key: 'fecha_fin', label: 'Fin' },
    { key: 'usuario_nombre', label: 'Usuario' },
    {
      key: 'estado', label: 'Estado',
      render: (row) => {
        const cls = row.estado === 'confirmada' ? 'estado-confirmada' : row.estado === 'cancelada' ? 'estado-cancelada' : 'estado-pendiente';
        return `<span class="${cls}">${row.estado}</span>`;
      },
    },
  ];

  onMount(async () => {
    try {
      [salas, sucursales] = await Promise.all([salasApi.listar(), sucursalesApi.listar()]);
      await loadData();
    } catch (e) { showNotification('Error al cargar datos', 'error'); }
    finally { loading = false; }
  });

  async function loadData() {
    try {
      const filtros = {};
      if (filtroSala) filtros.sala_id = filtroSala;
      if (filtroSucursal) filtros.sucursal_id = filtroSucursal;
      if (filtroEstado) filtros.estado = filtroEstado;
      if (filtroFechaDesde) filtros.fecha_desde = filtroFechaDesde;
      if (filtroFechaHasta) filtros.fecha_hasta = filtroFechaHasta;
      reservas = await api.listar(filtros);
    } catch (e) { showNotification(e.message, 'error'); }
  }

  function openCreate() {
    const now = new Date().toISOString().slice(0, 16);
    formData = { sala_id: '', fecha_inicio: now, fecha_fin: now };
    formErrors = {}; showModal = true;
  }

  async function save() {
    formErrors = {};
    if (!formData.sala_id) formErrors.sala_id = 'La sala es obligatoria';
    if (!formData.fecha_inicio) formErrors.fecha_inicio = 'La fecha de inicio es obligatoria';
    if (!formData.fecha_fin) formErrors.fecha_fin = 'La fecha de fin es obligatoria';
    if (Object.keys(formErrors).length) return;
    saving = true;
    try {
      await api.crear({
        sala_id: parseInt(formData.sala_id),
        fecha_inicio: formData.fecha_inicio + ':00',
        fecha_fin: formData.fecha_fin + ':00',
      });
      showNotification('Reserva creada'); showModal = false; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
    finally { saving = false; }
  }

  function confirmCancel(r) {
    if (r.estado === 'cancelada') { showNotification('Ya está cancelada', 'warning'); return; }
    cancelTarget = r;
  }

  async function handleCancel() {
    if (!cancelTarget) return;
    try { await api.cancelar(cancelTarget.id); showNotification('Reserva cancelada'); cancelTarget = null; await loadData(); }
    catch (e) { showNotification(e.message, 'error'); }
  }
</script>

<div class="max-w-6xl mx-auto space-y-4">
  <header class="flex justify-between items-start">
    <div>
      <h2 class="h2 m-0">Reservas</h2>
      <p class="opacity-60 text-sm mt-1">Gestión de reservas de salas</p>
    </div>
    <button class="btn preset-filled" onclick={openCreate}><Plus size={16} /> Nueva Reserva</button>
  </header>

  {#if isAdmin}
    <div class="flex gap-3 flex-wrap">
      <select bind:value={filtroSala} onchange={loadData} class="input max-w-[200px]">
        <option value="">Todas las salas</option>
        {#each salas as s}
          <option value={s.id}>{s.nombre}</option>
        {/each}
      </select>
      <select bind:value={filtroSucursal} onchange={loadData} class="input max-w-[200px]">
        <option value="">Todas las sucursales</option>
        {#each sucursales as sc}
          <option value={sc.id}>{sc.nombre}</option>
        {/each}
      </select>
      <select bind:value={filtroEstado} onchange={loadData} class="input max-w-[180px]">
        <option value="">Todos los estados</option>
        <option value="confirmada">Confirmada</option>
        <option value="cancelada">Cancelada</option>
      </select>
      <input type="date" bind:value={filtroFechaDesde} onchange={loadData} class="input max-w-[160px]" placeholder="Desde" />
      <input type="date" bind:value={filtroFechaHasta} onchange={loadData} class="input max-w-[160px]" placeholder="Hasta" />
    </div>
  {/if}

  <div class="card p-5">
    <DataTable {columns} rows={reservas} {loading} emptyText="No hay reservas registradas">
      {#snippet children(row)}
        {#if row.estado === 'confirmada'}
          <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => confirmCancel(row)}><Ban size={16} /> Cancelar</button>
        {:else}
          <span class="text-sm italic opacity-40">Cancelada</span>
        {/if}
      {/snippet}
    </DataTable>
  </div>
</div>

{#if showModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-md mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">Nueva Reserva</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showModal = false}><X size={18} /></button>
      </div>
      <form onsubmit={save} class="space-y-4">
        <div>
          <label class="label">Sala *</label>
          <select bind:value={formData.sala_id} class="input w-full">
            <option value="">-- Seleccionar --</option>
            {#each salas as s}
              <option value={s.id}>{s.nombre} ({s.sucursal_nombre})</option>
            {/each}
          </select>
          {#if formErrors.sala_id}<span class="text-sm text-[var(--color-error-500)]">{formErrors.sala_id}</span>{/if}
        </div>
        <div>
          <label class="label">Fecha y Hora Inicio *</label>
          <input type="datetime-local" bind:value={formData.fecha_inicio} class="input w-full" />
          {#if formErrors.fecha_inicio}<span class="text-sm text-[var(--color-error-500)]">{formErrors.fecha_inicio}</span>{/if}
        </div>
        <div>
          <label class="label">Fecha y Hora Fin *</label>
          <input type="datetime-local" bind:value={formData.fecha_fin} class="input w-full" />
          {#if formErrors.fecha_fin}<span class="text-sm text-[var(--color-error-500)]">{formErrors.fecha_fin}</span>{/if}
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" class="btn preset-outlined" onclick={() => showModal = false}>Cancelar</button>
          <button type="submit" class="btn preset-filled" disabled={saving}>
            {saving ? 'Guardando...' : 'Reservar'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

{#if cancelTarget}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => cancelTarget = null} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-sm mx-4" onclick={(e) => e.stopPropagation()}>
      <h3 class="h3 m-0 mb-2">Cancelar Reserva</h3>
      <p class="opacity-60 text-sm mb-6">¿Estás seguro de cancelar la reserva #{cancelTarget.id}?</p>
      <div class="flex gap-3 justify-end">
        <button class="btn preset-outlined" onclick={() => cancelTarget = null}>Volver</button>
        <button class="btn preset-filled-error" onclick={handleCancel}>Cancelar Reserva</button>
      </div>
    </div>
  </div>
{/if}

<style>
  :global(.estado-confirmada) {
    color: var(--color-success-600);
    font-weight: 600;
    text-transform: capitalize;
  }
  :global(.estado-cancelada) {
    color: var(--color-error-600);
    font-weight: 600;
    text-transform: capitalize;
  }
  :global(.estado-pendiente) {
    color: var(--color-warning-600);
    font-weight: 600;
    text-transform: capitalize;
  }
</style>
