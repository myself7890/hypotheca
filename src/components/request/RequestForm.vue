<script setup>
import { reactive, ref, nextTick } from 'vue';

const props = defineProps({
  action: { type: String, required: true },
  csrf: { type: Object, required: true }, // { name, value }
  success: { type: String, default: '' },
});

// Mêmes listes et mêmes règles que modules/hypotheca/models/RequestForm.php
const REASONS = [
  { id: 'purchase', label: 'Achat' },
  { id: 'renewal', label: 'Renouvellement' },
  { id: 'refinance', label: 'Refinancement' },
  { id: 'heloc', label: 'Marge de crédit hypothécaire' },
  { id: 'other', label: 'Autre' },
];
const CONTACT_PREFS = [
  { id: 'call', label: 'Un retour d’appel' },
  { id: 'email', label: 'Une réponse par courriel' },
];

const form = reactive({
  firstName: '', lastName: '', job: '', location: '',
  coFirstName: '', coLastName: '', coJob: '',
  reason: 'purchase', amount: '',
  phone: '', email: '', contactPref: 'call', message: '',
});
const honeypot = ref('');
const errors = ref({});
const status = ref('idle'); // idle | sending | sent
const formError = ref('');
const formEl = ref(null);
const doneEl = ref(null);

const REQUIRED = ['firstName', 'lastName', 'job', 'location', 'phone'];

function validate() {
  const e = {};
  for (const key of REQUIRED) if (!form[key].trim()) e[key] = 'Ce champ est obligatoire.';
  const email = form.email.trim();
  if (!email && form.contactPref === 'email') e.email = 'Indiquez votre courriel pour recevoir une réponse par courriel.';
  else if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) e.email = 'Ce courriel n’est pas valide.';
  const phone = form.phone.trim();
  if (phone && (phone.replace(/\D/g, '').length < 10 || !/^[\d\s().+-]+$/.test(phone))) {
    e.phone = 'Ce numéro de téléphone n’est pas valide.';
  }
  const amount = String(form.amount).trim();
  if (amount && !(Number(amount) >= 0)) e.amount = 'Indiquez un montant en chiffres.';
  return e;
}

// Place le curseur sur le premier champ en erreur (ordre du formulaire).
async function focusFirstError() {
  await nextTick();
  formEl.value?.querySelector('[aria-invalid="true"]')?.focus();
}

// Une fois corrigé, le message d'erreur du champ disparaît.
function clearError(key) {
  if (errors.value[key]) {
    const { [key]: _, ...rest } = errors.value;
    errors.value = rest;
  }
}

