<script setup>
defineProps({
  block: { type: Object, required: true },
  tone: { type: String, default: 'sky' },
  first: Boolean,
  last: Boolean,
});
</script>

<template>
  <section
    :id="block.slug"
    class="row container"
    :class="{ first, last, right: block.position === 'right' }"
  >
    <div class="media" :class="tone">
      <img v-if="block.image" :src="block.image.url" :alt="block.image.alt" loading="lazy" />
      <span v-else class="tag">[Image]</span>
    </div>
    <div class="body">
      <h2>{{ block.title }}</h2>
      <p>{{ block.text }}</p>
    </div>
  </section>
</template>

<style scoped>
.row {
  display: grid; grid-template-columns: 12.4rem 1fr; align-items: center; gap: 2.5rem;
  padding-block: 1.75rem; scroll-margin-top: 1rem;
  border-top: 1px solid var(--c-line);
}
.row.first { border-top: 1.5px solid var(--c-ink); }
.row.last { border-bottom: 1.5px solid var(--c-ink); }
.row.right { grid-template-columns: 1fr 12.4rem; }
.row.right .media { order: 2; }

.media { aspect-ratio: 198 / 134; border-radius: 0.9rem; overflow: hidden; display: grid; place-items: center; }
.media.sky { background: var(--c-sky); }
.media.sage { background: var(--c-sage); }
.media.mist { background: var(--c-mist); }
.media img { width: 100%; height: 100%; object-fit: cover; }
.tag { font-family: var(--font-title); font-weight: 600; font-size: 0.75rem; color: var(--c-ink); background: #fff; padding: 0.3rem 0.8rem; border-radius: 999px; }

h2 { font-size: 1.5rem; margin-bottom: 0.6rem; }
.body p { font-size: 0.9375rem; white-space: pre-line; }

@media (max-width: 720px) {
  .row, .row.right { grid-template-columns: 1fr; gap: 1rem; }
  .row.right .media { order: 0; }
}
</style>
