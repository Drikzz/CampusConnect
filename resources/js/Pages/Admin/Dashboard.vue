<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard Overview</h1>
        
        <!-- Dashboard Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
          <div v-for="(stat, index) in stats.dashboardStats" :key="index" 
               class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary-color/10 rounded-md p-3 mr-4">
                  <component 
                    :is="getIconForStat(stat.name)" 
                    class="w-6 h-6 text-primary-color"
                  />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-500 truncate">
                    {{ stat.name }}
                  </div>
                  <div class="mt-1 text-xl font-semibold text-gray-900">
                    {{ stat.value }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section with loading states -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- User Growth Chart -->
          <div class="bg-white shadow rounded-lg p-6">
            <div v-if="isLoading" class="flex items-center justify-center h-64">
              <div class="text-center">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-color border-r-transparent align-[-0.125em]"></div>
                <p class="mt-2 text-gray-500">Loading user data...</p>
              </div>
            </div>
            <UserChart v-else :user-data="userData" @filter-change="handleUserFilterChange" />
          </div>
          
          <!-- Products Chart -->
          <div class="bg-white shadow rounded-lg p-6">
            <div v-if="isLoading" class="flex items-center justify-center h-64">
              <div class="text-center">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-color border-r-transparent align-[-0.125em]"></div>
                <p class="mt-2 text-gray-500">Loading product data...</p>
              </div>
            </div>
            <ProductChart v-else :product-data="productData" @filter-change="handleProductFilterChange" />
          </div>
        </div>
        
        <!-- Full Width Transaction Chart -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
          <div v-if="isLoading" class="flex items-center justify-center h-64">
            <div class="text-center">
              <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-color border-r-transparent align-[-0.125em]"></div>
              <p class="mt-2 text-gray-500">Loading transaction data...</p>
            </div>
          </div>
          <TransactionChart v-else :transaction-data="transactionData" @filter-change="handleTransactionFilterChange" />
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
            <p class="mt-1 text-sm text-gray-500">Filter transactions by type to view details.</p>
            
            <!-- Transaction Type Tabs -->
            <div class="flex space-x-1 mt-4 border-b">
              <button 
                v-for="type in transactionTypes" 
                :key="type.value"
                @click="selectedTransactionType = type.value"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-t-lg focus:outline-none',
                  selectedTransactionType === type.value
                    ? 'bg-primary-color text-white'
                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
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
            :items="currentItems"
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
import { ref, computed, watch, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TransactionTable from '@/Components/Admin/TransactionTable.vue'
import UserChart from '@/Components/Admin/Charts/UserChart.vue'
import ProductChart from '@/Components/Admin/Charts/ProductChart.vue'
import TransactionChart from '@/Components/Admin/Charts/TransactionChart.vue'
import { 
  ClockIcon, UsersIcon, CubeIcon, 
  CurrencyDollarIcon, CheckCircleIcon, ExclamationCircleIcon, ShieldExclamationIcon,
  ShoppingBagIcon, ArrowsRightLeftIcon, BanknotesIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  }
})

// Data state variables
const userData = ref({})
const productData = ref({})
const transactionData = ref({})
const isLoading = ref(true)
const hasProcessedData = ref(false)

// Filter states
const userFilters = ref({ dateRange: '30days', status: 'all' })
const productFilters = ref({ dateRange: '30days', category: 'all' })
const transactionFilters = ref({ dateRange: '30days', category: 'all' })

// Helper function to generate demo dates
function generateDemoDateLabels(count, unit = 'days') {
  const labels = []
  const now = new Date()
  
  for (let i = count - 1; i >= 0; i--) {
    const date = new Date()
    if (unit === 'days') {
      date.setDate(now.getDate() - i)
      labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
    } else if (unit === 'weeks') {
      date.setDate(now.getDate() - (i * 7))
      labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
    }
  }
  
  return labels
}

