<script>
  import '../app.css';
  import { browser } from '$app/environment';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { auth } from '$lib/stores/auth';

  let { children } = $props();

  // Redirigir al login si no está autenticado
  $effect(() => {
    const unsub = auth.subscribe(a => {
      if (browser && !a.isLoggedIn && $page.url.pathname !== '/login') {
        goto('/login');
      }
    });
    return unsub;
  });
</script>

{@render children()}
