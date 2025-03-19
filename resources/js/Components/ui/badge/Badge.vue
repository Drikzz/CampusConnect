<script setup>
import { computed } from 'vue'
import { badgeVariants } from './badge-variants'
import { cn } from '@/lib/utils'

defineOptions({
  name: 'Badge'
})

const props = defineProps({
  variant: {
    type: String,
    default: 'default'
  },
  class: {
    type: String,
    default: ''
  }
})

const classes = computed(() => {
  return cn(badgeVariants({ variant: props.variant }), props.class)
})
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>
