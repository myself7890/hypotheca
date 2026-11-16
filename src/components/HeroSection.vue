<script setup>
import { computed } from 'vue';
import IntroSection from './IntroSection.vue';

const props = defineProps({
  home: { type: Object, required: true },
  requestUrl: { type: String, required: true },
  blocks: { type: Array, default: () => [] },
  contact: { type: Object, default: () => ({}) },
});

const telHref = computed(() =>
  props.contact.phone ? `tel:${props.contact.phone.replace(/[^\d+]/g, '')}` : null,
);
</script>

<template>
  <section class="hero">
    <!-- Bannière : photo à gauche, texte à droite -->
    <div class="banner">
      <div class="inner container">
        <div class="portrait">
          <img v-if="home.photo" :src="home.photo.url" :alt="home.photo.alt" />
          <span v-else class="placeholder">[Photo de la courtière]</span>
        </div>

        <div class="copy">
          <p class="kicker">{{ home.brokerName }}, courtière hypothécaire</p>
          <h1>{{ home.title }}</h1>
          <a v-if="contact.phone" :href="telHref" class="phone">
            <span class="phone-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" /></svg>
            </span>
            {{ contact.phone }}
          </a>
        </div>
      </div>
    </div>

    <!-- Texte d'introduction en bas du hero -->
    <div class="intro-strip">
      <!-- Facettes décoratives qui rappellent la bannière -->
      <svg class="facets" viewBox="0 0 400 300" preserveAspectRatio="none" aria-hidden="true">
        <polygon points="330,60 400,0 400,170" fill="#b0c80a" fill-opacity=".5" />
        <polygon points="330,60 400,170 250,150" fill="#9bd3e0" />
        <polygon points="250,150 400,170 400,300" fill="#41aec4" fill-opacity=".85" />
        <polygon points="250,150 400,300 90,300" fill="#7cc5d6" />
        <polygon points="150,225 250,150 90,300" fill="#ebf0cb" />
      </svg>

      <IntroSection
        embedded
        :text="home.intro"
        :cta-label="home.ctaLabel"
        :request-url="requestUrl"
        :blocks="blocks"
      />
    </div>
  </section>
</template>

<style scoped>
/* Bannière + intro = 100 % de la hauteur de l'écran */
.hero {
  position: relative;
  min-height: 100vh;
  min-height: 100svh;
  display: flex;
  flex-direction: column;
}
.banner {
  flex: none;
  height: 50vh;
  height: 50svh;
  min-height: 17rem;
  display: flex;
  flex-direction: column; /* garde la largeur du .container de .inner */
  background: #eaf6f9 url('../assets/hero-poly.svg') center / cover no-repeat;
  overflow: hidden;
}
/* L'intro prend toute la hauteur restante, contenu centré verticalement. */
.intro-strip {
  position: relative;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: #fff;
  overflow: hidden;
}
.intro-strip > section { position: relative; z-index: 1; }
.facets {
  position: absolute;
  right: 0;
  bottom: 0;
  width: clamp(12rem, 30vw, 32rem);
  height: 80%;
}


.inner {
  flex: 1;
  min-height: 0;
  display: grid;
  grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
  grid-template-rows: minmax(0, 1fr); /* la photo suit la hauteur de la bannière, pas l'inverse */
  align-items: end;
  gap: clamp(1rem, 4vw, 3rem);
  padding-top: clamp(4.75rem, 13svh, 6.5rem); /* place pour l'en-tête superposé */
}

/* Photo détourée (PNG transparent) affichée en entier, posée au bas de la bannière. */
.portrait {
  align-self: stretch;
  justify-self: center;
  width: 100%;
  max-width: 520px;
  min-height: 0;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}
.portrait img {
  height: 100%;
  width: auto;
  max-width: 100%;
  object-fit: contain;
  object-position: center bottom;
}
.placeholder {
  margin: auto;
  font-family: var(--font-title); font-weight: 600; font-size: 0.8rem; color: var(--c-ink);
  background: #fff; padding: 0.35rem 0.9rem; border-radius: 999px;
}

.copy { align-self: center; padding-bottom: 1.5rem; max-width: 40rem; }
/* Marine plutôt que turquoise : reste lisible quand il passe sur une facette turquoise. */
.kicker { font-family: var(--font-script); font-size: clamp(1.15rem, min(1.9vw, 3.4svh), 1.75rem); color: var(--c-ink); line-height: 1.2; margin-bottom: 0.5rem; }
/* Taille liée à la largeur ET à la hauteur : le titre doit tenir dans la demi-hauteur de la bannière. */
h1 { font-size: clamp(1.5rem, min(3.6vw, 5.6svh), 3rem); letter-spacing: -0.02em; max-width: 18ch; }
/* Le titre entre depuis le bord droit de l'écran (la bannière masque le débordement). */
h1 { animation: slide-in-right 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.15s both; }
@keyframes slide-in-right {
  from { transform: translateX(100vw); opacity: 0; }
  to { transform: none; opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
  h1 { animation: none; }
}
/* Téléphone en gros sous le titre */
.phone {
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
  margin-top: clamp(0.6rem, 2svh, 1.25rem);
  font-family: var(--font-title);
  font-weight: 700;
  font-size: clamp(1.25rem, min(2.5vw, 4svh), 2.1rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--c-ink);
  text-decoration: none;
  white-space: nowrap;
}
.phone-icon {
  flex: none;
  width: 1.5em;
  height: 1.5em;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: var(--c-ink);
  color: #fff;
  transition: background 0.15s;
}
.phone-icon svg { width: 0.75em; height: 0.75em; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
.phone:hover .phone-icon, .phone:focus-visible .phone-icon { background: var(--c-teal); }
.copy { display: flex; flex-direction: column; align-items: flex-start; }

@media (max-width: 820px) {
  .inner { grid-template-columns: 1fr; grid-template-rows: auto; align-items: start; padding-top: 5.5rem; }
  .copy { order: -1; padding-bottom: 1rem; }
  .banner { height: auto; }
  .portrait { height: 32svh; max-width: 300px; }
  .facets { height: 45%; }
}
</style>
