<script setup>
import { computed } from 'vue';
import RatesTable from '@/components/rates/RatesTable.vue';
import MortgageCalculator from '@/components/rates/MortgageCalculator.vue';
import ContactBand from '@/components/rates/ContactBand.vue';
import BlockList from '@/components/BlockList.vue';
import HeroBanner from '@/components/HeroBanner.vue';
import { highlightTerm } from '@/lib/highlight.js';

const props = defineProps({
  rates: { type: Object, required: true },
  home: { type: Object, default: null },
  blocks: { type: Array, default: () => [] },
  contact: { type: Object, default: () => ({}) },
  requestUrl: { type: String, required: true },
});

// Taux de départ du calculateur : 5 ans fixe s'il existe, sinon le premier taux de la grille.
const defaultRate = computed(() => {
  const items = props.rates.items.filter((r) => r.rate !== null);
  const fiveFixed = items.find((r) => /5\s*ans?\b.*fixe/i.test(r.term));
  return (fiveFixed ?? items[0])?.rate ?? 5;
});

const definitions = computed(() => (props.rates.definitions ?? []).map((d) => highlightTerm(d.text, d.term)));
const asides = computed(() => (props.rates.asides ?? []).map((d) => highlightTerm(d.text, d.term)));
</script>

<template>
  <!-- Même bannière que l'accueil, en tête de chaque page -->
  <HeroBanner v-if="home" :home="home" :contact="contact" title-tag="p" />

  <!-- Disposition de hypotheca.ca : titre + calculateur, puis grille + encadrés -->
  <div class="container layout">
    <header class="head">
      <p v-if="rates.kicker" class="kicker">{{ rates.kicker }}</p>
      <h1>{{ rates.title }}</h1>
      <p v-if="rates.intro" class="intro">{{ rates.intro }}</p>
    </header>

    <div class="main">
      <RatesTable v-if="rates.items.length" :items="rates.items" :note="rates.note" :updated-at="rates.updatedAt" />
      <div v-if="definitions.length" class="definitions">
        <p v-for="(html, i) in definitions" :key="i" v-html="html"></p>
      </div>
    </div>

    <!-- Colonne de droite : calculateur puis encadrés, sur toute la hauteur du contenu -->
    <div class="side">
      <MortgageCalculator class="calc" :default-rate="defaultRate" />
      <aside v-if="asides.length" class="notes">
        <p v-for="(html, i) in asides" :key="i" class="note" v-html="html"></p>
      </aside>
    </div>
  </div>

  <BlockList :blocks="blocks" />

  <ContactBand
    :kicker="rates.contactKicker"
    :text="rates.contactText"
    :photo="rates.photo"
    :contact="contact"
    :request-url="requestUrl"
  />
</template>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(16rem, 19rem);
  grid-template-rows: auto 1fr; /* la colonne de droite n'étire pas la ligne du titre */
  grid-template-areas:
    'head side'
    'main side';
  gap: 2rem clamp(1.5rem, 4vw, 3rem);
  padding-top: clamp(2.5rem, 6vw, 3.5rem);
}
.head { grid-area: head; }
.main { grid-area: main; }
.side { grid-area: side; display: grid; gap: 2rem; align-content: start; }
.notes { display: grid; gap: 1.75rem; }

/* Filet turquoise + lime, comme l'introduction de l'accueil */
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

.definitions { display: grid; gap: 1.1rem; margin-top: 2rem; font-size: 0.9375rem; }
.definitions :deep(strong), .note :deep(strong) { color: var(--c-ink); }

/* Encadrés à coin replié */
.note {
  position: relative;
  padding: 1.35rem 1.35rem 1.25rem;
  background: #eaf6f9;
  border-radius: 0.3rem;
  font-size: 0.875rem;
  font-style: italic;
  line-height: 1.6;
}
.note::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  border-style: solid;
  border-width: 0 1.5rem 1.5rem 0;
  border-color: #fff #fff var(--c-teal) var(--c-teal);
  border-bottom-left-radius: 0.2rem;
  opacity: 0.75;
}

@media (max-width: 820px) {
  .layout {
    grid-template-columns: minmax(0, 1fr);
    grid-template-rows: none;
    grid-template-areas: 'head' 'calc' 'main' 'notes';
  }
  /* Sur mobile : titre, calculateur, grille, puis encadrés */
  .side { display: contents; }
  .calc { grid-area: calc; max-width: 24rem; }
  .notes { grid-area: notes; }
}
</style>
