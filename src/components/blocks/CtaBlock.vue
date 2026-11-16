<script setup>
import AppButton from '../AppButton.vue';

defineProps({ block: { type: Object, required: true } });
</script>

<template>
  <section class="container wrap">
    <div class="banner">
      <span class="shape lime" aria-hidden="true"></span>
      <span class="shape teal" aria-hidden="true"></span>
      <div class="content">
        <h2>{{ block.title }}</h2>
        <p v-if="block.text">{{ block.text }}</p>
        <AppButton v-if="block.label && block.url" :href="block.url" variant="light">{{ block.label }}</AppButton>
      </div>
    </div>
  </section>
</template>

<style scoped>
.wrap { padding-block: 2.5rem; }
.banner { position: relative; overflow: hidden; border-radius: 1.6rem; background: var(--c-teal); padding: clamp(2rem, 5vw, 3.5rem); color: #fff; }
.content { position: relative; z-index: 1; max-width: 34rem; display: grid; justify-items: start; gap: 1rem; }
h2 { color: #fff; font-size: clamp(1.6rem, 3.4vw, 2.2rem); }
p { font-size: 1rem; white-space: pre-line; }
.shape { position: absolute; transform: skewX(-8deg); }
.shape.lime { right: 6%; top: -2rem; width: 9rem; height: 70%; background: var(--c-lime); border-radius: 0 0 1.2rem 1.2rem; opacity: 0.9; }
.shape.teal { right: -2rem; bottom: -2rem; width: 12rem; height: 55%; background: var(--c-ink); border-radius: 1.4rem 0 0 0; opacity: 0.85; }
@media (max-width: 720px) { .shape { opacity: 0.35 !important; } }
</style>
