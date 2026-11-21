<script setup>
import { computed } from 'vue';
import { percent } from '@/lib/format.js';

const props = defineProps({
  items: { type: Array, required: true },
  note: { type: String, default: '' },
  updatedAt: { type: String, default: '' },
});
defineEmits(['use-rate']);

// La colonne « Taux affiché » n'apparaît que si au moins un terme en a un.
const hasPosted = computed(() => props.items.some((r) => r.posted !== null));
const fmt = (x) => (x === null ? '—' : percent(x));
</script>

<template>
  <section class="container wrap" aria-labelledby="rates-title">
    <div class="head">
      <h2 id="rates-title">Les taux du moment</h2>
      <p v-if="updatedAt" class="updated">Mis à jour le {{ updatedAt }}</p>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th scope="col">Terme</th>
            <th scope="col" class="num">Taux Hypotheca</th>
            <th v-if="hasPosted" scope="col" class="num posted-col">Taux affiché</th>
            <th scope="col" class="action-col"><span class="sr-only">Calculer</span></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in items" :key="row.term">
            <th scope="row">{{ row.term }}</th>
            <td class="num rate">{{ fmt(row.rate) }}</td>
            <td v-if="hasPosted" class="num posted">{{ fmt(row.posted) }}</td>
            <td class="action-col">
              <button
                v-if="row.rate !== null"
                type="button"
                class="use"
                :aria-label="`Calculer mon paiement avec le taux ${row.term} (${fmt(row.rate)})`"
                @click="$emit('use-rate', row)"
              >
                <span class="use-label">Calculer</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="note" class="note">{{ note }}</p>
  </section>
</template>

<style scoped>
.wrap { padding-block: clamp(3rem, 7vw, 4.5rem) 1rem; }
.head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.5rem 1.5rem; margin-bottom: 1.25rem; }
h2 { font-size: clamp(1.5rem, 3vw, 1.875rem); }
.updated { font-size: 0.8125rem; color: #6b7280; }

.card {
  border: 2px solid var(--c-ink);
  border-radius: 1rem;
  padding: 0.5rem clamp(0.75rem, 2.5vw, 1.75rem);
  background: #fff;
}
table { width: 100%; border-collapse: collapse; }
th, td { padding: 0.95rem 0.5rem; text-align: left; border-bottom: 1px solid var(--c-line); }
tbody tr:last-child > * { border-bottom: 0; }
thead th {
  font-family: var(--font-title);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--c-ink);
  padding-block: 1.1rem 0.85rem;
}
tbody th { font-weight: 400; color: var(--c-text); }
.num { text-align: right; white-space: nowrap; }
.rate { font-family: var(--font-title); font-weight: 800; font-size: 1.0625rem; color: var(--c-ink); }
.posted { color: #7b818c; font-size: 0.9375rem; }
tbody tr { transition: background 0.15s; }
tbody tr:hover { background: #f5fafb; }

.action-col { width: 1%; padding-right: 0; text-align: right; }
.use {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 0;
  background: none;
  cursor: pointer;
  padding: 0.35rem 0.6rem;
  border-radius: 999px;
  font: 600 0.75rem var(--font-title);
  color: var(--c-teal-dark);
  transition: background 0.15s, color 0.15s;
}
.use svg { width: 1rem; height: 1rem; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
.use:hover, .use:focus-visible { background: var(--c-sky); color: var(--c-ink); }
.use:focus-visible { outline: 2px solid var(--c-teal); outline-offset: 2px; }

.note { margin-top: 0.85rem; font-size: 0.75rem; color: #6b7280; white-space: pre-line; }

@media (max-width: 560px) {
  th, td { padding-inline: 0.25rem; }
  .use-label { display: none; }
  .posted-col { font-size: 0.625rem; }
}
</style>
