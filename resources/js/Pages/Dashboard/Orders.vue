<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Search and Filter Controls -->
      <div class="flex gap-4 items-center mb-4">
        <div class="flex-1">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search orders..."
            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>
        <select
          v-model="statusFilter"
          class="px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
        >
          <option value="">All Statuses</option>
          <option v-for="status in orderStatuses" :key="status" :value="status">
            {{ status }}
          </option>
        </select>
        <select
          v-model="sortBy"
          class="px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
        >
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="total-asc">Total: Low to High</option>
          <option value="total-desc">Total: High to Low</option>
        </select>
      </div>

      <div class="rounded-lg border bg-card">
        <div class="relative w-full overflow-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Order #</TableHead>
                <TableHead>Items</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Meetup Details</TableHead>
                <TableHead>Payment</TableHead>
                <TableHead>Total</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="order in filteredOrders" :key="order.id">
                <TableCell class="font-medium">#{{ order.id }}</TableCell>
                <TableCell>
                  <div class="flex flex-col gap-1">
                    <div v-for="item in order.items" :key="item.id" class="text-sm">
                      <div class="flex justify-between items-center">
                        <span class="font-medium">{{ item.product.name }}</span>
                        <div class="text-gray-500">
                          <span class="font-semibold">{{ formatPrice(item.price) }}</span>
                          <span> × {{ item.quantity }}</span>
                          <span class="ml-2">=</span>
                          <span class="font-semibold ml-2">{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span>{{ formatDateTime(order.created_at) }}</span>
                    <span v-if="order.meetup_schedule" class="text-sm text-gray-500">
                      Meetup: {{ formatDateTime(order.meetup_schedule) }}
                    </span>
                  </div>
                </TableCell>
                <TableCell>
                  <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusColor(order.status)]">
                    {{ order.status }}
                  </span>
                </TableCell>
                <TableCell>
                  <div v-if="order.meetup_location_id" class="flex flex-col">
                    <span class="font-semibold">Location:</span>
                    <span class="text-sm">{{ order.meetup_location?.name }}</span>
                    <span v-if="order.meetup_confirmation_code" class="text-xs text-gray-500">
                      Code: {{ order.meetup_confirmation_code }}
                    </span>
                  </div>
                  <span v-else class="text-gray-500">Not scheduled</span>
                </TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span class="font-semibold">{{ order.payment_method }}</span>
                    <span class="text-sm text-gray-500">{{ order.address }}</span>
                  </div>
                </TableCell>
                <TableCell class="font-semibold">
                  {{ formatPrice(order.sub_total) }}
                </TableCell>
                <TableCell>
                  <div class="flex gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      @click="viewOrderDetails(order)"
                    >
                      View Details
                    </Button>
                    <Button
                      v-if="order.status === 'Pending'"
                      variant="destructive"
                      size="sm"
                      @click="$inertia.patch(route('orders.cancel', order.id))"
                    >
                      Cancel
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="orders.meta.last_page > 1" class="flex justify-center gap-2">
        <Button 
          v-for="page in orders.meta.links" 
          :key="page.label"
          :variant="page.active ? 'default' : 'outline'"
          :disabled="!page.url"
          :href="page.url"
          v-html="page.label"
        />
      </div>
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
import DashboardLayout from '../Dashboard/DashboardLayout.vue'
import { Link } from '@inertiajs/vue3'
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue'
import { 
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/Components/ui/card'
import { 
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/Components/ui/table'
import { Button } from '@/Components/ui/button'
import UserOrderDetails from './UserOrderDetails.vue'
import { Dialog, DialogContent } from '@/Components/ui/dialog'
import { useToast } from '@/Components/ui/toast/use-toast'

// Remove the incorrect import and use route function that's globally available

const props = defineProps({
  user: Object,
  stats: Object,
  orders: {
    type: Object,
    required: true,
    // Each order should include items with their product details
  }
})

// Add new reactive references for search and filter
const searchQuery = ref('')
const statusFilter = ref('')
const sortBy = ref('newest')

// Define available order statuses
const orderStatuses = [
  'Pending',
  'Accepted',
  'Meetup Scheduled',
  'Delivered',
  'Completed',
  'Cancelled',
  'Disputed'
]

// Add computed property for filtered orders
const filteredOrders = computed(() => {
  let filtered = [...props.orders.data]

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(order => 
      order.id.toString().includes(query) ||
      order.items.some(item => item.product.name.toLowerCase().includes(query)) ||
      order.meetup_location?.name.toLowerCase().includes(query) ||
      order.payment_method.toLowerCase().includes(query)
    )
  }

  // Apply status filter
  if (statusFilter.value) {
    filtered = filtered.filter(order => order.status === statusFilter.value)
  }

  // Apply sorting
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'oldest':
        return new Date(a.created_at) - new Date(b.created_at)
      case 'total-asc':
        return a.sub_total - b.sub_total
      case 'total-desc':
        return b.sub_total - a.sub_total
      default: // newest
        return new Date(b.created_at) - new Date(a.created_at)
    }
  })

  return filtered
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
const selectedOrder = ref(null)
const showOrderDetails = ref(false)
const { toast } = useToast()

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
