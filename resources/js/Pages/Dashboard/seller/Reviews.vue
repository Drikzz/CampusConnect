<script setup>
import { ref } from 'vue'
import DashboardLayout from '../DashboardLayout.vue'
import StatsCard from '../../../Components/StatsCard.vue'

const props = defineProps({
  user: Object,
  stats: Object,
  reviews: Object
})
</script>

<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">My Reviews</h2>
      </div>

      <!-- Reviews List -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6">
          <template v-if="reviews.data && reviews.data.length">
            <div class="divide-y">
              <div v-for="review in reviews.data" :key="review.id" class="py-4">
                <div class="flex items-start space-x-4">
                  <div class="flex-1">
                    <div class="flex items-center space-x-2">
                      <span class="font-medium">{{ review.buyer.name }}</span>
                      <span class="text-gray-500">•</span>
                      <span class="text-gray-500">{{ new Date(review.created_at).toLocaleDateString() }}</span>
                    </div>
                    <div class="mt-1 flex items-center">
                      <div class="flex items-center">
                        <template v-for="n in 5" :key="n">
                          <svg
                            :class="[
                              n <= review.rating ? 'text-yellow-400' : 'text-gray-300',
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
                    <p class="mt-2 text-gray-700">{{ review.comment }}</p>
                  </div>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="text-center py-8 text-gray-500">
            No reviews yet
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
