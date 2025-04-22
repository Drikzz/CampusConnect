<script setup>
import { cn } from '@/lib/utils'
import { provide, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: undefined
  },
  disabled: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: ''
  }
})

const emits = defineEmits(['update:modelValue'])

// Create internal state that can be shared with child components
const selectedValue = ref(props.modelValue)

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  selectedValue.value = newValue
})

// Update modelValue when internal state changes
watch(selectedValue, (newValue) => {
  emits('update:modelValue', newValue)
})

// Provide radio group context to child components
provide('RadioGroup', {
  selectedValue,
  disabled: props.disabled,
  updateValue: (value) => {
    selectedValue.value = value
  }
})
</script>

<template>
  <div
    role="radiogroup"
    :class="cn('grid gap-2', props.class)"
  >
    <slot />
  </div>
</template>
