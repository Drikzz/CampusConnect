<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header with refresh button -->
      <div class="flex items-center justify-between">
        <h2 class="text-xl md:text-2xl font-bold text-foreground">Wallet Requests</h2>
        <Button 
          variant="outline" 
          size="sm"
          @click="refreshData"
          :disabled="isRefreshing"
          class="flex items-center gap-2"
        >
          <RefreshCwIcon class="h-4 w-4" :class="{'animate-spin': isRefreshing}" />
          <span>{{ isRefreshing ? 'Refreshing...' : 'Refresh' }}</span>
        </Button>
      </div>
      
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Stats Cards -->
        <div v-for="(stat, index) in stats" :key="index" class="bg-card shadow rounded-lg p-4 border border-muted">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-muted-foreground">{{ stat.title }}</p>
              <h3 class="text-2xl font-bold mt-1">{{ stat.value }}</h3>
            </div>
            <div :class="`p-3 rounded-full bg-${stat.color}-50`">
              <component :is="getIcon(stat.icon)" class="h-5 w-5" :class="`text-${stat.color}-500`" />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Filter controls -->
      <Card className="p-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input 
              v-model="searchQuery" 
              placeholder="Search by seller name or code..." 
              class="pl-9"
            />
          </div>
          <Select v-model="statusFilter" class="w-full sm:w-48">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="in_process">In Process</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </Select>
          <Select v-model="typeFilter" class="w-full sm:w-48">
            <option value="all">All Types</option>
            <option value="refill">Refill</option>
            <option value="withdrawal">Withdrawal</option>
            <option value="verification">Verification</option>
          </Select>
        </div>
      </Card>
      
      <!-- Transactions Table -->
      <div class="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Date</TableHead>
              <TableHead>Seller</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="transaction in filteredTransactions" :key="transaction.id" class="hover:bg-muted/50">
              <TableCell>
                <div>
                  <div>{{ formatDateShort(transaction.created_at) }}</div>
                  <div class="text-xs text-muted-foreground">{{ formatTime(transaction.created_at) }}</div>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <UserCircleIcon class="h-8 w-8 text-muted-foreground" />
                  <div>
                    <div class="font-medium">{{ transaction.wallet?.user?.first_name || 'N/A' }} {{ transaction.wallet?.user?.last_name || '' }}</div>
                    <div class="text-xs text-muted-foreground">{{ transaction.seller_code || 'No code' }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <Badge :variant="getTypeVariant(transaction)">
                  {{ getTypeLabel(transaction) }}
                </Badge>
              </TableCell>
              <TableCell>
                <span class="font-medium" :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                  ₱{{ formatNumber(transaction.amount || 0) }}
                </span>
              </TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(transaction.status)">
                  {{ formatStatus(transaction.status) }}
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="outline" size="sm" @click="viewTransactionDetails(transaction)">
                    <EyeIcon class="h-3 w-3 mr-1" />
                    View
                  </Button>
                  <Button 
                    v-if="transaction.status === 'pending'"
                    variant="success" 
                    size="sm" 
                    @click="confirmAction('approve', transaction.id)"
                  >
                    <CheckIcon class="h-3 w-3 mr-1" />
                    Approve
                  </Button>
                  <Button 
                    v-if="transaction.status === 'pending'"
                    variant="destructive" 
                    size="sm" 
                    @click="confirmAction('reject', transaction.id)"
                  >
                    <XIcon class="h-3 w-3 mr-1" />
                    Reject
                  </Button>
                  <Button 
                    v-if="transaction.status === 'in_process' && transaction.reference_type === 'withdrawal'"
                    variant="outline" 
                    size="sm"
                    class="text-blue-600 border-blue-200 hover:bg-blue-50" 
                    @click="showCompleteWithdrawalDialog(transaction.id)"
                  >
                    <CheckSquareIcon class="h-3 w-3 mr-1" />
                    Complete
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            
            <!-- Empty state -->
            <TableRow v-if="filteredTransactions.length === 0">
              <TableCell colspan="6" class="py-10 text-center">
                <div class="flex flex-col items-center justify-center text-muted-foreground">
                  <SearchXIcon class="h-12 w-12 mb-3" />
                  <h3 class="font-medium text-lg mb-1">No requests found</h3>
                  <p class="text-sm">
                    {{ transactions.length ? 'Try adjusting your search or filters' : 'No wallet requests available yet' }}
                  </p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
    
    <!-- Transaction Details Dialog -->
    <Dialog :open="showDetailsDialog" @update:open="showDetailsDialog = false">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Request Details</DialogTitle>
          <DialogDescription>
            Complete information about this wallet request
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="selectedTransaction" class="space-y-4">
          <!-- Status badges -->
          <div class="flex items-center justify-between">
            <Badge :variant="getStatusVariant(selectedTransaction.status)">
              {{ formatStatus(selectedTransaction.status) }}
            </Badge>
            <Badge :variant="getTypeVariant(selectedTransaction)">
              {{ getTypeLabel(selectedTransaction) }}
            </Badge>
          </div>
          
          <!-- Basic info - Inline implementation instead of InfoGrid component -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">Transaction ID</p>
              <p class="font-mono text-sm">{{ selectedTransaction.id }}</p>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">Amount</p>
              <p class="font-medium" :class="{
                'text-green-600': selectedTransaction.type === 'credit',
                'text-red-600': selectedTransaction.type === 'debit'
              }">₱{{ formatNumber(selectedTransaction.amount || 0) }}</p>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">Date Requested</p>
              <p>{{ formatDate(selectedTransaction.created_at) }}</p>
            </div>
            <div class="space-y-1" v-if="selectedTransaction.processed_at">
              <p class="text-sm font-medium text-muted-foreground">Processed Date</p>
              <p>{{ formatDate(selectedTransaction.processed_at) }}</p>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">Seller Code</p>
              <p>{{ selectedTransaction.seller_code || 'N/A' }}</p>
            </div>
            <div class="space-y-1" v-if="selectedTransaction.reference_type">
              <p class="text-sm font-medium text-muted-foreground">Request Type</p>
              <p>{{ getTypeLabel(selectedTransaction) }}</p>
            </div>
            <div class="space-y-1" v-if="selectedTransaction.phone_number">
              <p class="text-sm font-medium text-muted-foreground">Phone Number</p>
              <p>{{ selectedTransaction.phone_number }}</p>
            </div>
            <div class="space-y-1" v-if="selectedTransaction.account_name">
              <p class="text-sm font-medium text-muted-foreground">Account Name</p>
              <p>{{ selectedTransaction.account_name }}</p>
            </div>
          </div>
          
          <!-- Receipt image if available -->
          <div v-if="selectedTransaction.receipt_path" class="space-y-2">
            <p class="text-sm font-medium text-muted-foreground">Receipt</p>
            <img 
              :src="'/storage/' + selectedTransaction.receipt_path" 
              alt="Receipt" 
              class="max-h-[200px] w-auto mx-auto object-contain border rounded-md" 
            />
          </div>
        </div>
        
        <!-- Action buttons -->
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Close</Button>
          <div v-if="selectedTransaction?.status === 'pending'" class="flex gap-2">
            <Button variant="success" @click="confirmAction('approve', selectedTransaction.id)">
              Approve
            </Button>
            <Button variant="destructive" @click="confirmAction('reject', selectedTransaction.id)">
              Reject
            </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    
    <!-- Simple Confirmation Dialog (inline implementation instead of using a separate component) -->
    <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ confirmDialogTitle }}</DialogTitle>
          <DialogDescription>
            {{ confirmDialogMessage }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showConfirmDialog = false">Cancel</Button>
          <Button :class="confirmActionButtonClass" @click="executeConfirmedAction">
            {{ confirmActionButtonText }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useToast } from "@/Components/ui/toast/use-toast"

// UI components
import { Card } from "@/Components/ui/card"
import { Input } from "@/Components/ui/input"
import { Select } from "@/Components/ui/select"
import { Button } from "@/Components/ui/button"
import { Badge } from "@/Components/ui/badge"
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/Components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription } from "@/Components/ui/dialog"