// Helper function to generate demo data
function generateRandomData(count, min, max, cumulative = false) {
  const data = []
  let value = Math.floor(Math.random() * (max - min)) + min
  
  for (let i = 0; i < count; i++) {
    if (cumulative) {
      const change = Math.floor(Math.random() * 10) - 3 // -3 to +6 change
      value = Math.max(min, Math.min(max, value + change))
      data.push(value)
    } else {
      data.push(Math.floor(Math.random() * (max - min)) + min)
    }
  }
  
  return data
}

// Improved data processing function with better error handling
function processData() {
  console.log('Processing dashboard data from:', props.stats)
  isLoading.value = true
  
  // Use a small timeout to allow UI to render loading state
  setTimeout(() => {
    try {
      // Process user data
      const userStatsData = props.stats.userStats || {}
      console.log('User data available:', Object.keys(userStatsData).length > 0)
      userData.value = processUserData(userStatsData)
      
      // Process product data
      const productStatsData = props.stats.productStats || {}
      console.log('Product data available:', Object.keys(productStatsData).length > 0)
      productData.value = processProductData(productStatsData)
      
      // Process transaction data
      const transactionStatsData = props.stats.transactionStats || {}
      console.log('Transaction data available:', Object.keys(transactionStatsData).length > 0)
      transactionData.value = processTransactionData(transactionStatsData)
      
      hasProcessedData.value = true
    } catch (e) {
      console.error('Error processing chart data:', e)
    } finally {
      isLoading.value = false
    } 
  }, 300) // Small delay for UI feedback
}

// Process user data with better fallback handling and improved flexibility
function processUserData(data) {
  // Check if data exists and has proper Chart.js structure
  if (data && data.labels && data.datasets) {
    // Check if we need to add unverified users dataset
    let hasUnverifiedDataset = data.datasets.some(
      ds => ds.label === 'Unverified Users' || ds.label.toLowerCase().includes('unverified')
    );
    
    // Clone the data to avoid mutation
    let processedData = JSON.parse(JSON.stringify(data));
    
    // If there's no unverified dataset but we have total and verified users
    if (!hasUnverifiedDataset) {
      const totalUsersDataset = processedData.datasets.find(ds => ds.label === 'Total Users');
      const verifiedUsersDataset = processedData.datasets.find(ds => ds.label === 'Verified Users');
      
      if (totalUsersDataset && verifiedUsersDataset && totalUsersDataset.data.length && verifiedUsersDataset.data.length) {
        // Calculate unverified = total - verified
        const unverifiedData = totalUsersDataset.data.map((total, i) => 
          total - (verifiedUsersDataset.data[i] || 0)
        );
        
        // Add the unverified users dataset
        processedData.datasets.push({
          label: 'Unverified Users',
          data: unverifiedData,
          borderColor: 'hsl(var(--chart-3))',
          backgroundColor: 'hsla(var(--chart-3), 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.2
        });
      }
    }
    
    // Update the datasets with shadcn styling
    processedData.datasets = processedData.datasets.map((dataset, index) => {
      const colorIndex = index + 1; // 1-based index for theme variables
      return {
        ...dataset,
        borderColor: dataset.borderColor || `hsl(var(--chart-${colorIndex}))`,
        backgroundColor: dataset.backgroundColor || `hsla(var(--chart-${colorIndex}), 0.1)`,
        borderWidth: 2,
        fill: true,
        tension: 0.2
      };
    });
    
    console.log('Using processed server-provided user chart data');
    return processedData;
  }
  
  // Generate demo data if server data is not available
  console.warn('Server data not in expected format, using demo data for user chart');
  
  // Generate demo data
  const dates = generateDemoDateLabels(30);
  const totalUsers = generateRandomData(30, 100, 200, true);
  const verifiedUsers = totalUsers.map(val => Math.round(val * 0.7));
  const unverifiedUsers = totalUsers.map((total, i) => total - verifiedUsers[i]);
  
  return {
    labels: dates,
    datasets: [
      {
        label: 'Total Users',
        data: totalUsers,
        borderColor: 'hsl(var(--chart-1))',
        backgroundColor: 'hsla(var(--chart-1), 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.2
      },
      {
        label: 'Verified Users',
        data: verifiedUsers,
        borderColor: 'hsl(var(--chart-2))',
        backgroundColor: 'hsla(var(--chart-2), 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.2
      },
      {
        label: 'Unverified Users',
        data: unverifiedUsers,
        borderColor: 'hsl(var(--chart-3))',
        backgroundColor: 'hsla(var(--chart-3), 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.2
      }
    ]
  };
}

