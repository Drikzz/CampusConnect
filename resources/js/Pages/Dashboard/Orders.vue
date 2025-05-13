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

      <!-- Orders Tabs - Make responsive -->
      <Tabs default-value="all" class="w-full">
        <TabsList class="grid w-full grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-1">
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
                    <!-- Order Items - Updated with image display -->
                    <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4">
                      <!-- Add product image -->
                      <div class="w-16 h-16 overflow-hidden rounded-md border flex-shrink-0">
                        <img 
                          :src="getProductImageUrl(item.product)"
                          :alt="item.product.name"
                          class="w-full h-full object-cover"
                          @error="handleImageError"
                        />
                      </div>
                      
                      <div class="flex-1 min-w-0">
                        <h4 class="font-medium truncate">{{ item.product.name }}</h4>
                        <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                        <p class="text-sm text-gray-500" v-if="item.product.seller">
                          Seller: {{ item.product.seller.first_name }} {{ item.product.seller.last_name }}
                        </p>
                      </div>
                      
                      <div class="text-right whitespace-nowrap">
                        <p class="font-semibold">{{ formatPrice(parseFloat(item.price) * parseInt(item.quantity)) }}</p>
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
                  <div class="font-semibold">Total: {{ formatPrice(parseFloat(order.sub_total || 0)) }}</div>
                  <div class="space-x-2">
                    <Button variant="outline" @click="viewOrderDetails(order)">
                      View Details
                    </Button>
                    <Button 
                      v-if="order.status === 'Pending'"
                      variant="destructive"
                      @click="confirmCancelOrder(order.id)"
                    >
                      Cancel
                    </Button>
                    <!-- Add Review Button for completed orders -->
                    <Button 
                      v-if="order.status === 'Completed'"
                      variant="secondary"
                      @click="openReviewDialogForOrder(order)"
                    >
                      <svg 
                        class="w-4 h-4 mr-1" 
                        xmlns="http://www.w3.org/2000/svg" 
                        viewBox="0 0 24 24"
                        fill="currentColor"
                      >
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                      </svg>
                      Review
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

    <!-- Reviews Dialog -->
    <Dialog :open="showReviewsDialog" @update:open="showReviewsDialog = $event">
      <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Seller Reviews</DialogTitle>
          <DialogDescription v-if="selectedOrder">
            Reviews for {{ selectedOrder.seller?.first_name + ' ' + selectedOrder.seller?.last_name }}
          </DialogDescription>
        </DialogHeader>
        
        <SellerReviews
          v-if="selectedOrder"
          :seller-code="selectedOrder.seller_code"
          :seller-name="selectedOrder.seller?.first_name + ' ' + selectedOrder.seller?.last_name"
          :transaction-id="selectedOrder.id"
          transaction-type="order"
          :is-completed="selectedOrder.status === 'Completed'"
          @review-submitted="handleReviewSubmitted"
        />
        
        <DialogFooter>
          <Button variant="outline" @click="showReviewsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Cancel Order Dialog -->
    <Dialog :open="showCancelDialog" @update:open="showCancelDialog = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Cancel Order</DialogTitle>
          <DialogDescription>
            Are you sure you want to cancel this order? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        
        <DialogFooter>
          <AlertDialogCancel @click="showCancelDialog = false">No, Keep it</AlertDialogCancel>
          <AlertDialogAction @click="confirmCancelOrder">Yes, Cancel Order</AlertDialogAction>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'
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
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription 
} from '@/Components/ui/dialog'
import { useToast } from '@/Components/ui/toast/use-toast'
import UserOrderDetails from './UserOrderDetails.vue'
import SellerReviews from '@/Components/SellerReviews.vue'

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
const showReviewsDialog = ref(false)
const showCancelDialog = ref(false)
const orderToCancel = ref(null)
const isCancelling = ref(false)
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

const formatDateTime = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

