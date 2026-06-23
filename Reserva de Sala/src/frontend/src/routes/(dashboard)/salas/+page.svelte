<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth';
  import { showNotification } from '$lib/stores/notification';
  import DataTable from '$lib/components/DataTable.svelte';
  import * as api from '$lib/api/salas';
  import * as sucursalesApi from '$lib/api/sucursales';
  import * as recursosApi from '$lib/api/recursos';
  import { Pencil, Trash2, Plus, Paperclip, X } from '@lucide/svelte';

  let loading = $state(true);
  let salas = $state([]);
  let sucursales = $state([]);
  let recursos = $state([]);
  let filtroSucursal = $state('');
  let showModal = $state(false);
  let editing = $state(false);
  let editId = $state(null);
  let formData = $state({ nombre: '', aforo: 20, descripcion: '', sucursal_id: '' });
  let formErrors = $state({});
  let saving = $state(false);
  let deleteTarget = $state(null);

  let showRecursoModal = $state(false);
  let selectedSala = $state(null);
  let recursoForm = $state({ recurso_id: '', cantidad: 1 });

  let isAdmin = $derived($auth.user?.rol === 'admin');

  const columns = [
    { key: 'id', label: 'ID', width: '60px' },
    { key: 'nombre', label: 'Nombre' },
    { key: 'aforo', label: 'Aforo' },
    { key: 'sucursal_nombre', label: 'Sucursal' },
    {
      key: 'recursos', label: 'Recursos',
      render: (row) => row.recursos?.map(r => `${r.nombre} (${r.cantidad})`).join(', ') || 'Ninguno',
    },
  ];

  onMount(async () => {
    try {
      [sucursales, recursos] = await Promise.all([sucursalesApi.listar(), recursosApi.listar()]);
      await loadData();
    } catch (e) { showNotification('Error al cargar datos', 'error'); }
    finally { loading = false; }
  });

  async function loadData() {
    try { salas = await api.listar(filtroSucursal ? parseInt(filtroSucursal) : null); }
    catch (e) { showNotification(e.message, 'error'); }
  }

  function openCreate() {
    editing = false; editId = null;
    formData = { nombre: '', aforo: 20, descripcion: '', sucursal_id: '' };
    formErrors = {}; showModal = true;
  }

  function openEdit(s) {
    editing = true; editId = s.id;
    formData = { nombre: s.nombre, aforo: s.aforo, descripcion: s.descripcion || '', sucursal_id: s.sucursal_id };
    formErrors = {}; showModal = true;
  }

  async function save() {
    formErrors = {};
    if (!formData.nombre.trim()) formErrors.nombre = 'El nombre es obligatorio';
    if (!formData.sucursal_id) formErrors.sucursal_id = 'La sucursal es obligatoria';
    if (Object.keys(formErrors).length) return;
    saving = true;
    try {
      if (editing) { await api.actualizar(editId, formData); showNotification('Sala actualizada'); }
      else { await api.crear(formData); showNotification('Sala creada'); }
      showModal = false; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
    finally { saving = false; }
  }

  function confirmDelete(s) { deleteTarget = s; }

  async function handleDelete() {
    if (!deleteTarget) return;
    try { await api.eliminar(deleteTarget.id); showNotification('Sala eliminada'); deleteTarget = null; await loadData(); }
    catch (e) { showNotification(e.message, 'error'); }
  }

  function openAsignarRecurso(s) {
    selectedSala = s; recursoForm = { recurso_id: '', cantidad: 1 }; showRecursoModal = true;
  }

  async function asignarRecurso() {
    if (!recursoForm.recurso_id) return;
    try {
      await api.asignarRecurso(selectedSala.id, parseInt(recursoForm.recurso_id), parseInt(recursoForm.cantidad));
      showNotification('Recurso asignado'); showRecursoModal = false; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
  }

  async function desasignarRecurso(salaId, recursoId) {
    try { await api.desasignarRecurso(salaId, recursoId); showNotification('Recurso desasignado'); await loadData(); }
    catch (e) { showNotification(e.message, 'error'); }
  }
</script>

<div class="max-w-6xl mx-auto space-y-4">
  <header class="flex justify-between items-start">
    <div>
      <h2 class="h2 m-0">Salas de Formación</h2>
      <p class="opacity-60 text-sm mt-1">Gestión de salas y sus recursos</p>
    </div>
    {#if isAdmin}
      <button class="btn preset-filled" onclick={openCreate}><Plus size={16} /> Nueva Sala</button>
    {/if}
  </header>

  <div class="flex gap-3">
    <select bind:value={filtroSucursal} onchange={loadData} class="input max-w-xs">
      <option value="">Todas las sucursales</option>
      {#each sucursales as s}
        <option value={s.id}>{s.nombre}</option>
      {/each}
    </select>
  </div>

  <div class="card p-5">
    <DataTable {columns} rows={salas} {loading} emptyText="No hay salas registradas">
      {#snippet children(row)}
        {#if isAdmin}
          <button class="btn btn-ghost btn-sm" onclick={() => openEdit(row)} title="Editar"><Pencil size={16} /></button>
          <button class="btn btn-ghost btn-sm" onclick={() => openAsignarRecurso(row)} title="Asignar recurso"><Paperclip size={16} /></button>
          <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => confirmDelete(row)} title="Eliminar"><Trash2 size={16} /></button>
        {:else}
          <button class="btn btn-ghost btn-sm" onclick={() => openAsignarRecurso(row)} title="Ver recursos"><Paperclip size={16} /></button>
        {/if}
      {/snippet}
    </DataTable>
  </div>
</div>

<!-- Create/Edit Modal -->
{#if showModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-md mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">{editing ? 'Editar Sala' : 'Nueva Sala'}</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showModal = false}><X size={18} /></button>
      </div>
      <form onsubmit={save} class="space-y-4">
        <div>
          <label class="label">Nombre *</label>
          <input type="text" bind:value={formData.nombre} class="input w-full" placeholder="Nombre de la sala" />
          {#if formErrors.nombre}<span class="text-sm text-[var(--color-error-500)]">{formErrors.nombre}</span>{/if}
        </div>
        <div>
          <label class="label">Aforo</label>
          <input type="number" bind:value={formData.aforo} min="1" class="input w-full" />
        </div>
        <div>
          <label class="label">Descripción</label>
          <textarea bind:value={formData.descripcion} class="input w-full min-h-[60px]" placeholder="Descripción opcional"></textarea>
        </div>
        <div>
          <label class="label">Sucursal *</label>
          <select bind:value={formData.sucursal_id} class="input w-full">
            <option value="">-- Seleccionar --</option>
            {#each sucursales as s}
              <option value={s.id}>{s.nombre}</option>
            {/each}
          </select>
          {#if formErrors.sucursal_id}<span class="text-sm text-[var(--color-error-500)]">{formErrors.sucursal_id}</span>{/if}
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" class="btn preset-outlined" onclick={() => showModal = false}>Cancelar</button>
          <button type="submit" class="btn preset-filled" disabled={saving}>
            {saving ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<!-- Asignar Recurso Modal -->
{#if showRecursoModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showRecursoModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-lg mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">Recursos de {selectedSala?.nombre}</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showRecursoModal = false}><X size={18} /></button>
      </div>

      {#if isAdmin}
        <div class="flex gap-2 items-end mb-4">
          <div class="flex-1">
            <label class="label text-xs">Recurso</label>
            <select bind:value={recursoForm.recurso_id} class="input w-full">
              <option value="">-- Seleccionar --</option>
              {#each recursos as r}
                <option value={r.id}>{r.nombre}</option>
              {/each}
            </select>
          </div>
          <div class="w-20">
            <label class="label text-xs">Cantidad</label>
            <input type="number" bind:value={recursoForm.cantidad} min="1" class="input w-full" />
          </div>
          <button class="btn preset-filled btn-sm" onclick={asignarRecurso}>Asignar</button>
        </div>
        <hr class="border-t border-[var(--color-surface-200)] my-4" />
      {/if}

      <div class="space-y-2">
        {#if selectedSala?.recursos?.length}
          {#each selectedSala.recursos as rec}
            <div class="flex justify-between items-center py-2 px-3 bg-[var(--color-surface-50)] rounded-lg">
              <span>{rec.nombre} (x{rec.cantidad})</span>
              {#if isAdmin}
                <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => desasignarRecurso(selectedSala.id, rec.recurso_id)}><X size={16} /></button>
              {/if}
            </div>
          {/each}
        {:else}
          <p class="text-center opacity-40 py-4">Sin recursos asignados</p>
        {/if}
      </div>

      <div class="flex justify-end pt-4">
        <button class="btn preset-outlined" onclick={() => showRecursoModal = false}>Cerrar</button>
      </div>
    </div>
  </div>
{/if}

{#if deleteTarget}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => deleteTarget = null} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-sm mx-4" onclick={(e) => e.stopPropagation()}>
      <h3 class="h3 m-0 mb-2">Eliminar Sala</h3>
      <p class="opacity-60 text-sm mb-6">¿Estás seguro de eliminar &ldquo;{deleteTarget.nombre}&rdquo;?</p>
      <div class="flex gap-3 justify-end">
        <button class="btn preset-outlined" onclick={() => deleteTarget = null}>Cancelar</button>
        <button class="btn preset-filled-error" onclick={handleDelete}>Eliminar</button>
      </div>
    </div>
  </div>
{/if}
