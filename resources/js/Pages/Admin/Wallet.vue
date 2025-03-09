<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Platform Fees Management -->
      <Card>
        <CardHeader>
          <CardTitle>Platform Fees Settings</CardTitle>
          <CardDescription>Configure platform-wide fee settings</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="updatePlatformFees" class="space-y-4">
            <div class="flex items-center gap-4">
              <div class="w-64">
                <Label>Listing Fee Percentage</Label>
                <div class="flex items-center gap-2">
                  <Input 
                    type="number" 
                    v-model="platformFees.listingFee" 
                    min="0" 
                    max="100" 
                    step="0.1" 
                  />
                  <span>%</span>
                </div>
              </div>
              <Button type="submit" :disabled="isSubmitting">
                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Wallet Insights -->
      <WalletChart :data="stats" />

      <!-- Seller Wallet Overview -->
      <WalletTable 
        :sellers="sellers" 
        @view-transactions="viewTransactions"
        @adjust-balance="showAdjustmentModal" 
      />

      <!-- Refund Requests -->
      <RefundTable 
        :requests="refundRequests"
        @approve="approveRefund"
        @reject="rejectRefund"
      />

      <!-- Transactions Log -->
      <Card>
        <CardHeader class="space-y-4">
          <div class="flex items-center justify-between">
            <CardTitle>Wallet Transactions</CardTitle>
            <div class="flex items-center gap-4">
              <Input 
                v-model="search" 
                placeholder="Search by username..."
                class="w-64"
              />
              <Select v-model="filters.type">
                <option value="">All Types</option>
                <option value="listing">Listing Fee</option>
                <option value="refund">Refund</option>
                <option value="escrow">Escrow Release</option>
              </Select>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>ID</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Amount</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <!-- ...transaction rows... -->
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <!-- Balance Adjustment Modal -->
    <WalletModal 
      :show="showModal"
      :seller="selectedSeller"
      @close="showModal = false"
      @submit="handleBalanceAdjustment"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import WalletTable from '@/Pages/Admin/Components/WalletTable.vue'
import RefundTable from '@/Pages/Admin/Components/RefundTable.vue'
import WalletModal from '@/Pages/Admin/Components/WalletModal.vue'
import WalletChart from './Components/WalletChart.vue'
import { useToast } from "@/Components/ui/toast/use-toast"
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card"
import { Input } from "@/Components/ui/input"
import { Select } from "@/Components/ui/select"
import { Button } from "@/Components/ui/button"
import { Label } from "@/Components/ui/label"
import { Table, TableHeader, TableBody, TableRow, TableHead } from "@/Components/ui/table"

const { toast } = useToast()

defineProps({
  sellers: Array,
  refundRequests: Array,
  transactions: Array,
  stats: Object
})

const platformFees = ref({
  listingFee: 2.0
})

const search = ref('')
const filters = ref({
  type: '',
  status: '',
  dateRange: null
})

const showModal = ref(false)
const selectedSeller = ref(null)
const isSubmitting = ref(false)

// Methods
const updatePlatformFees = async () => {
  isSubmitting.value = true
  try {
    await router.post(route('admin.wallet.update-fees'), platformFees.value)
    toast({
      title: "Success",
      description: "Platform fees updated successfully",
    })
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to update platform fees",
      variant: "destructive"
    })
  } finally {
    isSubmitting.value = false
  }
}

const showAdjustmentModal = (seller) => {
  selectedSeller.value = seller
  showModal.value = true
}

const handleBalanceAdjustment = async (data) => {
  try {
    await router.post(route('admin.wallet.adjust-balance'), data)
    showModal.value = false
    toast({
      title: "Success",
      description: "Wallet balance adjusted successfully",
    })
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to adjust wallet balance",
      variant: "destructive"
    })
  }
}

// ... additional methods for refund handling and transaction viewing
</script>
