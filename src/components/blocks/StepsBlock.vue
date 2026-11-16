<script setup>
defineProps({ block: { type: Object, required: true } });
</script>

<template>
  <section class="container wrap">
    <h2 v-if="block.title">{{ block.title }}</h2>
    <ol class="steps">
      <li v-for="(s, i) in block.items" :key="i" class="step">
        <span class="num" aria-hidden="true">{{ i + 1 }}</span>
        <h3>{{ s.title }}</h3>
        <p>{{ s.description }}</p>
      </li>
    </ol>
  </section>
</template>

<style scoped>
.wrap { padding-block: 2.5rem; }
h2 { font-size: 1.75rem; margin-bottom: 1.75rem; }
.steps { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: 1.25rem; }
.step { background: var(--c-sky); border-radius: 1.2rem; padding: 1.5rem; }
.step:nth-child(even) { background: var(--c-sage); }
.num {
  display: grid; place-items: center; width: 2.4rem; height: 2.4rem; border-radius: 50%;
  background: var(--c-ink); color: #fff; font-family: var(--font-title); font-weight: 800; margin-bottom: 1rem;
}
h3 { font-size: 1.1rem; margin-bottom: 0.4rem; }
p { font-size: 0.875rem; white-space: pre-line; }
</style>
