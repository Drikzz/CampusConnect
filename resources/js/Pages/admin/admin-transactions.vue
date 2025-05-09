<template>
  <AdminLayout>
    <div class="py-4 sm:py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-xl sm:text-2xl font-bold text-foreground mb-6">Transactions Management</h1>
        
        <!-- Transaction Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 sm:mb-8">
          <!-- Wallet Requests Card -->
          <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-4 sm:p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary/10 rounded-md p-3 mr-4">
                  <BanknotesIcon class="w-5 h-5 sm:w-6 sm:h-6 text-primary" />
                </div>
                <div class="flex-1">
                  <div class="text-xs sm:text-sm font-medium text-muted-foreground truncate">
                    Wallet Requests
                  </div>
                  <div class="mt-1 text-lg sm:text-xl font-semibold text-foreground">
                    {{ stats.wallet_requests_count || 0 }}
                  </div>
                  <div class="text-xs text-muted-foreground">
                    <span class="text-green-600 font-medium">{{ stats.pending_wallet_requests || 0 }}</span> pending
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Trades Card -->
          <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-4 sm:p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary/10 rounded-md p-3 mr-4">
                  <ArrowsRightLeftIcon class="w-5 h-5 sm:w-6 sm:h-6 text-primary" />
                </div>
                <div class="flex-1">
                  <div class="text-xs sm:text-sm font-medium text-muted-foreground truncate">
                    Trades
                  </div>
                  <div class="mt-1 text-lg sm:text-xl font-semibold text-foreground">
                    {{ stats.trades_count || 0 }}
                  </div>
                  <div class="text-xs text-muted-foreground">
                    <span class="text-green-600 font-medium">{{ stats.completed_trades_count || 0 }}</span> completed
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Orders Card -->
          <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-4 sm:p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary/10 rounded-md p-3 mr-4">
                  <ShoppingCartIcon class="w-5 h-5 sm:w-6 sm:h-6 text-primary" />
                </div>
                <div class="flex-1">
                  <div class="text-xs sm:text-sm font-medium text-muted-foreground truncate">
                    Orders
                  </div>
                  <div class="mt-1 text-lg sm:text-xl font-semibold text-foreground">
                    {{ stats.orders_count || 0 }}
                  </div>
                  <div class="text-xs text-muted-foreground">
                    <span class="text-green-600 font-medium">{{ stats.completed_orders_count || 0 }}</span> completed
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Transaction Analytics -->
        <div class="bg-card shadow rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
          <div v-if="isLoading" class="flex items-center justify-center h-64">
            <div class="text-center">
              <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent align-[-0.125em]"></div>
              <p class="mt-2 text-muted-foreground">Loading transaction data...</p>
            </div>
          </div>
          <div v-else-if="chartError" class="flex items-center justify-center h-64">
            <div class="text-center">
              <ExclamationTriangleIcon class="h-10 w-10 mx-auto mb-2 text-destructive" />
              <p class="text-muted-foreground">{{ chartError }}</p>
              <Button @click="reloadChartData()" size="sm" class="mt-3">Try Again</Button>
            </div>
          </div>
          <TransactionChart 
            v-else 
            :key="chartKey" 
            :transaction-data="chartData" 
            :initial-date-range="filterDateRange"
            @filter-change="handleFilterChange" 
          />
        </div>

        <!-- Transaction Management -->
        <div class="bg-card shadow rounded-lg overflow-hidden">
          <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-border">
            <div class="flex flex-wrap items-center justify-between">
              <div>
                <h3 class="text-base sm:text-lg font-medium text-foreground">All Transactions</h3>
                <p class="mt-1 text-xs sm:text-sm text-muted-foreground">
                  Filter and manage all system transactions.
                </p>
              </div>
              <div class="mt-2 sm:mt-0 space-x-2">
                <select 
                  v-model="filterType" 
                  @change="handleTypeChange"
                  class="bg-muted text-foreground px-3 py-2 rounded-md text-sm border-border border"
                >
                  <option value="all">All Types</option>
                  <option value="orders">Orders</option>
                  <option value="trades">Trades</option>
                  <option value="wallet">Wallet Requests</option>
                </select>
                <select 
                  v-model="filterStatus" 
                  @change="handleStatusChange"
                  class="bg-muted text-foreground px-3 py-2 rounded-md text-sm border-border border"
                >
                  <option value="all">All Status</option>
                  <option value="pending">Pending</option>
                  <option value="completed">Completed</option>
                  <option value="rejected">Rejected</option>
                  <option value="in_process">In Process</option>
                </select>
              </div>
            </div>
            
            <!-- Transaction Type Tabs -->
            <div class="flex flex-wrap gap-2 mt-4 border-b border-border">
              <button 
                v-for="type in transactionTypes" 
                :key="type.value"
                @click="selectedTransactionType = type.value"
                :class="[
                  'px-2 py-1 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium rounded-t-lg focus:outline-none',
                  selectedTransactionType === type.value
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-muted text-foreground hover:bg-muted/80'
                ]"
                :aria-pressed="selectedTransactionType === type.value"
                role="tab"
              >
                {{ type.label }}
              </button>
            </div>
          </div>
          
          <!-- Transaction Table Component -->
          <TransactionTable 
            :transaction-type="selectedTransactionType"
            :items="filteredItems"
            :current-page="currentPage"
            :items-per-page="itemsPerPage"
            @update-page="currentPage = $event"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TransactionTable from '@/Components/Admin/TransactionTable.vue'
