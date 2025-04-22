<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin"></div>
    </div>
    
    <!-- Error Message -->
    <div v-if="error" class="p-4 bg-red-50 text-red-500 rounded-md">
      {{ error }}
    </div>
    
    <!-- Write Review Section -->
    <div v-if="isCompleted && !hasReviewed && !loading" class="border rounded-lg p-4 bg-gray-50 mb-6">
      <h3 class="text-lg font-semibold mb-4">Leave a Review for {{ sellerName }}</h3>
      
      <form @submit.prevent="submitReview">
        <div class="space-y-4">
          <!-- Star Rating -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <div class="flex space-x-1">
              <button 
                v-for="star in 5" 
                :key="star" 
                type="button"
                @click="newReview.rating = star"
                class="focus:outline-none"
              >
                <svg 
                  :class="[ 
                    'w-8 h-8', 
                    star <= newReview.rating ? 'text-yellow-400 fill-current' : 'text-gray-300' 
                  ]"
                  xmlns="http://www.w3.org/2000/svg" 
                  viewBox="0 0 24 24"
                >
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                </svg>
              </button>
            </div>
            <div v-if="validationErrors.rating" class="text-red-500 text-sm mt-1">
              {{ validationErrors.rating }}
            </div>
          </div>
          
          <!-- Review Text -->
          <div>
            <label for="review" class="block text-sm font-medium text-gray-700 mb-1">Review (Optional)</label>
            <Textarea 
              id="review" 
              v-model="newReview.review" 
              placeholder="Share your experience with this seller..."
              class="w-full"
              rows="4"
            ></Textarea>
            <div v-if="validationErrors.review" class="text-red-500 text-sm mt-1">
              {{ validationErrors.review }}
            </div>
          </div>
          
          <!-- Submit Button -->
          <div class="flex justify-end">
            <Button 
              type="submit" 
              :disabled="submitting || !newReview.rating"
              class="relative"
            >
              <span v-if="!submitting">Submit Review</span>
              <span v-else class="absolute inset-0 flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
            </Button>
          </div>
        </div>
      </form>
    </div>

    <!-- Reviews Display Section -->
    <div v-if="!loading && reviews.length > 0" class="space-y-4">
      <h3 class="text-lg font-semibold">Reviews for {{ sellerName }}</h3>
      
      <div v-for="review in reviews" :key="review.id" class="border rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center">
            <div class="flex space-x-1 mr-2">
              <svg 
                v-for="star in 5" 
                :key="star" 
                :class="['w-5 h-5', star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300']"
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 24 24"
              >
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
              </svg>
            </div>
            <span class="font-medium">{{ review.reviewer && review.reviewer.name ? review.reviewer.name : 'Anonymous' }}</span>
          </div>
          <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
        </div>
        <p v-if="review.review" class="text-gray-700">{{ review.review }}</p>
        <p v-else class="text-gray-500 italic">No comment provided</p>
      </div>
    </div>
    
    <div v-if="!loading && reviews.length === 0" class="text-center py-4 text-gray-500">
      No reviews yet for this seller.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Components/ui/toast/use-toast';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';

const props = defineProps({
  sellerCode: String,
  sellerName: String,
  transactionId: [Number, String],
  transactionType: String,
  isCompleted: Boolean
});

// Define an emits option to handle the review submission event
const emit = defineEmits(['review-submitted']);

const { toast } = useToast();
const loading = ref(true);
const submitting = ref(false);
const error = ref(null);
const reviews = ref([]);
const userId = ref(null);
const hasReviewed = ref(false);
const newReview = ref({ rating: 0, review: '' });
const validationErrors = ref({});

const fetchReviews = async () => {
  try {
    loading.value = true;
    
    // Use fetch API instead of Inertia navigation
    const response = await fetch(`/seller-reviews/${props.sellerCode}`);
    
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    
    const data = await response.json();
    
    // Add debugging to see the exact structure of reviews data
    console.log('Reviews data from API:', data);
    
    if (data.success) {
      reviews.value = data.reviews;
      userId.value = data.user_id;
      hasReviewed.value = reviews.value.some(r => r.reviewer_id === userId.value);
    } else {
      error.value = 'Failed to load reviews';
    }
  } catch (err) {
    console.error('Review fetch error:', err);
    error.value = 'Failed to load reviews. Please try again later.';
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const submitReview = async () => {
  validationErrors.value = {};
  
  // Enhanced client-side validation
  if (!props.sellerCode || !props.transactionId || !props.transactionType) {
    error.value = 'Missing required information (seller, transaction ID, or transaction type)';
    return;
  }
  
  if (!newReview.value.rating) {
    validationErrors.value.rating = 'Please select a rating';
    return;
  }
  
  submitting.value = true;
  
  try {
    // Use fetch API for POST request with proper URL
    const response = await fetch('/seller-reviews', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        seller_code: props.sellerCode,
        rating: newReview.value.rating,
        review: newReview.value.review,
        transaction_id: props.transactionId,
        transaction_type: props.transactionType
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      toast({ title: 'Success', description: 'Review submitted successfully' });
      newReview.value = { rating: 0, review: '' };
      hasReviewed.value = true;
      emit('review-submitted');
      fetchReviews();
    } else {
      // More detailed error handling
      console.error('Review submission response:', data);
      error.value = data.message || 'Failed to submit review';
      
      if (data.errors) {
        validationErrors.value = data.errors;
      }
    }
  } catch (err) {
    console.error('Review submission error:', err);
    error.value = 'Failed to submit review. Please try again later.';
  } finally {
    submitting.value = false;
  }
};

onMounted(fetchReviews);
</script>