async function submit() {
  formError.value = '';
  errors.value = validate();
  if (Object.keys(errors.value).length) {
    focusFirstError();
    return;
  }

  status.value = 'sending';
  try {
    const res = await fetch(props.action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': props.csrf.value },
      body: JSON.stringify({ request: form, website: honeypot.value }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      errors.value = data.errors ?? {};
      formError.value = data.message || 'L’envoi a échoué. Réessayez dans un moment.';
      status.value = 'idle';
      focusFirstError();
      return;
    }
    status.value = 'sent';
    await nextTick();
    doneEl.value?.focus();
  } catch {
    formError.value = 'Connexion impossible. Vérifiez votre accès Internet et réessayez.';
    status.value = 'idle';
  }
}

const fieldProps = (key) => ({
  id: `req-${key}`,
  'aria-invalid': errors.value[key] ? 'true' : null,
  'aria-describedby': errors.value[key] ? `req-${key}-error` : null,
});
</script>

<template>
  <div v-if="status === 'sent'" ref="doneEl" class="done" tabindex="-1" role="status">
    <span class="done-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5" /></svg>
    </span>
    <p>{{ success || 'Merci! Votre demande a bien été envoyée.' }}</p>
  </div>

  <form v-else ref="formEl" class="form" novalidate @submit.prevent="submit">
    <!-- Champ piège pour les robots -->
    <div class="trap" aria-hidden="true">
      <label for="req-website">Site Web</label>
      <input id="req-website" v-model="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" />
    </div>

    <!-- Demandeur / Codemandeur côte à côte -->
    <div class="people">
      <fieldset class="group">
        <legend>Demandeur</legend>
        <div class="field">
          <label for="req-firstName">Prénom <span class="req" aria-hidden="true">*</span></label>
          <input v-bind="fieldProps('firstName')" v-model="form.firstName" type="text" autocomplete="given-name" required @input="clearError('firstName')" />
          <p v-if="errors.firstName" :id="'req-firstName-error'" class="error">{{ errors.firstName }}</p>
        </div>
        <div class="field">
          <label for="req-lastName">Nom <span class="req" aria-hidden="true">*</span></label>
          <input v-bind="fieldProps('lastName')" v-model="form.lastName" type="text" autocomplete="family-name" required @input="clearError('lastName')" />
          <p v-if="errors.lastName" :id="'req-lastName-error'" class="error">{{ errors.lastName }}</p>
        </div>
        <div class="field">
          <label for="req-job">Emploi actuel <span class="req" aria-hidden="true">*</span></label>
          <input v-bind="fieldProps('job')" v-model="form.job" type="text" autocomplete="organization-title" required @input="clearError('job')" />
          <p v-if="errors.job" :id="'req-job-error'" class="error">{{ errors.job }}</p>
        </div>
        <div class="field">
          <label for="req-location">Code postal ou ville <span class="req" aria-hidden="true">*</span></label>
          <input v-bind="fieldProps('location')" v-model="form.location" type="text" autocomplete="postal-code" required :aria-describedby="errors.location ? 'req-location-error req-location-help' : 'req-location-help'" @input="clearError('location')" />
          <p v-if="errors.location" :id="'req-location-error'" class="error">{{ errors.location }}</p>
          <p id="req-location-help" class="help">Cette information me permettra de mieux cerner votre projet selon votre région.</p>
        </div>
      </fieldset>

      <fieldset class="group">
        <legend>Codemandeur <span class="optional">(s’il y a lieu)</span></legend>
        <div class="field">
          <label for="req-coFirstName">Prénom</label>
          <input id="req-coFirstName" v-model="form.coFirstName" type="text" autocomplete="off" />
        </div>
        <div class="field">
          <label for="req-coLastName">Nom</label>
          <input id="req-coLastName" v-model="form.coLastName" type="text" autocomplete="off" />
        </div>
        <div class="field">
          <label for="req-coJob">Emploi actuel</label>
          <input id="req-coJob" v-model="form.coJob" type="text" autocomplete="off" />
        </div>
      </fieldset>
    </div>

    <fieldset class="group">
      <legend>Information sur le prêt</legend>
      <div class="row">
        <div class="field">
          <label for="req-reason">Raison du prêt</label>
          <select id="req-reason" v-model="form.reason">
            <option v-for="r in REASONS" :key="r.id" :value="r.id">{{ r.label }}</option>
          </select>
        </div>
        <div class="field">
          <label for="req-amount">Montant approximatif</label>
          <div class="money">
            <input v-bind="fieldProps('amount')" v-model="form.amount" type="number" min="0" step="1000" inputmode="numeric" @input="clearError('amount')" />
            <span aria-hidden="true">$</span>
          </div>
          <p v-if="errors.amount" :id="'req-amount-error'" class="error">{{ errors.amount }}</p>
        </div>
      </div>
    </fieldset>

    <fieldset class="group">
      <legend>Vos coordonnées</legend>
      <div class="row">
        <div class="field">
          <label for="req-phone">Téléphone <span class="req" aria-hidden="true">*</span></label>
          <input v-bind="fieldProps('phone')" v-model="form.phone" type="tel" autocomplete="tel" required @input="clearError('phone')" />
          <p v-if="errors.phone" :id="'req-phone-error'" class="error">{{ errors.phone }}</p>
        </div>
        <div class="field">
          <label for="req-email">Courriel <span v-if="form.contactPref === 'email'" class="req" aria-hidden="true">*</span></label>
          <input v-bind="fieldProps('email')" v-model="form.email" type="email" autocomplete="email" :required="form.contactPref === 'email'" @input="clearError('email')" />
          <p v-if="errors.email" :id="'req-email-error'" class="error">{{ errors.email }}</p>
        </div>
      </div>
      <div class="field">
        <label for="req-contactPref">Je désire…</label>
        <select id="req-contactPref" v-model="form.contactPref" @change="clearError('email')">
          <option v-for="c in CONTACT_PREFS" :key="c.id" :value="c.id">{{ c.label }}</option>
        </select>
      </div>
      <div class="field">
        <label for="req-message">Message</label>
        <textarea id="req-message" v-model="form.message" rows="5" maxlength="5000"></textarea>
      </div>
    </fieldset>

    <p v-if="formError" class="form-error" role="alert">{{ formError }}</p>

    <button type="submit" class="submit" :disabled="status === 'sending'">
      {{ status === 'sending' ? 'Envoi…' : 'Envoyer' }}
    </button>
  </form>
</template>

<style scoped>
.form { display: grid; gap: 2.25rem; }
.people { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem clamp(1.25rem, 3vw, 2.25rem); align-items: start; }

fieldset { border: 0; margin: 0; padding: 0; min-width: 0; }
.group { display: grid; gap: 1.1rem; }
/* Sections séparées par un filet, sauf la première rangée */
.form > .group { padding-top: 2.25rem; border-top: 1px solid var(--c-line); }

legend {
  float: left; /* place la légende dans la grille, comme un titre */
  width: 100%;
  padding: 0;
  margin-bottom: 0.25rem;
  font-family: var(--font-title);
  font-weight: 800;
  font-size: clamp(1.2rem, 2.2vw, 1.45rem);
  line-height: 1.2;
  color: var(--c-ink);
}
.optional { font-family: var(--font-body); font-weight: 400; font-size: 0.875rem; color: #7b818c; }

.row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem clamp(1.25rem, 3vw, 2.25rem); align-items: start; }
.field { display: grid; gap: 0.35rem; }
label { font-family: var(--font-title); font-weight: 700; font-size: 0.8125rem; color: var(--c-ink); }
.req { color: var(--c-teal-dark); }

input, select, textarea {
  width: 100%;
  border: 1.5px solid #c9ccd3;
  border-radius: 0.5rem;
  background: #fff;
  padding: 0.7rem 0.85rem;
  font: 400 1rem var(--font-body);
  color: var(--c-ink);
  transition: border-color 0.15s, box-shadow 0.15s;
}
input:hover, select:hover, textarea:hover { border-color: #9aa1ad; }
input:focus-visible, select:focus-visible, textarea:focus-visible {
  outline: none;
  border-color: var(--c-teal);
  box-shadow: 0 0 0 3px rgb(65 174 196 / 0.22);
}
input[aria-invalid='true'] { border-color: #c0392b; }
textarea { resize: vertical; min-height: 8rem; line-height: 1.5; }

/* Liste : petit triangle turquoise, comme le calculateur */
select {
  appearance: none;
  padding-right: 2.25rem;
  cursor: pointer;
  background-image: linear-gradient(45deg, transparent 50%, var(--c-teal) 50%), linear-gradient(135deg, var(--c-teal) 50%, transparent 50%);
  background-position: right 1.1rem center, right 0.8rem center;
  background-size: 0.3rem 0.3rem;
  background-repeat: no-repeat;
}

.money { position: relative; }
.money input { padding-right: 2rem; -moz-appearance: textfield; }
.money input::-webkit-outer-spin-button, .money input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.money span {
  position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%);
  font: 700 0.9375rem var(--font-title); color: var(--c-ink); pointer-events: none;
}

.help { font-size: 0.8125rem; line-height: 1.5; color: #6b717c; }
.error { font-size: 0.8125rem; font-weight: 600; color: #b03a2e; }
.form-error {
  padding: 0.85rem 1rem;
  border-left: 4px solid #c0392b;
  background: #fbeeec;
  border-radius: 0.3rem;
  font-size: 0.9375rem;
  color: #7d2a20;
}

.submit {
  justify-self: start;
  border: 0;
  cursor: pointer;
  padding: 0.85rem 2.1rem;
  border-radius: 999px;
  background: var(--c-ink);
  color: #fff;
  font: 700 0.9375rem var(--font-title);
  transition: background 0.15s;
}
.submit:hover, .submit:focus-visible { background: #1f2b3b; }
.submit:focus-visible { outline: 3px solid rgb(65 174 196 / 0.5); outline-offset: 2px; }
.submit:disabled { opacity: 0.7; cursor: progress; }

.trap { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

/* Confirmation */
.done {
  display: flex;
  align-items: center;
  gap: 1.1rem;
  padding: 1.75rem clamp(1.25rem, 3vw, 2rem);
  background: #eaf6f9;
  border-radius: 0.9rem;
  font-family: var(--font-title);
  font-weight: 700;
  font-size: 1.0625rem;
  line-height: 1.45;
  color: var(--c-ink);
}
.done:focus { outline: none; }
.done-icon {
  flex: none; width: 2.75rem; height: 2.75rem; border-radius: 50%;
  display: grid; place-items: center; background: var(--c-teal); color: #fff;
}
.done-icon svg { width: 1.4rem; height: 1.4rem; fill: none; stroke: currentColor; stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round; }

@media (max-width: 640px) {
  .people, .row { grid-template-columns: 1fr; }
}
</style>
