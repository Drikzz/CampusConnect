<template>
  <div class="space-y-4">
    <!-- Search and Filter Controls -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
      <div class="flex items-center gap-2">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search sellers..."
          class="px-3 py-2 border rounded-md text-sm"
        />
        <button 
          @click="searchQuery = ''" 
          class="p-2 bg-gray-100 rounded hover:bg-gray-200" 
          v-if="searchQuery"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <button 
        @click="fetchWallets" 
        class="flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
        :disabled="loading"
      >
        <svg 
          class="w-4 h-4" 
          :class="{ 'animate-spin': loading }" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh
      </button>
    </div>
    
    <!-- Error Alert -->
    <div v-if="error" class="p-4 bg-red-50 text-red-700 rounded-md">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p>{{ error }}</p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button @click="error = null" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:bg-red-100">
              <span class="sr-only">Dismiss</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Wallet Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th 
              v-for="column in columns" 
              :key="column.key" 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy(column.key)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <span v-if="sortColumn === column.key">
                  <svg v-if="sortDirection === 'asc'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
              </div>
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading" class="animate-pulse">
            <td :colspan="columns.length + 1" class="p-4">
              <div class="flex justify-center">
                <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </td>
          </tr>
          <tr v-else-if="filteredWallets.length === 0">
            <td :colspan="columns.length + 1" class="px-6 py-10 text-center text-gray-500">
              No active seller wallets found
            </td>
          </tr>
          <tr v-for="wallet in filteredWallets" :key="wallet.id" class="hover:bg-gray-50">
            <td v-for="column in columns" :key="column.key" class="px-6 py-4 whitespace-nowrap">
              <div v-if="column.key === 'name'" class="flex items-center">
                <div class="text-sm font-medium text-gray-900">{{ wallet.name }}</div>
              </div>
              <div v-else-if="column.key === 'balance'" class="text-sm font-medium text-gray-900">
                ₱{{ formatNumber(wallet.balance) }}
              </div>
              <div v-else-if="column.key === 'status'">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  wallet.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ wallet.status }}
                </span>
              </div>
              <div v-else class="text-sm text-gray-500">
                {{ wallet[column.key] }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button 
                class="text-indigo-600 hover:text-indigo-900 mr-2"
                @click="adjustBalance(wallet)"
              >
                Adjust
              </button>
              <button 
                class="text-gray-600 hover:text-gray-900"
                @click="viewDetails(wallet)"
              >
                Details
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  wallets: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['refresh', 'update-wallet']);

// Table state
const searchQuery = ref('');
const sortColumn = ref('name');
const sortDirection = ref('asc');

// Table columns configuration
const columns = [
  { key: 'name', label: 'Seller Name' },
  { key: 'seller_code', label: 'Seller Code' },
  { key: 'balance', label: 'Balance' },
  { key: 'status', label: 'Status' },
  { key: 'last_refill', label: 'Last Refill' }
];

// Computed properties for table
const filteredWallets = computed(() => {
  let result = [...props.wallets];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(wallet => 
      wallet.name.toLowerCase().includes(query) ||
      wallet.seller_code.toLowerCase().includes(query)
    );
  }
  
  // Apply sorting
  result.sort((a, b) => {
    let valA = a[sortColumn.value];
    let valB = b[sortColumn.value];
    
    // Special handling for numeric values
    if (sortColumn.value === 'balance') {
      valA = parseFloat(valA);
      valB = parseFloat(valB);
    }
    
    if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
    if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  
  return result;
});

// Table functions
const sortBy = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const fetchWallets = () => {
  emit('refresh');
};

// Format number as currency
const formatNumber = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

// Actions
const adjustBalance = (wallet) => {
  const amount = prompt(`Enter adjustment amount for ${wallet.name} (use negative for deduction):`);
  if (amount === null) return; // User cancelled

  const numericAmount = parseFloat(amount);
  if (isNaN(numericAmount)) {
    alert('Please enter a valid number');
    return;
  }

  emit('update-wallet', {
    walletId: wallet.id,
    amount: numericAmount, 
    reason: prompt('Reason for adjustment:') || 'Manual adjustment by admin'
  });
};

const viewDetails = (wallet) => {
  // Can be replaced with a modal or navigation to a detailed view
  alert(`Viewing details for ${wallet.name}\nBalance: ₱${formatNumber(wallet.balance)}\nSeller Code: ${wallet.seller_code}`);
};

onMounted(() => {
  if (!props.wallets.length && !props.loading) {
    fetchWallets();
  }
});
</script>
