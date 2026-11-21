<script setup>
import { ref, computed, nextTick } from 'vue';
import { FREQUENCIES, AMORTIZATIONS, INSURED_PRICE_CAP, computeMortgage } from '@/lib/mortgage.js';
import { money, moneyCents, percent } from '@/lib/format.js';

const props = defineProps({
  defaultRate: { type: Number, default: 5 },
  requestUrl: { type: String, required: true },
});

const price = ref(500000);
const down = ref(100000);
const rate = ref(props.defaultRate);
const rateSource = ref('');
const years = ref(25);
const frequency = ref('monthly');

const result = computed(() =>
  computeMortgage({
    price: price.value,
    down: down.value,
    ratePct: rate.value,
    years: years.value,
    frequency: frequency.value,
  }),
);

const downRatio = computed(() => (price.value > 0 ? (Number(down.value) || 0) / price.value * 100 : 0));
const downTooLow = computed(() => price.value > 0 && (Number(down.value) || 0) < Math.ceil(result.value.minDown));
const insured30 = computed(() => result.value.insured && years.value > 25);
const accelerated = computed(() => !!result.value.frequency.split);

// Proportion capital / intérêts pour la barre.
const capitalShare = computed(() => {
  const { loan, totalCost } = result.value;
  return totalCost > 0 ? (loan / totalCost) * 100 : 100;
});

const section = ref(null);
const rateInput = ref(null);

/** Appelé depuis la grille des taux. */
async function setRate(value, term) {
  rate.value = value;
  rateSource.value = term;
  await nextTick();
  section.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  rateInput.value?.focus({ preventScroll: true });
}
const onRateInput = () => { rateSource.value = ''; };

defineExpose({ setRate });
</script>

<template>
  <section ref="section" class="container wrap" aria-labelledby="calc-title">
    <h2 id="calc-title">Calculateur d’hypothèque</h2>
    <p class="lede">Estimation à titre indicatif. Les intérêts sont composés semi-annuellement, comme le veut la pratique au Canada.</p>

    <div class="layout">
      <form class="inputs" @submit.prevent>
        <div class="field">
          <label for="calc-price">Prix de la propriété</label>
          <div class="control">
            <input id="calc-price" v-model.number="price" type="number" min="0" step="1000" inputmode="numeric" />
            <span class="unit" aria-hidden="true">$</span>
          </div>
        </div>

        <div class="field">
          <label for="calc-down">Mise de fonds</label>
          <div class="control" :class="{ invalid: downTooLow }">
            <input
              id="calc-down"
              v-model.number="down"
              type="number"
              min="0"
              step="1000"
              inputmode="numeric"
              aria-describedby="calc-down-hint"
              :aria-invalid="downTooLow || null"
            />
            <span class="unit" aria-hidden="true">$</span>
          </div>
          <p id="calc-down-hint" class="hint" :class="{ warn: downTooLow }">
            <template v-if="downTooLow">
              Minimum requis : {{ money(Math.ceil(result.minDown)) }}<template v-if="price >= INSURED_PRICE_CAP"> (20 % à partir de {{ money(INSURED_PRICE_CAP) }})</template>
            </template>
            <template v-else>Soit {{ percent(downRatio, 1) }} du prix</template>
          </p>
        </div>

        <div class="field">
          <label for="calc-rate">Taux d’intérêt</label>
          <div class="control">
            <input
              id="calc-rate"
              ref="rateInput"
              v-model.number="rate"
              type="number"
              min="0"
              max="25"
              step="0.01"
              inputmode="decimal"
              aria-describedby="calc-rate-hint"
              @input="onRateInput"
            />
            <span class="unit" aria-hidden="true">%</span>
          </div>
          <p id="calc-rate-hint" class="hint" aria-live="polite">
            <template v-if="rateSource">Taux {{ rateSource }}</template>
          </p>
        </div>

        <div class="field">
          <label for="calc-years">Période d’amortissement</label>
          <div class="control select">
            <select id="calc-years" v-model.number="years">
              <option v-for="y in AMORTIZATIONS" :key="y" :value="y">{{ y }} ans</option>
            </select>
          </div>
        </div>

        <div class="field full">
          <label for="calc-freq">Fréquence des paiements</label>
          <div class="control select">
            <select id="calc-freq" v-model="frequency">
              <option v-for="f in FREQUENCIES" :key="f.id" :value="f.id">{{ f.label }}</option>
            </select>
          </div>
        </div>
      </form>

      <div class="result" aria-live="polite">
        <p class="eyebrow">Votre paiement estimé</p>
        <p class="amount">{{ moneyCents(result.payment) }}</p>
        <p class="per">{{ result.frequency.per }}</p>

        <div class="bar" role="img" :aria-label="`Capital ${Math.round(capitalShare)} %, intérêts ${100 - Math.round(capitalShare)} %`">
          <span class="bar-capital" :style="{ width: capitalShare + '%' }"></span>
        </div>
        <div class="legend" aria-hidden="true">
          <span><i class="dot lime"></i>Capital</span>
          <span><i class="dot teal"></i>Intérêts</span>
        </div>

        <dl class="rows">
          <div><dt>Montant de l’hypothèque</dt><dd>{{ money(result.principal) }}</dd></div>
          <div><dt>Prime d’assurance (SCHL) ajoutée</dt><dd>{{ money(result.premium) }}</dd></div>
          <div><dt>Intérêts totaux</dt><dd>{{ money(result.totalInterest) }}</dd></div>
          <div><dt>Coût total</dt><dd>{{ money(result.totalCost) }}</dd></div>
          <div v-if="accelerated"><dt>Remboursé en</dt><dd>{{ result.years.toLocaleString('fr-CA') }} ans</dd></div>
        </dl>

        <p v-if="insured30" class="fine">
          L’amortissement de 30 ans assuré est réservé aux premiers acheteurs et aux constructions neuves (prime majorée de 0,20 %).
        </p>

        <a :href="requestUrl" class="cta">Obtenir une pré-approbation</a>
      </div>
    </div>
  </section>