import TransactionChart from '@/Components/Admin/Charts/TransactionChart.vue'
import {
  ShoppingCartIcon, ArrowsRightLeftIcon, BanknotesIcon, ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import axios from 'axios'
import { Button } from '@/Components/ui/button'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      wallet_requests_count: 0,
      pending_wallet_requests: 0,
      trades_count: 0,
      completed_trades_count: 0,
      orders_count: 0,
      completed_orders_count: 0
    })
  },
  transactions: {
    type: Object,
    default: () => ({
      orders: [],
      trades: [],
      wallet: []
    })
  },
  filters: {
    type: Object,
    default: () => ({
      type: 'all',
      status: 'all',
      date_range: '30days'
    })
  }
})

// Chart data
const chartData = ref({
  labels: [],
  datasets: []
})

// State management
const isLoading = ref(true)
const filterType = ref(props.filters.type || 'all')
const filterStatus = ref(props.filters.status || 'all')
const filterDateRange = ref(props.filters.date_range || '30days')
const chartError = ref(null)
const chartKey = ref(Date.now())
const originalChartData = ref(null) // Store original chart data for client-side filtering

// Improved flags for better state management
const isInternalUpdate = ref(false)  // Flag to mark updates initiated by this component
const currentRequestId = ref(0)
const activeRequestId = ref(null)
const isProcessingFilterChange = ref(false)
const lastProcessedFilter = ref(null)
const filterChangeTimeout = ref(null)

// Watch for changes in props.filters to update local state - Fix the loop by checking isInternalUpdate
watch(() => props.filters, (newFilters) => {
  // Skip the update if it was initiated by this component
  if (isInternalUpdate.value) {
    return;
  }
  
  // Only update if values are different to prevent loops
  if (newFilters.date_range && newFilters.date_range !== filterDateRange.value) {
    filterDateRange.value = newFilters.date_range;
  }
  if (newFilters.type && newFilters.type !== filterType.value) {
    filterType.value = newFilters.type;
  }
  if (newFilters.status && newFilters.status !== filterStatus.value) {
    filterStatus.value = newFilters.status;
  }
}, { deep: true });

// Transaction type tabs
const transactionTypes = [
  { label: 'Orders', value: 'orders' },
  { label: 'Trades', value: 'trades' },
  { label: 'Wallet', value: 'wallet' }
]
const selectedTransactionType = ref('orders')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Reset page when changing transaction type
watch(selectedTransactionType, () => {
  currentPage.value = 1
})

// Get filtered items based on selected filters
const filteredItems = computed(() => {
  let items = []
  
  // Select items based on transaction type
  if (selectedTransactionType.value === 'orders') {
    items = props.transactions.orders || []
  } else if (selectedTransactionType.value === 'trades') {
    items = props.transactions.trades || []
  } else if (selectedTransactionType.value === 'wallet') {
    items = props.transactions.wallet || []
  }
  
  // Apply status filtering locally if needed
  if (filterStatus.value !== 'all') {
    items = items.filter(item => {
      // Improved status comparison with null/undefined checks
      let itemStatus = item.status || '';
      let filterStatusStr = filterStatus.value || '';
      
      // Normalize both to lowercase for case-insensitive comparison
      return itemStatus.toLowerCase() === filterStatusStr.toLowerCase();
    });
  }
  
  return items
})

// Handle filter changes
function handleTypeChange() {
  currentPage.value = 1; // Reset to first page on filter change
  
  // Set flag to prevent watch from reprocessing this update
  isInternalUpdate.value = true;
  
  router.get(route('admin.transactions'), {
    type: filterType.value,
    status: filterStatus.value,
    date_range: filterDateRange.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['transactions', 'stats'],
    onSuccess: () => {
      // Reset the flag after the update completes
      setTimeout(() => {
        isInternalUpdate.value = false;
      }, 300);
    },
    onError: (errors) => {
      isInternalUpdate.value = false;
      console.error('Error applying type filter:', errors);
    }
  });
}

