<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Back button -->
      <Button variant="ghost" @click="router.visit(route('seller.orders'))" class="flex items-center gap-2">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Orders
      </Button>

      <!-- Order Header -->
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Order #{{ order.id }}</h2>
        <OrderStatusBadge :status="order.status" />
      </div>

      <!-- Customer Info -->
      <Card>
        <CardHeader>
          <CardTitle>Customer Information</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Name</Label>
              <p class="font-medium">{{ order.buyer.name }}</p>
            </div>
            <div>
              <Label>Email</Label>
              <p class="font-medium">{{ order.buyer.email }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Order Items -->
      <Card>
        <CardHeader>
          <CardTitle>Order Items</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Product</TableHead>
                <TableHead>Price</TableHead>
                <TableHead>Quantity</TableHead>
                <TableHead>Total</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in order.items" :key="item.id">
                <TableCell>
                  <div class="flex items-center gap-3">
                    <img :src="item.product.image" class="w-12 h-12 rounded-lg object-cover" />
                    <span class="font-medium">{{ item.product.name }}</span>
                  </div>
                </TableCell>
                <TableCell>₱{{ formatNumber(item.price) }}</TableCell>
                <TableCell>{{ item.quantity }}</TableCell>
                <TableCell>₱{{ formatNumber(item.price * item.quantity) }}</TableCell>
              </TableRow>
            </TableBody>
            <TableFooter>
              <TableRow>
                <TableCell colSpan="3" class="text-right font-semibold">Total:</TableCell>
                <TableCell>₱{{ formatNumber(order.total) }}</TableCell>
              </TableRow>
            </TableFooter>
          </Table>
        </CardContent>
      </Card>

      <!-- Order Actions -->
      <Card v-if="showActions">
        <CardHeader>
          <CardTitle>Order Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Actions based on order status -->
            <template v-if="order.status === 'Pending'">
              <Button @click="acceptOrder" class="w-full">Accept Order</Button>
            </template>
            
            <template v-if="order.status === 'Accepted'">
              <MeetupScheduler :order="order" @scheduled="onMeetupScheduled" />
            </template>

            <template v-if="order.status === 'Meetup Scheduled'">
              <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium mb-2">Scheduled Meetup Details:</h4>
                <p>Location: {{ order.meetup_location.name }}</p>
                <p>Schedule: {{ formatDate(order.meetup_schedule) }}</p>
              </div>
              <Button @click="markAsDelivered" variant="success" class="w-full">
                Mark as Delivered
              </Button>
            </template>

            <Button 
              v-if="['Pending', 'Accepted', 'Meetup Scheduled'].includes(order.status)"
              @click="cancelOrder" 
              variant="destructive"
            >
              Cancel Order
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { ArrowLeftIcon } from 'lucide-vue-next'
import DashboardLayout from '../DashboardLayout.vue'
import OrderStatusBadge from '../../../Components/OrderStatusBadge.vue'
import MeetupScheduler from '../../../Components/MeetupScheduler.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell, TableFooter } from '@/Components/ui/table'
import { Label } from '@/Components/ui/label'

const props = defineProps({
  user: Object,
  stats: Object,
  order: Object
})

const showActions = computed(() => 
  !['Completed', 'Cancelled', 'Disputed'].includes(props.order.status)
)

const formatNumber = (num) => new Intl.NumberFormat().format(num)
const formatDate = (date) => new Date(date).toLocaleString()

// Order action methods
const acceptOrder = () => {
  router.put(route('seller.orders.update-status', props.order.id), {
    status: 'Accepted'
  })
}

const onMeetupScheduled = () => {
  router.reload()
}

const markAsDelivered = () => {
  router.put(route('seller.orders.update-status', props.order.id), {
    status: 'Delivered'
  })
}

const cancelOrder = () => {
  if (confirm('Are you sure you want to cancel this order?')) {
    router.put(route('seller.orders.update-status', props.order.id), {
      status: 'Cancelled'
    })
  }
}
</script>
