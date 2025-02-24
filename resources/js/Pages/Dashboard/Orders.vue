<template>
  <DashboardLayout :user="user" :stats="stats">
    <h2 class="text-2xl font-bold mb-6">My Orders</h2>

    <div v-if="!orders.data.length" class="text-center py-8">
      <p class="text-gray-500">You haven't placed any orders yet</p>
      <Link :href="route('products')" class="text-primary-color hover:underline mt-2 inline-block">
        Start Shopping
      </Link>
    </div>

    <div v-else class="space-y-6">
      <div v-for="order in orders.data" :key="order.id" class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold">Order #{{ order.id }}</h3>
            <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
          </div>
          <span :class="orderStatusClass(order.status)">
            {{ order.status }}
          </span>
        </div>

        <div class="divide-y">
          <div v-for="item in order.items" :key="item.id" class="py-4 flex items-center gap-4">
            <img :src="`/storage/${item.product.image}`" :alt="item.product.name"
                 class="w-16 h-16 object-cover rounded">

            <div class="flex-1">
              <h4 class="font-medium">{{ item.product.name }}</h4>
              <p class="text-sm text-gray-500">
                ₱{{ formatPrice(item.price) }} × {{ item.quantity }}
              </p>
            </div>

            <div class="text-right">
              <p class="font-medium">₱{{ formatPrice(item.subtotal) }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-500">Total Amount</p>
            <p class="text-lg font-semibold">₱{{ formatPrice(order.sub_total) }}</p>
          </div>

          <button v-if="order.status === 'Pending'"
                  @click="cancelOrder(order.id)"
                  class="text-red-500 hover:underline">
            Cancel Order
          </button>
        </div>
      </div>
    </div>

    <div v-if="orders.data.length" class="mt-6">
      <Pagination>
        <PaginationFirst v-if="orders.first_page_url" :href="orders.first_page_url" />
        <PaginationPrev v-if="orders.prev_page_url" :href="orders.prev_page_url" />
        <PaginationList>
          <template v-for="(link, i) in orders.links" :key="i">
            <PaginationListItem v-if="link.url" 
              :href="link.url" 
              :isActive="link.active">
              {{ link.label }}
            </PaginationListItem>
            <PaginationEllipsis v-else-if="link.label === '...'" />
          </template>
        </PaginationList>
        <PaginationNext v-if="orders.next_page_url" :href="orders.next_page_url" />
        <PaginationLast v-if="orders.last_page_url" :href="orders.last_page_url" />
      </Pagination>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'
import {
  Pagination,
  PaginationFirst,
  PaginationPrev,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationLast,
  PaginationEllipsis
} from '@/Components/ui/pagination'

const props = defineProps({
  user: Object,
  stats: Object,
  orders: Object
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

function formatPrice(price) {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

function orderStatusClass(status) {
  return {
    'px-3 py-1 rounded-full text-sm': true,
    'bg-green-100 text-green-800': status === 'Completed',
    'bg-red-100 text-red-800': status === 'Cancelled',
    'bg-yellow-100 text-yellow-800': !['Completed', 'Cancelled'].includes(status)
  }
}

function cancelOrder(orderId) {
  if (confirm('Are you sure you want to cancel this order?')) {
    router.patch(route('orders.cancel', orderId))
  }
}
</script>
