<script setup>
import { computed } from "vue";
import AppButton from "./AppButton.vue";

const props = defineProps({
  text: { type: String, default: "" },
  ctaLabel: { type: String, default: "" },
  requestUrl: { type: String, required: true },
  blocks: { type: Array, default: () => [] },
  embedded: Boolean, // true : grand texte d'accroche dans le hero
});

const escapeHtml = (s) =>
  s.replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
const escapeRe = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

// Transforme chaque nom de service trouvé dans le texte en lien d'ancrage.
const html = computed(() => {
  let out = escapeHtml(props.text);
  for (const s of props.blocks.filter((b) => b.type === "imageText")) {
    const re = new RegExp(`\\b(${escapeRe(escapeHtml(s.title))})`, "i");
    out = out.replace(re, `<a href="#${s.slug}">$1</a>`);
  }
  return out;
});
</script>

<template>
  <section class="intro container" :class="{ embedded }">
    <div class="main">
      <p class="text" v-html="html"></p>
      <AppButton :href="requestUrl">{{ ctaLabel }}</AppButton>
    </div>
  </section>
</template>

<style scoped>
.intro {
  display: grid;
  text-align: left;
  padding-block: 3.5rem 4.5rem;
}
.main { display: grid; justify-items: start; }
.main::before {
  content: "";
  width: 3.5rem;
  height: 4px;
  border-radius: 2px;
  margin-bottom: 2rem;
  background: linear-gradient(90deg, var(--c-teal) 60%, var(--c-lime) 60%);
}
.text {
  max-width: none;
  font-size: clamp(1.25rem, 2.6vw, 1.875rem);
  line-height: 1.55;
  margin-bottom: 2.25rem;
}
.text :deep(a) {
  font-family: var(--font-title);
  font-weight: 700;
  color: var(--c-ink);
  text-decoration: underline;
  text-decoration-color: var(--c-teal);
  text-decoration-thickness: 2px;
  text-underline-offset: 0.22em;
}
.text :deep(a:hover) {
  text-decoration-color: var(--c-lime);
}

/* Version hero : grand texte d'accroche sous la bannière.
   La taille suit aussi la hauteur de l'écran pour tenir dans la moitié basse. */
.intro.embedded {
  padding-block: clamp(1.5rem, 4svh, 3rem);
}
.embedded .main::before { margin-bottom: clamp(1rem, 3svh, 1.75rem); }
.embedded .text {
  max-width: 50ch;
  font-family: var(--font-title);
  font-weight: 500;
  font-size: clamp(1rem, min(2.1vw, 3.2svh), 2.1rem);
  line-height: 1.45;
  letter-spacing: -0.012em;
  color: var(--c-ink);
  text-wrap: pretty;
  margin-bottom: clamp(1.25rem, 3.5svh, 2.25rem);
}
/* Services en surligné (effet marqueur) plutôt que soulignés. */
.embedded .text :deep(a) {
  font-weight: 700;
  text-decoration: none;
  padding-inline: 0.1em;
  background: linear-gradient(transparent 58%, rgba(65, 174, 196, 0.28) 58%);
  -webkit-box-decoration-break: clone;
  box-decoration-break: clone;
  transition: background-color 0.2s;
}
.embedded .text :deep(a:hover),
.embedded .text :deep(a:focus-visible) {
  background-color: rgba(176, 200, 10, 0.25);
}
.embedded :deep(.btn) { font-size: 0.9rem; padding: 0.85rem 1.75rem; }
</style>
