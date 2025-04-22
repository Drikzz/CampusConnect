<template>
  <AdminLayout>
    <div class="space-y-6 md:space-y-8">
      <!-- Page Header with Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-foreground">Wallet Management</h2>
          <p class="text-muted-foreground mt-1">Manage platform fees, wallet balances, and transactions</p>
        </div>
        
        <div class="flex flex-wrap gap-2">
          <Button 
            variant="outline" 
            class="flex items-center gap-2"
            @click="router.visit(route('admin.wallet-requests'))"
          >
            <ClockIcon class="h-4 w-4" />
            <span>Wallet Requests</span>
            <Badge variant="secondary" class="ml-1">{{ stats?.pending_count || 0 }}</Badge>
          </Button>
          
          <Button 
            variant="default" 
            class="flex items-center gap-2"
            @click="refreshData"
            :disabled="isRefreshing"
          >
            <RefreshCwIcon class="h-4 w-4" :class="{'animate-spin': isRefreshing}" />
            <span>{{ isRefreshing ? 'Refreshing...' : 'Refresh' }}</span>
          </Button>
        </div>
      </div>
      
      <!-- Platform Fees Management -->
      <Card className="border border-muted transition-all duration-200 hover:shadow-md">
        <CardHeader className="pb-2">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <CardTitle class="text-lg md:text-xl flex items-center gap-2">
                <SettingsIcon class="h-5 w-5 text-muted-foreground" />
                Platform Fees Settings
              </CardTitle>
              <CardDescription class="text-sm text-muted-foreground">
                Configure platform-wide fee settings
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="updatePlatformFees" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
              <div class="w-full sm:w-64 space-y-2">
                <Label>Listing Fee Percentage</Label>
                <div class="flex items-center gap-2">
                  <Input 
                    type="number" 
                    v-model="platformFees.listingFee" 
                    min="0" 
                    max="100" 
                    step="0.1"
                    class="w-full"
                  />
                  <span class="text-muted-foreground">%</span>
                </div>
              </div>
              <Button type="submit" :disabled="isSubmitting" class="px-6">
                <SaveIcon class="h-4 w-4 mr-2" v-if="!isSubmitting" />
                <Loader2Icon class="h-4 w-4 mr-2 animate-spin" v-else />
                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Wallet Insights with enhanced styling -->
      <div class="bg-card rounded-lg shadow border border-muted overflow-hidden transition-all duration-200 hover:shadow-md">
        <div class="p-6">
          <h3 class="text-lg font-medium flex items-center gap-2 mb-4">
            <BarChart3Icon class="h-5 w-5 text-primary" />
            Wallet Insights
          </h3>
          <WalletChart :data="stats" />
        </div>
      </div>

      <!-- Seller Wallet Overview -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium flex items-center gap-2">
            <UsersIcon class="h-5 w-5 text-primary" />
            <span>Seller Wallet Overview</span>
          </h3>
          
          <Input 
            v-model="sellerSearch" 
            placeholder="Search sellers..."
            class="w-full max-w-xs"
          />
        </div>
        
        <Card className="border border-muted transition-all duration-200 hover:shadow-md">
          <CardContent className="p-0">
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Seller Name</TableHead>
                    <TableHead>Seller Code</TableHead>
                    <TableHead>Balance</TableHead>
                    <TableHead>Total Listings</TableHead>
                    <TableHead>Fees Collected</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="seller in filteredSellers" :key="seller.id" class="hover:bg-muted/50">
                    <TableCell>
                      <div class="flex items-center gap-3">
                        <UserCircleIcon class="h-8 w-8 text-muted-foreground" />
                        <div>
                          <p class="font-medium">{{ seller.name }}</p>
                          <p class="text-xs text-muted-foreground">{{ seller.email || 'No email' }}</p>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline" class="font-mono">
                        {{ seller.seller_code }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <span class="font-medium text-primary">
                        ₱{{ formatNumber(seller.balance) }}
                      </span>
                    </TableCell>
                    <TableCell>{{ seller.total_listings }}</TableCell>
                    <TableCell>₱{{ formatNumber(seller.fees_collected) }}</TableCell>
                    <TableCell class="text-right">
                      <div class="flex justify-end gap-2">
                        <Button 
                          variant="outline" 
                          size="sm" 
                          @click="viewTransactions(seller)"
                          class="flex items-center gap-1"
                        >
                          <HistoryIcon class="h-3 w-3" />
                          View History
                        </Button>
                        <Button 
                          variant="outline" 
                          size="sm" 
                          @click="showAdjustmentModal(seller)"
                          class="flex items-center gap-1"
                        >
                          <EditIcon class="h-3 w-3" />
                          Adjust
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                  
                  <!-- Empty state -->
                  <TableRow v-if="!filteredSellers.length">
                    <TableCell colspan="6" class="py-10 text-center">
                      <div class="flex flex-col items-center justify-center text-muted-foreground">
                        <SearchXIcon class="h-12 w-12 mb-3" />
                        <h3 class="font-medium text-lg mb-1">No sellers found</h3>
                        <p class="text-sm">
                          {{ sellers.length ? 'Try adjusting your search' : 'No seller wallets available yet' }}
                        </p>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Refund Requests -->
      <div class="space-y-2">
        <h3 class="text-lg font-medium flex items-center gap-2">
          <ArrowLeftRightIcon class="h-5 w-5 text-primary" />
          <span>Refund Requests</span>
          <Badge v-if="refundRequests?.length" variant="secondary">{{ refundRequests.length }}</Badge>
        </h3>
        
        <Card className="border border-muted transition-all duration-200 hover:shadow-md">
          <CardContent className="p-0">
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Order ID</TableHead>
                    <TableHead>Customer</TableHead>
                    <TableHead>Seller</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Reason</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="request in refundRequests" :key="request.id" class="hover:bg-muted/50">
                    <TableCell>
                      <span class="font-mono">#{{ request.order_id }}</span>
                    </TableCell>
                    <TableCell>
                      <div class="flex items-center gap-3">
                        <UserCircleIcon class="h-7 w-7 text-muted-foreground" />
                        <span>{{ request.customer_name }}</span>
                      </div>
                    </TableCell>
                    <TableCell>{{ request.seller_name }}</TableCell>
                    <TableCell>
                      <span class="font-medium">₱{{ formatNumber(request.amount) }}</span>
                    </TableCell>
                    <TableCell>
                      <!-- Simple tooltip using title attribute -->
                      <span 
                        class="line-clamp-1 cursor-help" 
                        :title="request.reason"
                      >
                        {{ request.reason }}
                      </span>
                    </TableCell>
                    <TableCell class="text-right">
                      <div class="flex justify-end gap-2">
                        <Button 
                          variant="success" 
                          size="sm" 
                          @click="approveRefund(request.id)"
                        >
                          <CheckIcon class="h-3 w-3 mr-1" />
                          Approve
                        </Button>
                        <Button 
                          variant="destructive" 
                          size="sm" 
                          @click="rejectRefund(request.id)"
                        >
                          <XIcon class="h-3 w-3 mr-1" />
                          Reject
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                  
                  <!-- Empty state -->
                  <TableRow v-if="!refundRequests.length">
                    <TableCell colspan="6" class="py-8 text-center">
                      <div class="flex flex-col items-center justify-center text-muted-foreground">
                        <CheckCircleIcon class="h-12 w-12 mb-3" />
                        <p>No pending refund requests</p>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </div>
      
      <!-- Transactions Log -->
      <div class="space-y-2">
        <h3 class="text-lg font-medium flex items-center gap-2">
          <HistoryIcon class="h-5 w-5 text-primary" />
          <span>Wallet Transactions</span>
        </h3>
        
        <Card className="border border-muted transition-all duration-200 hover:shadow-md">
          <CardHeader className="pb-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div class="flex items-center gap-2">
                <div class="relative flex-1">
                  <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input 
                    v-model="search" 
                    placeholder="Search by user or transaction..."
                    class="pl-9 min-w-[250px]"
                  />
                </div>
              </div>
              <div class="flex flex-wrap gap-2">
                <Select v-model="filters.type" class="w-full sm:w-auto">
                  <option value="">All Types</option>
                  <option value="listing">Listing Fee</option>
                  <option value="refund">Refund</option>
                  <option value="escrow">Escrow Release</option>
                </Select>
                <Select v-model="filters.status" class="w-full sm:w-auto">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="completed">Completed</option>
                  <option value="rejected">Rejected</option>
                </Select>
              </div>
            </div>
          </CardHeader>
          <CardContent className="p-0">
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-[80px]">ID</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="transaction in filteredTransactions" :key="transaction.id" class="hover:bg-muted/50">
                    <TableCell class="font-mono text-xs">{{ transaction.id }}</TableCell>
                    <TableCell>
                      <div class="flex items-center gap-2">
                        <UserCircleIcon class="h-7 w-7 text-muted-foreground" />
                        <span class="font-medium">{{ transaction.user_name }}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="getTypeVariant(transaction.type)">
                        {{ transaction.type }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <span class="font-medium" :class="{
                        'text-green-600': transaction.amount > 0,
                        'text-red-600': transaction.amount < 0
                      }">
                        ₱{{ formatNumber(transaction.amount) }}
                      </span>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="getStatusVariant(transaction.status)">
                        {{ transaction.status }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      {{ formatDateShort(transaction.created_at) }}
                    </TableCell>
                    <TableCell class="text-right">
                      <Button variant="ghost" size="icon" @click="viewTransactionDetails(transaction)">
                        <EyeIcon class="h-4 w-4" />
                      </Button>
                    </TableCell>
                  </TableRow>
                  
                  <!-- Empty state -->
                  <TableRow v-if="!filteredTransactions.length">
                    <TableCell colspan="7" class="py-10 text-center">
                      <div class="flex flex-col items-center justify-center text-muted-foreground">
                        <SearchXIcon class="h-12 w-12 mb-3" />
                        <h3 class="font-medium text-lg mb-1">No transactions found</h3>
                        <p class="text-sm">
                          {{ transactions.length ? 'Try adjusting your search or filters' : 'No transactions available yet' }}
                        </p>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
    
    <!-- Balance Adjustment Modal -->
    <WalletModal 
      :show="showModal"
      :seller="selectedSeller"
      @close="showModal = false"
      @submit="handleBalanceAdjustment"
    />
    
    <!-- Transaction Details Dialog -->
    <Dialog :open="showDetailsDialog" @update:open="showDetailsDialog = false">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Transaction Details</DialogTitle>
          <DialogDescription>
            Complete information about this transaction
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="selectedTransaction" class="space-y-4">
          <!-- Transaction Header -->
          <div class="flex items-center justify-between">
            <Badge :variant="getStatusVariant(selectedTransaction.status)">
              {{ selectedTransaction.status }}
            </Badge>
            <Badge :variant="getTypeVariant(selectedTransaction.type)">
              {{ selectedTransaction.type }}
            </Badge>
          </div>
          
          <!-- Transaction Details -->
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Transaction ID</p>
                <p class="font-mono text-sm">{{ selectedTransaction.id }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Amount</p>
                <p class="font-medium" :class="{
                  'text-green-600': selectedTransaction.amount > 0,
                  'text-red-600': selectedTransaction.amount < 0
                }">₱{{ formatNumber(selectedTransaction.amount) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Date Created</p>
                <p>{{ formatDate(selectedTransaction.created_at) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Last Updated</p>
                <p>{{ formatDate(selectedTransaction.updated_at) }}</p>
              </div>
            </div>
            
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">User</p>
              <div class="flex items-center gap-2">
                <UserCircleIcon class="h-8 w-8 text-muted-foreground" />
                <div>
                  <p class="font-medium">{{ selectedTransaction.user_name }}</p>
                  <p class="text-xs text-muted-foreground">{{ selectedTransaction.user_email }}</p>
                </div>
              </div>
            </div>
            
            <!-- Optional Fields -->
            <div v-if="selectedTransaction.reference_id" class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">Reference ID</p>
              <p class="font-mono">{{ selectedTransaction.reference_id }}</p>
            </div>
            
            <div v-if="selectedTransaction.description" class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">Description</p>
              <p>{{ selectedTransaction.description }}</p>
            </div>
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import WalletModal from '@/Pages/Admin/Components/WalletModal.vue'
import WalletChart from './Components/WalletChart.vue'
import { useToast } from "@/Components/ui/toast/use-toast"

// UI Components
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card"
import { Input } from "@/Components/ui/input"
import { Select } from "@/Components/ui/select"
import { Button } from "@/Components/ui/button"
import { Label } from "@/Components/ui/label"
import { Badge } from "@/Components/ui/badge"
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/Components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription } from "@/Components/ui/dialog"

// Icons
import { 
  UserCircleIcon, HistoryIcon, SettingsIcon, ClockIcon, 
  CheckCircleIcon, RefreshCwIcon, SaveIcon, Loader2Icon, 
  UsersIcon, EditIcon, EyeIcon, CheckIcon, XIcon,
  SearchIcon, SearchXIcon, BarChart3Icon, ArrowLeftRightIcon
} from 'lucide-vue-next'

const { toast } = useToast()

const props = defineProps({
  sellers: {
    type: Array,
    default: () => []
  },
  refundRequests: {
    type: Array,
    default: () => []
  },
  transactions: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// UI state
const platformFees = ref({
  listingFee: props.stats?.listing_fee || 2.0
})

const search = ref('')
const sellerSearch = ref('')
const filters = ref({
  type: '',
  status: '',
  dateRange: null
})

const showModal = ref(false)
const showDetailsDialog = ref(false)
const selectedSeller = ref(null)
const selectedTransaction = ref(null)
const isSubmitting = ref(false)
const isRefreshing = ref(false)

// Filtered sellers based on search
const filteredSellers = computed(() => {
  if (!props.sellers) return []
  
  if (!sellerSearch.value) return props.sellers
  
  const query = sellerSearch.value.toLowerCase()
  return props.sellers.filter(seller => 
    seller.name.toLowerCase().includes(query) || 
    (seller.email && seller.email.toLowerCase().includes(query)) ||
    seller.seller_code.toLowerCase().includes(query)
  )
})

// Filtered transactions based on search and filters
const filteredTransactions = computed(() => {
  if (!props.transactions) return []
  
  return props.transactions.filter(transaction => {
    // Filter by search
    const searchMatch = !search.value || 
      transaction.user_name.toLowerCase().includes(search.value.toLowerCase()) ||
      (transaction.description && transaction.description.toLowerCase().includes(search.value.toLowerCase()))
    
    // Filter by type
    const typeMatch = !filters.value.type || 
      transaction.type.toLowerCase() === filters.value.type.toLowerCase()
    
    // Filter by status
    const statusMatch = !filters.value.status || 
      transaction.status.toLowerCase() === filters.value.status.toLowerCase()
      
    return searchMatch && typeMatch && statusMatch
  })
})

// Format utilities
const formatNumber = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0)
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

const formatDateShort = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Get badge variant based on status
const getStatusVariant = (status) => {
  status = status.toLowerCase()
  switch (status) {
    case 'pending': return 'warning'
    case 'in_process': return 'info'
    case 'completed': return 'success'
    case 'rejected': return 'destructive'
    default: return 'secondary'
  }
}

// Get badge variant based on transaction type
const getTypeVariant = (type) => {
  type = type.toLowerCase()
  switch (type) {
    case 'listing': return 'secondary'
    case 'refund': return 'warning'
    case 'escrow': return 'info'
    case 'refill': return 'success'
    case 'withdrawal': return 'destructive'
    default: return 'outline'
  }
}

// Methods
const updatePlatformFees = async () => {
  isSubmitting.value = true
  try {
    await router.post(route('admin.wallet.update-fees'), platformFees.value)
    toast({
      title: "Success",
      description: "Platform fees updated successfully"
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

const refreshData = () => {
  isRefreshing.value = true
  router.reload({
    onFinish: () => {
      isRefreshing.value = false
    }
  })
}

const showAdjustmentModal = (seller) => {
  selectedSeller.value = seller
  showModal.value = true
}

const viewTransactionDetails = (transaction) => {
  selectedTransaction.value = transaction
  showDetailsDialog.value = true
}

const viewTransactions = (seller) => {
  router.visit(route('admin.seller-transactions', seller.id))
}

const handleBalanceAdjustment = async (data) => {
  try {
    await router.post(route('admin.wallet.adjust-balance'), data)
    showModal.value = false
    toast({
      title: "Success",
      description: "Wallet balance adjusted successfully"
    })
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to adjust wallet balance",
      variant: "destructive"
    })
  }
}

const approveRefund = (id) => {
  if (confirm('Are you sure you want to approve this refund request?')) {
    router.post(route('admin.refunds.approve', id), {}, {
      onSuccess: () => {
        toast({
          title: "Success",
          description: "Refund approved successfully"
        })
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Failed to approve refund",
          variant: "destructive"
        })
      }
    })
  }
}

const rejectRefund = (id) => {
  const reason = prompt('Enter reason for rejecting this refund (optional):')
  router.post(route('admin.refunds.reject', id), { reason }, {
    onSuccess: () => {
      toast({
        title: "Success", 
        description: "Refund rejected successfully"
      })
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to reject refund",
        variant: "destructive"
      })
    }
  })
}
</script>
