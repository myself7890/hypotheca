<script setup>
import LogoMark from './LogoMark.vue';
import AppButton from './AppButton.vue';

defineProps({
  nav: { type: Array, required: true },
  requestUrl: { type: String, required: true },
  overlay: Boolean, // true : en-tête transparent posé sur le hero (accueil)
});

const isCurrent = (href) => window.location.pathname === href;
</script>

<template>
  <header class="header" :class="{ overlay }">
    <div class="container bar">
      <LogoMark />
      <nav class="nav" aria-label="Navigation principale">
        <a
          v-for="item in nav"
          :key="item.href"
          :href="item.href"
          :aria-current="isCurrent(item.href) ? 'page' : null"
          class="link"
        >{{ item.label }}</a>
        <AppButton :href="requestUrl">Demande en ligne</AppButton>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.header { position: relative; z-index: 10; background: #fff; }
.header.overlay { position: absolute; inset: 0 0 auto 0; background: none; }
.bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding-block: 1.25rem; }
.nav { display: flex; align-items: center; gap: 1.75rem; }
.link {
  font-family: var(--font-title);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--c-ink);
  text-decoration: none;
  padding-block: 0.4rem;
  border-bottom: 2px solid transparent;
}
.link[aria-current='page'] { font-weight: 700; border-bottom-color: var(--c-teal); }
.link:hover { border-bottom-color: var(--c-teal); }

@media (max-width: 640px) {
  .link { display: none; }
  .bar { padding-block: 1rem; }
  .bar :deep(img) { height: 30px; }
  .bar :deep(.btn) { padding: 0.55rem 1.1rem; }
}
</style>
