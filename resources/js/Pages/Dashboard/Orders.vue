<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <Card v-for="order in orders.data" :key="order.id">
        <CardHeader>
          <div class="flex justify-between">
            <div>
              <CardTitle>Order #{{ order.id }}</CardTitle>
              <CardDescription>{{ formatDate(order.created_at) }}</CardDescription>
            </div>
            <OrderStatusBadge :status="order.status" />
          </div>
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
                  <div class="flex items-center space-x-3">
                    <img :src="`/storage/${item.product.images[0]}`" 
                         :alt="item.product.name"
                         class="h-12 w-12 rounded-md object-cover" />
                    <span>{{ item.product.name }}</span>
                  </div>
                </TableCell>
                <TableCell>{{ formatPrice(item.price) }}</TableCell>
                <TableCell>{{ item.quantity }}</TableCell>
                <TableCell>{{ formatPrice(item.price * item.quantity) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
        <CardFooter class="flex justify-between">
          <div class="text-lg font-semibold">
            Total: {{ formatPrice(order.sub_total) }}
          </div>
          <Button v-if="order.status === 'Pending'"
                  variant="destructive"
                  @click="$inertia.patch(route('orders.cancel', order.id))">
            Cancel Order
          </Button>
        </CardFooter>
      </Card>

      <!-- Pagination -->
      <div v-if="orders.meta.last_page > 1" class="flex justify-center gap-2">
        <Button v-if="orders.links.prev" 
                variant="outline" 
                :href="orders.links.prev">
          Previous
        </Button>
        <Button v-if="orders.links.next" 
                variant="outline" 
                :href="orders.links.next">
          Next
        </Button>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
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

const props = defineProps({
  user: Object,
  stats: Object,
  orders: Object
})

const formatDate = (date) => new Date(date).toLocaleDateString()
const formatPrice = (price) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price)
</script>
