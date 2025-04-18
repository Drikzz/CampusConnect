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

        <!-- Recent Transactions -->
        <div class="mt-8">
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
                >
                  {{ type.label }}
                </button>
              </div>
            </div>
            
            <!-- Orders Table -->
            <div v-if="selectedTransactionType === 'orders'" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
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
                  <tr v-for="order in stats.transactions.orders" :key="order.id" class="hover:bg-gray-50">
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
                  <tr v-if="!stats.transactions.orders || stats.transactions.orders.length === 0">
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
            </div>
            
            <!-- Trades Table -->
            <div v-if="selectedTransactionType === 'trades'" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
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
                  <tr v-for="trade in stats.transactions.trades" :key="trade.id" class="hover:bg-gray-50">
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
                  <tr v-if="!stats.transactions.trades || stats.transactions.trades.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                      <div class="flex flex-col items-center">
                        <ArrowsRightLeftIcon class="w-12 h-12 text-gray-300 mb-3" />
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No trades yet</h3>
                        <p class="text-sm">Trades will appear here once they are initiated.</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Wallet Transactions Table -->
            <div v-if="selectedTransactionType === 'wallet'" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
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
                  <tr v-for="transaction in stats.transactions.wallet" :key="transaction.id" class="hover:bg-gray-50">
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
                  <tr v-if="!stats.transactions.wallet || stats.transactions.wallet.length === 0">
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
            </div>
            
            <!-- Pagination could be added here in the future -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-right">
              <Link 
                :href="route('admin.orders')" 
                class="text-sm font-medium text-primary-color hover:text-primary-color/80">
                View all transactions →
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  ClockIcon, UsersIcon, CubeIcon, ShoppingCartIcon,
  CurrencyDollarIcon, CheckCircleIcon, ExclamationCircleIcon, ShieldExclamationIcon,
  ShoppingBagIcon, ArrowsRightLeftIcon, BanknotesIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  }
})

// Transaction type filter state
const transactionTypes = [
  { label: 'Orders', value: 'orders' },
  { label: 'Trades', value: 'trades' },
  { label: 'Wallet', value: 'wallet' }
]
const selectedTransactionType = ref('orders')

// Get the appropriate icon component for each stat
function getIconForStat(statName) {
  switch(statName) {
    case 'Total Users':
    case 'Verified Users':
      return UsersIcon
    case 'Pending Listings':
      return ClockIcon
    case 'Total Transactions':
    case 'Successful Transactions':
      return CurrencyDollarIcon
    case 'Refund Requests':
      return ExclamationCircleIcon
    case 'Reports Received':
      return ShieldExclamationIcon
    default:
      return CubeIcon
  }
}

// Format amount with proper currency formatting to ensure numbers display correctly
function formatCurrency(amount) {
  // Handle possible non-numeric values
  const numericAmount = parseFloat(amount);
  if (isNaN(numericAmount)) return '0.00';
  
  return numericAmount.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// Keep for backward compatibility but use formatCurrency internally
function formatAmount(amount) {
  return formatCurrency(amount);
}

// Format date in a more readable way
function formatDate(dateString) {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
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
  
  // Handle special cases for in_process status
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
