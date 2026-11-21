<script setup>
import { computed } from 'vue';
import { percent } from '@/lib/format.js';

const props = defineProps({
  items: { type: Array, required: true },
  note: { type: String, default: '' },
  updatedAt: { type: String, default: '' },
});

// La colonne « Affiché » n'apparaît que si au moins un terme en a un.
const hasPosted = computed(() => props.items.some((r) => r.posted !== null));
const fmt = (x) => (x === null ? '—' : percent(x));
</script>

<template>
  <div class="rates">
    <table>
      <caption class="sr-only">Taux hypothécaires par terme</caption>
      <thead>
        <tr>
          <th scope="col">Type</th>
          <th scope="col">Hypotheca</th>
          <th v-if="hasPosted" scope="col">Affiché</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in items" :key="row.term">
          <th scope="row">{{ row.term }}</th>
          <td class="rate">{{ fmt(row.rate) }}</td>
          <td v-if="hasPosted" class="posted">{{ fmt(row.posted) }}</td>
        </tr>
      </tbody>
    </table>
    <div class="meta">
      <p v-if="updatedAt">Mis à jour le {{ updatedAt }}</p>
      <p v-if="note" class="note">{{ note }}</p>
    </div>
  </div>
</template>

<style scoped>
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th, td {
  border: 1px solid #d3d6dc;
  padding: 0.4rem 0.75rem;
  text-align: center;
  font-variant-numeric: tabular-nums;
}
thead th {
  font-family: var(--font-title);
  font-weight: 700;
  font-size: 0.9375rem;
  color: var(--c-ink);
  background: #eaf6f9;
  border-top: 0;
  padding-block: 0.55rem;
}
thead th:first-child, tbody th { border-left: 0; }
thead th:last-child, tbody td:last-child { border-right: 0; }
tbody th { font-weight: 400; color: var(--c-text); }
.rate { font-family: var(--font-title); font-weight: 700; color: var(--c-ink); }
.posted { color: #6b7280; }
tbody tr:nth-child(even) { background: #fafbfc; }

.meta {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 0.25rem 1rem;
  margin-top: 0.4rem;
  font-size: 0.6875rem;
  color: #7b818c;
}
.note { margin-left: auto; font-style: italic; text-align: right; white-space: pre-line; }
</style>
