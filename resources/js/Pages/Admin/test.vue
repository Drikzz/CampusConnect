<template>
  <AdminLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-bold">Wallet & Fees</h2>
      
      <!-- Platform Fees Settings -->
      <div class="bg-white border rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Platform Fees Settings</h3>
        <form @submit.prevent="updateDeductionRate" class="space-y-4">
          <div class="flex items-center gap-4">
            <div class="w-64">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Wallet Deduction Rate
              </label>
              <div class="flex items-center gap-2">
                <input 
                  type="number" 
                  v-model="walletDeductionRate" 
                  min="0" 
                  max="100" 
                  step="0.1"
                  class="w-full p-2 border rounded focus:ring-2 focus:ring-indigo-500"
                />
                <span>%</span>
              </div>
            </div>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
      
      <!-- Wallet Stats Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Platform Revenue</p>
          <p class="text-2xl font-bold text-indigo-600">
            ₱{{ formatCurrency(totalRevenue) }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Active Seller Wallets</p>
          <p class="text-2xl font-bold text-green-600">
            {{ activeWallets }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Profit From Revenue</p>
          <p class="text-2xl font-bold text-yellow-600">
            ₱{{ formatCurrency(totalProfit) }}
          </p>
        </div>
      </div>
      
      <!-- Seller Wallets Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(seller, index) in sellers" :key="index" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="text-sm font-medium text-gray-900">
                    {{ seller.name }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ₱{{ seller.balance }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  seller.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ seller.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ seller.lastActivity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button 
                  class="px-3 py-1 bg-blue-100 text-blue-700 rounded mr-2"
                  @click="adjustBalance(seller.id)"
                >
                  Adjust Balance
                </button>
              </td>
            </tr>
            
            <!-- Empty state -->
            <tr v-if="!sellers.length">
              <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                No seller wallets found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import axios from 'axios'

// Data properties
const walletDeductionRate = ref(5)
const totalRevenue = ref(0)
const totalProfit = ref(0)
const activeWallets = ref(0)
const isSubmitting = ref(false)
const sellers = ref([
  {
    id: 1,
    name: 'John Doe',
    balance: '1,250.00',
    status: 'active',
    lastActivity: '2023-04-25 15:30'
  },
  {
    id: 2,
    name: 'Jane Smith',
    balance: '780.00',
    status: 'active',
    lastActivity: '2023-04-24 09:15'
  },
  {
    id: 3,
    name: 'Robert Johnson',
    balance: '0.00',
    status: 'inactive',
    lastActivity: '2023-03-15 11:20'
  }
])

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    console.log('Fetching dashboard data...');
    const response = await axios.get('/admin/wallet/dashboard-data');
    console.log('Response:', response.data);
    
    walletDeductionRate.value = parseFloat(response.data.walletDeductionRate);
    totalRevenue.value = response.data.totalRevenue;
    totalProfit.value = response.data.totalProfit;
    activeWallets.value = response.data.activeWallets;
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    // Set default values on error
    walletDeductionRate.value = 5;
    totalRevenue.value = 0;
    totalProfit.value = 0;
    activeWallets.value = 0;
    
    // Show user-friendly error
    alert('Unable to load dashboard data. Please try again later.');
  }
}

// Update wallet deduction rate
const updateDeductionRate = async () => {
  isSubmitting.value = true
  
  try {
    await axios.post('/admin/wallet/update-deduction-rate', {
      rate: walletDeductionRate.value
    })
    // Refresh data after update
    await fetchDashboardData()
  } catch (error) {
    console.error('Error updating deduction rate:', error)
    alert('Failed to update deduction rate. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

// Helper to format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const adjustBalance = (sellerId) => {
  const amount = prompt('Enter adjustment amount (use negative for deduction):')
  if (amount) {
    alert(`Balance adjusted for seller #${sellerId}: ${amount > 0 ? '+' : ''}${amount}`)
  }
}

// Fetch data on component mount
onMounted(fetchDashboardData)
</script>