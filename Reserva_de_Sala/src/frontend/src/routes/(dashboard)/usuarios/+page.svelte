<script>
  import { onMount } from 'svelte';
  import { showNotification } from '$lib/stores/notification';
  import DataTable from '$lib/components/DataTable.svelte';
  import * as api from '$lib/api/usuarios';
  import * as sucursalesApi from '$lib/api/sucursales';
  import { Pencil, Trash2, Plus, X } from '@lucide/svelte';

  let loading = $state(true);
  let usuarios = $state([]);
  let sucursales = $state([]);
  let showModal = $state(false);
  let editing = $state(false);
  let editId = $state(null);
  let formData = $state({ nombre: '', email: '', password: '', rol: 'coordinador', sucursal_id: '' });
  let formErrors = $state({});
  let saving = $state(false);
  let deleteTarget = $state(null);

  const columns = [
    { key: 'id', label: 'ID', width: '60px' },
    { key: 'nombre', label: 'Nombre' },
    { key: 'email', label: 'Email' },
    {
      key: 'rol', label: 'Rol',
      render: (row) => `<span class="badge ${row.rol === 'admin' ? 'variant-filled-primary' : 'variant-filled-warning'}">${row.rol}</span>`,
    },
    { key: 'sucursal_nombre', label: 'Sucursal' },
  ];

  onMount(async () => {
    try { sucursales = await sucursalesApi.listar(); await loadData(); }
    catch (e) { showNotification('Error al cargar datos', 'error'); }
    finally { loading = false; }
  });

  async function loadData() {
    try { usuarios = await api.listar(); }
    catch (e) { showNotification(e.message, 'error'); }
  }

  function openCreate() {
    editing = false; editId = null;
    formData = { nombre: '', email: '', password: '', rol: 'coordinador', sucursal_id: '' };
    formErrors = {}; showModal = true;
  }

  function openEdit(u) {
    editing = true; editId = u.id;
    formData = { nombre: u.nombre, email: u.email, password: '', rol: u.rol, sucursal_id: u.sucursal_id || '' };
    formErrors = {}; showModal = true;
  }

  async function save() {
    formErrors = {};
    if (!formData.nombre.trim()) formErrors.nombre = 'El nombre es obligatorio';
    if (!formData.email.trim()) formErrors.email = 'El email es obligatorio';
    if (!editing && !formData.password) formErrors.password = 'La contraseña es obligatoria';
    if (formData.rol === 'coordinador' && !formData.sucursal_id) formErrors.sucursal_id = 'El coordinador debe tener una sucursal';
    if (Object.keys(formErrors).length) return;
    saving = true;
    try {
      const payload = { ...formData };
      if (!payload.password) delete payload.password;
      if (!payload.sucursal_id) payload.sucursal_id = null;
      if (editing) { await api.actualizar(editId, payload); showNotification('Usuario actualizado'); }
      else { await api.crear(payload); showNotification('Usuario creado'); }
      showModal = false; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
    finally { saving = false; }
  }

  function confirmDelete(u) { deleteTarget = u; }

  async function handleDelete() {
    if (!deleteTarget) return;
    try { await api.eliminar(deleteTarget.id); showNotification('Usuario eliminado'); deleteTarget = null; await loadData(); }
    catch (e) { showNotification(e.message, 'error'); }
  }
</script>

<div class="max-w-6xl mx-auto space-y-6">
  <header class="flex justify-between items-start">
    <div>
      <h2 class="h2 m-0">Usuarios</h2>
      <p class="opacity-60 text-sm mt-1">Gestión de usuarios del sistema</p>
    </div>
    <button class="btn preset-filled" onclick={openCreate}><Plus size={16} /> Nuevo Usuario</button>
  </header>

  <div class="card p-5">
    <DataTable {columns} rows={usuarios} {loading} emptyText="No hay usuarios registrados">
      {#snippet children(row)}
        <button class="btn btn-ghost btn-sm" onclick={() => openEdit(row)}><Pencil size={16} /></button>
        <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => confirmDelete(row)}><Trash2 size={16} /></button>
      {/snippet}
    </DataTable>
  </div>
</div>

{#if showModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-md mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">{editing ? 'Editar Usuario' : 'Nuevo Usuario'}</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showModal = false}><X size={18} /></button>
      </div>
      <form onsubmit={save} class="space-y-4">
        <div>
          <label class="label">Nombre *</label>
          <input type="text" bind:value={formData.nombre} class="input w-full" placeholder="Nombre completo" />
          {#if formErrors.nombre}<span class="text-sm text-[var(--color-error-500)]">{formErrors.nombre}</span>{/if}
        </div>
        <div>
          <label class="label">Email *</label>
          <input type="email" bind:value={formData.email} class="input w-full" placeholder="email@ejemplo.com" />
          {#if formErrors.email}<span class="text-sm text-[var(--color-error-500)]">{formErrors.email}</span>{/if}
        </div>
        <div>
          <label class="label">Contraseña {editing ? '(dejar vacío para mantener)' : '*'}</label>
          <input type="password" bind:value={formData.password} class="input w-full" placeholder="••••••••" />
          {#if formErrors.password}<span class="text-sm text-[var(--color-error-500)]">{formErrors.password}</span>{/if}
        </div>
        <div>
          <label class="label">Rol *</label>
          <select bind:value={formData.rol} class="input w-full">
            <option value="coordinador">Coordinador</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        {#if formData.rol === 'coordinador'}
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
        {/if}
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

{#if deleteTarget}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => deleteTarget = null} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-sm mx-4" onclick={(e) => e.stopPropagation()}>
      <h3 class="h3 m-0 mb-2">Eliminar Usuario</h3>
      <p class="opacity-60 text-sm mb-6">¿Estás seguro de eliminar a &ldquo;{deleteTarget.nombre}&rdquo;?</p>
      <div class="flex gap-3 justify-end">
        <button class="btn preset-outlined" onclick={() => deleteTarget = null}>Cancelar</button>
        <button class="btn preset-filled-error" onclick={handleDelete}>Eliminar</button>
      </div>
    </div>
  </div>
{/if}