</template>

<style scoped>
.wrap { padding-block: clamp(3rem, 7vw, 4.5rem); scroll-margin-top: 1rem; }
h2 { font-size: clamp(1.5rem, 3vw, 1.875rem); }
.lede { margin-top: 0.5rem; font-size: 0.9375rem; }

.layout {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: clamp(1rem, 2.5vw, 1.75rem);
  align-items: start;
  margin-top: 2rem;
}

/* --- Saisie --- */
.inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem 1.25rem;
  background: #eaf6f9;
  border-radius: 1.25rem;
  padding: clamp(1.25rem, 3vw, 2rem);
}
.field { display: grid; gap: 0.45rem; align-content: start; }
.field.full { grid-column: 1 / -1; }
label { font-family: var(--font-title); font-weight: 700; font-size: 0.8125rem; color: var(--c-ink); }
.control { position: relative; }
input, select {
  width: 100%;
  height: 2.9rem;
  border: 2px solid var(--c-ink);
  border-radius: 0.6rem;
  background: #fff;
  padding: 0 2.2rem 0 0.9rem;
  font: 400 0.9375rem var(--font-title);
  color: var(--c-ink);
  appearance: none;
  -moz-appearance: textfield;
}
input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input:focus-visible, select:focus-visible { outline: 3px solid rgb(65 174 196 / 0.45); outline-offset: 1px; }
.unit {
  position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%);
  font: 700 0.8125rem var(--font-title); color: var(--c-ink); pointer-events: none;
}
.select::after {
  content: ''; position: absolute; right: 1rem; top: 50%; width: 0.45rem; height: 0.45rem;
  border-right: 2px solid var(--c-ink); border-bottom: 2px solid var(--c-ink);
  transform: translateY(-70%) rotate(45deg); pointer-events: none;
}
select { cursor: pointer; }
.control.invalid input { border-color: #c2410c; }
.hint { min-height: 1.1em; font-size: 0.75rem; color: #5b6472; }
.hint.warn { color: #b23a0a; font-weight: 600; }

/* --- Résultat --- */
.result {
  background: var(--c-ink);
  color: #fff;
  border-radius: 1.25rem;
  padding: clamp(1.5rem, 3vw, 2rem);
}
.eyebrow { font: 700 0.6875rem var(--font-title); letter-spacing: 0.08em; text-transform: uppercase; }
.amount {
  font: 800 clamp(2.25rem, 5vw, 3.1rem) var(--font-title);
  line-height: 1.05;
  letter-spacing: -0.02em;
  margin-top: 0.6rem;
  font-variant-numeric: tabular-nums;
}
.per { margin-top: 0.2rem; }

.bar { margin-top: 1.5rem; height: 0.55rem; border-radius: 999px; background: #00bcd9; overflow: hidden; }
.bar-capital { display: block; height: 100%; background: var(--c-lime); border-radius: 999px 0 0 999px; transition: width 0.3s ease; }
.legend { display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.6875rem; color: #cfd8e3; }
.dot { display: inline-block; width: 0.4rem; height: 0.4rem; border-radius: 1px; margin-right: 0.35rem; vertical-align: 0.1em; }
.dot.lime { background: var(--c-lime); }
.dot.teal { background: #00bcd9; }

.rows { margin: 1.25rem 0 0; }
.rows > div {
  display: flex; justify-content: space-between; gap: 1rem;
  padding-block: 0.7rem; border-top: 1px solid rgb(255 255 255 / 0.14);
  font-size: 0.875rem;
}
dd { margin: 0; font-family: var(--font-title); font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
.fine { margin-top: 0.5rem; font-size: 0.6875rem; color: #cfd8e3; }

.cta {
  display: block; margin-top: 1.5rem; padding: 0.9rem 1.25rem; border-radius: 999px; text-align: center;
  background: var(--c-lime); color: var(--c-ink); text-decoration: none;
  font: 700 0.875rem var(--font-title);
  transition: background 0.15s, transform 0.15s;
}
.cta:hover, .cta:focus-visible { background: #c3dc14; transform: translateY(-1px); }

@media (max-width: 860px) {
  .layout { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .inputs { grid-template-columns: 1fr; }
}
</style>
