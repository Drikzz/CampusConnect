<template>
  <div class="p-4 hover:bg-gray-50 transition-colors">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div :class="[
          'p-2 rounded-full',
          transaction.type === 'credit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'
        ]">
          <component :is="transactionIcon" class="w-5 h-5" />
        </div>
        <div>
          <p class="font-medium">{{ transaction.description }}</p>
          <p class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</p>
        </div>
      </div>
      <div class="text-right">
        <p :class="[
          'font-medium',
          transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'
        ]">
          {{ transaction.type === 'credit' ? '+' : '-' }}₱{{ transaction.amount }}
        </p>
        <p class="text-sm" :class="statusColor">
          {{ transaction.status }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { ArrowUpCircleIcon, ArrowDownCircleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  transaction: {
    type: Object,
    required: true
  }
})

const transactionIcon = computed(() => {
  return props.transaction.type === 'credit' ? ArrowUpCircleIcon : ArrowDownCircleIcon
})

const statusColor = computed(() => {
  const colors = {
    pending: 'text-yellow-600',
    completed: 'text-green-600',
    failed: 'text-red-600'
  }
  return colors[props.transaction.status] || 'text-gray-600'
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  })
}
</script>
