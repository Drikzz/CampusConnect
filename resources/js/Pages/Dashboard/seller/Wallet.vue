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
                <Link v-if="!user.email_verified_at" :href="route('verification.notice')"
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

      <!-- Wallet Status Messages -->
      <div v-else-if="showStatusMessage" class="max-w-2xl mx-auto mb-6">
        <!-- Verification Pending -->
        <div v-if="walletData.status === 'pending_approval'"
          class="bg-yellow-50 border border-yellow-200 p-6 rounded-lg">
          <h2 class="text-xl font-semibold text-yellow-800 mb-2">Wallet Verification in Progress</h2>
          <p class="text-yellow-700">
            Your seller wallet verification is under review. This process typically takes 24-48 hours.
          </p>
          <div class="mt-4 flex items-center text-yellow-700">
            <span>Request submitted on {{ formatDate(verificationData.submitted_at) }}</span>
          </div>
        </div>

        <!-- Verification Rejected -->
        <div v-else-if="verificationData?.status === 'rejected'" class="bg-red-50 border border-red-200 p-6 rounded-lg">
          <h2 class="text-xl font-semibold text-red-800 mb-2">Verification Request Rejected</h2>
          <p v-if="verificationData.remarks" class="text-red-700 mb-4">
            {{ verificationData.remarks }}
          </p>
          <p v-if="verificationData.description" class="text-gray-600 mb-4">
            {{ verificationData.description }}
          </p>
          <ul class="list-disc list-inside text-red-700 mb-4 space-y-2">
            <li>Valid ID is clear and readable</li>
            <li>Information matches your profile</li>
            <li>You meet all seller requirements</li>
          </ul>
          <p class="text-sm text-gray-500 mb-4">
            Rejected on {{ formatDate(verificationData.processed_at) }}
          </p>
          <Button @click="resetWalletStatus" variant="destructive">
            Submit New Request
          </Button>
        </div>
      </div>

      <!-- Active Wallet UI -->
      <div v-else-if="wallet?.is_activated">
        <!-- Fund Addition Status Messages -->
        <div v-if="hasPendingTransaction" class="bg-blue-50 border border-blue-200 p-6 rounded-lg mb-6">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-18c4.411 0 8 3.589 8 8s-3.589 8-8 8-8-3.589-8-8 3.589-8 8-8zM11 7h2v2h-2zm0 4h2v6h-2z" />
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

        <!-- After the Fund Addition Status Message, add a Withdrawal In Process Message -->
        <div v-if="hasInProcessWithdrawal" class="bg-blue-50 border border-blue-200 p-6 rounded-lg mb-6">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-18c4.411 0 8 3.589 8 8s-3.589 8-8 8-8-3.589-8-8 3.589-8 8-8zM11 7h2v2h-2zm0 4h2v6h-2z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-blue-800">Withdrawal in Progress</h3>
              <p class="mt-1 text-blue-700">
                Your withdrawal request is being processed. Funds will be sent to your GCash account within 24-48 hours.
              </p>
            </div>
          </div>
        </div>

        <!-- Success Message - Only show for non-activated wallets -->
        <div v-if="$page.props.flash?.success && !wallet?.is_activated"
          class="bg-green-50 border border-green-200 p-6 rounded-lg mb-4">
          <h3 class="text-lg font-medium text-green-800">Success!</h3>
          <p class="text-green-700">{{ $page.props.flash.success }}</p>
        </div>

        <!-- Wallet Stats with Refill Button -->
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold">Wallet Details</h2>
        </div>

        <!-- GCash Support Note -->
        <div class="bg-blue-50 rounded-lg p-3 mb-4 text-sm text-blue-700 flex items-start">
          <svg class="w-5 h-5 mr-2 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p>
            <strong>Note:</strong> We currently only support GCash transactions for wallet funding and withdrawals.
          </p>
        </div>

        <!-- Add QR Code Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
          <h3 class="text-lg font-medium mb-4">Scan to Pay via GCash</h3>
          <div class="flex items-start space-x-6">
            <div class="flex-shrink-0">
              <img src="../../../../../public/storage/images/campus-connect-qr.jpg" alt="GCash QR Code" class="w-48 h-48 object-cover rounded-lg shadow-sm" />
            </div>
            <div class="flex-grow space-y-3">
              <div>
                <h4 class="font-medium text-gray-700">Steps to Add Funds:</h4>
                <ol class="list-decimal list-inside text-gray-600 space-y-2 mt-2">
                  <li>Open your GCash app</li>
                  <li>Scan the QR code or send to the number below</li>
                  <li>Enter the amount you want to add</li>
                  <li>Complete the payment</li>
                  <li>Copy the reference number</li>
                  <li>Click "Add Funds" and submit the details</li>
                </ol>
              </div>
              <div class="pt-2">
                <p class="text-gray-600"><span class="font-medium">GCash Number:</span> 09123456789</p>
                <p class="text-gray-600"><span class="font-medium">Account Name:</span> Campus Connect</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Wallet Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-lg shadow-md border border-primary-color/20">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-sm font-medium text-gray-500">Current Balance</h3>
              <WalletIcon class="w-6 h-6 text-primary-color" />
            </div>
            <div class="text-2xl font-bold text-primary-color">₱{{ formatNumber(wallet?.balance || 0) }}</div>
            <div class="mt-2 text-xs text-gray-500">Available for withdrawal or purchases</div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md border border-green-200">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-sm font-medium text-gray-500">Earnings from Sales</h3>
              <ArrowTrendingUpIcon class="w-6 h-6 text-green-500" />
            </div>
            <div class="text-2xl font-bold text-green-600">₱{{ formatNumber(stats.total_sales || 0) }}</div>
            <div class="mt-2 text-xs text-gray-500">Income from products sold</div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md border border-red-200">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-sm font-medium text-gray-500">Deductions from Listings</h3>
              <BanknotesIcon class="w-6 h-6 text-red-500" />
            </div>
            <div class="text-2xl font-bold text-red-600">₱{{ formatNumber(stats.total_deductions || 0) }}</div>
            <div class="mt-2 text-xs text-gray-500">Platform fees and other expenses</div>
          </div>
        </div>

        <!-- Show refill CTA if balance is 0 -->
        <div v-if="wallet?.balance === 0"
          class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-6 text-center mt-6">
          <h3 class="text-lg font-medium text-yellow-800 mb-2">Initialize Your Seller Wallet</h3>
          <p class="text-yellow-600 mb-4">You need to add funds to your wallet to start selling products.</p>
          <button @click="showRefillModal = true"
            class="bg-primary-color text-white px-6 py-2 rounded-lg hover:bg-primary-color/90 transition-all shadow-md hover:shadow-lg">
            Refill Wallet
          </button>
        </div>

        <!-- Replace transactions list with data-table component -->
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-5">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              Transactions History
            </h3>
          </div>

          <!-- Data table for transactions -->
          <div class="px-4">
            <DataTable :columns="transactionColumns" :data="filteredTransactions" :get-actions="getTransactionActions"
              :selectable="false" :items-per-page="transactionItemsPerPage"
              @update:items-per-page="transactionItemsPerPage = $event" search-placeholder="Search transactions..."
              reset-button-label="Reset Filters" :show-reset-button="true" :show-custom-buttons="true"
              :empty-state="{ 
                message: 'No Transactions Yet',
                description: 'You haven\'t made any transactions yet. Start by adding funds to your wallet to begin your seller journey.',
                icon: 'WalletIcon'
              }">
              <!-- Pass the custom buttons as a slot -->
              <template #custom-buttons>
                <Button @click="showWithdrawModal = true" variant="outline" class="flex items-center">
                  <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
                  Withdraw
                </Button>
                <Button @click="showRefillModal = true" variant="default" class="flex items-center">
                  <PlusIcon class="w-4 h-4 mr-2" />
                  Add Funds
                </Button>
              </template>
            </DataTable>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <Dialog :open="showTransactionModal" @update:open="showTransactionModal = false">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {{ getTransactionTypeLabel(selectedTransaction) }} Transaction Details
          </DialogTitle>
          <DialogDescription v-if="selectedTransaction?.status === 'rejected'">
            This request was rejected by the administrator.
          </DialogDescription>
          <DialogDescription
            v-else-if="selectedTransaction?.status === 'in_process' && selectedTransaction?.reference_type === 'withdrawal'">
            Your withdrawal is being processed and will be sent within 24-48 hours.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedTransaction" class="space-y-4">
          <!-- Status and Amount -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <p class="font-medium" :class="getStatusColor(selectedTransaction.status)">
                {{ formatStatus(selectedTransaction.status) }}
              </p>
            </div>
            <div v-if="parseFloat(selectedTransaction.amount) > 0">
              <p class="text-sm text-gray-500">Amount</p>
              <p class="font-medium">₱{{ selectedTransaction.amount }}</p>
            </div>
          </div>

          <!-- Date Information -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Requested on</p>
              <p class="font-medium">{{ formatDate(selectedTransaction.created_at) }}</p>
            </div>
            <div v-if="selectedTransaction.processed_at">
              <p class="text-sm text-gray-500">Processed on</p>
              <p class="font-medium">{{ formatDate(selectedTransaction.processed_at) }}</p>
            </div>
          </div>

          <!-- Show Wallet Balance - especially important for rejected withdrawals -->
          <div v-if="selectedTransaction.reference_type === 'withdrawal' &&
            (selectedTransaction.status === 'rejected' || selectedTransaction.status === 'completed')"
            class="border-t pt-3 grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Current Balance</p>
              <p class="font-medium text-primary-color">₱{{ formatNumber(wallet?.balance || 0) }}</p>
            </div>
          </div>

          <!-- Rejection Reason -->
          <div v-if="selectedTransaction.status === 'rejected'" class="bg-red-50 p-3 rounded-md border border-red-200">
            <h4 class="font-medium text-red-800 mb-1">Rejection Reason</h4>
            <p class="text-red-700 text-sm">{{ selectedTransaction.remarks || 'No reason provided' }}</p>
          </div>

          <!-- Reference Info -->
          <div v-if="selectedTransaction.reference_id" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">
              {{ selectedTransaction.reference_type === 'withdrawal' && selectedTransaction.status === 'completed'
                ? 'GCash Reference Number'
                : 'Reference ID'
              }}
            </p>
            <p class="text-sm break-all">{{ selectedTransaction.reference_id }}</p>
          </div>

          <!-- Transaction Description -->
          <div v-if="selectedTransaction.description" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Description</p>
            <p class="text-sm">{{ selectedTransaction.description }}</p>
          </div>

          <!-- Receipt Image (if available) -->
          <div v-if="selectedTransaction.receipt_path" class="border-t pt-3">
            <p class="text-sm text-gray-500 mb-1">Receipt</p>
            <div class="flex justify-center">
              <img :src="'/storage/' + selectedTransaction.receipt_path" alt="Transaction Receipt"
                class="rounded-md border max-h-[50vh] object-contain" />
            </div>
          </div>

          <!-- Download Receipt Button -->
          <div v-if="canDownloadReceipt(selectedTransaction)" class="flex justify-center pt-3">
            <button @click="downloadReceipt(selectedTransaction.id)"
              class="bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
              Download Receipt as PDF
            </button>
          </div>
        </div>

        <DialogFooter class="mt-4">
          <Button @click="showTransactionModal = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

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
              <Input id="amount" type="number" v-model="refillForm.amount" placeholder="Minimum ₱50" required
                min="50" />
            </div>
            <div class="grid gap-2">
              <Label for="reference">GCash Reference Number</Label>
              <Input id="reference" type="text" v-model="refillForm.reference_number"
                placeholder="Payment reference number" required />
            </div>
            <div class="grid gap-2">
              <Label for="receipt">Payment Screenshot</Label>
              <Input id="receipt" type="file" @change="handleFileUpload" required accept="image/*" />
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

    <!-- Add the new withdraw modal -->
    <Dialog :open="showWithdrawModal" @close="closeWithdrawModal">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Withdraw Funds</DialogTitle>
          <DialogDescription>
            Request to withdraw funds from your wallet to your GCash account.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitWithdraw">
          <div class="grid gap-4 py-4">
            <div class="grid gap-2">
              <Label for="withdraw-amount">Amount (₱)</Label>
              <Input id="withdraw-amount" type="number" v-model="withdrawForm.amount" placeholder="Minimum ₱50"
                :max="wallet?.balance || 0" required min="50" />
              <p class="text-xs text-gray-500">Available balance: ₱{{ formatNumber(wallet?.balance || 0) }}</p>
            </div>
            <div class="grid gap-2">
              <Label for="phone-number">GCash Phone Number</Label>
              <Input id="phone-number" type="tel" v-model="withdrawForm.phone_number" placeholder="09XXXXXXXXX" required
                pattern="^(09|\+639)\d{9}$" />
              <div class="flex justify-between">
                <p class="text-xs text-gray-500">Enter the GCash phone number where you want to receive funds</p>
                <button type="button" @click="withdrawForm.phone_number = user.phone || ''"
                  class="text-xs text-primary-color hover:underline">
                  Use my phone number
                </button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="payout-details">Account Name (Optional)</Label>
              <Input id="account-name" v-model="withdrawForm.account_name"
                placeholder="Name registered with your GCash account" />
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeWithdrawModal">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting || withdrawForm.amount > (wallet?.balance || 0)">
              {{ isSubmitting ? 'Processing...' : 'Request Withdrawal' }}
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
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  PlusIcon,
  ArrowUpTrayIcon, // Changed from ArrowDownTrayIcon
  WalletIcon,
  ArrowTrendingUpIcon, // Changed from TrendingUpIcon
  ClockIcon
} from '@heroicons/vue/24/outline'
import {
  BanknotesIcon,
  CurrencyDollarIcon,
  ShieldCheckIcon
} from '@heroicons/vue/24/solid'
import DashboardLayout from '../DashboardLayout.vue'
import StatCard from '../Components/StatCard.vue'
import TransactionItem from '../Components/TransactionItem.vue'
import DataTable from '@/Components/ui/data-table.vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/Components/ui/dialog'
import { AlertDialog, AlertDialogContent, AlertDialogHeader, AlertDialogTitle, AlertDialogDescription, AlertDialogAction, AlertDialogFooter } from '@/Components/ui/alert-dialog'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Checkbox } from '@/Components/ui/checkbox'
import { useToast } from "@/Components/ui/toast/use-toast"
import axios from 'axios'
import { Textarea } from '@/Components/ui/textarea'
import { Select } from '@/Components/ui/select'

