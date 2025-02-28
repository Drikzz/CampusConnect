<template>
  <DashboardLayout :user="user" :stats="stats">
    <h2 class="text-2xl font-bold mb-6">My Wishlist</h2>

    <div v-if="!wishlists.length" class="text-center py-8">
      <p class="text-gray-500">Your wishlist is empty</p>
      <Link :href="route('products')" class="text-primary-color hover:underline mt-2 inline-block">
        Browse Products
      </Link>
    </div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="wishlist in wishlists" :key="wishlist.id"
           class="bg-white p-4 rounded-lg shadow flex items-center gap-4">
        <img :src="`/storage/${wishlist.product.image}`" :alt="wishlist.product.name"
             class="w-20 h-20 object-cover rounded">

        <div class="flex-1">
          <h3 class="font-semibold">{{ wishlist.product.name }}</h3>
          <p class="text-primary-color">₱{{ formatPrice(wishlist.product.price) }}</p>

          <div class="flex gap-2 mt-2">
            <Link :href="route('prod.details', wishlist.product.id)"
                  class="text-sm text-primary-color hover:underline">
              View Product
            </Link>
            <button @click="removeFromWishlist(wishlist.id)"
                    class="text-sm text-red-500 hover:underline">
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="wishlists.length" class="mt-4">
      <Pagination>
        <PaginationFirst v-if="links.first" :href="links.first" />
        <PaginationPrev v-if="links.prev" :href="links.prev" />
        <PaginationList>
          <template v-for="(link, i) in links.pages" :key="i">
            <PaginationListItem v-if="link.url" 
              :href="link.url" 
              :isActive="link.active">
              {{ link.label }}
            </PaginationListItem>
            <PaginationEllipsis v-else-if="link.label === '...'" />
          </template>
        </PaginationList>
        <PaginationNext v-if="links.next" :href="links.next" />
        <PaginationLast v-if="links.last" :href="links.last" />
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
  wishlists: Array,
  links: Object
})

function formatPrice(price) {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

function removeFromWishlist(id) {
  if (confirm('Are you sure you want to remove this item from your wishlist?')) {
    router.delete(route('wishlist.remove', id))
  }
}
</script>
