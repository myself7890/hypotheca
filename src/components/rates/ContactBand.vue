<script setup>
import { computed } from 'vue';
import AppButton from '../AppButton.vue';

const props = defineProps({
  kicker: { type: String, default: '' },
  text: { type: String, default: '' },
  photo: { type: Object, default: null },
  contact: { type: Object, default: () => ({}) },
  requestUrl: { type: String, required: true },
});

const href = computed(() => (props.contact.email ? `mailto:${props.contact.email}` : props.requestUrl));
</script>

<template>
  <section v-if="kicker || text" class="band">
    <div class="container inner">
      <div class="avatar">
        <img v-if="photo" :src="photo.url" :alt="photo.alt" width="120" height="120" />
        <span v-else aria-hidden="true">[Photo]</span>
      </div>
      <div class="copy">
        <p v-if="kicker" class="kicker">{{ kicker }}</p>
        <p v-if="text" class="text">{{ text }}</p>
      </div>
      <AppButton :href="href">Me contacter</AppButton>
    </div>
  </section>
</template>

<style scoped>
.band { background: #eaf6f9; padding-block: clamp(2rem, 5vw, 3rem); margin-top: 2rem; }
.inner { display: flex; align-items: center; gap: clamp(1rem, 3vw, 1.75rem); }
.avatar {
  flex: none; width: 6.25rem; height: 6.25rem; border-radius: 50%; overflow: hidden;
  display: grid; place-items: center; background: #d5dde3;
  font: 600 0.6875rem var(--font-title); color: var(--c-ink);
}
.avatar img { width: 100%; height: 100%; object-fit: cover; object-position: center top; }
.copy { flex: 1; }
.kicker { font-family: var(--font-script); font-size: clamp(1.3rem, 2.2vw, 1.6rem); line-height: 1.2; color: var(--c-teal-dark); }
.text { font-family: var(--font-title); font-weight: 700; font-size: clamp(0.95rem, 1.6vw, 1.125rem); color: var(--c-ink); }
.inner :deep(.btn) { font-size: 0.875rem; padding: 0.85rem 1.6rem; }

@media (max-width: 720px) {
  .inner { flex-direction: column; text-align: center; }
}
</style>