const { toast } = useToast()

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
  verification: {
    type: Object,
    default: null
  },
  stats: {
    type: Object,
    required: true
  }
})

// Initialize reactive refs
const walletData = ref({ ...props.wallet })
const verificationData = ref({ ...props.verification })
const showActivationModal = ref(false)
const showDepositModal = ref(false)
const showRefillModal = ref(false)
const isSubmitting = ref(false)
const pendingRequest = ref(false)
const showWithdrawModal = ref(false)

const setupForm = ref({
  id_image: null,
  terms_accepted: false
})

const refillForm = ref({
  amount: 50,
  reference_number: '',
  receipt_image: null
})

// Fix the initialization of withdrawForm to properly use props.user
const withdrawForm = ref({
  amount: 50,
  phone_number: props.user?.phone || '',
  account_name: ''
})

// Update the hasPendingTransaction computed property to check for both refill and withdrawal requests
const hasPendingTransaction = computed(() => {
  return walletData.value?.transactions?.some(
    transaction => transaction.status === 'pending' &&
      (transaction.reference_type === 'refill' || transaction.reference_type === 'withdrawal')
  ) || false
})

// Add a computed property to detect in-process withdrawals specifically for UI indicators
const hasInProcessWithdrawal = computed(() => {
  return walletData.value?.transactions?.some(
    transaction => transaction.status === 'in_process' && transaction.reference_type === 'withdrawal'
  ) || false
})

