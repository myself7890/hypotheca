<script setup>
defineProps({ home: { type: Object, required: true } });
</script>

<template>
  <section class="hero container">
    <div class="portrait">
      <span class="shape shape-lime" aria-hidden="true"></span>
      <span class="shape shape-teal" aria-hidden="true"></span>
      <div class="frame">
        <img v-if="home.photo" :src="home.photo.url" :alt="home.photo.alt" />
        <span v-else class="placeholder">[Photo de la courtière]</span>
      </div>
    </div>

    <div class="copy">
      <p class="kicker">{{ home.brokerName }}, courtière hypothécaire</p>
      <h1>{{ home.title }}</h1>
      <p class="bio">{{ home.bio }}</p>
      <p v-if="home.licenseNotice" class="license">{{ home.licenseNotice }}</p>
    </div>
  </section>
</template>

<style scoped>
.hero {
  display: grid;
  grid-template-columns: minmax(0, 380px) minmax(0, 1fr);
  align-items: center;
  gap: clamp(2rem, 5vw, 4rem);
  padding-block: 1.5rem 3.5rem;
}

.portrait { position: relative; aspect-ratio: 282 / 366; max-width: 380px; width: 100%; margin: 0 0 1rem; }
.frame {
  position: relative; z-index: 1; height: 100%;
  border: 1.5px solid var(--c-ink); border-radius: 1.4rem;
  background: var(--c-mist); overflow: hidden;
  display: grid; place-items: center;
}
.frame img { width: 100%; height: 100%; object-fit: cover; }
.placeholder {
  font-family: var(--font-title); font-weight: 600; font-size: 0.8rem; color: var(--c-ink);
  background: #fff; padding: 0.35rem 0.9rem; border-radius: 999px;
}
.shape { position: absolute; }
.shape-lime {
  top: -1rem; right: -1.25rem; width: 38%; height: 38%;
  background: var(--c-lime); border-radius: 0.5rem 1.2rem 0 0;
  transform: skewX(-8deg);
}
.shape-teal {
  left: -1.9rem; bottom: -1.5rem; width: 62%; height: 62%;
  background: var(--c-teal); border-radius: 0 0 1.6rem 0;
  transform: skewX(-8deg);
}

.copy { max-width: 46rem; justify-self: center; padding-block: 1rem; }
.kicker { font-family: var(--font-script); font-size: 1.75rem; color: var(--c-teal); line-height: 1.2; margin-bottom: 0.5rem; }
h1 { font-size: clamp(2.4rem, 4.6vw, 3.6rem); letter-spacing: -0.02em; max-width: 20ch; }
.bio { margin-top: 1.75rem; max-width: 48ch; font-size: 1.0625rem; line-height: 1.75; }
.license { margin-top: 1rem; font-size: 0.8125rem; opacity: 0.8; }

@media (max-width: 820px) {
  .hero { grid-template-columns: 1fr; }
  .portrait { max-width: 300px; margin-inline: 1.5rem auto; }
  .copy { justify-self: start; padding-block: 0; }
  .hero { padding-bottom: 1rem; }
}
</style>
