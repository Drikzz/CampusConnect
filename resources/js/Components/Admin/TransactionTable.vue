<template>
  <div class="overflow-x-auto">
    <!-- Orders Table -->
    <table v-if="transactionType === 'orders'" class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Order ID
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Buyer
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Total Amount
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Status
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Date
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="order in paginatedItems" :key="order.id" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ order.id }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-xs font-medium">
                  {{ order.user?.name?.charAt(0) || 'U' }}
                </span>
              </div>
              <div class="ml-3">
                <div class="font-medium">{{ order.user?.name || 'N/A' }}</div>
                <div class="text-xs text-gray-500" v-if="order.user?.seller_code">
                  {{ order.user.seller_code }}
                </div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
            ₱{{ formatCurrency(order.amount) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
              :class="getStatusClasses(order.status)">
              {{ formatStatus(order.status) }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
            {{ formatDate(order.created_at) }}
          </td>
        </tr>
        <tr v-if="!paginatedItems || paginatedItems.length === 0">
          <td colspan="5" class="px-6 py-12 text-center text-gray-500">
            <div class="flex flex-col items-center">
              <ShoppingCartIcon class="w-12 h-12 text-gray-300 mb-3" />
              <h3 class="text-lg font-medium text-gray-900 mb-1">No orders yet</h3>
              <p class="text-sm">Orders will appear here once they are placed.</p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Trades Table -->
    <table v-if="transactionType === 'trades'" class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Trade ID
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Buyer
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Product
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Trade Breakdown
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Status
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Date
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="trade in paginatedItems" :key="trade.id" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ trade.id }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-xs font-medium">
                  {{ trade.user?.name?.charAt(0) || 'U' }}
                </span>
              </div>
              <div class="ml-3">
                <div class="font-medium">{{ trade.user?.name || 'N/A' }}</div>
                <div class="text-xs text-gray-500" v-if="trade.user?.seller_code">
                  {{ trade.user.seller_code }}
                </div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <div class="font-medium">{{ trade.product_name }}</div>
            <div class="text-xs text-gray-500">Value: ₱{{ formatCurrency(trade.product_value) }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm">
            <div class="space-y-1">
              <div class="flex justify-between">
                <span class="text-gray-600">Product:</span>
                <span class="font-medium">₱{{ formatCurrency(trade.product_value) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Offered Items:</span>
                <span class="font-medium">₱{{ formatCurrency(trade.offered_items_value) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Additional Cash:</span>
                <span class="font-medium">₱{{ formatCurrency(trade.additional_cash) }}</span>
              </div>
              <div class="flex justify-between border-t pt-1">
                <span class="text-gray-800 font-semibold">Total Value:</span>
                <span class="font-semibold text-green-600">₱{{ formatCurrency(trade.amount) }}</span>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
              :class="getStatusClasses(trade.status)">
              {{ formatStatus(trade.status) }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
            {{ formatDate(trade.created_at) }}
          </td>
        </tr>
        <tr v-if="!paginatedItems || paginatedItems.length === 0">
          <td colspan="6" class="px-6 py-12 text-center text-gray-500">
            <div class="flex flex-col items-center">
              <ArrowsRightLeftIcon class="w-12 h-12 text-gray-300 mb-3" />
              <h3 class="text-lg font-medium text-gray-900 mb-1">No trades yet</h3>
              <p class="text-sm">Trades will appear here once they are initiated.</p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Wallet Transactions Table -->
    <table v-if="transactionType === 'wallet'" class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Transaction ID
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            User
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Amount
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Type
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Status
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Date
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="transaction in paginatedItems" :key="transaction.id" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ transaction.id }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-xs font-medium">
                  {{ transaction.user?.name?.charAt(0) || 'U' }}
                </span>
              </div>
              <div class="ml-3">
                <div class="font-medium">{{ transaction.user?.name || 'N/A' }}</div>
                <div class="text-xs text-gray-500" v-if="transaction.user?.seller_code">
                  {{ transaction.user.seller_code }}
                </div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="{
            'text-green-600': transaction.type === 'credit' && transaction.reference_type !== 'withdrawal',
            'text-red-600': transaction.type === 'debit' || transaction.reference_type === 'withdrawal'
          }">
            <div class="flex items-center">
              <span v-if="transaction.type === 'credit' && transaction.reference_type !== 'withdrawal'" class="mr-1">+</span>
              <span v-else class="mr-1">-</span>
              ₱{{ formatCurrency(transaction.amount) }}
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
            {{ formatTransactionType(transaction.reference_type) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
              :class="getStatusClasses(transaction.status)">
              {{ formatStatus(transaction.status) }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
            {{ formatDate(transaction.created_at) }}
          </td>
        </tr>
        <tr v-if="!paginatedItems || paginatedItems.length === 0">
          <td colspan="6" class="px-6 py-12 text-center text-gray-500">
            <div class="flex flex-col items-center">
              <BanknotesIcon class="w-12 h-12 text-gray-300 mb-3" />
              <h3 class="text-lg font-medium text-gray-900 mb-1">No wallet transactions yet</h3>
              <p class="text-sm">Wallet transactions will appear here once processed.</p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="px-6 py-3 flex items-center justify-between border-t border-gray-200 bg-gray-50">
      <div class="flex-1 flex justify-between sm:hidden">
        <button 
          @click="$emit('update-page', currentPage - 1)" 
          :disabled="currentPage <= 1"
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
        >
          Previous
        </button>
        <button 
          @click="$emit('update-page', currentPage + 1)" 
          :disabled="currentPage >= totalPages"
          class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
        >
          Next
        </button>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Showing 
            <span class="font-medium">{{ startItem }}</span>
            to
            <span class="font-medium">{{ endItem }}</span>
            of
            <span class="font-medium">{{ totalItems }}</span>
            results
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button
              @click="$emit('update-page', 1)"
              :disabled="currentPage <= 1"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              <span class="sr-only">First Page</span>
              <ChevronDoubleLeftIcon class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
              @click="$emit('update-page', currentPage - 1)"
              :disabled="currentPage <= 1"
              class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              <span class="sr-only">Previous</span>
              <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
              v-for="page in visiblePageNumbers"
              :key="page"
              @click="$emit('update-page', page)"
              :class="[
                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                currentPage === page
                  ? 'z-10 bg-primary-color border-primary-color text-white'
                  : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            <button
              @click="$emit('update-page', currentPage + 1)"
              :disabled="currentPage >= totalPages"
              class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              <span class="sr-only">Next</span>
              <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
              @click="$emit('update-page', totalPages)"
              :disabled="currentPage >= totalPages"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              <span class="sr-only">Last Page</span>
              <ChevronDoubleRightIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  ShoppingCartIcon, ArrowsRightLeftIcon, BanknotesIcon,
  ChevronLeftIcon, ChevronRightIcon, ChevronDoubleLeftIcon, ChevronDoubleRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  transactionType: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    default: () => []
  },
  currentPage: {
    type: Number,
    required: true
  },
  itemsPerPage: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['update-page']);

// Calculate pagination values
const totalItems = computed(() => props.items.length);
const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / props.itemsPerPage)));

// Calculate visible page range (show 5 pages at a time)
const visiblePageNumbers = computed(() => {
  const range = [];
  const maxVisiblePages = 5;
  let startPage = Math.max(1, props.currentPage - Math.floor(maxVisiblePages / 2));
  let endPage = startPage + maxVisiblePages - 1;
  
  if (endPage > totalPages.value) {
    endPage = totalPages.value;
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    range.push(i);
  }
  
  return range;
});

// Get paginated items
const paginatedItems = computed(() => {
  const start = (props.currentPage - 1) * props.itemsPerPage;
  const end = start + props.itemsPerPage;
  return props.items.slice(start, end);
});

// Calculate displayed item range
const startItem = computed(() => {
  if (totalItems.value === 0) return 0;
  return (props.currentPage - 1) * props.itemsPerPage + 1;
});

const endItem = computed(() => {
  return Math.min(props.currentPage * props.itemsPerPage, totalItems.value);
});

// Format amount with proper currency formatting
function formatCurrency(amount) {
  const numericAmount = parseFloat(amount);
  if (isNaN(numericAmount)) return '0.00';
  
  return numericAmount.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// Format date in a readable way
function formatDate(dateString) {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
}

// Format the transaction type for better display
function formatTransactionType(type) {
  if (!type) return 'Unknown';
  
  const typeMap = {
    'order': 'Order',
    'trade': 'Trade',
    'refill': 'Wallet Refill',
    'withdrawal': 'Wallet Withdrawal',
    'verification': 'Verification'
  };
  
  return typeMap[type] || type.charAt(0).toUpperCase() + type.slice(1);
}

// Format the status for better display
function formatStatus(status) {
  if (!status) return 'Unknown';
  
  if (status === 'in_process') return 'In Process';
  
  return status.charAt(0).toUpperCase() + status.slice(1);
}

// Get the status classes for styling
function getStatusClasses(status) {
  const statusLower = String(status).toLowerCase();
  
  const classes = {
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'accepted': 'bg-blue-100 text-blue-800',
    'in_process': 'bg-blue-100 text-blue-800',
    'rejected': 'bg-red-100 text-red-800',
    'failed': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'canceled': 'bg-gray-100 text-gray-800'
  };
  
  return classes[statusLower] || 'bg-gray-100 text-gray-800';
}
</script>