function handleStatusChange() {
  currentPage.value = 1; // Reset to first page on filter change
  
  // Set flag to prevent watch from reprocessing this update
  isInternalUpdate.value = true;
  
  router.get(route('admin.transactions'), {
    type: filterType.value,
    status: filterStatus.value,
    date_range: filterDateRange.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['transactions', 'stats'],
    onSuccess: () => {
      // Reset the flag after the update completes
      setTimeout(() => {
        isInternalUpdate.value = false;
      }, 300);
    },
    onError: (errors) => {
      isInternalUpdate.value = false;
      console.error('Error applying status filter:', errors);
    }
  });
}

// Enhanced chart filter change handler with better state synchronization
function handleFilterChange(filters) {
  console.log('Chart filter change received:', filters);
  
  // Create a filter signature for comparison
  const filterSignature = `${filters.dateRange}-${filters.category}`;
  
  // Reset processing state for new filters
  if (lastProcessedFilter.value !== filterSignature) {
    isProcessingFilterChange.value = false;
    lastProcessedFilter.value = null;
  }
  
  // Skip if already processing this filter combination
  if (isProcessingFilterChange.value) {
    console.log('Already processing filter change, skipping:', filterSignature);
    return;
  }
  
  // Clear any existing timeout to prevent race conditions
  if (filterChangeTimeout.value) {
    clearTimeout(filterChangeTimeout.value);
  }
  
  // Reset error state
  chartError.value = null;
  
  // Set guard and remember what we're processing
  isProcessingFilterChange.value = true;
  lastProcessedFilter.value = filterSignature;
  
  // Update local filter state
  filterDateRange.value = filters.dateRange;
  
  // Try to filter client-side first (faster and reduces server load)
  if (originalChartData.value && originalChartData.value.labels && originalChartData.value.datasets) {
    try {
      // Apply client-side filtering
      const filteredData = filterTransactionData(originalChartData.value, filters);
      chartData.value = filteredData;
      chartKey.value = Date.now(); // Force chart re-render
      
      // Update URL parameters to keep in sync with chart filters (don't need server round-trip)
      router.get(route('admin.transactions'), {
        type: filterType.value,
        status: filterStatus.value,
        date_range: filters.dateRange
      }, {
        preserveState: true,
        preserveScroll: true,
        only: [], // Don't reload any data
        replace: true, // Replace current history entry instead of adding a new one
      });
      
      // Reset processing state
      setTimeout(() => {
        isProcessingFilterChange.value = false;
      }, 300);
      
      return; // Exit early - we've handled this client-side
    } catch (error) {
      console.error('Client-side filtering failed, falling back to server:', error);
    }
  }
  
  // If client-side filtering didn't work, continue with server-side filtering
  isLoading.value = true;
  
  // Synchronize with route parameters to keep URL in sync with chart filters
  router.get(route('admin.transactions'), {
    type: filterType.value,
    status: filterStatus.value,
    date_range: filters.dateRange
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['transactions', 'stats'],
    replace: true, // Replace current history entry instead of adding a new one
    onSuccess: () => {
      console.log('Chart filter URL parameters updated successfully');
    },
    onError: (errors) => {
      console.error('Error updating chart filter URL parameters:', errors);
    }
  });
  
  // Generate a unique request ID for this filter change
  const requestId = ++currentRequestId.value;
  activeRequestId.value = requestId;
  
  // Debounce the API call
  filterChangeTimeout.value = setTimeout(() => {
    // Fetch filtered chart data
    axios.get('/api/admin/transactions/chart', {
      params: {
        date_range: filters.dateRange,
        category: filters.category
      }
    })
    .then(response => {
      // Only process if this is still the active request
      if (requestId !== activeRequestId.value) {
        console.log(`Skipping stale response for request ${requestId}`);
        return;
      }
      
      // Enhanced data validation
      if (validateChartData(response.data)) {
        try {
          // Create a deep copy of the data to ensure proper reactivity
          const deepCopiedData = JSON.parse(JSON.stringify(response.data));
          
          // Store original data for client-side filtering
          originalChartData.value = deepCopiedData;
          
          // Update chart data and force re-render with new key
          chartData.value = deepCopiedData;
          chartKey.value = Date.now();
          console.log('Chart data updated successfully');
        } catch (error) {
          console.error('Error processing chart data:', error);
          chartError.value = 'Failed to process chart data. Please try again.';
        }
      } else {
        console.error('Invalid chart data structure:', response.data);
        chartError.value = 'Received invalid chart data from server.';
      }
    })
    .catch(error => {
      console.error('Failed to fetch chart data:', error);
      chartError.value = 'Failed to load chart data. Please try again.';
    })
    .finally(() => {
      // Only update loading state if this is the active request
      if (requestId === activeRequestId.value) {
        isLoading.value = false;
        
        // Reset the processing flag after a longer delay
        setTimeout(() => {
          if (requestId === activeRequestId.value) {
            isProcessingFilterChange.value = false;
          }
        }, 300);
      }
    });
  }, 200); // Debounce for 200ms
}

