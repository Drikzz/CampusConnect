<template>
  <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="statusClasses">
    {{ status }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusClasses = computed(() => {
  switch (props.status.toLowerCase()) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'processing':
    case 'accepted':
      return 'bg-blue-100 text-blue-800'
    case 'meetup scheduled':
      return 'bg-purple-100 text-purple-800'
    case 'delivered':
      return 'bg-indigo-100 text-indigo-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    case 'disputed':
      return 'bg-orange-100 text-orange-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
})
</script>