// Icons
import { 
  UserCircleIcon, ClockIcon, CheckCircleIcon, WalletIcon, 
  EyeIcon, CheckIcon, XIcon, CheckSquareIcon, 
  RefreshCwIcon, SearchIcon, SearchXIcon 
} from 'lucide-vue-next'

const { toast } = useToast()

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  }
})

// UI state
const isRefreshing = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const typeFilter = ref('all')
const showDetailsDialog = ref(false)
const selectedTransaction = ref(null)

// Confirmation dialog state
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmActionType = ref('')
const confirmActionId = ref(null)
const confirmActionButtonText = ref('')
const confirmActionButtonClass = ref('')

// Computed stats for displaying in cards
const stats = computed(() => [
  {
    title: 'Pending Requests',
    value: pendingCount.value,
    icon: 'ClockIcon',
    color: 'yellow'
  },
  {
    title: 'Processed Today',
    value: todayProcessedCount.value,
    icon: 'CheckCircleIcon',
    color: 'green'
  },
  {
    title: 'Total Volume',
    value: `₱${totalAmount.value}`,
    icon: 'WalletIcon',
    color: 'primary'
  }
])

// Helper function to get icon component
const getIcon = (name) => {
  const icons = {
    ClockIcon,
    CheckCircleIcon,
    WalletIcon
  }
  return icons[name] || null
}