// Process product data with better fallback handling and improved flexibility
function processProductData(data) {
  // Check if data exists and has proper Chart.js structure
  if (data && data.labels && data.datasets) {
    // Apply shadcn styling to the datasets
    let processedData = JSON.parse(JSON.stringify(data));
    
    processedData.datasets = processedData.datasets.map((dataset, index) => {
      const colorIndex = index + 1; // 1-based index for theme variables
      return {
        ...dataset,
        backgroundColor: `hsla(var(--chart-${colorIndex}), 0.2)`,
        borderColor: `hsl(var(--chart-${colorIndex}))`,
        borderWidth: 2,
        borderRadius: 4
      };
    });
    
    console.log('Using server-provided product chart data with shadcn styling');
    return processedData;
  }
  
  // Log warning for fallback
  console.warn('Server data not in expected format, using demo data for product chart');
  
  // Generate demo data
  const dates = generateDemoDateLabels(12, 'weeks');
  const forSale = generateRandomData(12, 30, 80);
  const forTrade = generateRandomData(12, 20, 50);
  
  return {
    labels: dates,
    datasets: [
      {
        label: 'For Sale',
        data: forSale,
        backgroundColor: 'hsla(var(--chart-1), 0.2)',
        borderColor: 'hsl(var(--chart-1))',
        borderWidth: 2,
        borderRadius: 4
      },
      {
        label: 'For Trade',
        data: forTrade,
        backgroundColor: 'hsla(var(--chart-2), 0.2)',
        borderColor: 'hsl(var(--chart-2))',
        borderWidth: 2,
        borderRadius: 4
      }
    ]
  };
}

// Process transaction data with better fallback handling and improved flexibility
function processTransactionData(data) {
  // Check if data exists and has proper Chart.js structure
  if (data && data.labels && data.datasets) {
    // Apply shadcn styling to the datasets
    let processedData = JSON.parse(JSON.stringify(data));
    
    processedData.datasets = processedData.datasets.map((dataset, index) => {
      const colorIndex = index + 1; // 1-based index for theme variables
      return {
        ...dataset,
        borderColor: `hsl(var(--chart-${colorIndex}))`,
        backgroundColor: `hsla(var(--chart-${colorIndex}), 0.1)`,
        borderWidth: 2,
        fill: true,
        tension: 0.2
      };
    });
    
    console.log('Using server-provided transaction chart data with shadcn styling');
    return processedData;
  }
  
  // Log warning for fallback
  console.warn('Server data not in expected format, using demo data for transaction chart');
  
  // Generate demo data
  const dates = generateDemoDateLabels(30);
  const orders = generateRandomData(30, 5, 25);
  const trades = generateRandomData(30, 3, 15);
  const wallet = generateRandomData(30, 10, 40);
  
  return {
    labels: dates,
    datasets: [
      {
        label: 'Orders',
        data: orders,
        borderColor: 'hsl(var(--chart-1))',
        backgroundColor: 'hsla(var(--chart-1), 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.2
      },
      {
        label: 'Trades',
        data: trades,
        borderColor: 'hsl(var(--chart-2))',
        backgroundColor: 'hsla(var(--chart-2), 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.2
      },
      {
        label: 'Wallet Transactions',
        data: wallet,
        borderColor: 'hsl(var(--chart-3))',
        backgroundColor: 'hsla(var(--chart-3), 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.2
      }
    ]
  };
}

// Transaction type filter state
const transactionTypes = [
  { label: 'Orders', value: 'orders' },
  { label: 'Trades', value: 'trades' },
  { label: 'Wallet', value: 'wallet' }
]
const selectedTransactionType = ref('orders')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(5)

// Reset page when changing transaction type
watch(selectedTransactionType, () => {
  currentPage.value = 1
})