// Fix the broken showStatusMessage computed property
const showStatusMessage = computed(() => {
  return walletData.value && (
    walletData.value.status === 'pending_approval' ||
    verificationData.value?.status === 'rejected'
  )
})

// Fix the hasNonVerificationTransactions computed property
const hasNonVerificationTransactions = computed(() => {
  if (!walletData.value?.transactions?.length) return false;

  // Return true if there are any transactions that are not verification transactions
  return walletData.value.transactions.some(transaction => {
    return !(transaction.reference_type === 'verification' && transaction.verification_type === 'seller_activation');
  });
})

const showSetupForm = computed(() => {
  // Only show setup form when:
  // 1. Not activated AND
  // 2. Either has no status OR status is pending AND
  // 3. Not rejected AND not pending approval
  return (
    !walletData.value?.is_activated &&
    (!walletData.value?.status || walletData.value?.status === 'pending') &&
    !showStatusMessage.value
  )
})

const resetWalletStatus = () => {
  walletData.value = {
    ...walletData.value,
    status: 'pending'
  }
  verificationData.value = null
  setupForm.value = {
    id_image: null,
    terms_accepted: false
  }
}

// Watch for changes in props
watch(() => props.wallet, (newWallet) => {
  if (newWallet) {
    walletData.value = { ...newWallet }
  }
}, { deep: true, immediate: true })

