<template>
  <DashboardLayout :user="user" :stats="stats" :flash="$page.props.flash">
    <div class="space-y-6">
      <!-- Initial Setup Form - Show when wallet exists but not activated and has no status or pending status -->
      <div v-if="showSetupForm" class="max-w-2xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-2xl font-bold mb-4">Set Up Your Wallet</h2>
          <p class="text-gray-600 mb-6">Complete wallet verification to start selling</p>

          <form @submit.prevent="submitWalletSetup" class="space-y-6">
            <!-- Email Verification Status -->
            <div class="space-y-2">
              <Label>WMSU Email Verification</Label>
              <div class="flex items-center justify-between p-4 rounded-lg" 
                   :class="user.email_verified_at ? 'bg-green-50' : 'bg-yellow-50'">
                <div>
                  <p class="font-medium">{{ user.wmsu_email }}</p>
                  <p class="text-sm" :class="user.email_verified_at ? 'text-green-600' : 'text-yellow-600'">
                    {{ user.email_verified_at ? 'Email Verified' : 'Email Not Verified' }}
                  </p>
                </div>
                <Link v-if="!user.email_verified_at"
                      :href="route('verification.notice')"
                      class="text-primary-color hover:underline text-sm">
                  Verify Now
                </Link>
              </div>
            </div>

            <!-- ID Verification -->
            <div class="space-y-2">
              <Label>Valid ID Screenshot</Label>
              <Input type="file" @change="handleIdUpload" accept="image/*" required />
              <p class="text-sm text-gray-500">Upload a clear photo of your valid ID</p>
            </div>

            <!-- Terms Acceptance -->
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <Checkbox v-model="setupForm.terms_accepted" required />
                <Label>I agree to the Campus Connect Wallet Terms & Conditions</Label>
              </div>
            </div>

            <Button type="submit" :disabled="isSubmitting || !user.email_verified_at">
              {{ isSubmitting ? 'Submitting...' : 'Submit for Approval' }}
            </Button>
            
            <p v-if="!user.email_verified_at" class="text-sm text-yellow-600">
              * You must verify your WMSU email before setting up your wallet
            </p>
          </form>
        </div>
      </div>

      <!-- Wallet Status Messages - Show for pending_approval or denied -->
      <div v-else-if="showStatusMessage" class="max-w-2xl mx-auto mb-6">
        <!-- Verification Pending -->
        <div v-if="wallet.status === 'pending_approval'" 
             class="bg-yellow-50 border border-yellow-200 p-6 rounded-lg">
          <h2 class="text-xl font-semibold text-yellow-800 mb-2">Wallet Verification in Progress</h2>
          <p class="text-yellow-700">
            Your seller wallet verification is under review. This process typically takes 24-48 hours.
          </p>
          <div class="mt-4 flex items-center text-yellow-700">
            <span>Request submitted on {{ formatDate(wallet.created_at) }}</span>
          </div>
        </div>

        <!-- Verification Denied -->
        <div v-else-if="wallet.status === 'denied'" 
             class="bg-red-50 border border-red-200 p-6 rounded-lg">
          <h2 class="text-xl font-semibold text-red-800 mb-2">Verification Request Denied</h2>
          <p class="text-red-700 mb-4">Your verification request was not approved. Please check:</p>
          <ul class="list-disc list-inside text-red-700 mb-4 space-y-2">
            <li>Valid ID is clear and readable</li>
            <li>Information matches your profile</li>
            <li>You meet all seller requirements</li>
          </ul>
          <Button @click="submitWalletSetup" variant="destructive">
            Submit New Request
          </Button>
        </div>
      </div>

      <!-- Active Wallet UI -->
      <div v-else-if="wallet?.is_activated">
        <!-- Fund Addition Status Messages -->
        <div v-if="hasPendingTransaction" 
             class="bg-blue-50 border border-blue-200 p-6 rounded-lg mb-6">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-18c4.411 0 8 3.589 8 8s-3.589 8-8 8-8-3.589-8-8 3.589-8 8-8zM11 7h2v2h-2zm0 4h2v6h-2z"/>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-blue-800">Fund Addition in Progress</h3>
              <p class="mt-1 text-blue-700">
                Your request to add funds is being processed. We'll update your balance once the payment is verified.
              </p>
            </div>
          </div>
        </div>

        <!-- Refill Success Message -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 p-6 rounded-lg mb-4">
          <h3 class="text-lg font-medium text-green-800">Success!</h3>
          <p class="text-green-700">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Wallet Stats with Refill Button -->
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold">Wallet Details</h2>
          <Button @click="showRefillModal = true" variant="default">
            <PlusIcon class="w-4 h-4 mr-2" />
            Add Funds
          </Button>
        </div>

        <!-- Wallet Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <StatCard
            title="Current Balance"
            :value="wallet?.balance || 0"
            type="money"
            icon="wallet"
          />
          <StatCard
            title="Total Earnings"
            :value="stats.total_credits || 0"
            type="money"
            icon="trending-up"
          />
          <StatCard
            title="Pending Transactions"
            :value="stats.pending_transactions || 0"
            icon="clock"
          />
        </div>

        <!-- Show refill CTA if balance is 0 -->
        <div v-if="wallet?.balance === 0" 
             class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
          <h3 class="text-lg font-medium text-yellow-800 mb-2">Initialize Your Seller Wallet</h3>
          <p class="text-yellow-600 mb-4">You need to add funds to your wallet to start selling products.</p>
          <button @click="showRefillModal = true"
                  class="bg-primary-color text-white px-6 py-2 rounded-lg hover:bg-primary-color/90">
            Refill Wallet
          </button>
        </div>

        <!-- Only show transactions if there are no pending refills -->
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              Recent Transactions
            </h3>
          </div>
          <div class="divide-y divide-gray-200">
            <TransactionItem 
              v-for="transaction in wallet?.transactions" 
              :key="transaction.id"
              :transaction="transaction"
            />
            <div v-if="!wallet?.transactions?.length" class="p-4 text-center text-gray-500">
              No transactions found
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add modals at the end of template -->
    <Dialog :open="showRefillModal" @close="closeRefillModal">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Add Funds to Wallet</DialogTitle>
          <DialogDescription>
            Upload your payment details to refill your wallet.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitRefill">
          <div class="grid gap-4 py-4">
            <div class="grid gap-2">
              <Label for="amount">Amount (₱)</Label>
              <Input 
                id="amount"
                type="number" 
                v-model="refillForm.amount" 
                placeholder="Minimum ₱100"
                required 
                min="100"
              />
            </div>
            <div class="grid gap-2">
              <Label for="reference">Reference Number</Label>
              <Input 
                id="reference"
                type="text" 
                v-model="refillForm.reference_number" 
                placeholder="Payment reference number"
                required
              />
            </div>
            <div class="grid gap-2">
              <Label for="receipt">Payment Screenshot</Label>
              <Input 
                id="receipt"
                type="file" 
                @change="handleFileUpload" 
                required 
                accept="image/*"
              />
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeRefillModal">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Alert when request is pending -->
    <AlertDialog :open="!!pendingRequest" @close="closePendingAlert">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Refill Request Pending</AlertDialogTitle>
          <AlertDialogDescription>
            Your wallet refill request has been submitted and is awaiting admin approval.
            We'll notify you once it's approved.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogAction @click="closePendingAlert">Okay</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3' // Add usePage import