// Get current items based on selected transaction type
const currentItems = computed(() => {
  if (selectedTransactionType.value === 'orders') {
    return props.stats.transactions?.orders || []
  } else if (selectedTransactionType.value === 'trades') {
    return props.stats.transactions?.trades || []
  } else if (selectedTransactionType.value === 'wallet') {
    return props.stats.transactions?.wallet || []
  }
  return []
})

// Get the appropriate icon component for each stat
function getIconForStat(statName) {
  switch(statName) {
    case 'Total Users':
    case 'Verified Users':
      return UsersIcon
    case 'Pending Listings':
      return ClockIcon
    case 'For Sale Products':
      return ShoppingBagIcon
    case 'For Trade Products':
      return ArrowsRightLeftIcon
    case 'Total Transactions':
    case 'Successful Transactions':
      return CurrencyDollarIcon
    case 'Refund Requests':
      return ExclamationCircleIcon
    case 'Wallet Refill Requests':
      return BanknotesIcon
    case 'Reports Received':
      return ShieldExclamationIcon
    default:
      return CubeIcon
  }
}

// Initialize chart data when component mounts
onMounted(() => {
  console.log('Admin Dashboard mounted, processing data...')
  processData()
})

// Watch for changes in the stats prop and reprocess data if needed
watch(() => props.stats, (newStats, oldStats) => {
  if (JSON.stringify(newStats) !== JSON.stringify(oldStats)) {
    console.log('Stats data changed, reprocessing...')
    processData()
  }
}, { deep: true })

// Filter handler methods
function handleUserFilterChange(filters) {
  console.log('User chart filters changed:', filters)
  userFilters.value = filters
  
  // Apply client-side filtering for user data if we have the full dataset
  if (props.stats.userStats && hasProcessedData.value) {
    userData.value = filterUserData(props.stats.userStats, filters)
  } else {
    // Alternatively, fetch new data from the server with these filters
    // fetchFilteredUserData(filters)
  }
}

function handleProductFilterChange(filters) {
  console.log('Product chart filters changed:', filters)
  productFilters.value = filters
  
  // Apply client-side filtering for product data
  if (props.stats.productStats && hasProcessedData.value) {
    productData.value = filterProductData(props.stats.productStats, filters)
  } else {
    // Alternatively, fetch new data from the server with these filters
    // fetchFilteredProductData(filters)
  }
}

function handleTransactionFilterChange(filters) {
  console.log('Transaction chart filters changed:', filters)
  transactionFilters.value = filters
  
  // Apply client-side filtering for transaction data
  if (props.stats.transactionStats && hasProcessedData.value) {
    transactionData.value = filterTransactionData(props.stats.transactionStats, filters)
  } else {
    // Alternatively, fetch new data from the server with these filters
    // fetchFilteredTransactionData(filters)
  }
}

// Client-side filtering functions
function filterUserData(data, filters) {
  // Clone the data to avoid mutating the original
  const filteredData = JSON.parse(JSON.stringify(data))
  
  if (!filteredData || !filteredData.labels || !filteredData.datasets) {
    console.warn('Cannot filter invalid user data structure')
    return filteredData
  }
  
  // Apply date range filter
  const { startIndex, endIndex } = getDateRangeIndices(filteredData.labels, filters.dateRange)
  
  filteredData.labels = filteredData.labels.slice(startIndex, endIndex)
  
  // Improved filtering for verified/unverified users
  if (filters.status === 'verified' || filters.status === 'unverified') {
    // Keep only datasets that match the filter
    filteredData.datasets = filteredData.datasets.filter(dataset => {
      const isVerifiedDataset = dataset.label === 'Verified Users' || 
                               dataset.label.toLowerCase().includes('verified');
      
      if (filters.status === 'verified') {
        return isVerifiedDataset;
      } else if (filters.status === 'unverified') {
        return dataset.label === 'Unverified Users' || 
              (dataset.label.toLowerCase().includes('unverified'));
      }
      return true;
    });
    
    // If no matching dataset was found for unverified users, 
    // create one by calculating unverified = total - verified
    if (filters.status === 'unverified' && filteredData.datasets.length === 0) {
      const totalUsers = data.datasets.find(ds => ds.label === 'Total Users');
      const verifiedUsers = data.datasets.find(ds => ds.label === 'Verified Users');
      
      if (totalUsers && verifiedUsers && totalUsers.data.length && verifiedUsers.data.length) {
        const unverifiedData = totalUsers.data.map((total, i) => 
          total - (verifiedUsers.data[i] || 0)
        ).slice(startIndex, endIndex);
        
        filteredData.datasets.push({
          label: 'Unverified Users',
          data: unverifiedData,
          borderColor: 'hsl(var(--chart-3))',
          backgroundColor: 'hsla(var(--chart-3), 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.2
        });
      }
    }
  } else {
    // For 'all' filter, just apply date range filter to all datasets
    filteredData.datasets = filteredData.datasets.map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }));
  }
  
  return filteredData
}

