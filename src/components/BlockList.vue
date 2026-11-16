<script setup>
import { computed } from 'vue';
import ImageTextBlock from './blocks/ImageTextBlock.vue';
import QuoteBlock from './blocks/QuoteBlock.vue';
import StatsBlock from './blocks/StatsBlock.vue';
import StepsBlock from './blocks/StepsBlock.vue';
import FaqBlock from './blocks/FaqBlock.vue';
import CtaBlock from './blocks/CtaBlock.vue';

const props = defineProps({ blocks: { type: Array, default: () => [] } });

const components = {
  imageText: ImageTextBlock,
  quote: QuoteBlock,
  stats: StatsBlock,
  steps: StepsBlock,
  faq: FaqBlock,
  cta: CtaBlock,
};

const tones = ['sky', 'sage', 'mist'];

// Ajoute à chaque bloc sa position dans une suite de blocs « Image + texte »
// (pour les filets de séparation et l'alternance des couleurs automatiques).
const items = computed(() => {
  let run = 0;
  return props.blocks
    .filter((b) => components[b.type])
    .map((b, i, all) => {
      const isRow = b.type === 'imageText';
      run = isRow ? (all[i - 1]?.type === 'imageText' ? run + 1 : 0) : 0;
      return {
        block: b,
        is: components[b.type],
        extra: isRow
          ? {
              tone: b.tone === 'auto' ? tones[run % tones.length] : b.tone,
              first: run === 0,
              last: all[i + 1]?.type !== 'imageText',
            }
          : {},
      };
    });
});
</script>

<template>
  <div class="blocks">
    <component :is="it.is" v-for="it in items" :key="it.block.id" :block="it.block" v-bind="it.extra" />
  </div>
</template>

<style scoped>
.blocks { padding-bottom: 2rem; }
</style>
