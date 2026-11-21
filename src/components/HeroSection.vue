<script setup>
import HeroBanner from './HeroBanner.vue';
import IntroSection from './IntroSection.vue';

defineProps({
  home: { type: Object, required: true },
  requestUrl: { type: String, required: true },
  blocks: { type: Array, default: () => [] },
  contact: { type: Object, default: () => ({}) },
});
</script>

<template>
  <section class="hero">
    <HeroBanner :home="home" :contact="contact" />

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

@media (max-width: 820px) {
  .facets { height: 45%; }
}
</style>
