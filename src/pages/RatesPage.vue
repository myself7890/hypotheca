<script setup>
import { ref, computed } from 'vue';
import RatesTable from '@/components/rates/RatesTable.vue';
import MortgageCalculator from '@/components/rates/MortgageCalculator.vue';
import ContactBand from '@/components/rates/ContactBand.vue';
import BlockList from '@/components/BlockList.vue';
import HeroBanner from '@/components/HeroBanner.vue';

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

const calculator = ref(null);
const useRate = (row) => calculator.value?.setRate(row.rate, row.term);
</script>

<template>
  <!-- Même bannière que l'accueil, en tête de chaque page -->
  <HeroBanner v-if="home" :home="home" :contact="contact" title-tag="p" />

  <section class="page-head container">
    <p v-if="rates.kicker" class="kicker">{{ rates.kicker }}</p>
    <h1>{{ rates.title }}</h1>
    <p v-if="rates.intro" class="intro">{{ rates.intro }}</p>
  </section>

  <RatesTable
    v-if="rates.items.length"
    :items="rates.items"
    :note="rates.note"
    :updated-at="rates.updatedAt"
    @use-rate="useRate"
  />

  <MortgageCalculator ref="calculator" :default-rate="defaultRate" :request-url="requestUrl" />

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
.page-head { padding-top: clamp(2.5rem, 6vw, 4rem); }
/* Filet turquoise + lime, comme l'introduction de l'accueil */
.page-head::before {
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
</style>
