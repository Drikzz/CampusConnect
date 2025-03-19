<script setup>
import { ref, computed, onMounted, watchEffect } from 'vue'
import DashboardLayout from '../DashboardLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent } from '@/Components/ui/card'
import { useToast } from '@/Components/ui/toast/use-toast'
import { Toaster } from '@/Components/ui/toast'
import axios from 'axios'

const props = defineProps({
  user: Object,
  stats: Object,
  reviews: Object
})

const { toast } = useToast()
const isLoading = ref(false)
const reviewsData = ref(props.reviews?.data || [])
const currentPage = ref(1)
const averageRating = ref(0)
const totalReviews = ref(props.reviews?.total || 0)
const scrollContainer = ref(null)
const canScrollLeft = ref(false)
const canScrollRight = ref(true)

// Format date to a more readable format
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Calculate rating distribution
const ratingDistribution = computed(() => {
  if (!reviewsData.value.length) return Array(5).fill(0)
  
  const distribution = Array(5).fill(0)
  reviewsData.value.forEach(review => {
    if (review.rating > 0 && review.rating <= 5) {
      distribution[review.rating - 1]++
    }
  })
  
  return distribution
})

// Calculate percentages for rating distribution
const ratingPercentages = computed(() => {
  if (!totalReviews.value) return Array(5).fill(0)
  
  return ratingDistribution.value.map(count => 
    Math.round((count / totalReviews.value) * 100) || 0
  )
})

// Check scroll position and update button visibility
const checkScrollPosition = () => {
  if (!scrollContainer.value) return
  
  const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value
  canScrollLeft.value = scrollLeft > 0
  canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 5 // 5px buffer
}

// Scroll left or right through reviews
const scroll = (direction) => {
  if (!scrollContainer.value) return
  
  const scrollAmount = scrollContainer.value.clientWidth / 2
  const currentScroll = scrollContainer.value.scrollLeft
  
  scrollContainer.value.scrollTo({
    left: direction === 'left' ? currentScroll - scrollAmount : currentScroll + scrollAmount,
    behavior: 'smooth'
  })
  
  // Update button visibility after scrolling
  setTimeout(checkScrollPosition, 300)
}

// Fetch the seller's average rating
const fetchRating = async () => {
  if (!props.user?.seller_code) return
  
  try {
    isLoading.value = true
    const response = await axios.get(`/api/seller-reviews/rating/${props.user.seller_code}`)
    if (response.data.success) {
      console.log('Raw rating from API:', response.data.stats.avg_rating)
      
      // Don't recalculate, just use the properly calculated value from the backend
      averageRating.value = Number(response.data.stats.avg_rating);
      totalReviews.value = response.data.stats.total_reviews
      
      // If rating distribution data is available from backend, use it
      if (response.data.stats.rating_counts) {
        // Process rating counts to update distribution display
        updateRatingDistribution(response.data.stats.rating_counts);
      }
    }
  } catch (error) {
    console.error('Error fetching rating:', error)
    toast({
      title: 'Error',
      description: 'Failed to fetch rating statistics',
      variant: 'destructive'
    })
  } finally {
    isLoading.value = false
  }
}

// Helper function to update rating distribution from API data
const updateRatingDistribution = (ratingCounts) => {
  const distribution = Array(5).fill(0);
  
  // Fill in the counts we received
  for (const [rating, count] of Object.entries(ratingCounts)) {
    const index = parseInt(rating) - 1;
    if (index >= 0 && index < 5) {
      distribution[index] = count;
    }
  }
  
  // Now we can update component data if needed
  // For example, if you're storing the distribution separately
}

// Get star color based on rating
const getStarColor = (rating) => {
  if (rating >= 4) return 'text-yellow-400'
  if (rating >= 3) return 'text-amber-400'
  return 'text-orange-400'
}