function filterProductData(data, filters) {
  // Clone the data to avoid mutating the original
  const filteredData = JSON.parse(JSON.stringify(data))
  
  if (!filteredData || !filteredData.labels || !filteredData.datasets) {
    console.warn('Cannot filter invalid product data structure')
    return filteredData
  }
  
  // Apply date range filter
  const { startIndex, endIndex } = getDateRangeIndices(filteredData.labels, filters.dateRange)
  
  filteredData.labels = filteredData.labels.slice(startIndex, endIndex)
  
  // Filter datasets based on product type (sale/trade) instead of status
  if (filters.category && filters.category !== 'all') {
    filteredData.datasets = filteredData.datasets.filter(dataset => {
      const label = dataset.label.toLowerCase();
      if (filters.category === 'sale') {
        return label.includes('sale') || label.includes('buyable');
      } else if (filters.category === 'trade') {
        return label.includes('trade') || label.includes('tradable');
      }
      return true;
    }).map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }));
  } else {
    // For 'all' filter, apply date range filter to all datasets
    filteredData.datasets = filteredData.datasets.map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }));
  }
  
  return filteredData;
}

function filterTransactionData(data, filters) {
  // Clone the data to avoid mutating the original
  const filteredData = JSON.parse(JSON.stringify(data))
  
  if (!filteredData || !filteredData.labels || !filteredData.datasets) {
    console.warn('Cannot filter invalid transaction data structure')
    return filteredData
  }
  
  // Apply date range filter
  const { startIndex, endIndex } = getDateRangeIndices(filteredData.labels, filters.dateRange)
  
  filteredData.labels = filteredData.labels.slice(startIndex, endIndex)
  
  // Filter datasets based on category
  if (filters.category !== 'all') {
    filteredData.datasets = filteredData.datasets.filter(dataset => {
      // Keep datasets that match the selected category
      if (filters.category === 'orders' && dataset.label === 'Orders') return true
      if (filters.category === 'trades' && dataset.label === 'Trades') return true
      if (filters.category === 'wallet' && dataset.label === 'Wallet Transactions') return true
      return false
    }).map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }))
  } else {
    // Just apply date range filter to all datasets
    filteredData.datasets = filteredData.datasets.map(dataset => ({
      ...dataset,
      data: dataset.data.slice(startIndex, endIndex)
    }))
  }
  
  return filteredData
}

// Helper function to get start and end indices based on date range
function getDateRangeIndices(labels, dateRange) {
  if (!labels || !Array.isArray(labels)) {
    return { startIndex: 0, endIndex: 0 }
  }
  
  let startIndex = 0
  const endIndex = labels.length
  
  // Calculate start index based on date range
  switch (dateRange) {
    case '7days':
      startIndex = Math.max(0, labels.length - 7)
      break
    case '30days':
      startIndex = Math.max(0, labels.length - 30)
      break
    case '90days':
      startIndex = Math.max(0, labels.length - 90)
      break
    case 'year':
      startIndex = Math.max(0, labels.length - 365)
      break
    case 'all':
    default:
      startIndex = 0
  }
  
  return { startIndex, endIndex }
}
</script>
