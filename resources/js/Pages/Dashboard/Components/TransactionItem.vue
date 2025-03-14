<template>
  <!-- Don't display the item at all if it's a verification transaction -->
  <div v-if="transaction.reference_type !== 'verification'" class="p-4 hover:bg-gray-50 transition-colors">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <!-- Left side with icon and description -->
      <div class="flex items-center space-x-3">
        <!-- Ensure both refill and withdrawal have the same styling structure -->
        <div :class="[
          'p-2 rounded-full',
          typeof getIconBackgroundColor === 'string' 
            ? getIconBackgroundColor 
            : Object.entries(getIconBackgroundColor).find(([k, v]) => v)[0]
        ]">
          <component :is="transactionIcon" class="w-5 h-5" :class="getIconColor" />
        </div>
        <div>
          <p class="font-medium">{{ transaction.description }}</p>
          <p class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</p>
        </div>
      </div>
      
      <!-- Right side with amount, status and view button -->
      <div class="flex items-center gap-4 ml-11 sm:ml-0">
        <!-- Amount and Status -->
        <div class="text-right">
          <p v-if="displayableAmount" 
             :class="[
               'font-medium',
               transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'
             ]">
            {{ transaction.type === 'credit' ? '+' : '-' }}₱{{ transaction.amount }}
          </p>
          <p class="text-sm" :class="statusColor">
            {{ capitalizeFirstLetter(transaction.status) }}
          </p>
        </div>
        
        <!-- Only View Button -->
        <div>
          <button 
            @click="openDetailsModal"
            class="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded hover:bg-gray-200 transition">
            View
          </button>
        </div>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <Dialog :open="showDetailsModal" @update:open="showDetailsModal = false">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {{ getTransactionTypeLabel }} Transaction Details
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
              <p class="font-medium" :class="statusColor">{{ capitalizeFirstLetter(transaction.status) }}</p>
            </div>
            <div v-if="displayableAmount">
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
            <p class="text-sm text-gray-500 mb-1">GCash Reference ID</p>
            <p class="text-sm break-all">{{ transaction.reference_id }}</p>
          </div>

          <!-- Transaction Description -->
          <div v-if="transaction.description" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Description</p>
            <p class="text-sm">{{ transaction.description }}</p>
          </div>

          <!-- Receipt Image (if available) - Add responsive sizing for portrait images -->
          <div v-if="transaction.receipt_path" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Receipt</p>
            <div class="flex justify-center">
              <img 
                :src="'/storage/' + transaction.receipt_path" 
                alt="Transaction Receipt" 
                class="rounded-md border max-h-[50vh] object-contain"
              />
            </div>
          </div>
          
          <!-- Download Receipt Button - Only show if status is not pending -->
          <div v-if="canDownloadReceipt && transaction.status.toLowerCase() !== 'pending'" class="flex justify-center pt-3">
            <button 
              @click="downloadReceipt"
              class="bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
              Download Receipt as PDF
            </button>
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
  ArrowDownTrayIcon,
  BanknotesIcon,
  ShieldCheckIcon, 
  CurrencyDollarIcon  // Added coin/currency icon for refill
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

// Determine if amount should be displayed
const displayableAmount = computed(() => {
  return props.transaction.reference_type !== 'verification' && 
         parseFloat(props.transaction.amount) > 0;
});

// Get user-friendly transaction type label
const getTransactionTypeLabel = computed(() => {
  if (!props.transaction.reference_type) {
    return 'Wallet Activation';
  }
  
  switch(props.transaction.reference_type) {
    case 'verification': return 'Verification';
    case 'refill': return 'Refill';
    case 'withdrawal': return 'Withdrawal';
    default: return ucfirst(props.transaction.reference_type);
  }
});

// Helper function to capitalize first letter
const ucfirst = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1);
};

// Add this new function to capitalize status text
const capitalizeFirstLetter = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

// Determine if we can show the receipt download button - updated to exclude pending status
const canDownloadReceipt = computed(() => {
  return (
    ['completed', 'approved'].includes(props.transaction.status.toLowerCase()) ||
    (props.transaction.reference_type === 'refill' && 
     props.transaction.receipt_path && 
     props.transaction.status.toLowerCase() !== 'pending')
  );
});

const openDetailsModal = () => {
  showDetailsModal.value = true;
};

const downloadReceipt = () => {
  window.open(route('seller.wallet.receipt', props.transaction.id), '_blank');
};

// Determine icon based on transaction type
const transactionIcon = computed(() => {
  if (!props.transaction.reference_type) {
    return ShieldCheckIcon; // Use shield icon for wallet activation
  }
  
  switch(props.transaction.reference_type) {
    case 'verification':
      return ShieldCheckIcon;
    case 'refill':
      return CurrencyDollarIcon; // Changed to CurrencyDollarIcon for coin/money visual
    case 'withdrawal':
      return BanknotesIcon;
    default:
      return props.transaction.type === 'credit' ? ArrowUpCircleIcon : ArrowDownTrayIcon;
  }
});

// Fix the issue with getIconBackgroundColor - ensure consistent styling
const getIconBackgroundColor = computed(() => {
  // Handle empty reference_type (wallet activation)
  if (!props.transaction.reference_type) {
    return {
      'bg-blue-100': props.transaction.status.toLowerCase() === 'pending',
      'bg-green-100': ['completed', 'approved'].includes(props.transaction.status.toLowerCase()),
      'bg-red-100': props.transaction.status.toLowerCase() === 'rejected'
    };
  }
  
  switch(props.transaction.reference_type) {
    case 'verification':
      return {
        'bg-blue-100': props.transaction.status.toLowerCase() === 'pending',
        'bg-green-100': ['completed', 'approved'].includes(props.transaction.status.toLowerCase()),
        'bg-red-100': props.transaction.status.toLowerCase() === 'rejected'
      };
    case 'refill':
      return 'bg-green-100'; // Background for refill
    case 'withdrawal':
      return 'bg-red-100'; // Background for withdrawal - explicitly defined
    default:
      return props.transaction.type === 'credit' ? 'bg-green-100' : 'bg-red-100';
  }
});

// Ensure consistent icon styling between refill and withdrawal
const getIconColor = computed(() => {
  // Handle empty reference_type (wallet activation)
  if (!props.transaction.reference_type) {
    return {
      'text-blue-600': props.transaction.status.toLowerCase() === 'pending',
      'text-green-600': ['completed', 'approved'].includes(props.transaction.status.toLowerCase()),
      'text-red-600': props.transaction.status.toLowerCase() === 'rejected'
    };
  }
  
  switch(props.transaction.reference_type) {
    case 'verification':
      return {
        'text-blue-600': props.transaction.status.toLowerCase() === 'pending',
        'text-green-600': ['completed', 'approved'].includes(props.transaction.status.toLowerCase()),
        'text-red-600': props.transaction.status.toLowerCase() === 'rejected'
      };
    case 'refill':
      return 'text-green-600'; // Green color for refill
    case 'withdrawal':
      return 'text-red-600'; // Red color for withdrawal
    default:
      return props.transaction.type === 'credit' ? 'text-green-600' : 'text-red-600';
  }
});

const statusColor = computed(() => {
  const colors = {
    pending: 'text-yellow-600',
    completed: 'text-green-600',
    approved: 'text-green-600',
    denied: 'text-red-600',
    rejected: 'text-red-600',
    failed: 'text-red-600'
  };
  return colors[props.transaction.status.toLowerCase()] || 'text-gray-600';
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  });
};
</script>