// Get rating text description
const getRatingText = (rating) => {
  // Ensure we're working with a numeric value
  const numericRating = Number(rating);
  
  // No calculation needed if there are no reviews
  if (totalReviews.value === 0) return 'No Rating';
  
  // Now determine the text description based on the numeric rating value
  if (numericRating >= 4.5) return 'Excellent';
  if (numericRating >= 4) return 'Very Good';
  if (numericRating >= 3) return 'Good';
  if (numericRating >= 2) return 'Fair';
  return 'Poor';
}

onMounted(() => {
  fetchRating()
  
  // Set up scroll event listener
  if (scrollContainer.value) {
    scrollContainer.value.addEventListener('scroll', checkScrollPosition)
    // Initial check
    checkScrollPosition()
  }
  
  // Resize observer for responsive changes
  const resizeObserver = new ResizeObserver(() => {
    checkScrollPosition()
  })
  
  if (scrollContainer.value) {
    resizeObserver.observe(scrollContainer.value)
  }
  
  return () => {
    if (scrollContainer.value) {
      scrollContainer.value.removeEventListener('scroll', checkScrollPosition)
      resizeObserver.disconnect()
    }
  }
})

// Watch for changes to review data
watchEffect(() => {
  if (props.reviews?.data) {
    reviewsData.value = props.reviews.data
  }
})
</script>