watch(() => props.verification, (newVerification) => {
  if (newVerification) {
    verificationData.value = { ...newVerification }
  }
}, { deep: true, immediate: true })

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
  refillForm.value = { amount: 50, reference_number: '', receipt_image: null }
}

const closeWithdrawModal = () => {
  showWithdrawModal.value = false
  // Reset the form but maintain the user's phone number
  withdrawForm.value = {
    amount: 50,
    phone_number: props.user?.phone || '',
    account_name: ''
  }
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
      refillForm.value = { amount: 50, reference_number: '', receipt_image: null }
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}

const submitWithdraw = () => {
  if (withdrawForm.value.amount > (props.wallet?.balance || 0)) {
    toast({
      title: "Insufficient Balance",
      description: "You cannot withdraw more than your available balance",
      variant: "destructive"
    })
    return
  }

  // Validate phone number format - simplified validation that still checks format
  if (!withdrawForm.value.phone_number || withdrawForm.value.phone_number.length < 10) {
    toast({
      title: "Invalid Phone Number",
      description: "Please enter a valid GCash phone number",
      variant: "destructive"
    })
    return
  }

  isSubmitting.value = true
  router.post(route('seller.wallet.withdraw'), withdrawForm.value, {
    preserveScroll: true,
    onSuccess: (page) => {
      showWithdrawModal.value = false
      isSubmitting.value = false
      withdrawForm.value = {
        amount: 50,
        phone_number: props.user?.phone || '',
        account_name: ''
      }
      toast({
        title: "Success",
        description: "Withdrawal request submitted for approval",
        variant: "success"
      })
    },
    onError: (errors) => {
      isSubmitting.value = false
      const errorMessage = Object.values(errors)[0] || "An error occurred"
      toast({
        title: "Error",
        description: errorMessage,
        variant: "destructive"
      })
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
    isSubmitting.value = false
    toast({
      title: "Error",
      description: error.message || "An error occurred",
      variant: "destructive"
    })
  }
}

// Update formatDate function for compact display
const formatDate = (dateString) => {
  if (!dateString) return '';

  const date = new Date(dateString);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const month = months[date.getMonth()];
  const day = date.getDate();
  const year = String(date.getFullYear()).substr(2);
  let hours = date.getHours();
  const minutes = date.getMinutes();
  const ampm = hours >= 12 ? 'PM' : 'AM';

  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'

  return `${month} ${day}, ${year} ${hours}:${minutes < 10 ? '0' + minutes : minutes} ${ampm}`;
}

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const handleError = (error) => {
  console.error('Wallet operation error:', error)
  toast({
    title: "Error",
    description: error.message || "An error occurred",
    variant: "destructive"
  })
}

let pollInterval = null
const POLL_INTERVAL = 20000 // 30 seconds
const MAX_RETRIES = 3
let retryCount = 0

const shouldPoll = computed(() => {
  // Only poll for status updates when:
  // 1. Wallet is pending approval, or
  // 2. Has transactions in process (like withdrawals being processed), or
  // 3. Has pending transactions (via hasPendingTransaction)
  if (!walletData.value) return false

  return (
    walletData.value.status === 'pending_approval' ||
    walletData.value?.transactions?.some(t => t.status === 'in_process') ||
    hasPendingTransaction.value
  )
})

const fetchWalletStatus = async () => {
  if (retryCount >= MAX_RETRIES) {
    console.log('Max retries reached, stopping polling');
    clearInterval(pollInterval);
    return;
  }

  try {
    const response = await axios.get(route('seller.wallet.status'));
    const data = response.data;
    retryCount = 0;

    if (data) {
      // Update the entire wallet object instead of individual properties
      walletData.value = data;

      // Force full reactive update of props.wallet
      Object.assign(props.wallet, data);

      if (data.verification) {
        verificationData.value = data.verification;
      }

      // Log status changes for debugging
      // console.log('Wallet status updated:', {
      //   status: data.status,
      //   is_activated: data.is_activated,
      //   previous: props.wallet.is_activated
      // });
    }
  } catch (error) {
    retryCount++;
    console.error('Error fetching wallet status:', error);
  }
};

// Update the polling management to use the shouldPoll computed property
onMounted(() => {
  // Always fetch wallet status once on mount
  fetchWalletStatus();

  // Set up polling interval if needed
  setupPolling();
});

// Create a new function to handle polling setup/teardown
const setupPolling = () => {
  // Clear any existing interval
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }

  // Only set up polling if needed
  if (shouldPoll.value) {
    pollInterval = setInterval(fetchWalletStatus, POLL_INTERVAL);
    // console.log('Wallet status polling started');
  }
};

// Watch for changes in the shouldPoll value to start/stop polling
watch(shouldPoll, (newValue) => {
  // console.log('Should poll changed:', newValue);
  setupPolling();
}, { immediate: true });

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
    //console.log('Wallet status polling stopped');
  }
});

