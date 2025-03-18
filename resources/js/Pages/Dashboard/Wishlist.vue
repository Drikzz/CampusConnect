<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="container mx-auto px-4">
      <h2 class="text-2xl font-bold mb-6">My Wishlist</h2>

      <!-- Show error message if exists -->
      <div v-if="error" class="text-center py-8 bg-red-50 rounded-lg">
        <p class="text-red-600">{{ error }}</p>
      </div>

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

                  <div class="flex gap-3 mt-4">
                    <Link :href="route('products.show', item.product.id)"
                          class="inline-block flex-1 px-4 py-2 bg-black text-white text-center rounded-lg hover:bg-gray-800 transition-colors">
                      View Details
                    </Link>
                    <button @click="removeFromWishlist(item.id)"
                            :disabled="deletingIds.includes(item.id)"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-red-500 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed">
                      <svg v-if="!deletingIds.includes(item.id)" 
                           class="w-5 h-5" 
                           fill="none" 
                           stroke="currentColor" 
                           viewBox="0 0 24 24">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <svg v-else 
                           class="w-5 h-5 animate-spin" 
                           fill="none" 
                           viewBox="0 0 24 24">
                        <circle class="opacity-25" 
                                cx="12" 
                                cy="12" 
                                r="10" 
                                stroke="currentColor" 
                                stroke-width="4" />
                        <path class="opacity-75" 
                              fill="currentColor" 
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
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
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3' // Add router import
import DashboardLayout from './DashboardLayout.vue'
import { useToast } from '@/Components/ui/toast/use-toast'

const { toast } = useToast()

const props = defineProps({
    wishlistItems: {
        type: Array,
        required: true,
        default: () => []
    },
    user: Object,
    stats: Object,
    error: {
        type: String,
        default: null
    }
});

const loading = ref(false);
const deletingIds = ref([]);

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

// Remove fetchWishlist function since we don't need pagination anymore

async function removeFromWishlist(id) {
  if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
    return;
  }

  try {
    deletingIds.value.push(id);
    
    // Use Inertia to handle the deletion
    router.delete(`/dashboard/wishlist/${id}`);
    
  } catch (error) {
    console.error('Error removing item:', error);
  } finally {
    deletingIds.value = deletingIds.value.filter(itemId => itemId !== id);
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
