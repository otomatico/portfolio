<script>
  import { page } from '$app/stores';
  import { auth } from '$lib/stores/auth';
  import { permisos } from '$lib/stores/permisos';
  import { sidebarOpen } from '$lib/stores/ui';
  import { notification } from '$lib/stores/notification';

  let { children } = $props();
  import {
    LayoutDashboard, Building2, DoorOpen, Package,
    Calendar, Users, Settings, Shield,
    Menu, LogOut, User as UserIcon,
    ChevronDown
  } from '@lucide/svelte';

  let showDropdown = $state(false);
  let userMenuRef = $state(null);

  $effect(() => {
    permisos.cargar();
  });

  let userPermisos = $state([]);
  $effect(() => {
    const unsub = permisos.subscribe(p => { userPermisos = p; });
    return unsub;
  });

  const menuItems = [
    { path: '/', label: 'Dashboard', icon: LayoutDashboard, componente: 'dashboard' },
    { path: '/sucursales', label: 'Sucursales', icon: Building2, componente: 'sucursales' },
    { path: '/salas', label: 'Salas', icon: DoorOpen, componente: 'salas' },
    { path: '/recursos', label: 'Recursos', icon: Package, componente: 'recursos' },
    { path: '/reservas', label: 'Reservas', icon: Calendar, componente: 'reservas' },
    {
      label: 'Ajustes',
      icon: Settings,
      children: [
        { path: '/usuarios', label: 'Usuarios', icon: Users, componente: 'usuarios' },
        { path: '/maestros', label: 'Maestros', icon: Settings, componente: 'maestros' },
        { path: '/permisos', label: 'Permisos', icon: Shield, componente: 'permisos' },
      ],
    },
  ];

  // --- Helper functions ---
  function isActive(path) {
    return $page.url.pathname === path;
  }

  function isChildActive(children) {
    const currentPath = $page.url.pathname;
    return children?.some(child =>
      currentPath === child.path || currentPath.startsWith(child.path + '/')
    );
  }

  function tieneLectura(componente) {
    const p = userPermisos.find(p => p.componente === componente);
    return p ? p.permiso_lectura : false;
  }

  // --- Estado para submenús colapsables ---
  // Tristate: undefined = auto (sigue la ruta activa), true = abierto, false = cerrado
  let submenuState = $state({});

  function toggleSubmenu(label) {
    const current = submenuState[label];
    // Si está auto (undefined) o abierto → cerrar; si está cerrado → abrir
    submenuState = { ...submenuState, [label]: current === false };
  }

  function isSubmenuOpen(item) {
    if (!item.children) return false;
    const state = submenuState[item.label];
    // Si el usuario nunca tocó este submenú, sigue la ruta activa
    if (state === undefined) return isChildActive(item.children);
    return state;
  }

  /**
   * Filtra ítems del menú según permisos.
   * 
   * CRÍTICO: `userPermisos` se referencia DIRECTAMENTE aquí para que Svelte 5
   * registre la dependencia reactiva. NO usar una función helper externa con
   * $derived.by, porque el callback de `subscribe()` actualiza userPermisos
   * fuera del contexto reactivo y el tracking se pierde.
   */
  let visibleMenuItems = $derived(
    menuItems.filter(item => {
      if (item.children) {
        // Submenú: solo visible si al menos un hijo tiene permiso de lectura
        return item.children.some(child =>
          userPermisos.find(p => p.componente === child.componente)?.permiso_lectura ?? false
        );
      }
      // Ítem simple: visible solo si tiene permiso de lectura
      return userPermisos.find(p => p.componente === item.componente)?.permiso_lectura ?? false;
    })
  );

  let currentUser = $state(null);
  $effect(() => {
    const unsub = auth.subscribe(a => { currentUser = a.user; });
    return unsub;
  });

  let notificationData = $state(null);
  $effect(() => {
    const unsub = notification.subscribe(v => { notificationData = v; });
    return unsub;
  });

  function toggleSidebar() {
    sidebarOpen.update(v => !v);
  }

  function logout() {
    auth.logout();
    // goto is handled by root layout redirect
  }

  function handleKeydown(e) {
    if (e.key === 'Escape') showDropdown = false;
  }
</script>

<svelte:window onkeydown={handleKeydown} />

