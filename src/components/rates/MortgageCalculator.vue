<script setup>
import { ref, computed } from 'vue';
import { FREQUENCIES, AMORTIZATIONS, mortgagePayment } from '@/lib/mortgage.js';
import { moneyCents } from '@/lib/format.js';

const props = defineProps({
  defaultRate: { type: Number, default: 5 },
});

const price = ref(375000);
const down = ref(75000);
const loan = ref(300000);
const rate = ref(props.defaultRate);
const years = ref(25);
const frequency = ref('monthly');

const num = (v) => Number(v) || 0;

// Comme sur hypotheca.ca : prix − mise de fonds = hypothèque ;
// modifier l'hypothèque recalcule le prix.
const syncLoan = () => { loan.value = Math.max(0, num(price.value) - num(down.value)); };
const syncPrice = () => { price.value = num(loan.value) + num(down.value); };

const payment = computed(() =>
  mortgagePayment({ loan: loan.value, ratePct: rate.value, years: years.value, frequency: frequency.value }),
);
</script>

<template>
  <form class="calc" aria-label="Calculateur de paiement hypothécaire" @submit.prevent>
    <div class="field">
      <label for="calc-price">Prix de la maison</label>
      <input id="calc-price" v-model.number="price" type="number" min="0" step="1000" inputmode="numeric" @input="syncLoan" />
    </div>
    <div class="field">
      <label for="calc-rate">Taux</label>
      <input id="calc-rate" v-model.number="rate" type="number" min="0" max="25" step="0.01" inputmode="decimal" />
    </div>
    <div class="field">
      <label for="calc-down">Mise de fonds</label>
      <input id="calc-down" v-model.number="down" type="number" min="0" step="1000" inputmode="numeric" @input="syncLoan" />
    </div>
    <div class="field">
      <label for="calc-years">Amortissement</label>
      <select id="calc-years" v-model.number="years">
        <option v-for="y in AMORTIZATIONS" :key="y" :value="y">{{ y }} ans</option>
      </select>
    </div>
    <div class="field">
      <label for="calc-loan">Hypothèque</label>
      <input id="calc-loan" v-model.number="loan" type="number" min="0" step="1000" inputmode="numeric" @input="syncPrice" />
    </div>
    <div class="field">
      <label for="calc-freq">Fréquence</label>
      <select id="calc-freq" v-model="frequency">
        <option v-for="f in FREQUENCIES" :key="f.id" :value="f.id">{{ f.label }}</option>
      </select>
    </div>

    <output class="result" for="calc-price calc-rate calc-down calc-years calc-loan calc-freq" aria-live="polite">
      {{ moneyCents(payment) }}
    </output>
    <p class="fine">*Simulation seulement</p>
  </form>
</template>

<style scoped>
.calc {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem 1.25rem;
  padding: 1.25rem 1.35rem 1rem;
  background: #fff;
  border: 1px solid var(--c-line);
  border-radius: 0.9rem;
  box-shadow: 0 12px 32px -14px rgb(45 58 77 / 0.35);
}
.field { display: grid; gap: 0.1rem; position: relative; }
label { font-size: 0.75rem; font-style: italic; color: #7b818c; text-align: right; }
input, select {
  width: 100%;
  border: 0;
  border-bottom: 2px solid var(--c-ink);
  border-radius: 0;
  background: transparent;
  padding: 0.15rem 0 0.2rem;
  font: 700 1rem var(--font-title);
  color: var(--c-ink);
  text-align: right;
  appearance: none;
  -moz-appearance: textfield;
}
input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
select { text-align-last: right; padding-right: 1rem; cursor: pointer; }
/* Petit triangle des listes, comme sur hypotheca.ca */
.field:has(select)::after {
  content: '';
  position: absolute;
  right: 0.1rem;
  bottom: 0.7rem;
  border: 0.28rem solid transparent;
  border-top: 0.34rem solid var(--c-teal);
  pointer-events: none;
}
input:focus-visible, select:focus-visible { outline: none; border-bottom-color: var(--c-teal); background: #f5fafb; }

.result {
  grid-column: 1 / -1;
  margin-top: 0.4rem;
  text-align: right;
  font: 800 1.6rem var(--font-title);
  color: var(--c-teal-dark);
  font-variant-numeric: tabular-nums;
}
.fine { grid-column: 1 / -1; font-size: 0.6875rem; font-style: italic; color: #7b818c; }
</style>