const formatPrice = (price) => {
  // Ensure price is a valid number
  const numericPrice = parseFloat(price);
  
  // Handle NaN, null, or undefined
  if (isNaN(numericPrice) || numericPrice === null || numericPrice === undefined) {
    return '₱0.00';
  }
  
  return new Intl.NumberFormat('en-PH', { 
    style: 'currency', 
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numericPrice);
}

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
}

const viewOrderDetails = (order) => {
  selectedOrder.value = order
  showOrderDetails.value = true
  
  // Ensure each item in the order has properly normalized prices
  if (selectedOrder.value && selectedOrder.value.items) {
    selectedOrder.value.items.forEach(item => {
      // Make sure price is a valid number to prevent NaN display
      if (item.price) {
        item.price = parseFloat(item.price);
      }
      if (item.subtotal) {
        item.subtotal = parseFloat(item.subtotal);
      }
    });
  }
  
  // Normalize order total amounts
  if (selectedOrder.value) {
    if (selectedOrder.value.sub_total) {
      selectedOrder.value.sub_total = parseFloat(selectedOrder.value.sub_total);
    }
    if (selectedOrder.value.total) {
      selectedOrder.value.total = parseFloat(selectedOrder.value.total);
    }
    if (selectedOrder.value.transaction_fee) {
      selectedOrder.value.transaction_fee = parseFloat(selectedOrder.value.transaction_fee);
    }
  }
}

const closeOrderDetails = () => {
  showOrderDetails.value = false
  selectedOrder.value = null
}

const openReviewDialog = () => {
  if (selectedOrder.value) {
    showReviewsDialog.value = true
  }
}

// Add function to open review dialog directly for a specific order
const openReviewDialogForOrder = (order) => {
  selectedOrder.value = order;
  showReviewsDialog.value = true;
};

// Handle review submission completion
const handleReviewSubmitted = () => {
  toast({
    title: 'Review Submitted',
    description: 'Thank you for your feedback!',
    variant: 'default'
  });
  
  // Close the dialog after a short delay
  setTimeout(() => {
    showReviewsDialog.value = false;
  }, 1500);
};

// Add function to get the product image URL
const getProductImageUrl = (product) => {
  // Check if product has images
  if (!product || !product.images) return '/images/placeholder-product.jpg';
  
  // Handle different image formats
  if (typeof product.images === 'string') {
    try {
      // Try to parse as JSON
      const images = JSON.parse(product.images);
      if (Array.isArray(images) && images.length > 0) {
        return formatImagePath(images[0]);
      }
      return formatImagePath(product.images);
    } catch (e) {
      // Not valid JSON, use as direct path
      return formatImagePath(product.images);
    }
  } else if (Array.isArray(product.images) && product.images.length > 0) {
    return formatImagePath(product.images[0]);
  }
  
  return '/images/placeholder-product.jpg';
};

// Helper function to format image paths
const formatImagePath = (path) => {
  if (!path) return '/images/placeholder-product.jpg';
  
  // If it's a full URL, return as is
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  
  // Handle storage paths
  if (path.startsWith('storage/')) {
    return '/' + path;
  } else if (path.startsWith('/storage/')) {
    return path;
  } else {
    return '/storage/' + path;
  }
};

// Add image error handler
const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg';
};

// Update the confirmCancelOrder function to not prompt for a reason
const confirmCancelOrder = (orderId) => {
  if (!orderId) return;
  
  router.patch(route('orders.cancel', orderId), {}, {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: 'Order cancelled successfully',
        variant: 'default'
      });
    },
    onError: (error) => {
      toast({
        title: 'Error',
        description: error.message || 'Failed to cancel order. Please try again.',
        variant: 'destructive'
      });
    }
  });
};
</script>

<style scoped>
.bg-card {
  background-color: white;
}

/* Add responsive card styles */
@media (max-width: 640px) {
  :deep(.card-footer) {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  :deep(.card-footer > div) {
    width: 100%;
    display: flex;
    justify-content: center;
  }
}
</style>