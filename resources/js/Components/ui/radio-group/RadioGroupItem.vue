<script setup>
import { cn } from '@/lib/utils'
import { inject, computed } from 'vue'

const props = defineProps({
  value: {
    type: [String, Number, Boolean],
    required: true
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

// Inject radio group context
const radioGroup = inject('RadioGroup')

// Computed property to determine if this radio is checked
const isChecked = computed(() => {
  return radioGroup.selectedValue.value === props.value
})

// Computed property to determine if this radio is disabled
const isDisabled = computed(() => {
  return props.disabled || radioGroup.disabled
})

// Update the radio group value when this radio is clicked
function handleChange() {
  if (!isDisabled.value) {
    radioGroup.updateValue(props.value)
  }
}
</script>

<template>
  <div
    :class="cn('flex items-center space-x-2', props.class)"
  >
    <button
      type="button"
      role="radio"
      :aria-checked="isChecked"
      :data-state="isChecked ? 'checked' : 'unchecked'"
      :disabled="isDisabled"
      @click="handleChange"
      class="aspect-square h-4 w-4 rounded-full border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
      :class="{ 'bg-primary': isChecked }"
    >
      <div class="flex items-center justify-center" v-if="isChecked">
        <div class="h-2 w-2 rounded-full bg-white"></div>
      </div>
    </button>
    <slot />
  </div>
</template>
