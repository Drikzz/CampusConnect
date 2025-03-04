<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Wallet Activation Notice -->
      <div v-if="!wallet?.is_activated" 
           class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm text-yellow-700">
              Your wallet needs to be activated before you can start selling products and managing orders.
            </p>
            <div class="mt-4">
              <Button @click="showActivateModal = true">
                Activate Wallet
              </Button>
            </div>
          </div>
        </div>
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

      <!-- Action Buttons -->
      <div class="flex gap-4 justify-end" v-if="wallet?.is_activated">
        <Button @click="showRefillModal = true" variant="outline">
          Refill Wallet
        </Button>
        <Button @click="showWithdrawModal = true">
          Withdraw Funds
        </Button>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
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

    <!-- Modals -->
    <ActivateWalletModal
      v-model:open="showActivateModal"
      @activated="handleWalletActivated"
    />

    <RefillWalletModal
      v-if="wallet?.is_activated"
      v-model:open="showRefillModal"
      @refilled="handleRefillComplete"
    />

    <WithdrawFundsModal
      v-if="wallet?.is_activated"
      v-model:open="showWithdrawModal"
      :available-balance="wallet?.balance"
      @withdrawn="handleWithdrawComplete"
    />
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '../DashboardLayout.vue'
import StatCard from '../Components/StatCard.vue'
import TransactionItem from '../Components/TransactionItem.vue'
import ActivateWalletModal from '../../../Components/ui/modals/ActivateWalletModal.vue'
import RefillWalletModal from '../../../Components/ui/Modals/RefillWalletModal.vue'
import WithdrawFundsModal from '../../../Components/ui/Modals/WithdrawFundsModal.vue'
import { Button } from '@/Components/ui/button'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  user: Object,
  wallet: Object,
  stats: Object
})

const showActivateModal = ref(false)
const showRefillModal = ref(false)
const showWithdrawModal = ref(false)

const handleWalletActivated = () => {
  router.visit(route('seller.wallet.index'), {
    preserveScroll: true,
    onSuccess: () => {
      showActivateModal.value = false
    }
  })
}

const handleRefillComplete = () => {
  router.reload({ preserveScroll: true })
  showRefillModal.value = false
}

const handleWithdrawComplete = () => {
  router.reload({ preserveScroll: true })
  showWithdrawModal.value = false
}
</script>
