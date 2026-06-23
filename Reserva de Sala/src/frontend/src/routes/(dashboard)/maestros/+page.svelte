<script>
  import { onMount } from 'svelte';
  import { showNotification } from '$lib/stores/notification';
  import DataTable from '$lib/components/DataTable.svelte';
  import * as maestrosApi from '$lib/api/maestros';
  import { Pencil, Trash2, Plus, List, X } from '@lucide/svelte';

  let loading = $state(true);
  let maestros = $state([]);
  let selectedMaestro = $state(null);
  let opciones = $state([]);
  let loadingOpciones = $state(false);

  let showGrupoModal = $state(false);
  let editingGrupo = $state(false);
  let grupoForm = $state({ codigo: '', nombre: '' });
  let grupoErrors = $state({});
  let savingGrupo = $state(false);

  let showOpcionModal = $state(false);
  let editingOpcion = $state(false);
  let opcionEditId = $state(null);
  let opcionForm = $state({ codigo: '', nombre: '', orden: 0, activo: true });
  let opcionErrors = $state({});
  let savingOpcion = $state(false);

  let deleteTarget = $state(null);
  let deleteType = $state('');

  const columns = [
    { key: 'codigo', label: 'Código' },
    { key: 'nombre', label: 'Nombre' },
  ];

  const opcionColumns = [
    { key: 'id', label: 'ID', width: '60px' },
    { key: 'codigo', label: 'Código' },
    { key: 'nombre', label: 'Nombre' },
    { key: 'orden', label: 'Orden' },
    {
      key: 'activo', label: 'Activo',
      render: (row) => row.activo
        ? '<span class="text-[var(--color-success-500)] font-bold">✓</span>'
        : '<span class="text-[var(--color-error-500)]">✗</span>',
    },
  ];

  onMount(loadData);

  async function loadData() {
    loading = true;
    try { maestros = await maestrosApi.listarGrupos(); }
    catch (e) { showNotification(e.message, 'error'); }
    finally { loading = false; }
  }

  async function selectMaestro(m) {
    selectedMaestro = m; await loadOpciones();
  }

  async function loadOpciones() {
    if (!selectedMaestro) return;
    loadingOpciones = true;
    try { opciones = await maestrosApi.listarOpciones(selectedMaestro.codigo); }
    catch (e) { showNotification(e.message, 'error'); }
    finally { loadingOpciones = false; }
  }

  function openCreateGrupo() {
    editingGrupo = false; grupoForm = { codigo: '', nombre: '' }; grupoErrors = {}; showGrupoModal = true;
  }

  function openEditGrupo(m) {
    editingGrupo = true; grupoForm = { codigo: m.codigo, nombre: m.nombre }; grupoErrors = {}; showGrupoModal = true;
  }

  async function saveGrupo() {
    grupoErrors = {};
    if (!grupoForm.codigo.trim()) grupoErrors.codigo = 'El código es obligatorio';
    if (!grupoForm.nombre.trim()) grupoErrors.nombre = 'El nombre es obligatorio';
    if (Object.keys(grupoErrors).length) return;
    savingGrupo = true;
    try {
      if (editingGrupo) { await maestrosApi.actualizarGrupo(grupoForm.codigo, { nombre: grupoForm.nombre }); showNotification('Grupo actualizado'); }
      else { await maestrosApi.crearGrupo({ codigo: grupoForm.codigo, nombre: grupoForm.nombre }); showNotification('Grupo creado'); }
      showGrupoModal = false; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
    finally { savingGrupo = false; }
  }

  function confirmDeleteGrupo(m) { deleteTarget = m; deleteType = 'grupo'; }

  async function handleDeleteGrupo() {
    if (!deleteTarget) return;
    try {
      await maestrosApi.eliminarGrupo(deleteTarget.codigo);
      showNotification('Grupo eliminado');
      if (selectedMaestro?.codigo === deleteTarget.codigo) { selectedMaestro = null; opciones = []; }
      deleteTarget = null; await loadData();
    } catch (e) { showNotification(e.message, 'error'); }
  }

  function openCreateOpcion() {
    if (!selectedMaestro) { showNotification('Selecciona un grupo primero', 'warning'); return; }
    editingOpcion = false; opcionEditId = null;
    opcionForm = { codigo: '', nombre: '', orden: 0, activo: true }; opcionErrors = {}; showOpcionModal = true;
  }

  function openEditOpcion(o) {
    editingOpcion = true; opcionEditId = o.id;
    opcionForm = { codigo: o.codigo, nombre: o.nombre, orden: o.orden, activo: o.activo };
    opcionErrors = {}; showOpcionModal = true;
  }

  async function saveOpcion() {
    opcionErrors = {};
    if (!opcionForm.codigo.trim()) opcionErrors.codigo = 'El código es obligatorio';
    if (!opcionForm.nombre.trim()) opcionErrors.nombre = 'El nombre es obligatorio';
    if (Object.keys(opcionErrors).length) return;
    savingOpcion = true;
    try {
      if (editingOpcion) { await maestrosApi.actualizarOpcion(opcionEditId, opcionForm); showNotification('Opción actualizada'); }
      else { await maestrosApi.crearOpcion(selectedMaestro.codigo, opcionForm); showNotification('Opción creada'); }
      showOpcionModal = false; await loadOpciones();
    } catch (e) { showNotification(e.message, 'error'); }
    finally { savingOpcion = false; }
  }

  function confirmDeleteOpcion(o) { deleteTarget = o; deleteType = 'opcion'; }

  async function handleDeleteOpcion() {
    if (!deleteTarget) return;
    try { await maestrosApi.eliminarOpcion(deleteTarget.id); showNotification('Opción eliminada'); deleteTarget = null; await loadOpciones(); }
    catch (e) { showNotification(e.message, 'error'); }
  }
</script>

<div class="max-w-6xl mx-auto space-y-6">
  <header class="flex justify-between items-start">
    <div>
      <h2 class="h2 m-0">Datos Maestros</h2>
      <p class="opacity-60 text-sm mt-1">Gestión de grupos y opciones del sistema</p>
    </div>
    <button class="btn preset-filled" onclick={openCreateGrupo}><Plus size={16} /> Nuevo Grupo</button>
  </header>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card p-5">
      <h3 class="h3 m-0 mb-4">Grupos Maestros</h3>
      <DataTable {columns} rows={maestros} {loading} emptyText="No hay grupos registrados">
        {#snippet children(row)}
          <button class="btn btn-ghost btn-sm" onclick={() => selectMaestro(row)} title="Ver opciones"><List size={16} /></button>
          <button class="btn btn-ghost btn-sm" onclick={() => openEditGrupo(row)}><Pencil size={16} /></button>
          <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => confirmDeleteGrupo(row)}><Trash2 size={16} /></button>
        {/snippet}
      </DataTable>
    </div>

    <div class="card p-5">
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">
          {selectedMaestro ? `Opciones: ${selectedMaestro.nombre}` : 'Opciones'}
        </h3>
        {#if selectedMaestro}
          <button class="btn preset-filled btn-sm" onclick={openCreateOpcion}><Plus size={14} /> Opción</button>
        {/if}
      </div>

      {#if !selectedMaestro}
        <p class="text-center opacity-40 py-12">Selecciona un grupo para ver sus opciones</p>
      {:else}
        <DataTable columns={opcionColumns} rows={opciones} loading={loadingOpciones} emptyText="Este grupo no tiene opciones">
          {#snippet children(row)}
            <button class="btn btn-ghost btn-sm" onclick={() => openEditOpcion(row)}><Pencil size={16} /></button>
            <button class="btn btn-ghost btn-sm text-[var(--color-error-500)]" onclick={() => confirmDeleteOpcion(row)}><Trash2 size={16} /></button>
          {/snippet}
        </DataTable>
      {/if}
    </div>
  </div>
</div>

<!-- Grupo Modal -->
{#if showGrupoModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showGrupoModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-md mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">{editingGrupo ? 'Editar Grupo' : 'Nuevo Grupo'}</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showGrupoModal = false}><X size={18} /></button>
      </div>
      <form onsubmit={saveGrupo} class="space-y-4">
        <div>
          <label class="label">Código *</label>
          <input type="text" bind:value={grupoForm.codigo} class="input w-full" placeholder="codigo_unico" disabled={editingGrupo} />
          {#if grupoErrors.codigo}<span class="text-sm text-[var(--color-error-500)]">{grupoErrors.codigo}</span>{/if}
        </div>
        <div>
          <label class="label">Nombre *</label>
          <input type="text" bind:value={grupoForm.nombre} class="input w-full" placeholder="Nombre del grupo" />
          {#if grupoErrors.nombre}<span class="text-sm text-[var(--color-error-500)]">{grupoErrors.nombre}</span>{/if}
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" class="btn preset-outlined" onclick={() => showGrupoModal = false}>Cancelar</button>
          <button type="submit" class="btn preset-filled" disabled={savingGrupo}>
            {savingGrupo ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<!-- Opcion Modal -->
{#if showOpcionModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => showOpcionModal = false} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-md mx-4" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4">
        <h3 class="h3 m-0">{editingOpcion ? 'Editar Opción' : 'Nueva Opción'}</h3>
        <button class="btn btn-ghost btn-sm" onclick={() => showOpcionModal = false}><X size={18} /></button>
      </div>
      <form onsubmit={saveOpcion} class="space-y-4">
        <div>
          <label class="label">Código *</label>
          <input type="text" bind:value={opcionForm.codigo} class="input w-full" placeholder="codigo_opcion" disabled={editingOpcion} />
          {#if opcionErrors.codigo}<span class="text-sm text-[var(--color-error-500)]">{opcionErrors.codigo}</span>{/if}
        </div>
        <div>
          <label class="label">Nombre *</label>
          <input type="text" bind:value={opcionForm.nombre} class="input w-full" placeholder="Nombre de la opción" />
          {#if opcionErrors.nombre}<span class="text-sm text-[var(--color-error-500)]">{opcionErrors.nombre}</span>{/if}
        </div>
        <div>
          <label class="label">Orden</label>
          <input type="number" bind:value={opcionForm.orden} class="input w-full" />
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" bind:checked={opcionForm.activo} id="activo" class="checkbox" />
          <label for="activo" class="text-sm">Activo</label>
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" class="btn preset-outlined" onclick={() => showOpcionModal = false}>Cancelar</button>
          <button type="submit" class="btn preset-filled" disabled={savingOpcion}>
            {savingOpcion ? 'Guardando...' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<!-- Delete Confirm Modals -->
{#if deleteTarget && deleteType === 'grupo'}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => deleteTarget = null} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-sm mx-4" onclick={(e) => e.stopPropagation()}>
      <h3 class="h3 m-0 mb-2">Eliminar Grupo</h3>
      <p class="opacity-60 text-sm mb-6">¿Estás seguro de eliminar &ldquo;{deleteTarget.nombre}&rdquo;?</p>
      <div class="flex gap-3 justify-end">
        <button class="btn preset-outlined" onclick={() => deleteTarget = null}>Cancelar</button>
        <button class="btn preset-filled-error" onclick={handleDeleteGrupo}>Eliminar</button>
      </div>
    </div>
  </div>
{/if}

{#if deleteTarget && deleteType === 'opcion'}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick={() => deleteTarget = null} role="dialog" aria-modal="true">
    <div class="card p-6 w-full max-w-sm mx-4" onclick={(e) => e.stopPropagation()}>
      <h3 class="h3 m-0 mb-2">Eliminar Opción</h3>
      <p class="opacity-60 text-sm mb-6">¿Estás seguro de eliminar &ldquo;{deleteTarget.nombre}&rdquo;?</p>
      <div class="flex gap-3 justify-end">
        <button class="btn preset-outlined" onclick={() => deleteTarget = null}>Cancelar</button>
        <button class="btn preset-filled-error" onclick={handleDeleteOpcion}>Eliminar</button>
      </div>
    </div>
  </div>
{/if}