import { PlusIcon } from '@heroicons/vue/24/outline'
import DashboardLayout from '../DashboardLayout.vue'
import StatCard from '../Components/StatCard.vue'
import TransactionItem from '../Components/TransactionItem.vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/Components/ui/dialog'
import { AlertDialog, AlertDialogContent, AlertDialogHeader, AlertDialogTitle, AlertDialogDescription, AlertDialogAction, AlertDialogFooter } from '@/Components/ui/alert-dialog'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Checkbox } from '@/Components/ui/checkbox'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  wallet: {
    type: Object,
    default: () => ({
      is_activated: false,
      status: null,
      balance: 0,
      transactions: []
    })
  },
  stats: {
    type: Object,
    required: true
  }
})

const page = usePage() // Add this line to access page props

// Initialize reactive refs
const showActivationModal = ref(false)
const showDepositModal = ref(false)
const showRefillModal = ref(false)
const isSubmitting = ref(false)
const pendingRequest = ref(false)

const refillForm = ref({
  amount: 100,
  reference_number: '',
  receipt_image: null
})

const setupForm = ref({
  id_image: null,
  terms_accepted: false
})

// Fix: Properly reference props.wallet in setup state
const setupPending = computed(() => 
  props.wallet?.status === 'pending_approval'
)

// Add computed for verification status messages
const verificationStatus = computed(() => {
  if (!props.wallet) return null
  
  switch(props.wallet.status) {
    case 'pending_approval':
      return {
        type: 'warning',
        title: 'Verification Pending',
        message: 'Your seller verification is under review'
      }
    case 'denied':
      return {
        type: 'error',
        title: 'Verification Denied',
        message: 'Your verification request was not approved'
      }
    case 'active':
      return {
        type: 'success',
        title: 'Verified',
        message: 'Your wallet is active'
      }
    default:
      return null
  }
})

