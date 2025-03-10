<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard Overview</h1>
        
        <!-- Dashboard Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
          <div v-for="(stat, index) in dashboardStats" :key="index" 
            class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
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
          <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Transactions</h3>
              <div class="mt-4 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                  <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                      <thead>
                        <tr>
                          <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">ID</th>
                          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200">
                        <tr v-for="transaction in stats.recentTransactions" :key="transaction.id">
                          <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">{{ transaction.id }}</td>
                          <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                            {{ transaction.user?.name || 'N/A' }}
                            <span class="text-gray-500" v-if="transaction.user?.seller_code">
                              ({{ transaction.user.seller_code }})
                            </span>
                          </td>
                          <td class="whitespace-nowrap px-3 py-4 text-sm" :class="{
                            'text-green-600': transaction.type === 'credit',
                            'text-red-600': transaction.type === 'debit'
                          }">
                            {{ transaction.type === 'credit' ? '+' : '-' }}₱{{ Math.abs(transaction.amount).toFixed(2) }}
                          </td>
                          <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ transaction.reference_type }}</td>
                          <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" 
                              :class="{
                                'bg-green-100 text-green-800': transaction.status === 'completed',
                                'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                                'bg-red-100 text-red-800': transaction.status === 'failed'
                              }">
                              {{ transaction.status }}
                            </span>
                          </td>
                          <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                            {{ new Date(transaction.created_at).toLocaleDateString() }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue' // Updated import path
import { ClockIcon, UsersIcon, CubeIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline'

const dashboardStats = [
  { name: 'Total Users', value: '0' },
  { name: 'Verified Users', value: '0' },
  { name: 'Pending Listings', value: '0' },
  { name: 'Total Transactions', value: '0' },
  { name: 'Successful Transactions', value: '0' },
  { name: 'Refund Requests', value: '0' },
  { name: 'Reports Received', value: '0' }
]

defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>
