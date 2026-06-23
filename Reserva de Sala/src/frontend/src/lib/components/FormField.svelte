<script>
  let {
    label = '', type = 'text', value, placeholder = '', required = false,
    error = '', disabled = false, options = [], optionLabel = 'nombre', optionValue = 'id',
  } = $props();
</script>

<div class="mb-4">
  {#if label}
    <label for={label} class="block mb-1.5 font-medium text-sm text-[var(--color-surface-700)]">
      {label}
      {#if required}<span class="text-[var(--color-error-500)] ml-0.5">*</span>{/if}
    </label>
  {/if}

  {#if type === 'select'}
    <select
      id={label} bind:value {disabled}
      class="input w-full {error ? 'border-[var(--color-error-500)]' : ''}"
    >
      <option value="">-- Seleccionar --</option>
      {#each options as opt}
        <option value={opt[optionValue]}>{opt[optionLabel]}</option>
      {/each}
    </select>
  {:else if type === 'textarea'}
    <textarea
      id={label} bind:value {placeholder} {disabled}
      class="input w-full min-h-[60px] {error ? 'border-[var(--color-error-500)]' : ''}"
    ></textarea>
  {:else}
    <input
      {type} id={label} bind:value {placeholder} {required} {disabled}
      class="input w-full {error ? 'border-[var(--color-error-500)]' : ''}"
    />
  {/if}

  {#if error}
    <span class="text-xs text-[var(--color-error-500)] mt-1 block">{error}</span>
  {/if}
</div>
