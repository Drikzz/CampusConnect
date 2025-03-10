<template>
  <AdminLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-bold">Wallet Transactions</h2>
      
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Pending Requests</p>
          <p class="text-2xl font-bold text-yellow-600">
            {{ pendingCount }}
          </p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Processed Today</p>
          <p class="text-2xl font-bold text-green-600">
            {{ todayProcessedCount }}
          </p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Volume (₱)</p>
          <p class="text-2xl font-bold text-primary-color">
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
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
                      {{ transaction.wallet.user.first_name }} {{ transaction.wallet.user.last_name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ transaction.seller_code }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ transaction.type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                ₱{{ transaction.amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  {
                    'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                    'bg-green-100 text-green-800': transaction.status === 'completed',
                    'bg-red-100 text-red-800': transaction.status === 'rejected'
                  }
                ]">
                  {{ transaction.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <Button v-if="transaction.receipt_url" 
                        variant="link" 
                        @click="showReceipt(transaction)">
                  View Receipt
                </Button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap space-x-2">
                <Button v-if="transaction.status === 'pending'"
                        variant="success" 
                        size="sm" 
                        @click="approveRequest(transaction.id)">
                  Approve
                </Button>
                <Button v-if="transaction.status === 'pending'"
                        variant="destructive" 
                        size="sm" 
                        @click="rejectRequest(transaction.id)">
                  Reject
                </Button>
                <span v-else-if="transaction.processed_at" class="text-sm text-gray-500">
                  Processed {{ formatDate(transaction.processed_at) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Receipt Preview Dialog -->
    <Dialog :open="!!selectedTransaction" @close="selectedTransaction = null">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Payment Receipt</DialogTitle>
          <DialogDescription>
            Transaction #{{ selectedTransaction?.id }} - {{ formatDate(selectedTransaction?.created_at) }}
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <img 
            v-if="selectedTransaction"
            :src="selectedTransaction.receipt_url" 
            :alt="'Receipt for transaction #' + selectedTransaction.id"
            class="w-full rounded-lg"
          />
        </div>
        <DialogFooter>
          <Button @click="selectedTransaction = null">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/Components/ui/dialog'

const props = defineProps({
  transactions: Array
})

const selectedTransaction = ref(null)

const showReceipt = (transaction) => {
  selectedTransaction.value = transaction
}

const formatDate = (date) => {
  return new Date(date).toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  })
}

const pendingCount = computed(() => 
  props.transactions.filter(t => t.status === 'pending').length
)

const todayProcessedCount = computed(() => {
  const today = new Date().toDateString()
  return props.transactions.filter(t => 
    t.processed_at && new Date(t.processed_at).toDateString() === today
  ).length
})

const totalAmount = computed(() => 
  props.transactions
    .filter(t => t.status === 'completed')
    .reduce((sum, t) => sum + parseFloat(t.amount), 0)
    .toLocaleString('en-PH')
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
</script>