// Transaction related state
const selectedTransaction = ref(null)
const showTransactionModal = ref(false)

// Update the transaction columns definition to include transaction type
const transactionColumns = [
  {
    key: 'rowNumber',
    label: '#',
    sortable: false
  },
  {
    key: 'type',
    label: 'Type',
    sortable: true
  },
  {
    key: 'description',
    label: 'Description',
    sortable: true
  },
  {
    key: 'amount',
    label: 'Amount',
    sortable: true
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true
  },
  {
    key: 'created_at',
    label: 'Date',
    sortable: true
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false
  }
]

// Update the filteredTransactions computed property
const filteredTransactions = computed(() => {
  if (!walletData.value?.transactions?.length) return []

  return walletData.value.transactions
    .filter(t => t.reference_type !== 'verification')
    .map((t, index) => {
      // Determine if this is a withdrawal to force red color
      const isWithdrawal = t.reference_type === 'withdrawal';
      const isCredit = t.type === 'credit' && !isWithdrawal;

      return {
        ...t,
        id: t.id, // Ensure ID is passed for proper identification
        rowNumber: index + 1,
        // Add formatted transaction type
        type: isWithdrawal ? 'Withdrawal' : (isCredit ? 'Credit' : 'Debit'),
        // Add formatted fields for display in the table
        description: t.description || getDefaultDescription(t),
        // For withdrawals, always show negative amount (red color)
        amount: isWithdrawal || t.type === 'debit'
          ? `-₱${formatNumber(t.amount)}`
          : `+₱${formatNumber(t.amount)}`,
        created_at: formatDate(t.created_at),
        // Add explicit flags for styling
        isWithdrawal: isWithdrawal,
        isCredit: isCredit
      }
    })
})

