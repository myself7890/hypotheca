<script setup>
defineProps({
  href: { type: String, required: true },
  variant: { type: String, default: 'dark' }, // dark | light
});
</script>

<template>
  <a :href="href" class="btn" :class="variant"><slot /></a>
</template>

<style scoped>
.btn {
  display: inline-block;
  white-space: nowrap;
  padding: 0.7rem 1.45rem;
  border-radius: 999px;
  font-family: var(--font-title);
  font-weight: 700;
  font-size: 0.8125rem;
  text-decoration: none;
  transition: background 0.15s, transform 0.15s;
}
.dark { background: var(--c-ink); color: #fff; }
.dark:hover, .dark:focus-visible { background: #1f2b3b; }
.light { background: #fff; color: var(--c-ink); }
.light:hover, .light:focus-visible { background: var(--c-sky); transform: translateY(-1px); }
</style>
