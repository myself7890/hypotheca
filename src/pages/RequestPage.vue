<script setup>
import HeroBanner from '@/components/HeroBanner.vue';
import RequestForm from '@/components/request/RequestForm.vue';
import ContactCard from '@/components/request/ContactCard.vue';

defineProps({
  request: { type: Object, required: true },
  home: { type: Object, default: null },
  contact: { type: Object, default: () => ({}) },
});
</script>

<template>
  <!-- Même bannière que l'accueil, en tête de chaque page -->
  <HeroBanner v-if="home" :home="home" :contact="contact" title-tag="p" />

  <!-- Même disposition que la page des taux : titre + contenu à gauche, colonne à droite -->
  <div class="container layout">
    <header class="head">
      <p v-if="request.kicker" class="kicker">{{ request.kicker }}</p>
      <h1>{{ request.title }}</h1>
      <p v-if="request.intro" class="intro">{{ request.intro }}</p>
    </header>

    <div class="main">
      <RequestForm :action="request.action" :csrf="request.csrf" :success="request.success" />
    </div>

    <div class="side">
      <ContactCard :contact="contact" />
    </div>
  </div>
</template>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(16rem, 19rem);
  grid-template-rows: auto 1fr;
  grid-template-areas:
    'head side'
    'main side';
  gap: 2.5rem clamp(1.5rem, 4vw, 3.5rem);
  padding-top: clamp(2.5rem, 6vw, 3.5rem);
}
.head { grid-area: head; }
.main { grid-area: main; }
.side { grid-area: side; }
/* La carte suit le défilement le long du formulaire */
.side > * { position: sticky; top: 1.5rem; }

/* Filet turquoise + lime, comme la page des taux */
.head::before {
  content: '';
  display: block;
  width: 3.5rem;
  height: 4px;
  border-radius: 2px;
  margin-bottom: 1.5rem;
  background: linear-gradient(90deg, var(--c-teal) 60%, var(--c-lime) 60%);
}
.kicker {
  font-family: var(--font-script);
  font-size: clamp(1.35rem, 2.4vw, 1.75rem);
  line-height: 1.2;
  color: var(--c-teal-dark);
  margin-bottom: 0.35rem;
}
h1 { font-size: clamp(2rem, 5vw, 3.25rem); letter-spacing: -0.02em; }
.intro { max-width: 40rem; margin-top: 1.1rem; font-size: 1.0625rem; }

@media (max-width: 820px) {
  .layout {
    grid-template-columns: minmax(0, 1fr);
    grid-template-rows: none;
    grid-template-areas: 'head' 'main' 'side';
  }
  .side > * { position: static; }
}
</style>
