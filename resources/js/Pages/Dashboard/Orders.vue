<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Search Bar -->
      <div class="w-full">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search orders..."
          class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
        />
      </div>

      <!-- Orders Tabs -->
      <Tabs default-value="all" class="w-full">
        <TabsList class="grid w-full grid-cols-8">
          <TabsTrigger value="all">
            All ({{ groupedOrders.all.length }})
          </TabsTrigger>
          <TabsTrigger value="pending">
            Pending ({{ groupedOrders.pending.length }})
          </TabsTrigger>
          <TabsTrigger value="accepted">
            Accepted ({{ groupedOrders.accepted.length }})
          </TabsTrigger>
          <TabsTrigger value="scheduled">
            Scheduled ({{ groupedOrders.scheduled.length }})
          </TabsTrigger>
          <TabsTrigger value="delivered">
            Delivered ({{ groupedOrders.delivered.length }})
          </TabsTrigger>
          <TabsTrigger value="completed">
            Completed ({{ groupedOrders.completed.length }})
          </TabsTrigger>
          <TabsTrigger value="cancelled">
            Cancelled ({{ groupedOrders.cancelled.length }})
          </TabsTrigger>
          <TabsTrigger value="disputed">
            Disputed ({{ groupedOrders.disputed.length }})
          </TabsTrigger>
        </TabsList>

        <template v-for="(orders, status) in groupedOrders" :key="status">
          <TabsContent :value="status">
            <div class="grid gap-4">
              <Card v-for="order in orders" :key="order.id" class="w-full">
                <CardHeader>
                  <CardTitle class="flex justify-between">
                    <span>Order #{{ order.id }}</span>
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusColor(order.status)]">
                      {{ order.status }}
                    </span>
                  </CardTitle>
                  <CardDescription>
                    Placed on {{ formatDateTime(order.created_at) }}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="space-y-4">
                    <!-- Order Items -->
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center">
                      <div>
                        <h4 class="font-medium">{{ item.product.name }}</h4>
                        <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                      </div>
                      <div class="text-right">
                        <p class="font-semibold">{{ formatPrice(item.price * item.quantity) }}</p>
                      </div>
                    </div>
                    
                    <!-- Meetup Details if available -->
                    <div v-if="order.meetup_location" class="mt-4 p-4 bg-gray-50 rounded-lg">
                      <h4 class="font-medium">Meetup Details</h4>
                      <p>Location: {{ order.meetup_location.name }}</p>
                      <p v-if="order.meetup_schedule">Schedule: {{ formatDateTime(order.meetup_schedule) }}</p>
                      <p v-if="order.meetup_confirmation_code" class="text-sm text-gray-600">
                        Confirmation Code: {{ order.meetup_confirmation_code }}
                      </p>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                  <div class="font-semibold">Total: {{ formatPrice(order.sub_total) }}</div>
                  <div class="space-x-2">
                    <Button variant="outline" @click="viewOrderDetails(order)">
                      View Details
                    </Button>
                    <Button 
                      v-if="order.status === 'Pending'"
                      variant="destructive"
                      @click="$inertia.patch(route('orders.cancel', order.id))"
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

    <!-- Order Details Dialog -->
    <Dialog :open="showOrderDetails" @update:open="closeOrderDetails">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
        <UserOrderDetails
          v-if="selectedOrder"
          :order="selectedOrder"
          :user="user"
          @close="closeOrderDetails"
        />
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
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
import UserOrderDetails from './UserOrderDetails.vue'
import { Dialog, DialogContent } from '@/Components/ui/dialog'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps({
  user: Object,
  stats: Object,
  orders: {
    type: Object,
    required: true
  }
})

const searchQuery = ref('')
const selectedOrder = ref(null)
const showOrderDetails = ref(false)
const { toast } = useToast()

const orderStatuses = [
  'All',
  'Pending',
  'Accepted',
  'Meetup Scheduled',
  'Delivered',
  'Completed',
  'Cancelled',
  'Disputed'
]

const groupedOrders = computed(() => {
  const groups = {
    all: props.orders.data,
    pending: props.orders.data.filter(o => o.status === 'Pending'),
    accepted: props.orders.data.filter(o => o.status === 'Accepted'),
    scheduled: props.orders.data.filter(o => o.status === 'Meetup Scheduled'),
    delivered: props.orders.data.filter(o => o.status === 'Delivered'),
    completed: props.orders.data.filter(o => o.status === 'Completed'),
    cancelled: props.orders.data.filter(o => o.status === 'Cancelled'),
    disputed: props.orders.data.filter(o => o.status === 'Disputed'),
  }
  
  // Filter by search query if present
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    Object.keys(groups).forEach(key => {
      groups[key] = groups[key].filter(order => 
        order.id.toString().includes(query) ||
        order.items.some(item => item.product.name.toLowerCase().includes(query))
      )
    })
  }
  
  return groups
})

const formatDate = (date) => new Date(date).toLocaleDateString()
const formatPrice = (price) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price)

// Add new formatting functions
const getStatusColor = (status) => {
  const colors = {
    'Pending': 'bg-yellow-100 text-yellow-800',
    'Accepted': 'bg-blue-100 text-blue-800',
    'Meetup Scheduled': 'bg-purple-100 text-purple-800',
    'Delivered': 'bg-green-100 text-green-800',
    'Completed': 'bg-green-200 text-green-900',
    'Cancelled': 'bg-red-100 text-red-800',
    'Disputed': 'bg-orange-100 text-orange-800'
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

// Add these to your script setup
const viewOrderDetails = (order) => {
  selectedOrder.value = order
  showOrderDetails.value = true
}

const closeOrderDetails = () => {
  selectedOrder.value = null
  showOrderDetails.value = false
}
</script>

<style scoped>
.bg-card {
  background-color: white;
}
</style>

