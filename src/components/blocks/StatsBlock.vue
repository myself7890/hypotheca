<script setup>
defineProps({ block: { type: Object, required: true } });
</script>

<template>
  <section class="container wrap">
    <div class="panel">
      <h2 v-if="block.title">{{ block.title }}</h2>
      <ul class="grid">
        <li v-for="(s, i) in block.items" :key="i">
          <span class="value">{{ s.value }}</span>
          <span class="label">{{ s.label }}</span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.wrap { padding-block: 2.5rem; }
.panel { background: var(--c-ink); border-radius: 1.6rem; padding: clamp(2rem, 5vw, 3.25rem); color: #fff; }
h2 { color: #fff; font-size: 1.5rem; margin-bottom: 2rem; }
.grid { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 2rem; }
li { display: grid; gap: 0.35rem; padding-left: 1rem; border-left: 3px solid var(--c-teal); }
li:nth-child(even) { border-left-color: var(--c-lime); }
.value { font-family: var(--font-title); font-weight: 800; font-size: clamp(2rem, 4.5vw, 3rem); line-height: 1; }
.label { font-size: 0.875rem; color: #cfd8e3; }
</style>
