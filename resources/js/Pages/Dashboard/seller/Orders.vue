<script setup lang="ts">
import { ref, shallowRef, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '../DashboardLayout.vue'
import StatsCard from '@/Components/StatsCard.vue'
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue'
import { Button } from '@/Components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  FlexRender,
  getCoreRowModel,
  getPaginationRowModel,
  getFilteredRowModel,
  useVueTable,
  type ColumnDef,
} from '@tanstack/vue-table'
import { h } from 'vue'
import { useToast } from '@/Components/ui/toast'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select"
import { Input } from "@/Components/ui/input"
import { 
  Dialog, 
  DialogContent, 
  DialogHeader, 
  DialogTitle, 
  DialogDescription,
  DialogFooter,
} from '@/Components/ui/dialog'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card'

const props = defineProps({
  user: Object,
  stats: Object,
  orders: Object,
  orderCounts: Object
})

const data = shallowRef(props.orders.data)
const searchQuery = ref('')
const selectedStatus = ref('All')
const { toast } = useToast()

// Cancel order dialog
const showCancelDialog = ref(false)
const selectedOrderId = ref(null)
const cancellationReason = ref('')

// New refs for order details dialog
const showOrderDetails = ref(false)
const selectedOrder = ref(null)

const orderStatuses = [
  'All',
  'Pending',
  'Accepted',
  'Meetup Scheduled',
  'Delivered',
  'Completed',
  'Cancelled',
]

const filteredData = computed(() => {
  let filtered = [...props.orders.data]
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(order => 
      // Check order ID
      (order.id?.toString().includes(query)) ||
      // Check buyer information
      (order.buyer?.name?.toLowerCase().includes(query)) ||
      (order.buyer?.first_name?.toLowerCase().includes(query)) ||
      (order.buyer?.last_name?.toLowerCase().includes(query)) ||
      (order.buyer?.email?.toLowerCase().includes(query)) ||
      (order.buyer?.wmsu_email?.toLowerCase().includes(query)) ||
      // Check product names
      (order.items?.some(item => item.product?.name?.toLowerCase().includes(query)))
    )
  }
  
  if (selectedStatus.value !== 'All') {
    filtered = filtered.filter(order => order.status === selectedStatus.value)
  }
  
  return filtered
})

const columns: ColumnDef<any>[] = [
  {
    accessorKey: 'id',
    header: 'Order ID',
    cell: ({ row }) => h('div', {}, `#${row.getValue('id')}`),
  },
  {
    accessorKey: 'buyer',
    header: 'Customer',
    cell: ({ row }) => {
      const buyer = row.getValue('buyer');
      return h('div', { class: 'flex flex-col' }, [
        h('div', { class: 'font-medium' }, buyer.name || `${buyer.first_name} ${buyer.last_name}`),
        h('div', { class: 'text-sm text-gray-500' }, buyer.email || buyer.wmsu_email),
      ]);
    },
  },
  {
    accessorKey: 'items',
    header: 'Products',
    cell: ({ row }) => {
      const items = row.getValue('items');
      if (!items || !items.length) return h('div', {}, 'No items');
      
      return h('div', { class: 'flex flex-col gap-1' }, [
        h('div', { class: 'flex -space-x-2' }, 
          items.slice(0, 3).map(item => 
            h('img', { 
              src: item.product.image_url || (item.product.images && item.product.images.length > 0 ? `/storage/${item.product.images[0]}` : '/storage/placeholder.jpg'), 
              class: 'w-8 h-8 rounded-md object-cover border border-white',
              alt: item.product.name,
              onerror: "this.src='/storage/placeholder.jpg';"
            })
          )
        ),
        h('div', { class: 'text-sm mt-1' }, 
          items.length > 0 ? `${items[0].product.name}${items.length > 1 ? ` + ${items.length - 1} more` : ''}` : ''
        ),
        h('div', { class: 'text-xs text-gray-500' }, 
          items.reduce((acc, item) => acc + item.quantity, 0) + ' items'
        ),
      ]);
    }
  },
  {
    accessorKey: 'total',
    header: 'Total',
    cell: ({ row }) => h('div', { class: 'font-medium' }, `₱${formatNumber(row.getValue('total'))}`),
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) => h(OrderStatusBadge, { status: row.getValue('status') }),
  },
  {
    accessorKey: 'created_at',
    header: 'Date',
    cell: ({ row }) => h('div', {}, formatDate(row.getValue('created_at'))),
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h('div', { class: 'flex space-x-2' }, [
      h(Button, {
        variant: 'outline',
        size: 'sm',
        onClick: () => viewOrder(row.original.id),
      }, 'View'),
      row.original.status === 'Pending' ? h(Button, {
        variant: 'default',
        size: 'sm',
        onClick: () => updateOrderStatus(row.original.id, 'Accepted'),
      }, 'Accept') : null,
      row.original.status === 'Accepted' ? h(Button, {
        variant: 'default',
        size: 'sm',
        onClick: () => updateOrderStatus(row.original.id, 'Meetup Scheduled'),
      }, 'Schedule') : null,
      row.original.status === 'Meetup Scheduled' ? h(Button, {
        variant: 'default',
        size: 'sm',
        onClick: () => updateOrderStatus(row.original.id, 'Delivered'),
      }, 'Deliver') : null,
      row.original.status === 'Delivered' ? h(Button, {
        variant: 'default',
        size: 'sm',
        onClick: () => updateOrderStatus(row.original.id, 'Completed'),
      }, 'Complete') : null,
      (row.original.status === 'Pending' || row.original.status === 'Accepted') ? h(Button, {
        variant: 'destructive',
        size: 'sm',
        onClick: () => openCancelDialog(row.original.id),
      }, 'Cancel') : null,
    ]),
  },
]

