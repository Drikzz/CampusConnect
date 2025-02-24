<template>
  <div class="mb-6">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ title }}</h3>
    <ul class="space-y-2">
      <slot />
    </ul>
  </div>
</template>

<script setup>
defineProps({
  title: String
})
</script>