<template>
  <DashboardLayout :user="user" :stats="stats">
    <Toaster />
    
    <div class="space-y-8 px-1">
      <!-- Header with Rating Summary -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
          <h2 class="text-2xl font-bold">My Reviews</h2>
          <p class="text-gray-500 mt-1">See what buyers are saying about your service</p>
        </div>
        
        <div class="w-full md:w-auto flex flex-col sm:flex-row bg-white rounded-lg shadow-md overflow-hidden">
          <div class="flex items-center justify-center p-4 sm:p-5 bg-gradient-to-br from-primary-color to-primary-color/90">
            <div class="text-center">
              <div class="flex items-center justify-center">
                <span class="text-3xl font-bold text-white">{{ averageRating }}</span>
                <span class="text-lg text-white/80 ml-1">/5</span>
              </div>
              <div class="flex items-center justify-center mt-1">
                <template v-for="n in 5" :key="n">
                  <svg
                    :class="[
                      n <= Math.round(averageRating) ? 'text-white' : 'text-white/30',
                      'h-4 w-4'
                    ]"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </template>
              </div>
              <p v-if="totalReviews > 0" class="text-sm text-white/90 mt-1">{{ getRatingText(averageRating) }}</p>
              <p class="text-sm text-white/80 mt-1">{{ totalReviews }} {{ totalReviews === 1 ? 'review' : 'reviews' }}</p>
            </div>
          </div>
          
          <!-- Rating Distribution Bars -->
          <div class="p-4 sm:p-5 flex-1">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Rating Distribution</h3>
            <div class="flex flex-col space-y-2">
              <div v-for="i in 5" :key="i" class="flex items-center gap-2.5">
                <div class="flex items-center min-w-[40px]">
                  <span class="text-xs font-medium w-3 text-right">{{ 6-i }}</span>
                  <svg class="w-3.5 h-3.5 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-primary-color transition-all duration-500 ease-out"
                    :style="`width: ${ratingPercentages[5-i]}%`"
                  ></div>
                </div>
                <span class="text-xs min-w-[30px] text-gray-500">{{ ratingPercentages[5-i] }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews Horizontal Scrolling Section -->
      <div class="relative">
        <Button 
          @click="scroll('left')"
          variant="outline"
          :disabled="!canScrollLeft"
          :class="[
            'absolute left-0 top-1/2 -translate-y-1/2 z-10 rounded-full h-10 w-10 p-0 flex items-center justify-center bg-white shadow-md transition-all duration-200',
            canScrollLeft ? 'opacity-100' : 'opacity-0 cursor-default'
          ]"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </Button>
        
        <div 
          ref="scrollContainer"
          class="flex overflow-x-auto gap-4 px-8 py-4 pb-6 scroll-smooth hide-scrollbar"
        >
          <div v-if="isLoading" class="flex-shrink-0 w-full flex justify-center items-center h-60">
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 border-4 border-primary-color/30 border-t-primary-color rounded-full animate-spin"></div>
              <p class="mt-4 text-gray-500">Loading reviews...</p>
            </div>
          </div>
          
          <template v-else-if="reviewsData && reviewsData.length">
            <Card 
              v-for="review in reviewsData" 
              :key="review.id" 
              class="flex-shrink-0 w-[300px] md:w-[320px] hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-primary-color/30"
            >
              <CardContent class="p-6">
                <!-- Star Rating - Moved to top for better hierarchy -->
                <div class="flex items-center mb-4">
                  <div class="flex">
                    <template v-for="n in 5" :key="n">
                      <svg
                        :class="[
                          n <= review.rating ? getStarColor(review.rating) : 'text-gray-300',
                          'h-5 w-5'
                        ]"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    </template>
                  </div>
                </div>
                
                <!-- Review Text - Move up for better readability -->
                <div class="text-gray-700 mb-4">
                  <p v-if="review.review" class="line-clamp-4">{{ review.review }}</p>
                  <p v-else class="text-gray-500 italic">No written review provided</p>
                </div>
                
                <!-- Reviewer info and date -->
                <div class="flex justify-between items-start pt-3 border-t border-gray-100">
                  <div>
                    <p class="font-medium">
                      {{ review.reviewer?.first_name && review.reviewer?.last_name 
                        ? `${review.reviewer.first_name} ${review.reviewer.last_name}` 
                        : (review.reviewer?.name || 'Anonymous') }}
                    </p>
                    <p class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</p>
                  </div>
                  
                  <div v-if="review.is_verified_purchase" 
                       class="text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-full flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Verified
                  </div>
                </div>
              </CardContent>
            </Card>
          </template>
          
          <!-- Empty state with illustration -->
          <div v-else class="w-full py-12 flex flex-col items-center justify-center">
            <div class="mb-6 text-gray-300">
              <!-- Simple star rating illustration -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <p class="text-gray-700 text-lg font-medium">No reviews yet</p>
            <p class="text-gray-500 mt-2 text-center max-w-sm">
              As you complete more sales, reviews from your buyers will appear here
            </p>
          </div>
        </div>
        
        <Button 
          @click="scroll('right')"
          variant="outline"
          :disabled="!canScrollRight"
          :class="[
            'absolute right-0 top-1/2 -translate-y-1/2 z-10 rounded-full h-10 w-10 p-0 flex items-center justify-center bg-white shadow-md transition-all duration-200',
            canScrollRight ? 'opacity-100' : 'opacity-0 cursor-default'
          ]"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </Button>
      </div>
      
      <!-- Scroll indicator dots -->
      <div class="flex justify-center space-x-2 pt-2">
        <div
          :class="[
            'w-2 h-2 rounded-full transition-all duration-300',
            !canScrollLeft ? 'bg-primary-color scale-125' : 'bg-gray-300'
          ]"
        ></div>
        <div
          :class="[
            'w-2 h-2 rounded-full transition-all duration-300',
            (canScrollLeft && canScrollRight) ? 'bg-primary-color scale-125' : 'bg-gray-300'
          ]"
        ></div>
        <div
          :class="[
            'w-2 h-2 rounded-full transition-all duration-300',
            !canScrollRight ? 'bg-primary-color scale-125' : 'bg-gray-300'
          ]"
        ></div>
      </div>
      
      <!-- Tips for getting reviews -->
      <div v-if="totalReviews === 0" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md mt-8">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Tips for getting reviews</h3>
            <div class="mt-2 text-sm text-blue-700">
              <ul class="list-disc pl-5 space-y-1">
                <li>Provide excellent customer service during trades</li>
                <li>Ensure your products meet or exceed buyer expectations</li>
                <li>Politely ask satisfied customers to leave a review</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Subtle card hover animation */
.card {
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-3px);
}

/* Improve tap target size on mobile */
@media (max-width: 640px) {
  button {
    min-height: 44px;
    min-width: 44px;
  }
}

/* Rating bar animation */
@keyframes grow-bar {
  from { width: 0; }
  to { width: var(--final-width); }
}
</style>
