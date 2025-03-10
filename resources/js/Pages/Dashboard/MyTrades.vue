<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Search Bar -->
      <div class="w-full">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search trades..."
          class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
        />
      </div>

      <!-- Trades Tabs -->
      <Tabs default-value="all" class="w-full">
        <TabsList class="grid w-full grid-cols-5">
          <TabsTrigger value="all">
            All ({{ groupedTrades.all.length }})
          </TabsTrigger>
          <TabsTrigger value="pending">
            Pending ({{ groupedTrades.pending.length }})
          </TabsTrigger>
          <TabsTrigger value="accepted">
            Accepted ({{ groupedTrades.accepted.length }})
          </TabsTrigger>
          <TabsTrigger value="rejected">
            Rejected ({{ groupedTrades.rejected.length }})
          </TabsTrigger>
          <TabsTrigger value="completed">
            Completed ({{ groupedTrades.completed.length }})
          </TabsTrigger>
        </TabsList>

        <template v-for="(trades, status) in groupedTrades" :key="status">
          <TabsContent :value="status">
            <div class="grid gap-4">
              <Card v-for="trade in trades" :key="trade.id" class="w-full">
                <CardHeader>
                  <CardTitle class="flex justify-between">
                    <span>Trade #{{ trade.id }}</span>
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusColor(trade.status)]">
                      {{ trade.status.charAt(0).toUpperCase() + trade.status.slice(1) }}
                    </span>
                  </CardTitle>
                  <CardDescription>
                    Offered on {{ formatDateTime(trade.created_at) }}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="space-y-4">
                    <!-- Product Being Traded For -->
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                      <div>
                        <h4 class="font-medium">Trading for: {{ trade.seller_product.name }}</h4>
                        <p class="text-sm text-gray-500">Owner: {{ trade.seller ? trade.seller.name : 'Unknown Seller' }}</p>
                      </div>
                      <div class="text-right">
                        <p class="font-semibold">Value: {{ formatPrice(trade.seller_product.price) }}</p>
                      </div>
                    </div>
                    
                    <!-- Offered Items -->
                    <div v-if="trade.offered_items && trade.offered_items.length" class="mt-2">
                      <h4 class="font-medium mb-2">Your Offered Items:</h4>
                      <div v-for="item in trade.offered_items" :key="item.id" class="flex justify-between items-center py-2 border-b last:border-0">
                        <div>
                          <h5 class="font-medium">{{ item.name }}</h5>
                          <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                        </div>
                        <div class="text-right">
                          <p class="font-semibold">{{ formatPrice(item.estimated_value) }}</p>
                        </div>
                      </div>
                      
                      <!-- Additional Cash -->
                      <div v-if="trade.additional_cash > 0" class="flex justify-between items-center mt-2 pt-2 border-t">
                        <p class="font-medium">Additional Cash</p>
                        <p class="font-semibold">{{ formatPrice(trade.additional_cash) }}</p>
                      </div>
                    </div>
                    
                    <!-- Notes -->
                    <div v-if="trade.notes" class="mt-4 p-4 bg-gray-50 rounded-lg">
                      <h4 class="font-medium">Notes</h4>
                      <p class="text-sm mt-1">{{ trade.notes }}</p>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                  <div class="font-semibold">
                    Total Value: {{ formatPrice(calculateTotalValue(trade)) }}
                  </div>
                  <div class="space-x-2">
                    <Button variant="outline" @click="viewTradeDetails(trade)">
                      View Details
                    </Button>
                    <Button 
                      v-if="trade.status === 'pending'"
                      variant="destructive"
                      @click="cancelTrade(trade.id)"
                    >
                      Cancel
                    </Button>
                  </div>
                </CardFooter>
              </Card>
            </div>
          </TabsContent>
        </template>
      </Tabs>
    </div>

    <!-- Trade Details Dialog -->
    <Dialog :open="showTradeDetails" @update:open="closeTradeDetails">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
        <div v-if="selectedTrade" class="space-y-6">
          <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold">Trade #{{ selectedTrade.id }}</h2>
            <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusColor(selectedTrade.status)]">
              {{ selectedTrade.status.charAt(0).toUpperCase() + selectedTrade.status.slice(1) }}
            </span>
          </div>
          
          <div class="space-y-4">
            <!-- More detailed view of the trade -->
            <!-- Close button at bottom -->
            <Button variant="outline" @click="closeTradeDetails">Close</Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/Components/ui/tabs'
import { Dialog, DialogContent } from '@/Components/ui/dialog'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps({
  user: Object,
  stats: Object,
  trades: {
    type: Object,
    required: true
  }
})

const searchQuery = ref('')
const selectedTrade = ref(null)
const showTradeDetails = ref(false)
const { toast } = useToast()

const groupedTrades = computed(() => {
  const groups = {
    all: props.trades.data || [],
    pending: (props.trades.data || []).filter(t => t.status === 'pending'),
    accepted: (props.trades.data || []).filter(t => t.status === 'accepted'),
    rejected: (props.trades.data || []).filter(t => t.status === 'rejected'),
    completed: (props.trades.data || []).filter(t => t.status === 'completed'),
  }
  
  // Filter by search query if present
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    Object.keys(groups).forEach(key => {
      groups[key] = groups[key].filter(trade => 
        trade.id.toString().includes(query) ||
        (trade.seller_product && trade.seller_product.name.toLowerCase().includes(query)) ||
        (trade.offered_items && trade.offered_items.some(item => item.name.toLowerCase().includes(query)))
      )
    })
  }
  
  return groups
})

const formatPrice = (price) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price)

const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'accepted': 'bg-blue-100 text-blue-800',
    'rejected': 'bg-red-100 text-red-800',
    'canceled': 'bg-gray-100 text-gray-800',
    'completed': 'bg-green-100 text-green-800'
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long', 
    day: 'numeric'
  });
};

const calculateTotalValue = (trade) => {
  // Calculate total value of offered items
  const itemsTotal = trade.offered_items ? trade.offered_items.reduce((sum, item) => {
    return sum + (item.estimated_value * item.quantity);
  }, 0) : 0;
  
  // Add additional cash
  return itemsTotal + (trade.additional_cash || 0);
};

const viewTradeDetails = (trade) => {
  selectedTrade.value = trade
  showTradeDetails.value = true
}

const closeTradeDetails = () => {
  selectedTrade.value = null
  showTradeDetails.value = false
}

const cancelTrade = (tradeId) => {
  router.patch(route('trades.cancel', tradeId), {}, {
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Trade offer canceled successfully",
      });
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to cancel trade offer",
        variant: "destructive"
      });
    }
  });
}
</script>

<style scoped>
.bg-card {
  background-color: white;
}
</style>