// Get transaction actions for the data table
const getTransactionActions = (row) => {
  const actions = [
    {
      label: 'View Details',
      action: () => viewTransactionDetails(row),
      variant: 'outline',
      class: ''
    }
  ]

  // Add download receipt action if applicable
  if (canDownloadReceipt(row)) {
    actions.push({
      label: 'Download Receipt',
      action: () => downloadReceipt(row.id),
      variant: 'default',
      class: 'bg-primary-color text-white hover:bg-primary-color/90'
    })
  }

  return actions
}

// Fix the download receipt function
const downloadReceipt = (id) => {
  // Use window.location instead of router.get to force a full navigation/download
  window.location.href = route('seller.wallet.receipt', id);
}

// Update the function to allow receipt downloads for all withdrawal statuses
const canDownloadReceipt = (transaction) => {
  if (!transaction) return false

  return (
    // Complete/approved transactions always have receipts
    ['completed', 'approved'].includes(transaction.status.toLowerCase()) ||

    // ALL withdrawal transactions should have receipts regardless of status
    transaction.reference_type === 'withdrawal' ||

    // Refills with receipt path and not pending
    (transaction.reference_type === 'refill' &&
      transaction.receipt_path &&
      transaction.status.toLowerCase() !== 'pending')
  )
}

const viewTransactionDetails = (transaction) => {
  selectedTransaction.value = transaction
  showTransactionModal.value = true
}

