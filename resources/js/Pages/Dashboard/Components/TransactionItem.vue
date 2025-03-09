<template>
  <div class="p-4 hover:bg-gray-50 transition-colors">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div :class="[
          'p-2 rounded-full',
          getIconBackgroundColor
        ]">
          <component :is="transactionIcon" class="w-5 h-5" :class="getIconColor" />
        </div>
        <div>
          <p class="font-medium">{{ transaction.description }}</p>
          <p class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</p>
        </div>
      </div>
      <div class="text-right">
        <p v-if="transaction.reference_type !== 'verification'" 
           :class="[
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
import { 
  ArrowUpCircleIcon, 
  ArrowDownCircleIcon,
  BanknotesIcon,
  ShieldCheckIcon 
} from '@heroicons/vue/24/solid'

const props = defineProps({
  transaction: {
    type: Object,
    required: true
  }
})

// Determine icon based on transaction type
const transactionIcon = computed(() => {
  switch(props.transaction.reference_type) {
    case 'verification':
      return ShieldCheckIcon
    case 'refill':
      return BanknotesIcon
    case 'withdrawal':
      return ArrowDownCircleIcon
    default:
      return props.transaction.type === 'credit' ? ArrowUpCircleIcon : ArrowDownCircleIcon
  }
})

// Get icon background color based on transaction type
const getIconBackgroundColor = computed(() => {
  switch(props.transaction.reference_type) {
    case 'verification':
      return {
        'bg-blue-100': props.transaction.status === 'pending',
        'bg-green-100': props.transaction.status === 'approved',
        'bg-red-100': props.transaction.status === 'denied'
      }
    default:
      return props.transaction.type === 'credit' ? 'bg-green-100' : 'bg-red-100'
  }
})

// Get icon color based on transaction type
const getIconColor = computed(() => {
  switch(props.transaction.reference_type) {
    case 'verification':
      return {
        'text-blue-600': props.transaction.status === 'pending',
        'text-green-600': props.transaction.status === 'approved',
        'text-red-600': props.transaction.status === 'denied'
      }
    default:
      return props.transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'
  }
})

const statusColor = computed(() => {
  const colors = {
    pending: 'text-yellow-600',
    completed: 'text-green-600',
    approved: 'text-green-600',
    denied: 'text-red-600',
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