// Filtered transactions based on search and filters
const filteredTransactions = computed(() => {
  return props.transactions.filter(t => {
    // Build search string from relevant fields
    const searchString = [
      t.wallet?.user?.first_name,
      t.wallet?.user?.last_name,
      t.seller_code,
      t.reference_number
    ].filter(Boolean).join(' ').toLowerCase()
    
    return (
      (!searchQuery.value || searchString.includes(searchQuery.value.toLowerCase())) &&
      (statusFilter.value === 'all' || t.status === statusFilter.value) &&
      (typeFilter.value === 'all' || t.reference_type === typeFilter.value)
    )
  })
})

// Computed stats
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
  formatNumber(props.transactions
    ?.filter(t => t.status === 'completed')
    .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0) || 0)
)

// Format utilities
const formatNumber = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
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
    day: 'numeric'
  })
}

const formatTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleTimeString('en-PH', {
    hour: 'numeric',
    minute: '2-digit'
  })
}

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

// Get type label with more friendly names
const getTypeLabel = (transaction) => {
  const typeMap = {
    'refill': 'Refill',
    'withdrawal': 'Withdrawal',
    'verification': 'Verification'
  }
  return typeMap[transaction.reference_type] || transaction.reference_type || 'Unknown'
}

// Get badge variant based on transaction type
const getTypeVariant = (transaction) => {
  switch (transaction.reference_type) {
    case 'refill': return 'success'
    case 'withdrawal': return 'warning'
    case 'verification': return 'secondary'
    default: return 'outline'
  }
}

// Get badge variant based on status
const getStatusVariant = (status) => {
  switch (status) {
    case 'pending': return 'warning'
    case 'in_process': return 'info'
    case 'completed': return 'success'
    case 'rejected': return 'destructive'
    default: return 'secondary'
  }
}

// View transaction details
const viewTransactionDetails = (transaction) => {
  selectedTransaction.value = transaction
  showDetailsDialog.value = true
}

// Show confirm dialog for actions
const confirmAction = (type, id) => {
  confirmActionType.value = type
  confirmActionId.value = id
  
  if (type === 'approve') {
    confirmDialogTitle.value = 'Approve Request'
    confirmDialogMessage.value = 'Are you sure you want to approve this wallet request? This will process the transaction.'
    confirmActionButtonText.value = 'Approve'
    confirmActionButtonClass.value = 'bg-green-600 hover:bg-green-700 text-white'
  } else if (type === 'reject') {
    confirmDialogTitle.value = 'Reject Request'
    confirmDialogMessage.value = 'Are you sure you want to reject this wallet request? This action cannot be undone.'
    confirmActionButtonText.value = 'Reject'
    confirmActionButtonClass.value = 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
  }
  
  showConfirmDialog.value = true
}

// Execute confirmed action
const executeConfirmedAction = () => {
  showConfirmDialog.value = false
  
  if (confirmActionType.value === 'approve') {
    approveRequest(confirmActionId.value)
  } else if (confirmActionType.value === 'reject') {
    rejectRequest(confirmActionId.value)
  }
}

const approveRequest = (id) => {
  router.post(route('admin.wallet-requests.approve', id), {}, {
    onSuccess: () => {
      toast({
        title: "Request Approved",
        description: "The wallet request has been approved successfully.",
        variant: "success",
      })
      showDetailsDialog.value = false
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to approve the request. Please try again.",
        variant: "destructive",
      })
    }
  })
}

const rejectRequest = (id) => {
  const reason = prompt('Optional: Enter reason for rejection')
  
  router.post(route('admin.wallet-requests.reject', id), { reason }, {
    onSuccess: () => {
      toast({
        title: "Request Rejected",
        description: "The wallet request has been rejected.",
        variant: "info",
      })
      showDetailsDialog.value = false
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to reject the request. Please try again.",
        variant: "destructive",
      })
    }
  })
}

const showCompleteWithdrawalDialog = (id) => {
  const reference = prompt('Enter GCash reference number to complete this withdrawal:')
  if (reference) {
    router.post(route('admin.wallet-requests.complete-withdrawal', id), {
      gcash_reference: reference
    }, {
      onSuccess: () => {
        toast({
          title: "Withdrawal Completed",
          description: "The withdrawal has been marked as completed.",
          variant: "success",
        })
        showDetailsDialog.value = false
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Failed to complete the withdrawal. Please try again.",
          variant: "destructive",
        })
      }
    })
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
</script>