// Helper function to format transaction type for display
const formatTransactionType = (transaction) => {
  if (!transaction.reference_type) {
    return 'Wallet Activation'
  }

  switch (transaction.reference_type) {
    case 'verification': return 'Verification'
    case 'refill': return 'Add Funds'
    case 'withdrawal': return 'Withdrawal'
    default: return capitalizeFirstLetter(transaction.reference_type)
  }
}

// Get user-friendly transaction type label
const getTransactionTypeLabel = (transaction) => {
  if (!transaction?.reference_type) {
    return 'Wallet Activation'
  }

  switch (transaction.reference_type) {
    case 'verification': return 'Verification'
    case 'refill': return 'Refill'
    case 'withdrawal': return 'Withdrawal'
    default: return capitalizeFirstLetter(transaction.reference_type)
  }
}

// Helper function to format status
const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'in_process': 'In Process',
    'completed': 'Completed',
    'rejected': 'Rejected'
  }
  return statusMap[status.toLowerCase()] || capitalizeFirstLetter(status)
}

// Get status color based on status
const getStatusColor = (status) => {
  const colors = {
    pending: 'text-yellow-600',
    in_process: 'text-blue-600',
    completed: 'text-green-600',
    approved: 'text-green-600',
    denied: 'text-red-600',
    rejected: 'text-red-600',
    failed: 'text-red-600'
  }
  return colors[status.toLowerCase()] || 'text-gray-600'
}

// Helper function to capitalize first letter
const capitalizeFirstLetter = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
}

// Get transaction icon
const getTransactionIcon = (transaction) => {
  if (!transaction.reference_type) {
    return ShieldCheckIcon
  }

  switch (transaction.reference_type) {
    case 'verification':
      return ShieldCheckIcon
    case 'refill':
      return CurrencyDollarIcon
    case 'withdrawal':
      return BanknotesIcon
    default:
      return transaction.type === 'credit' ? CurrencyDollarIcon : BanknotesIcon
  }
}

// Get icon background color class
const getIconBackgroundColor = (transaction) => {
  if (!transaction.reference_type) {
    switch (transaction.status.toLowerCase()) {
      case 'pending': return 'bg-blue-100'
      case 'rejected': return 'bg-red-100'
      default: return 'bg-green-100'
    }
  }

  switch (transaction.reference_type) {
    case 'verification':
      switch (transaction.status.toLowerCase()) {
        case 'pending': return 'bg-blue-100'
        case 'rejected': return 'bg-red-100'
        default: return 'bg-green-100'
      }
    case 'refill': return 'bg-green-100'
    case 'withdrawal': return 'bg-red-100'
    default:
      return transaction.type === 'credit' ? 'bg-green-100' : 'bg-red-100'
  }
}

// Get icon color class
const getIconColor = (transaction) => {
  if (!transaction.reference_type) {
    switch (transaction.status.toLowerCase()) {
      case 'pending': return 'text-blue-600'
      case 'rejected': return 'text-red-600'
      default: return 'text-green-600'
    }
  }

  switch (transaction.reference_type) {
    case 'verification':
      switch (transaction.status.toLowerCase()) {
        case 'pending': return 'text-blue-600'
        case 'rejected': return 'text-red-600'
        default: return 'text-green-600'
      }
    case 'refill': return 'text-green-600'
    case 'withdrawal': return 'text-red-600'
    default:
      return transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'
  }
}

// Helper function to provide default description when missing
const getDefaultDescription = (transaction) => {
  if (!transaction.reference_type) {
    return 'Wallet Activation'
  }

  switch (transaction.reference_type) {
    case 'refill': return 'Add funds to wallet'
    case 'withdrawal': return 'Withdraw funds to GCash'
    default: return capitalizeFirstLetter(transaction.reference_type) + ' transaction'
  }
}

// Add state for transaction items per page
const transactionItemsPerPage = ref(10)
</script>
