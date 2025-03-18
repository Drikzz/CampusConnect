<template>
  <AdminLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-bold">Wallet & Fees</h2>
      
      <!-- Platform Fees Settings -->
      <div class="bg-white border rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Platform Fees Settings</h3>
        <form @submit.prevent="updatePlatformFees" class="space-y-4">
          <div class="flex items-center gap-4">
            <div class="w-64">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Listing Fee Percentage
              </label>
              <div class="flex items-center gap-2">
                <input 
                  type="number" 
                  v-model="platformFees.listingFee" 
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
            ₱{{ totalRevenue }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Active Seller Wallets</p>
          <p class="text-2xl font-bold text-green-600">
            {{ activeWallets }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Pending Fee Collection</p>
          <p class="text-2xl font-bold text-yellow-600">
            ₱{{ pendingFees }}
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// Mock data
const platformFees = ref({
  listingFee: 2.5,
})

const totalRevenue = ref('8,750.00')
const activeWallets = ref(12)
const pendingFees = ref('450.00')
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

const updatePlatformFees = () => {
  isSubmitting.value = true
  
  // Simulate API call
  setTimeout(() => {
    isSubmitting.value = false
    alert('Platform fees updated successfully!')
  }, 1000)
}

const adjustBalance = (sellerId) => {
  const amount = prompt('Enter adjustment amount (use negative for deduction):')
  if (amount) {
    alert(`Balance adjusted for seller #${sellerId}: ${amount > 0 ? '+' : ''}${amount}`)
  }
}
</script>