<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth';
  import { showNotification } from '$lib/stores/notification';
  import DataTable from '$lib/components/DataTable.svelte';
  import * as api from '$lib/api/sucursales';
  import { Pencil, Trash2, Plus, X } from '@lucide/svelte';

  let loading = $state(true);
  let sucursales = $state([]);
  let showModal = $state(false);
  let editing = $state(false);
  let editId = $state(null);
  let formData = $state({ nombre: '', direccion: '' });
  let formErrors = $state({});
  let saving = $state(false);
  let deleteTarget = $state(null);

  let isAdmin = $derived($auth.user?.rol === 'admin');

  const columns = [
    { key: 'id', label: 'ID', width: '60px' },
    { key: 'nombre', label: 'Nombre' },
    { key: 'direccion', label: 'Dirección' },
  ];

  onMount(loadData);

  async function loadData() {
    loading = true;
    try { sucursales = await api.listar(); }
    catch (e) { showNotification(e.message, 'error'); }
    finally { loading = false; }
  }

  function openCreate() {
    editing = false; editId = null;
    formData = { nombre: '', direccion: '' }; formErrors = {}; showModal = true;
  }

  function openEdit(s) {
    editing = true; editId = s.id;
    formData = { nombre: s.nombre, direccion: s.direccion }; formErrors = {}; showModal = true;
  }

  async function save() {
    formErrors = {};
    if (!formData.nombre.trim()) { formErrors.nombre = 'El nombre es obligatorio'; return; }
    saving = true;
    try {
      if (editing) { await api.actualizar(editId, formData); showNotification('Sucursal actualizada'); }
      else { await api.crear(formData); showNotification('Sucursal creada'); }
      showModal = false; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
    finally { saving = false; }
  }

  function confirmDelete(s) { deleteTarget = s; }

  async function handleDelete() {
    if (!deleteTarget) return;
    try {
      await api.eliminar(deleteTarget.id); showNotification('Sucursal eliminada');
      deleteTarget = null; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
  }
</script>

<div class="max-w-6xl mx-auto space-y-6">
  <header class="flex justify-between items-start">
    <div>
      <h2 class="h2 m-0">Sucursales</h2>
      <p class="opacity-60 text-sm mt-1">Gestión de sucursales del call center</p>
    </div>
    {#if isAdmin}
      <button class="btn preset-filled" onclick={openCreate}>
        <Plus size={16} /> Nueva Sucursal
      </button>
    {/if}
  </header>

  <div class="card p-5">
    <DataTable {columns} rows={sucursales} {loading} emptyText="No hay sucursales registradas">
      {#snippet children(row)}
        {#if isAdmin}
          <button class="btn btn-ghost btn-sm" onclick={() => openEdit(row)}><Pencil size={16} /></button>
          <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => confirmDelete(row)}><Trash2 size={16} /></button>
        {/if}
      {/snippet}
    </DataTable>
  </div>
</div>

<!-- Modal -->
{#if showModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-md mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">{editing ? 'Editar Sucursal' : 'Nueva Sucursal'}</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showModal = false}><X size={18} /></button>
      </div>
      <form onsubmit={save} class="space-y-4">
        <div>
          <label class="label">Nombre *</label>
          <input type="text" bind:value={formData.nombre} class="input w-full" placeholder="Nombre de la sucursal" />
          {#if formErrors.nombre}<span class="text-sm text-[var(--color-error-500)]">{formErrors.nombre}</span>{/if}
        </div>
        <div>
          <label class="label">Dirección</label>
          <input type="text" bind:value={formData.direccion} class="input w-full" placeholder="Dirección de la sucursal" />
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

<!-- Delete Confirm Modal -->
{#if deleteTarget}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => deleteTarget = null} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-sm mx-4" onclick={(e) => e.stopPropagation()}>
      <h3 class="h3 m-0 mb-2">Eliminar Sucursal</h3>
      <p class="opacity-60 text-sm mb-6">¿Estás seguro de eliminar &ldquo;{deleteTarget.nombre}&rdquo;?</p>
      <div class="flex gap-3 justify-end">
        <button class="btn preset-outlined" onclick={() => deleteTarget = null}>Cancelar</button>
        <button class="btn preset-filled-error" onclick={handleDelete}>Eliminar</button>
      </div>
    </div>
  </div>
{/if}
