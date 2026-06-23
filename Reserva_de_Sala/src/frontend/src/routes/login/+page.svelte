<script>
  import { auth } from '$lib/stores/auth';
  import { showNotification } from '$lib/stores/notification';
  import { goto } from '$app/navigation';
  import * as authApi from '$lib/api/auth';

  let email = $state('');
  let password = $state('');
  let error = $state('');
  let loading = $state(false);

  async function handleSubmit(e) {
    e.preventDefault();
    error = '';
    loading = true;
    try {
      const result = await authApi.login(email, password);
      auth.login(result.user, result.token);
      showNotification('Inicio de sesión exitoso');
      goto('/');
    } catch (e) {
      error = e.message;
      showNotification(e.message, 'error');
    } finally {
      loading = false;
    }
  }
</script>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-primary-900)] to-[var(--color-surface-950)] p-4">
  <div class="card p-8 w-full max-w-sm space-y-6">
    <div class="text-center">
      <h1 class="h1 m-0">Salas de Formación</h1>
      <p class="opacity-60 text-sm m-0 mt-1">Sistema de Gestión</p>
    </div>

    <form onsubmit={handleSubmit} class="space-y-4">
      {#if error}
        <div class="bg-[var(--color-error-50)] text-[var(--color-error-600)] px-4 py-3 rounded-lg text-sm text-center">{error}</div>
      {/if}

      <div>
        <label for="email" class="label">Email</label>
        <input id="email" type="email" bind:value={email} class="input w-full" placeholder="usuario@ejemplo.com" required disabled={loading} />
      </div>

      <div>
        <label for="password" class="label">Contraseña</label>
        <input id="password" type="password" bind:value={password} class="input w-full" placeholder="••••••••" required disabled={loading} />
      </div>

      <button type="submit" class="btn preset-filled w-full" disabled={loading}>
        {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
      </button>
    </form>

    <div class="pt-4 border-t border-[var(--color-surface-200)] text-center">
      <p class="text-xs opacity-50 m-0 mb-2">Credenciales de prueba:</p>
      <code class="block text-xs opacity-60 bg-[var(--color-surface-100)] px-2 py-1 rounded mb-1">admin@example.com / Password123</code>
      <code class="block text-xs opacity-60 bg-[var(--color-surface-100)] px-2 py-1 rounded">coordinador@example.com / Password123</code>
    </div>
  </div>
</div>