const hasPendingTransaction = computed(() => {
  return props.wallet?.transactions?.some(t => 
    t.status === 'pending' && t.reference_type === 'refill'
  ) || false
})

// Add new computed property to control setup form visibility
const shouldShowSetupForm = computed(() => {
  return (!props.wallet?.status || props.wallet?.status === 'pending') && !props.wallet?.is_activated
})

const showSetupForm = computed(() => {
  return props.wallet && props.wallet.status === 'pending' && !props.wallet.is_activated
})

const showStatusMessage = computed(() => {
  return props.wallet && (props.wallet.status === 'pending_approval' || props.wallet.status === 'denied') && !props.wallet.is_activated
})

// Add debug watcher to help troubleshoot
watch(() => props.wallet, (newWallet) => {
  console.log('Wallet data:', {
    wallet: newWallet,
    showSetupForm: showSetupForm.value,
    showStatusMessage: showStatusMessage.value,
    status: newWallet?.status,
    is_activated: newWallet?.is_activated
  })
}, { immediate: true })

const handleActivateWallet = () => {
  router.post(route('seller.wallet.activate'), null, {
    preserveScroll: true,
    onSuccess: () => {
      showActivationModal.value = false
    }
  })
}

const handleDeposit = (formData) => {
  router.post(route('seller.wallet.deposit'), formData, {
    preserveScroll: true,
    onSuccess: () => {
      showDepositModal.value = false
    }
  })
}

const handleFileUpload = (e) => {
  refillForm.value.receipt_image = e.target.files[0]
}

const handleIdUpload = (e) => {
  setupForm.value.id_image = e.target.files[0]
}

const closeRefillModal = () => {
  showRefillModal.value = false
  refillForm.value = { amount: 100, reference_number: '', receipt_image: null }
}

const closePendingAlert = () => {
  pendingRequest.value = false
}

const submitRefill = () => {
  isSubmitting.value = true
  
  const formData = new FormData()
  formData.append('amount', refillForm.value.amount)
  formData.append('reference_number', refillForm.value.reference_number)
  formData.append('receipt_image', refillForm.value.receipt_image)

  router.post(route('seller.wallet.refill'), formData, {
    preserveScroll: true,
    onSuccess: () => {
      showRefillModal.value = false
      pendingRequest.value = true
      isSubmitting.value = false
      refillForm.value = { amount: 100, reference_number: '', receipt_image: null }
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}

const submitWalletSetup = async () => {
  if (!setupForm.value.id_image) {
    toast({
      title: "Validation Error",
      description: "Please upload your ID image",
      variant: "destructive"
    })
    return
  }

  isSubmitting.value = true
  
  try {
    const formData = new FormData()
    formData.append('id_image', setupForm.value.id_image)
    formData.append('terms_accepted', setupForm.value.terms_accepted)

    await router.post(route('seller.wallet.setup'), formData, {
      preserveScroll: true,
      onSuccess: () => {
        isSubmitting.value = false
        setupForm.value = { id_image: null, terms_accepted: false }
      }
    })
  } catch (error) {
    handleError(error)
    isSubmitting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleError = (error) => {
  console.error('Wallet operation error:', error)
  toast({
    title: "Error",
    description: error.message || "An error occurred",
    variant: "destructive"
  })
}
</script>
