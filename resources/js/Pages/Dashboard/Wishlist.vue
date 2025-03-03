<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="container mx-auto px-4">
      <h2 class="text-2xl font-bold mb-6">My Wishlist</h2>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading wishlist items...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!wishlistItems.length" class="text-center py-8 bg-white rounded-lg shadow">
        <p class="text-gray-500 mb-4">Your wishlist is empty</p>
        <Link :href="route('products')" 
              class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
          Browse Products
        </Link>
      </div>

      <!-- Horizontal Scrollable Wishlist Items -->
      <div v-else class="relative max-w-[1000px] mx-auto">
        <button @click="scroll('left')" 
                :disabled="atStart"
                :class="['absolute left-[-20px] top-1/2 -translate-y-1/2 z-10 bg-white p-2 rounded-full shadow-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-200',
                        { 'opacity-0': atStart }]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        
        <div class="overflow-hidden" ref="scrollContainer">
          <div class="flex gap-6 transition-transform duration-300 ease-in-out"
               :style="{ transform: `translateX(-${currentScroll}px)` }">
            <div v-for="item in wishlistItems" :key="item.id" 
                 class="flex-none w-[calc((100%-2rem)/3)]">
              <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="relative h-72">
                  <img :src="item.product.images?.[0] ? `/storage/${item.product.images[0]}` : '/placeholder.png'"
                       :alt="item.product.name"
                       class="w-full h-full object-cover">
                </div>
                
                <div class="p-4">
                  <h3 class="font-semibold text-lg mb-2">{{ item.product.name }}</h3>
                  
                  <div class="mb-3">
                    <p class="text-lg font-bold text-primary-600">
                      ₱{{ formatPrice(item.product.discounted_price || item.product.price) }}
                    </p>
                    <p v-if="item.product.discounted_price" 
                       class="text-sm text-gray-500 line-through">
                      ₱{{ formatPrice(item.product.price) }}
                    </p>
                  </div>

                  <div class="flex gap-3 justify-between">
                    <Link :href="route('products.show', item.product.id)"
                          class="flex-1 text-center px-3 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">
                      View Details
                    </Link>
                    <button @click="removeFromWishlist(item.id)"
                            :disabled="deletingIds.includes(item.id)"
                            class="px-3 py-2 text-red-500 hover:bg-red-50 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                      <i class="fas" :class="deletingIds.includes(item.id) ? 'fa-spinner fa-spin' : 'fa-trash'"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button @click="scroll('right')"
                :disabled="atEnd"
                :class="['absolute right-[-20px] top-1/2 -translate-y-1/2 z-10 bg-white p-2 rounded-full shadow-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-200',
                        { 'opacity-0': atEnd }]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="mt-6 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <button v-for="page in pagination.last_page"
                  :key="page"
                  @click="fetchWishlist(page)"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === pagination.current_page
                      ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]">
            {{ page }}
          </button>
        </nav>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'
import { useToast } from '@/Components/ui/toast/use-toast'

const { toast } = useToast()

const props = defineProps({
  user: Object,
  stats: Object
})

const loading = ref(true)
const wishlistItems = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1
})
const deletingIds = ref([])

const scrollContainer = ref(null)
const currentScroll = ref(0)
const atStart = ref(true)
const atEnd = ref(false)

function formatPrice(price) {
  return Number(price).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

async function fetchWishlist(page = 1) {
  try {
    loading.value = true
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/wishlist?page=${page}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf || '',
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin'
    })
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      throw new Error(errorData.message || 'Failed to fetch wishlist')
    }
    
    const data = await response.json()
    
    if (data.status === 'success') {
      wishlistItems.value = data.wishlists.data
      pagination.value = {
        current_page: data.wishlists.current_page,
        last_page: data.wishlists.last_page
      }
    } else {
      throw new Error('Invalid response format')
    }
  } catch (error) {
    console.error('Error fetching wishlist:', error)
    toast({
      title: 'Error',
      description: `Failed to load wishlist items: ${error.message}. Please try refreshing the page.`,
      variant: 'destructive'
    })
  } finally {
    loading.value = false
  }
}

async function removeFromWishlist(id) {
  if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
    return
  }

  try {
    deletingIds.value.push(id)
    
    // Get CSRF token from meta tag
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!csrf) {
      throw new Error('CSRF token not found')
    }

    const response = await fetch(`/wishlist/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'include', // Important for cookies
    })

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      throw new Error(errorData.message || 'Failed to remove item')
    }
    
    const data = await response.json()

    if (data.status === 'success') {
      wishlistItems.value = wishlistItems.value.filter(item => item.id !== id)
      toast({
        title: 'Success',
        description: 'Item removed from wishlist successfully',
        variant: 'default'
      })
      
      // Refresh stats if they exist
      if (props.stats && typeof props.stats.wishlist_count !== 'undefined') {
        props.stats.wishlist_count--
      }
    } else {
      throw new Error(data.message || 'Failed to remove item')
    }
  } catch (error) {
    console.error('Error removing item:', error)
    toast({
      title: 'Error',
      description: 'Failed to remove item from wishlist: ' + error.message,
      variant: 'destructive'
    })
  } finally {
    deletingIds.value = deletingIds.value.filter(itemId => itemId !== id)
  }
}

function updateScrollButtons() {
  if (!scrollContainer.value) return
  
  atStart.value = currentScroll.value <= 0
  const maxScroll = scrollContainer.value.scrollWidth - scrollContainer.value.clientWidth
  atEnd.value = currentScroll.value >= maxScroll
}

function scroll(direction) {
  if (!scrollContainer.value) return

  const containerWidth = scrollContainer.value.clientWidth
  const scrollAmount = containerWidth / 3 // Scroll one item width

  if (direction === 'left') {
    currentScroll.value = Math.max(0, currentScroll.value - scrollAmount)
  } else {
    const maxScroll = scrollContainer.value.scrollWidth - scrollContainer.value.clientWidth
    currentScroll.value = Math.min(maxScroll, currentScroll.value + scrollAmount)
  }

  updateScrollButtons()
}

onMounted(() => {
  fetchWishlist()
  updateScrollButtons()
  window.addEventListener('resize', updateScrollButtons)
})

// Clean up event listener
onUnmounted(() => {
  window.removeEventListener('resize', updateScrollButtons)
})
</script>

<style>
/* Hide scrollbar but keep functionality */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Add smooth transitions */
.transition-transform {
  transition: transform 0.3s ease-in-out;
}
</style>
