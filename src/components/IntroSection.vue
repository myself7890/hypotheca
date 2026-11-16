<script setup>
import AppButton from "./AppButton.vue";

const props = defineProps({
  text: { type: String, default: "" },
  ctaLabel: { type: String, default: "" },
  requestUrl: { type: String, required: true },
  blocks: { type: Array, default: () => [] },
});
</script>

<template>
  <section class="intro container">
    <p class="text">{{ text }}</p>
    <AppButton :href="requestUrl">{{ ctaLabel }}</AppButton>
  </section>
</template>

<style scoped>
.intro {
  display: grid;
  justify-items: start;
  text-align: left;
  padding-block: 3.5rem 4.5rem;
}
.intro::before {
  content: "";
  width: 3.5rem;
  height: 4px;
  border-radius: 2px;
  margin-bottom: 2rem;
  background: linear-gradient(90deg, var(--c-teal) 60%, var(--c-lime) 60%);
}
.text {
  max-width: none;
  font-size: clamp(1.25rem, 2.6vw, 1.875rem);
  line-height: 1.55;
  margin-bottom: 2.25rem;
}
.text :deep(a) {
  font-family: var(--font-title);
  font-weight: 700;
  color: var(--c-ink);
  text-decoration: underline;
  text-decoration-color: var(--c-teal);
  text-decoration-thickness: 2px;
  text-underline-offset: 0.22em;
}
.text :deep(a:hover) {
  text-decoration-color: var(--c-lime);
}
</style>
