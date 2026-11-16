<script setup>
import logoColor from '@/assets/logo-color.svg';
import logoReversed from '@/assets/logo-reversed.svg';

// `light` : version renversée, pour les fonds foncés.
defineProps({ light: Boolean });
</script>

<template>
  <a href="/" class="logo" aria-label="Hypotheca, cabinet en courtage hypothécaire — accueil">
    <img :src="light ? logoReversed : logoColor" alt="Hypotheca" height="48" />
  </a>
</template>

<style scoped>
.logo { display: inline-block; line-height: 0; }
img { height: 48px; width: auto; }
</style>
