<script setup>
import { computed } from 'vue';

const props = defineProps({
  contact: { type: Object, default: () => ({}) },
});

const telHref = computed(() =>
  props.contact.phone ? `tel:${props.contact.phone.replace(/[^\d+]/g, '')}` : null,
);
</script>

<template>
  <aside class="card" aria-labelledby="contact-card-title">
    <h2 id="contact-card-title">Me joindre</h2>
    <dl>
      <div v-if="contact.name">
        <dt>Courtière</dt>
        <dd>{{ contact.name }}<br />Courtière hypothécaire</dd>
      </div>
      <div v-if="contact.phone">
        <dt>Téléphone</dt>
        <dd><a :href="telHref">{{ contact.phone }}</a></dd>
      </div>
      <div v-if="contact.email">
        <dt>Courriel</dt>
        <dd><a :href="`mailto:${contact.email}`">{{ contact.email }}</a></dd>
      </div>
    </dl>
  </aside>
</template>

<style scoped>
.card {
  position: relative;
  overflow: hidden;
  padding: 1.6rem 1.6rem 1.5rem;
  background: var(--c-ink);
  color: #fff;
  border-radius: 0.9rem;
  box-shadow: 0 12px 32px -14px rgb(45 58 77 / 0.55);
}
/* Filet turquoise + lime, comme les titres de page */
.card::before {
  content: '';
  display: block;
  width: 3rem;
  height: 4px;
  border-radius: 2px;
  margin-bottom: 1.1rem;
  background: linear-gradient(90deg, var(--c-teal) 60%, var(--c-lime) 60%);
}
h2 { color: #fff; font-size: 1.35rem; margin-bottom: 1.1rem; }
dl { margin: 0; display: grid; gap: 1rem; }
dt {
  font-family: var(--font-title);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--c-teal);
}
dd { margin: 0.15rem 0 0; font-size: 0.9375rem; line-height: 1.5; overflow-wrap: anywhere; }
a { text-decoration: none; border-bottom: 1px solid rgb(255 255 255 / 0.35); }
a:hover, a:focus-visible { border-bottom-color: var(--c-teal); }
</style>
