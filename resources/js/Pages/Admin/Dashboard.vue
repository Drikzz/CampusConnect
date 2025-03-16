<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard Overview</h1>
        
        <!-- Dashboard Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
          <div v-for="(stat, index) in dashboardStats" :key="index" 
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
              <p class="mt-1 text-sm text-gray-500">A list of all recent transactions on the platform.</p>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      ID
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
                  <tr v-for="transaction in stats.recentTransactions" :key="transaction.id" class="hover:bg-gray-50">
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
                      'text-green-600': transaction.type === 'credit',
                      'text-red-600': transaction.type === 'debit'
                    }">
                      <div class="flex items-center">
                        <span v-if="transaction.type === 'credit'" class="mr-1">+</span>
                        <span v-else class="mr-1">-</span>
                        ₱{{ Math.abs(transaction.amount).toFixed(2) }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                      {{ transaction.reference_type }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                        :class="{
                          'bg-green-100 text-green-800': transaction.status === 'completed',
                          'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                          'bg-red-100 text-red-800': transaction.status === 'failed'
                        }">
                        {{ transaction.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                      {{ formatDate(transaction.created_at) }}
                    </td>
                  </tr>
                  
                  <!-- Empty state -->
                  <tr v-if="!stats.recentTransactions || stats.recentTransactions.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                      <div class="flex flex-col items-center">
                        <CurrencyDollarIcon class="w-12 h-12 text-gray-300 mb-3" />
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No transactions yet</h3>
                        <p class="text-sm">Transactions will appear here once they are processed.</p>
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
        
        <!-- Additional section could be added here for other metrics -->
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  ClockIcon, UsersIcon, CubeIcon, ShoppingCartIcon,
  CurrencyDollarIcon, CheckCircleIcon, ExclamationCircleIcon, ShieldExclamationIcon 
} from '@heroicons/vue/24/outline'

// Sample dashboard stats (replace with real data from props)
const dashboardStats = [
  { name: 'Total Users', value: '0', icon: 'UsersIcon' },
  { name: 'Verified Users', value: '0', icon: 'CheckCircleIcon' },
  { name: 'Pending Listings', value: '0', icon: 'ClockIcon' },
  { name: 'Total Transactions', value: '0', icon: 'CurrencyDollarIcon' },
  { name: 'Successful Transactions', value: '0', icon: 'CheckCircleIcon' },
  { name: 'Refund Requests', value: '0', icon: 'ExclamationCircleIcon' },
  { name: 'Reports Received', value: '0', icon: 'ShieldExclamationIcon' }
]

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

defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>
