<script setup>
import SiteHeader from '@/components/SiteHeader.vue';
import HeroSection from '@/components/HeroSection.vue';
import BlockList from '@/components/BlockList.vue';
import SiteFooter from '@/components/SiteFooter.vue';

defineProps({ page: { type: Object, required: true } });
</script>

<template>
  <SiteHeader :nav="page.nav" :request-url="page.requestUrl" />
  <main>
    <HeroSection
      :home="page.home"
      :request-url="page.requestUrl"
      :blocks="page.blocks"
      :contact="page.contact"
    />
    <BlockList :blocks="page.blocks" />
  </main>
  <SiteFooter :contact="page.contact" :nav="page.nav" :request-url="page.requestUrl" />
</template>
