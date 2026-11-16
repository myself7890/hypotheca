<script setup>
import LogoMark from './LogoMark.vue';

defineProps({
  contact: { type: Object, required: true },
  nav: { type: Array, required: true },
  requestUrl: { type: String, required: true },
});

const year = new Date().getFullYear();
</script>

<template>
  <footer class="footer">
    <div class="container cols">
      <div class="brand"><LogoMark light /></div>

      <div class="col">
        <h3>Me joindre</h3>
        <ul>
          <li>{{ contact.name }}</li>
          <li>{{ contact.phone }}</li>
          <li>{{ contact.email }}</li>
        </ul>
      </div>

      <div class="col">
        <h3>Navigation</h3>
        <ul>
          <li v-for="item in nav" :key="item.href"><a :href="item.href">{{ item.label }}</a></li>
          <li><a :href="requestUrl">Demande en ligne</a></li>
        </ul>
      </div>
    </div>
    <p class="container legal">© {{ year }} Hypotheca. Tous droits réservés.</p>
  </footer>
</template>

<style scoped>
.footer { background: var(--c-ink); color: #fff; padding-block: 3.5rem 2rem; margin-top: 3.5rem; font-size: 0.8125rem; }
.cols { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 2rem; }
.col h3 { font-size: 0.8125rem; font-weight: 700; color: #fff; margin-bottom: 0.75rem; }
ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.35rem; }
a { text-decoration: none; }
a:hover { text-decoration: underline; }
.legal { margin-top: 3.5rem; font-size: 0.6875rem; }

@media (max-width: 720px) {
  .cols { grid-template-columns: 1fr; }
}
</style>
