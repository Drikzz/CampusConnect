<template>
  <div class="p-4 hover:bg-gray-50 transition-colors">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <!-- Left side with icon and description -->
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
      
      <!-- Right side with amount, status and action button -->
      <div class="flex items-center gap-4 ml-11 sm:ml-0">
        <!-- Amount and Status -->
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
        
        <!-- View Details Button for Rejected Transactions -->
        <button 
          v-if="showDetailsButton" 
          @click="openDetailsModal"
          class="text-xs text-white bg-primary-color px-2 py-1 rounded hover:bg-primary-color/90">
          View Details
        </button>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <Dialog :open="showDetailsModal" @update:open="showDetailsModal = false">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {{ transaction.reference_type === 'refill' ? 'Refill' : 'Withdrawal' }} Request Details
          </DialogTitle>
          <DialogDescription v-if="transaction.status === 'rejected'">
            This request was rejected by the administrator.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
          <!-- Status and Amount -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <p class="font-medium" :class="statusColor">{{ transaction.status }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Amount</p>
              <p class="font-medium">₱{{ transaction.amount }}</p>
            </div>
          </div>

          <!-- Date Information -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Requested on</p>
              <p class="font-medium">{{ formatDate(transaction.created_at) }}</p>
            </div>
            <div v-if="transaction.processed_at">
              <p class="text-sm text-gray-500">Processed on</p>
              <p class="font-medium">{{ formatDate(transaction.processed_at) }}</p>
            </div>
          </div>
          
          <!-- Rejection Reason -->
          <div v-if="transaction.status === 'rejected'" class="bg-red-50 p-3 rounded-md">
            <h4 class="font-medium text-red-800 mb-1">Rejection Reason</h4>
            <p class="text-red-700 text-sm">{{ transaction.remarks || 'No reason provided' }}</p>
          </div>

          <!-- Reference Info -->
          <div v-if="transaction.reference_id" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Reference ID</p>
            <p class="text-sm break-all">{{ transaction.reference_id }}</p>
          </div>

          <!-- Transaction Description -->
          <div v-if="transaction.description" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Description</p>
            <p class="text-sm">{{ transaction.description }}</p>
          </div>

          <!-- Receipt Image -->
          <div v-if="transaction.receipt_path" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Receipt</p>
            <div class="max-h-[40vh] overflow-auto">
              <img 
                :src="'/storage/' + transaction.receipt_path" 
                alt="Transaction Receipt" 
                class="w-full h-auto rounded-md border"
              />
            </div>
          </div>
        </div>

        <DialogFooter class="mt-4">
          <Button @click="showDetailsModal = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { 
  ArrowUpCircleIcon, 
  ArrowDownCircleIcon,
  BanknotesIcon,
  ShieldCheckIcon 
} from '@heroicons/vue/24/solid'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'

const props = defineProps({
  transaction: {
    type: Object,
    required: true
  }
})

// Modal state
const showDetailsModal = ref(false)

// Determine if we should show the details button
const showDetailsButton = computed(() => {
  return (props.transaction.reference_type === 'refill' || 
          props.transaction.reference_type === 'withdrawal') &&
         (props.transaction.status === 'rejected' || props.transaction.status === 'failed')
})

const openDetailsModal = () => {
  showDetailsModal.value = true
}

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
    rejected: 'text-red-600',
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