const table = useVueTable({
  get data() {
    return filteredData.value
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  state: {
    pagination: {
      pageSize: 10,
      pageIndex: 0,
    },
  },
})

const formatNumber = (num) => {
  // Handle string values by parsing them first
  if (typeof num === 'string') {
    num = parseFloat(num);
  }
  // Check if the value is a valid number
  if (isNaN(num)) return '0.00';
  return new Intl.NumberFormat().format(num);
}

const formatDate = (date: string) => new Date(date).toLocaleDateString()

const formatPrice = (price) => {
  // Handle string values and check for NaN
  if (typeof price === 'string') {
    price = parseFloat(price);
  }
  if (isNaN(price)) return '₱0.00';
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);
}

const formatDateTime = (date) => {
  if (!date) return 'Not set';
  return new Intl.DateTimeFormat('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date));
}

// Function to calculate subtotal from order items
const calculateSubtotal = (items) => {
  if (!items || !items.length) return 0;
  
  return items.reduce((sum, item) => {
    const itemPrice = parseFloat(item.price) || 0;
    const quantity = parseInt(item.quantity) || 0;
    return sum + (itemPrice * quantity);
  }, 0);
}

const viewOrder = (orderId: number) => {
  // Find order with better error handling
  const order = props.orders.data.find(o => o.id === orderId)
  if (order) {
    selectedOrder.value = order
    showOrderDetails.value = true
  } else {
    toast({
      title: "Error",
      description: "Order details not found",
      variant: "destructive"
    })
  }
}

const closeOrderDetails = () => {
  showOrderDetails.value = false
  setTimeout(() => {
    selectedOrder.value = null
  }, 300) // Wait for the dialog animation to finish
}

const updateOrderStatus = (orderId: number, status: string) => {
  router.put(route('seller.orders.update-status', orderId), {
    status: status
  }, {
    onSuccess: () => {
      toast({
        title: "Success!",
        description: `Order status updated to ${status}`,
        variant: "default"
      })
      
      // Refresh the page after a short delay to show the toast
      setTimeout(() => {
        router.reload()
      }, 1000)
    },
    onError: () => {
      toast({
        title: "Error!",
        description: "Failed to update order status",
        variant: "destructive"
      })
    }
  })
}

const openCancelDialog = (orderId: number) => {
  selectedOrderId.value = orderId
  cancellationReason.value = ''
  showCancelDialog.value = true
}

const cancelOrder = () => {
  if (!cancellationReason.value.trim()) {
    toast({
      title: "Error!",
      description: "Please provide a cancellation reason",
      variant: "destructive"
    })
    return
  }

  router.put(route('seller.orders.update-status', selectedOrderId.value), {
    status: 'Cancelled',
    cancellation_reason: cancellationReason.value,
    cancelled_by: 'seller'
  }, {
    onSuccess: () => {
      toast({
        title: "Success!",
        description: "Order cancelled successfully",
        variant: "default"
      })
      showCancelDialog.value = false
      
      // Refresh the page after a short delay to show the toast
      setTimeout(() => {
        router.reload()
      }, 1000)
    },
    onError: () => {
      toast({
        title: "Error!",
        description: "Failed to cancel order",
        variant: "destructive"
      })
    }
  })
}

const handleStatusChange = (status: string) => {
  selectedStatus.value = status
}

const exportOrders = () => {
  // Add export functionality here
  toast({
    title: "Export Started",
    description: "Your orders data is being prepared for export",
    variant: "default"
  })
}

// Add function for handling image errors
const handleImageError = (event) => {
  event.target.src = '/storage/placeholder.jpg'
}
</script>

<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard title="Pending Orders" :value="orderCounts.pendingOrders" />
        <StatsCard title="Active Orders" :value="orderCounts.activeOrders" />
        <StatsCard title="Total Orders" :value="orderCounts.totalOrders" />
        <StatsCard title="Total Sales" :value="formatPrice(orderCounts.totalSales)" />
      </div>
      
      <!-- Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between space-y-2 sm:space-y-0 sm:space-x-2">
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full sm:w-auto">
          <Input
            placeholder="Search by order ID or customer..."
            class="w-full sm:w-[250px]"
            v-model="searchQuery"
          />
          <Select v-model="selectedStatus" @update:modelValue="handleStatusChange">
            <SelectTrigger class="w-full sm:w-[180px]">
              <SelectValue placeholder="Filter by status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="status in orderStatuses" :key="status" :value="status">
                {{ status }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <div class="flex space-x-2 w-full sm:w-auto justify-end">
          <Button variant="outline" size="sm" @click="exportOrders">
            Export Data
          </Button>
          <Button variant="outline" size="sm" @click="router.reload()">
            Refresh
          </Button>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
              <TableHead v-for="header in headerGroup.headers" :key="header.id" class="font-semibold">
                <FlexRender
                  v-if="!header.isPlaceholder"
                  :render="header.column.columnDef.header"
                  :props="header.getContext()"
                />
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="table.getRowModel().rows?.length">
              <TableRow
                v-for="row in table.getRowModel().rows"
                :key="row.id"
                class="hover:bg-muted/50"
              >
                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                  <FlexRender
                    :render="cell.column.columnDef.cell"
                    :props="cell.getContext()"
                  />
                </TableCell>
              </TableRow>
            </template>
            <TableRow v-else>
              <TableCell :colspan="columns.length" class="h-24 text-center">
                No orders found.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between space-x-2 py-4">
        <div class="flex-1 text-sm text-muted-foreground">
          Showing {{ table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1 }} 
          to {{ Math.min((table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize, filteredData.length) }} 
          of {{ filteredData.length }} orders
        </div>
        <div class="flex items-center space-x-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="!table.getCanPreviousPage()"
            @click="table.previousPage()"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="!table.getCanNextPage()"
            @click="table.nextPage()"
          >
            Next
          </Button>
        </div>
      </div>
    </div>

    <!-- Cancel Order Dialog -->
    <Dialog :open="showCancelDialog" @update:open="showCancelDialog = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Cancel Order</DialogTitle>
          <DialogDescription>
            Please provide a reason for cancelling this order. This will be visible to the customer.
          </DialogDescription>
        </DialogHeader>
        
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="reason">Cancellation Reason</Label>
            <Textarea
              id="reason"
              v-model="cancellationReason"
              placeholder="Enter reason for cancellation..."
              rows="4"
            />
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="showCancelDialog = false">Cancel</Button>
          <Button variant="destructive" @click="cancelOrder">Confirm Cancellation</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Order Details Dialog -->
    <Dialog :open="showOrderDetails" @update:open="closeOrderDetails">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader class="flex justify-between items-center">
          <DialogTitle class="text-xl font-bold">
            Order #{{ selectedOrder?.id }} Details
          </DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedOrder" class="space-y-6">
          <!-- Order Status and Date -->
          <div class="flex justify-between items-center">
            <OrderStatusBadge :status="selectedOrder.status" class="text-base" />
            <div class="text-sm text-gray-500">
              {{ formatDateTime(selectedOrder.created_at) }}
            </div>
          </div>
          
          <!-- Customer Information -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Customer Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <div class="text-sm font-medium text-gray-500">Name</div>
                  <div>{{ selectedOrder.buyer.name || `${selectedOrder.buyer.first_name} ${selectedOrder.buyer.last_name}` }}</div>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-500">Email</div>
                  <div>{{ selectedOrder.buyer.email || selectedOrder.buyer.wmsu_email }}</div>
                </div>
                <div v-if="selectedOrder.phone">
                  <div class="text-sm font-medium text-gray-500">Phone</div>
                  <div>{{ selectedOrder.phone }}</div>
                </div>
                <div v-if="selectedOrder.buyer.student_id">
                  <div class="text-sm font-medium text-gray-500">Student ID</div>
                  <div>{{ selectedOrder.buyer.student_id }}</div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <!-- Order Items -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Order Items</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-for="item in selectedOrder.items" :key="item.id" 
                     class="flex items-center justify-between py-3 border-b last:border-0">
                  <div class="flex items-center gap-4">
                    <img 
                      :src="item.product.image_url || (item.product.images && item.product.images.length > 0 ? `/storage/${item.product.images[0]}` : '/storage/placeholder.jpg')" 
                      class="w-16 h-16 object-cover rounded-md"
                      :alt="item.product.name"
                      @error="handleImageError"
                    />
                    <div>
                      <div class="font-medium">{{ item.product.name }}</div>
                      <div class="text-sm text-gray-500">
                        {{ formatPrice(item.price) }} × {{ item.quantity }}
                      </div>
                      <div v-if="item.product.description" class="text-xs text-gray-500 mt-1 line-clamp-1">
                        {{ item.product.description }}
                      </div>
                    </div>
                  </div>
                  <div class="font-medium">
                    {{ formatPrice(item.price * item.quantity) }}
                  </div>
                </div>
              </div>
              
              <div class="mt-6 pt-4 border-t space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal</span>
                  <span>{{ formatPrice(calculateSubtotal(selectedOrder.items)) }}</span>
                </div>
                <div v-if="selectedOrder.fees" class="flex justify-between text-sm">
                  <span class="text-gray-600">Platform Fee</span>
                  <span>{{ formatPrice(selectedOrder.fees) }}</span>
                </div>
                <div class="flex justify-between font-medium text-lg">
                  <span>Total</span>
                  <span>{{ formatPrice(calculateSubtotal(selectedOrder.items)) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <!-- Order Timeline/History -->
          <Card v-if="selectedOrder.created_at || selectedOrder.accepted_at || selectedOrder.completed_at">
            <CardHeader>
              <CardTitle class="text-lg">Order Timeline</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Placed</span>
                  <span>{{ formatDateTime(selectedOrder.created_at) }}</span>
                </div>
                <div v-if="selectedOrder.accepted_at" class="flex justify-between">
                  <span class="text-gray-600">Accepted</span>
                  <span>{{ formatDateTime(selectedOrder.accepted_at) }}</span>
                </div>
                <div v-if="selectedOrder.meetup_schedule" class="flex justify-between">
                  <span class="text-gray-600">Meetup Scheduled</span>
                  <span>{{ formatDateTime(selectedOrder.meetup_schedule) }}</span>
                </div>
                <div v-if="selectedOrder.delivered_at" class="flex justify-between">
                  <span class="text-gray-600">Delivered</span>
                  <span>{{ formatDateTime(selectedOrder.delivered_at) }}</span>
                </div>
                <div v-if="selectedOrder.completed_at" class="flex justify-between">
                  <span class="text-gray-600">Completed</span>
                  <span>{{ formatDateTime(selectedOrder.completed_at) }}</span>
                </div>
                <div v-if="selectedOrder.cancelled_at" class="flex justify-between text-red-600">
                  <span>Cancelled</span>
                  <span>{{ formatDateTime(selectedOrder.cancelled_at) }}</span>
                </div>
              </div>
              <div v-if="selectedOrder.cancellation_reason" class="mt-3 p-3 bg-red-50 rounded-md">
                <div class="text-sm font-medium text-red-600">Cancellation Reason:</div>
                <div class="text-sm text-red-700">{{ selectedOrder.cancellation_reason }}</div>
                <div v-if="selectedOrder.cancelled_by" class="text-xs text-gray-500 mt-1">
                  Cancelled by: {{ selectedOrder.cancelled_by }}
                </div>
              </div>
            </CardContent>
          </Card>
          
          <!-- Meetup Details -->
          <Card v-if="selectedOrder.meetup_location || selectedOrder.meetup_schedule">
            <CardHeader>
              <CardTitle class="text-lg">Meetup Details</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="selectedOrder.meetup_location" class="mb-3">
                <div class="text-sm font-medium text-gray-500">Location</div>
                <div class="font-medium">{{ selectedOrder.meetup_location.name }}</div>
                <div class="text-sm text-gray-600">{{ selectedOrder.meetup_location.full_name }}</div>
                <div v-if="selectedOrder.meetup_location.description" class="text-sm text-gray-500 mt-1">
                  {{ selectedOrder.meetup_location.description }}
                </div>
              </div>
              <div v-if="selectedOrder.meetup_schedule" class="mb-3">
                <div class="text-sm font-medium text-gray-500">Schedule</div>
                <div class="font-medium">{{ formatDateTime(selectedOrder.meetup_schedule) }}</div>
              </div>
              <div v-if="selectedOrder.meetup_confirmation_code" class="mb-3">
                <div class="text-sm font-medium text-gray-500">Confirmation Code</div>
                <div class="bg-blue-50 p-2 rounded text-center font-mono font-bold text-blue-700">
                  {{ selectedOrder.meetup_confirmation_code }}
                </div>
              </div>
              <div v-if="selectedOrder.meetup_notes" class="mt-3">
                <div class="text-sm font-medium text-gray-500">Notes</div>
                <div class="text-sm p-2 bg-gray-50 rounded">{{ selectedOrder.meetup_notes }}</div>
              </div>
            </CardContent>
          </Card>
          
          <!-- Payment Information -->
          <Card v-if="selectedOrder.payment_method">
            <CardHeader>
              <CardTitle class="text-lg">Payment Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <div class="text-sm font-medium text-gray-500">Payment Method</div>
                  <div class="capitalize">{{ selectedOrder.payment_method }}</div>
                </div>
                <div v-if="selectedOrder.payment_status">
                  <div class="text-sm font-medium text-gray-500">Payment Status</div>
                  <div class="capitalize">{{ selectedOrder.payment_status }}</div>
                </div>
                <div v-if="selectedOrder.transaction_id">
                  <div class="text-sm font-medium text-gray-500">Transaction ID</div>
                  <div class="font-mono text-sm">{{ selectedOrder.transaction_id }}</div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <!-- Actions -->
          <div class="flex justify-end gap-2">
            <Button variant="outline" @click="closeOrderDetails">Close</Button>
            
            <!-- Dynamic action buttons based on order status -->
            <template v-if="selectedOrder.status === 'Pending'">
              <Button @click="updateOrderStatus(selectedOrder.id, 'Accepted')">
                Accept Order
              </Button>
              <Button 
                variant="destructive" 
                @click="openCancelDialog(selectedOrder.id); closeOrderDetails();"
              >
                Cancel Order
              </Button>
            </template>
            
            <template v-if="selectedOrder.status === 'Accepted'">
              <Button @click="updateOrderStatus(selectedOrder.id, 'Meetup Scheduled')">
                Schedule Meetup
              </Button>
            </template>
            
            <template v-if="selectedOrder.status === 'Meetup Scheduled'">
              <Button @click="updateOrderStatus(selectedOrder.id, 'Delivered')">
                Mark as Delivered
              </Button>
            </template>
            
            <template v-if="selectedOrder.status === 'Delivered'">
              <Button @click="updateOrderStatus(selectedOrder.id, 'Completed')">
                Complete Order
              </Button>
            </template>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>