<div class="app-shell flex min-h-screen">
  <aside
    class="sidebar flex-shrink-0 bg-[var(--color-surface-950)] text-[var(--color-surface-50)] transition-all duration-200 flex flex-col"
    class:!w-16={!$sidebarOpen}
    style="width: 260px;"
  >
    <div class="sidebar-header px-4 py-4 border-b border-[var(--color-surface-800)]">
      <h2 class="font-bold text-sm whitespace-nowrap overflow-hidden" class:hidden={!$sidebarOpen}>
        Salas Formación
      </h2>
    </div>
    <nav class="flex-1 p-2 space-y-0.5">
      {#each visibleMenuItems as item}
        {#if item.children}
          <!-- Submenu group -->
          <div>
            <button
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-[var(--color-surface-400)] transition-all duration-150 hover:bg-[var(--color-surface-800)] hover:text-[var(--color-surface-50)] cursor-pointer border-0 bg-transparent text-left"
              class:bg-[var(--color-primary-600)]!={isChildActive(item.children) && !$sidebarOpen}
              class:text-[var(--color-primary-400)]!={isChildActive(item.children)}
              onclick={() => toggleSubmenu(item.label)}
            >
              <item.icon size={20} />
              {#if $sidebarOpen}
                <span class="flex-1 whitespace-nowrap overflow-hidden">{item.label}</span>
                <ChevronDown
                  size={16}
                  class="transition-transform duration-200 {isSubmenuOpen(item) ? 'rotate-180' : ''}"
                />
              {/if}
            </button>
            {#if $sidebarOpen && isSubmenuOpen(item)}
              <div class="ml-4 mt-0.5 space-y-0.5 border-l border-[var(--color-surface-700)] pl-2">
                {#each item.children as child}
                  {#if tieneLectura(child.componente)}
                    <a
                      href={child.path}
                      class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-[var(--color-surface-400)] no-underline transition-all duration-150 hover:bg-[var(--color-surface-800)] hover:text-[var(--color-surface-50)]"
                      class:bg-[var(--color-primary-600)]!={isActive(child.path)}
                      class:text-white!={isActive(child.path)}
                      data-sveltekit-preload-data="off"
                    >
                      <child.icon size={18} />
                      <span class="whitespace-nowrap overflow-hidden">{child.label}</span>
                    </a>
                  {/if}
                {/each}
              </div>
            {/if}
          </div>
        {:else}
          <!-- Regular menu item -->
          <a
            href={item.path}
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-[var(--color-surface-400)] no-underline transition-all duration-150 hover:bg-[var(--color-surface-800)] hover:text-[var(--color-surface-50)]"
            class:bg-[var(--color-primary-600)]!={isActive(item.path)}
            class:text-white!={isActive(item.path)}
            data-sveltekit-preload-data="off"
          >
            <item.icon size={20} />
            {#if $sidebarOpen}
              <span class="whitespace-nowrap overflow-hidden">{item.label}</span>
            {/if}
          </a>
        {/if}
      {/each}
    </nav>
  </aside>

  <div class="main-area flex-1 flex flex-col min-w-0">
    <header class="flex items-center justify-between px-6 h-14 bg-white border-b border-[var(--color-surface-200)] sticky top-0 z-10">
      <div class="flex items-center gap-4">
        <button class="btn btn-ghost btn-sm p-1.5 rounded-lg hover:bg-[var(--color-surface-100)] cursor-pointer" onclick={toggleSidebar}>
          <Menu size={20} />
        </button>
        <h1 class="text-lg font-semibold text-[var(--color-surface-900)] m-0">Sistema de Gestión de Salas</h1>
      </div>
      <div class="flex items-center gap-4">
        <div class="relative" bind:this={userMenuRef}>
          <button
            class="flex items-center gap-2 px-3 py-1.5 border border-[var(--color-surface-200)] rounded-lg cursor-pointer hover:bg-[var(--color-surface-50)]"
            onclick={() => showDropdown = !showDropdown}
          >
            <span class="w-8 h-8 rounded-full bg-[var(--color-primary-600)] text-white flex items-center justify-center font-semibold text-sm">
              {currentUser?.nombre?.charAt(0) || 'U'}
            </span>
            <span class="text-sm text-[var(--color-surface-700)] hidden sm:inline">{currentUser?.nombre || 'Usuario'}</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-[var(--color-primary-100)] text-[var(--color-primary-700)] capitalize hidden sm:inline">{currentUser?.rol || ''}</span>
          </button>
          {#if showDropdown}
            <div class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-xl min-w-[200px] z-50" role="menu" onmouseleave={() => showDropdown = false}>
              <div class="px-4 py-3 flex flex-col gap-1">
                <strong class="text-sm text-[var(--color-surface-900)]">{currentUser?.nombre}</strong>
                <span class="text-xs text-[var(--color-surface-500)]">{currentUser?.email}</span>
                <span class="text-xs text-[var(--color-surface-500)] capitalize">Rol: {currentUser?.rol}</span>
              </div>
              <hr class="border-t border-[var(--color-surface-200)] m-0">
              <button class="w-full px-4 py-3 text-sm text-[var(--color-error-600)] hover:bg-[var(--color-error-50)] text-left cursor-pointer border-0 bg-transparent" onclick={logout}>
                <div class="flex items-center gap-2">
                  <LogOut size={16} /> Cerrar sesión
                </div>
              </button>
            </div>
          {/if}
        </div>
      </div>
    </header>

    <main class="flex-1 p-6 bg-[var(--color-surface-100)] overflow-y-auto">
      {@render children()}
    </main>
  </div>
</div>

<!-- Notification Toast -->
{#if notificationData}
  <div
    class="fixed top-4 right-4 z-[2000] px-5 py-3 rounded-lg text-sm text-white shadow-xl animate-[slideIn_0.3s_ease]"
    class:bg-[var(--color-success-600)]={notificationData.type === 'success'}
    class:bg-[var(--color-error-600)]={notificationData.type === 'error'}
    class:bg-[var(--color-warning-600)]={notificationData.type === 'warning'}
    role="alert"
  >
    {notificationData.message}
  </div>
{/if}

<style>
  :global(body) {
    margin: 0;
  }
  @keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
</style>
