<script setup>
import SiteHeader from '@/components/SiteHeader.vue';
import HeroSection from '@/components/HeroSection.vue';
import IntroSection from '@/components/IntroSection.vue';
import BlockList from '@/components/BlockList.vue';
import SiteFooter from '@/components/SiteFooter.vue';

defineProps({ page: { type: Object, required: true } });
</script>

<template>
  <SiteHeader :nav="page.nav" :request-url="page.requestUrl" />
  <main>
    <HeroSection :home="page.home" />
    <IntroSection
      :text="page.home.intro"
      :cta-label="page.home.ctaLabel"
      :request-url="page.requestUrl"
      :blocks="page.blocks"
    />
    <BlockList :blocks="page.blocks" />
  </main>
  <SiteFooter :contact="page.contact" :nav="page.nav" :request-url="page.requestUrl" />
</template>
