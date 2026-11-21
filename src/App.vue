<script setup>
import SiteHeader from '@/components/SiteHeader.vue';
import HeroSection from '@/components/HeroSection.vue';
import BlockList from '@/components/BlockList.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import RatesPage from '@/pages/RatesPage.vue';

defineProps({ page: { type: Object, required: true } });
</script>

<template>
  <SiteHeader :nav="page.nav" :request-url="page.requestUrl" :overlay="!!page.home" />
  <main>
    <RatesPage
      v-if="page.name === 'rates'"
      :rates="page.rates"
      :home="page.home"
      :blocks="page.blocks"
      :contact="page.contact"
      :request-url="page.requestUrl"
    />
    <template v-else>
      <HeroSection
        v-if="page.home"
        :home="page.home"
        :request-url="page.requestUrl"
        :blocks="page.blocks"
        :contact="page.contact"
      />
      <BlockList :blocks="page.blocks" />
    </template>
  </main>
  <SiteFooter
    :contact="page.contact"
    :nav="page.nav"
    :request-url="page.requestUrl"
    :flush="page.name === 'rates'"
  />
</template>