// Enhanced data validation function
function validateChartData(data) {
  // Check if data exists and has proper structure
  if (!data || typeof data !== 'object') return false;
  
  // Check for labels array
  if (!Array.isArray(data.labels)) return false;
  
  // Check for datasets array
  if (!Array.isArray(data.datasets) || data.datasets.length === 0) return false;
  
  // Validate each dataset has required properties
  return data.datasets.every(dataset => {
    return (
      typeof dataset === 'object' &&
      dataset !== null &&
      typeof dataset.label === 'string' &&
      Array.isArray(dataset.data) &&
      dataset.data.length === data.labels.length
    );
  });
}

// Add these functions from Dashboard.vue for client-side filtering
function filterTransactionData(data, filters) {
  // Clone the data to avoid mutating the original
  const filteredData = JSON.parse(JSON.stringify(data));
  
  if (!filteredData || !filteredData.labels || !filteredData.datasets) {
    console.warn('Cannot filter invalid transaction data structure');
    return filteredData;
  }
  
  // Apply date range filter
  const { startIndex, endIndex } = getDateRangeIndices(filteredData.labels, filters.dateRange);
  
  filteredData.labels = filteredData.labels.slice(startIndex, endIndex);
  
  // Filter datasets based on category
  if (filters.category && filters.category !== 'all') {
    filteredData.datasets = filteredData.datasets.filter(dataset => {
      // Keep datasets that match the selected category
      const label = dataset.label.toLowerCase();
      if (filters.category === 'orders' && label.includes('order')) return true;
      if (filters.category === 'trades' && label.includes('trade')) return true;
      if (filters.category === 'wallet' && (label.includes('wallet') || label.includes('transaction'))) return true;
      return false;
    }).map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }));
  } else {
    // Just apply date range filter to all datasets
    filteredData.datasets = filteredData.datasets.map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }));
  }
  
  return filteredData;
}

// Helper function to get start and end indices based on date range
function getDateRangeIndices(labels, dateRange) {
  if (!labels || !Array.isArray(labels)) {
    return { startIndex: 0, endIndex: 0 };
  }
  
  let startIndex = 0;
  const endIndex = labels.length;
  
  // Calculate start index based on date range
  switch (dateRange) {
    case '7days':
      startIndex = Math.max(0, labels.length - 7);
      break;
    case '30days':
      startIndex = Math.max(0, labels.length - 30);
      break;
    case '90days':
      startIndex = Math.max(0, labels.length - 90);
      break;
    case 'year':
      startIndex = Math.max(0, labels.length - 365);
      break;
    case 'all':
    default:
      startIndex = 0;
  }
  
  return { startIndex, endIndex };
}

// Function to reload chart data after error
function reloadChartData() {
  chartError.value = null;
  isLoading.value = true;
  
  axios.get('/api/admin/transactions/chart', {
    params: {
      date_range: filterDateRange.value,
      category: 'all'
    }
  })
  .then(response => {
    if (validateChartData(response.data)) {
      const deepCopiedData = JSON.parse(JSON.stringify(response.data));
      originalChartData.value = deepCopiedData; // Store original data
      chartData.value = deepCopiedData;
      chartKey.value = Date.now();
    } else {
      chartError.value = 'Received invalid chart data from server.';
    }
  })
  .catch(error => {
    console.error('Failed to reload chart data:', error);
    chartError.value = 'Failed to reload chart data. Please try again.';
  })
  .finally(() => {
    isLoading.value = false;
  });
}

// Initialize chart data on component mount
onMounted(() => {
  // Make sure local filters match URL parameters
  if (props.filters) {
    filterType.value = props.filters.type || 'all';
    filterStatus.value = props.filters.status || 'all';
    filterDateRange.value = props.filters.date_range || '30days';
    console.log('Initialized filters from URL:', { 
      type: filterType.value,
      status: filterStatus.value,
      dateRange: filterDateRange.value
    });
  }

  // Fetch initial chart data with current filters from URL
  axios.get('/api/admin/transactions/chart', {
    params: {
      date_range: filterDateRange.value,
      category: 'all'
    }
  })
  .then(response => {
    if (validateChartData(response.data)) {
      // Deep copy to ensure reactivity
      const deepCopiedData = JSON.parse(JSON.stringify(response.data));
      originalChartData.value = deepCopiedData; // Store original data for client-side filtering
      chartData.value = deepCopiedData;
    } else {
      console.error('Invalid initial chart data structure:', response.data);
      chartError.value = 'Received invalid chart data from server.';
    }
  })
  .catch(error => {
    console.error('Failed to fetch chart data:', error);
    chartError.value = 'Failed to load chart data. Please try again.';
  })
  .finally(() => {
    isLoading.value = false;
  });
});
</script>
