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
                  {{ transaction.reference_type }}
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
                    'bg-blue-100 text-blue-800': transaction.status === 'in_process',
                    'bg-green-100 text-green-800': transaction.status === 'completed',
                    'bg-red-100 text-red-800': transaction.status === 'rejected'
                  }
                ]">
                  {{ formatStatus(transaction.status) }}
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
                <!-- Pending transaction actions -->
                <template v-if="transaction.status === 'pending'">
                  <Button variant="success" 
                          size="sm" 
                          @click="approveRequest(transaction.id)">
                    Approve
                  </Button>
                  <Button variant="destructive" 
                          size="sm" 
                          @click="rejectRequest(transaction.id)">
                    Reject
                  </Button>
                </template>
                
                <!-- In Process withdrawal - Add GCash reference completion button -->
                <Button v-if="transaction.status === 'in_process' && transaction.reference_type === 'withdrawal'"
                        variant="outline"
                        size="sm"
                        class="bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100"
                        @click="showCompleteWithdrawalDialog(transaction.id)">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    Complete Withdrawal
                  </span>
                </Button>
                
                <!-- Processed transaction info -->
                <div v-else-if="transaction.processed_at && transaction.status !== 'in_process'" class="text-sm text-gray-500">
                  <div>Processed {{ formatDate(transaction.processed_at) }}</div>
                  <div v-if="transaction.reference_id && transaction.status === 'completed' && transaction.reference_type !== 'verification'" 
                       class="text-xs text-blue-600 mt-1">
                    GCash Ref: {{ transaction.reference_id }}
                  </div>
                </div>
                
                <!-- In-process transaction info -->
                <div v-else-if="transaction.status === 'in_process' && transaction.reference_type !== 'withdrawal'" class="text-sm text-gray-500">
                  Processing since {{ formatDate(transaction.processed_at) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Receipt Preview Dialog -->
    <Dialog :open="!!selectedTransaction" @close="selectedTransaction = null">
      <DialogContent class="sm:max-w-md lg:max-w-sm">
        <DialogHeader>
          <DialogTitle>Payment Receipt</DialogTitle>
          <DialogDescription>
            Transaction #{{ selectedTransaction?.id }} - {{ formatDate(selectedTransaction?.created_at) }}
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <!-- Transaction details -->
          <div class="bg-gray-50 p-4 rounded-md space-y-2">
            <div class="flex justify-between text-sm">
              <span class="font-medium text-gray-500">Amount:</span>
              <span class="font-bold">₱{{ selectedTransaction?.amount }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="font-medium text-gray-500">Reference #:</span>
              <span>{{ selectedTransaction?.reference_id }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="font-medium text-gray-500">Status:</span>
              <span :class="{
                'text-yellow-600': selectedTransaction?.status === 'pending',
                'text-green-600': selectedTransaction?.status === 'completed',
                'text-red-600': selectedTransaction?.status === 'rejected'
              }">{{ selectedTransaction?.status }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="font-medium text-gray-500">Type:</span>
              <span>{{ selectedTransaction?.reference_type }}</span>
            </div>
          </div>
          
          <!-- Receipt image -->
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
    
    <!-- Complete Withdrawal Dialog -->
    <Dialog :open="showCompleteDialog" @close="closeCompleteDialog">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Complete Withdrawal</DialogTitle>
          <DialogDescription>
            Enter the GCash reference number to complete this withdrawal transaction.
          </DialogDescription>
        </DialogHeader>
        <!-- Fix the form to use @submit.prevent to handle form submission properly -->
        <form @submit.prevent="completeWithdrawal">
          <div class="space-y-4 py-4">
            <div class="space-y-2">
              <Label for="gcash-reference">GCash Reference Number</Label>
              <Input 
                id="gcash-reference"
                v-model="gcashReference"
                placeholder="Enter GCash reference number"
                required
              />
              <p class="text-xs text-gray-500">This will be visible to the seller as confirmation of payment.</p>
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeCompleteDialog">Cancel</Button>
            <Button 
              type="submit" 
              variant="default" 
              :disabled="!gcashReference || isSubmitting"
            >
              {{ isSubmitting ? 'Submitting...' : 'Complete Withdrawal' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/Components/ui/dialog'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
// Import the useToast composable
import { useToast } from '@/Components/ui/toast/use-toast'

// Initialize the toast
const { toast } = useToast()

const props = defineProps({
  transactions: Array
})

const selectedTransaction = ref(null)
const showCompleteDialog = ref(false)
const selectedWithdrawalId = ref(null)
const gcashReference = ref('')
const isSubmitting = ref(false)

// Format transaction status for display
const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'in_process': 'In Process',
    'completed': 'Completed',
    'rejected': 'Rejected'
  }
  return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1)
}

const showCompleteWithdrawalDialog = (id) => {
  selectedWithdrawalId.value = id
  gcashReference.value = ''
  showCompleteDialog.value = true
}

const closeCompleteDialog = () => {
  showCompleteDialog.value = false
  selectedWithdrawalId.value = null
  gcashReference.value = ''
}

const completeWithdrawal = () => {
  if (!gcashReference.value) return
  
  isSubmitting.value = true
  router.post(route('admin.wallet-requests.complete-withdrawal', selectedWithdrawalId.value), {
    gcash_reference: gcashReference.value
  }, {
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Withdrawal completed successfully with GCash reference.",
        variant: "success"
      });
      closeCompleteDialog();
      isSubmitting.value = false;
    },
    onError: (errors) => {
      isSubmitting.value = false;
      toast({
        title: "Error",
        description: errors.gcash_reference || "Failed to complete withdrawal",
        variant: "destructive"
      });
    },
    preserveScroll: true
  })
}

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
