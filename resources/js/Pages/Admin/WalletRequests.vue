<template>
  <AdminLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-bold">Wallet Requests</h2>
      
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Pending Requests</p>
          <p class="text-2xl font-bold text-yellow-600">
            {{ pendingCount }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Processed Today</p>
          <p class="text-2xl font-bold text-green-600">
            {{ todayProcessedCount }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Volume (₱)</p>
          <p class="text-2xl font-bold text-red-600">
            {{ totalAmount }}
          </p>
        </div>
      </div>
      
      <!-- Transactions Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                {{ formatDate(transaction.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ transaction.wallet?.user?.first_name || 'N/A' }} {{ transaction.wallet?.user?.last_name || '' }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ transaction.seller_code || 'No code' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ transaction.reference_type || 'Unknown' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                ₱{{ transaction.amount || '0.00' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  {
                    'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                    'bg-blue-100 text-blue-800': transaction.status === 'in_process',
                    'bg-green-100 text-green-800': transaction.status === 'completed',
                    'bg-red-100 text-red-800': transaction.status === 'rejected'
                  }
                ]">
                  {{ formatStatus(transaction.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap space-x-2">
                <!-- Pending transaction actions -->
                <template v-if="transaction.status === 'pending'">
                  <button class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm" 
                          @click="approveRequest(transaction.id)">
                    Approve
                  </button>
                  <button class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm" 
                          @click="rejectRequest(transaction.id)">
                    Reject
                  </button>
                </template>
                
                <!-- In Process withdrawal - Add GCash reference completion button -->
                <button v-if="transaction.status === 'in_process' && transaction.reference_type === 'withdrawal'"
                        class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-sm"
                        @click="showCompleteWithdrawalDialog(transaction.id)">
                  Complete Withdrawal
                </button>
              </td>
            </tr>
            
            <!-- Empty state when no transactions -->
            <tr v-if="!transactions || transactions.length === 0">
              <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                No wallet requests found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  }
})

// Format transaction status for display
const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'in_process': 'In Process',
    'completed': 'Completed',
    'rejected': 'Rejected'
  }
  return statusMap[status] || status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  })
}

const pendingCount = computed(() => 
  props.transactions?.filter(t => t.status === 'pending').length || 0
)

const todayProcessedCount = computed(() => {
  const today = new Date().toDateString()
  return props.transactions?.filter(t => 
    t.processed_at && new Date(t.processed_at).toDateString() === today
  ).length || 0
})

const totalAmount = computed(() => 
  props.transactions
    ?.filter(t => t.status === 'completed')
    .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)
    .toLocaleString('en-PH') || '0.00'
)

const approveRequest = (id) => {
  if (confirm('Are you sure you want to approve this request?')) {
    router.post(route('admin.wallet-requests.approve', id))
  }
}

const rejectRequest = (id) => {
  if (confirm('Are you sure you want to reject this request?')) {
    router.post(route('admin.wallet-requests.reject', id))
  }
}

const showCompleteWithdrawalDialog = (id) => {
  const reference = prompt('Enter GCash reference number to complete this withdrawal:')
  if (reference) {
    router.post(route('admin.wallet-requests.complete-withdrawal', id), {
      gcash_reference: reference
    })
  }
}
</script>
