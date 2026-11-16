<script setup>
defineProps({ block: { type: Object, required: true } });
</script>

<template>
  <section class="container wrap">
    <figure class="quote">
      <svg class="mark" viewBox="0 0 48 36" aria-hidden="true">
        <path d="M0 36V20C0 8 6 1.5 18 0v7C11.500 8.500 9 12 9 17h9v19H0zm27 0V20C27 8 33 1.500 45 0v7c-6.500 1.500-9 5-9 10h9v19H27z" />
      </svg>
      <blockquote>{{ block.text }}</blockquote>
      <figcaption v-if="block.author">{{ block.author }}</figcaption>
    </figure>
  </section>
</template>

<style scoped>
.wrap { padding-block: 2.5rem; }
.quote {
  position: relative; margin: 0; padding: clamp(2rem, 5vw, 3.5rem) clamp(1.5rem, 6vw, 4.5rem);
  background: var(--c-sage); border-radius: 1.6rem; overflow: hidden;
}
.quote::after {
  content: ''; position: absolute; right: -2rem; bottom: -2rem; width: 9rem; height: 9rem;
  background: var(--c-lime); opacity: 0.35; border-radius: 1.4rem; transform: skewX(-8deg);
}
.mark { width: 2.75rem; fill: var(--c-teal); margin-bottom: 1rem; }
blockquote {
  position: relative; z-index: 1; margin: 0; max-width: 34ch;
  font-family: var(--font-title); font-weight: 700; font-size: clamp(1.25rem, 2.6vw, 1.75rem);
  line-height: 1.35; color: var(--c-ink); white-space: pre-line;
}
figcaption { position: relative; z-index: 1; margin-top: 1.25rem; font-family: var(--font-script); font-size: 1.5rem; color: var(--c-teal); }
</style>
