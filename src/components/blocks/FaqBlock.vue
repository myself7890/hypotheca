<script setup>
defineProps({ block: { type: Object, required: true } });
</script>

<template>
  <section class="container wrap">
    <div class="inner">
    <h2 v-if="block.title">{{ block.title }}</h2>
    <div class="list">
      <details v-for="(q, i) in block.items" :key="i" class="item">
        <summary>{{ q.question }}</summary>
        <p>{{ q.answer }}</p>
      </details>
    </div>
    </div>
  </section>
</template>

<style scoped>
.wrap { padding-block: 2.5rem; }
.inner { max-width: 52rem; }
h2 { font-size: 1.75rem; margin-bottom: 1.5rem; }
.item { border-bottom: 1px solid var(--c-line); }
.item:first-child { border-top: 1.5px solid var(--c-ink); }
summary {
  list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 1rem;
  padding: 1.1rem 0; font-family: var(--font-title); font-weight: 700; color: var(--c-ink);
}
summary::-webkit-details-marker { display: none; }
summary::after {
  content: '+'; flex: none; display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 50%;
  background: var(--c-sky); color: var(--c-ink); font-size: 1.1rem; transition: transform 0.2s, background 0.2s;
}
details[open] summary::after { content: '\2212'; background: var(--c-teal); color: #fff; }
summary:focus-visible { outline: 2px solid var(--c-teal); outline-offset: 2px; }
p { padding-bottom: 1.25rem; font-size: 0.9375rem; max-width: 60ch; white-space: pre-line; }
</style>
