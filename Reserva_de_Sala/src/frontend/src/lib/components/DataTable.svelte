<script>
  let { columns = [], rows = [], loading = false, emptyText = 'No hay datos disponibles', children } = $props();
</script>

<div class="table-container w-full overflow-x-auto">
  {#if loading}
    <div class="flex flex-col items-center justify-center py-8">
      <progress class="progress w-48" indeterminate></progress>
    </div>
  {:else if rows.length === 0}
    <div class="text-center py-12 text-[var(--color-surface-400)]">{emptyText}</div>
  {:else}
    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            {#each columns as col}
              <th style={col.width ? `width: ${col.width}` : ''}>{col.label || col.key}</th>
            {/each}
            {#if children}
              <th class="w-1 whitespace-nowrap text-right">Acciones</th>
            {/if}
          </tr>
        </thead>
        <tbody>
          {#each rows as row, i}
            <tr class="even:bg-[var(--color-surface-50)] odd:bg-white hover:bg-[var(--color-primary-50)] transition-colors">
              {#each columns as col}
                <td>
                  {#if col.render}
                    {@const value = col.render(row)}
                    {@html value}
                  {:else}
                    {row[col.key] ?? '-'}
                  {/if}
                </td>
              {/each}
              {#if children}
                <td class="whitespace-nowrap text-right">
                  <div class="flex gap-1 justify-end">
                    {@render children(row)}
                  </div>
                </td>
              {/if}
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  {/if}
</div>
