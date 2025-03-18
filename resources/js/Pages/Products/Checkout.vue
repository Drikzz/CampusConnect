<template>
  <form @submit.prevent="submitForm" id="checkout-form">
    <input type="hidden" name="product_id" :value="product.id">
    <input type="hidden" name="sub_total" id="form-total" :value="product.discounted_price">
    <input type="hidden" name="quantity" id="form-quantity" :value="quantity">

    <div class="min-h-screen bg-gray-50 pb-24 pt-12 md:pb-20 md:pt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
          <!-- Left Column - Order Summary -->
          <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-sm h-fit">
            <div>
              <h2 class="text-xl md:text-2xl font-Satoshi-bold mb-6">Order Summary</h2>

              <!-- Product Card -->
              <div class="flex flex-col gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-lg space-y-4 sm:space-y-0">
                  <!-- Product Image and Name -->
                  <div class="flex items-center gap-4 w-full sm:w-auto">
                    <img :src="'/storage/' + product.images[0]" :alt="product.name" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                    <h3 class="font-Satoshi-bold">{{ capitalizeFirst(product.name) }}</h3>
                  </div>

                  <!-- Quantity Controls -->
                  <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                    <div class="relative flex items-center max-w-[8rem]">
                      <button type="button" @click="decrementQuantity" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 focus:ring-2 focus:outline-none">
                        <svg class="w-3 h-3 text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                        </svg>
                      </button>

                      <input type="text" v-model="quantity" readonly class="bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-0 focus:outline-none block w-full py-2.5">

                      <button type="button" @click="incrementQuantity" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 focus:ring-2 focus:outline-none">
                        <svg class="w-3 h-3 text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                        </svg>
                      </button>
                    </div>

                    <!-- Subtotal -->
                    <div class="text-right ml-4">
                      <p class="font-Satoshi-bold whitespace-nowrap">₱{{ formatPrice(calculateSubtotal) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Variant Selection if applicable -->
              <div v-if="product.has_variants" class="mb-6">
                <label class="block text-sm font-medium mb-2">Size/Variant</label>
                <select v-model="selectedVariant" class="w-full md:w-48 rounded-md border-gray-200">
                  <option v-for="variant in product.variants" :key="variant.id" :value="variant.id">
                    {{ variant.name }}
                  </option>
                </select>
              </div>

              <!-- Seller Information -->
              <div class="border-t border-gray-100 py-4 mb-6">
                <h4 class="font-Satoshi-bold mb-2">Seller Information</h4>
                <div class="flex items-center gap-3">
                  <img :src="'/storage/' + product.seller.profile_picture" class="w-8 h-8 rounded-full object-cover">
                  <div>
                    <p class="font-medium">{{ product.seller.first_name }}</p>
                    <p class="text-sm text-gray-500">{{ product.seller.location || 'Location N/A' }}</p>
                  </div>
                </div>
              </div>

              <!-- Price Breakdown -->
              <div class="space-y-3 border-t border-b py-4">
                <div class="flex justify-between">
                  <span class="font-Satoshi">Original Price</span>
                  <span class="text-black font-Satoshi">₱{{ formatPrice(product.price) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="font-Satoshi">Discount ({{ product.discount }}%)</span>
                  <span class="text-red-500 font-Satoshi">-₱{{ formatPrice(calculateDiscount) }}</span>
                </div>
                <div class="flex justify-between font-Satoshi-bold text-lg">
                  <span class="font-Satoshi-bold">Total</span>
                  <span class="font-Satoshi-bold">₱{{ formatPrice(calculateTotal) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Checkout Form -->
          <div class="md:col-span-1 bg-white rounded-lg shadow-sm flex flex-col">
            <div class="md:col-span-1 bg-white rounded-lg shadow-sm flex flex-col">
  <div class="bg-white p-6 border-b">
    <h2 class="text-2xl font-Satoshi-bold">Checkout Details</h2>
  </div>

  <div class="p-6 overflow-y-auto flex-1">
    <div class="space-y-6">
      <!-- Contact Information -->
      <div>
        <div class="flex items-center justify-between w-full py-5 font-medium border-b border-gray-200">
          <span class="font-Satoshi-bold text-black">Contact Information</span>
        </div>
        <div class="py-5 border-b border-gray-200 space-y-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-900">WMSU Email</label>
            <input type="email" v-model="form.email" :value="user.wmsu_email"
              class="w-full px-4 py-2 border rounded-md bg-gray-100" readonly>
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-900">Phone number</label>
            <input type="tel" v-model="form.phone" :value="user.phone"
              class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none">
          </div>
        </div>
      </div>

      <!-- Meetup Schedule Section -->
      <div>
        <div class="flex items-center justify-between w-full py-5 font-medium border-b border-gray-200">
          <span class="font-Satoshi-bold text-black">Meetup Schedule</span>
        </div>
        <div class="py-5 border-b border-gray-200 space-y-4">
          <template v-for="location in product.seller.meetupLocations" :key="location.id">
            <div class="border rounded-lg p-4 space-y-4">
              <div class="font-medium text-gray-900">{{ location.full_name }}</div>
              <div class="text-sm text-gray-600">{{ location.description }}</div>

              <!-- Available Schedules -->
              <div class="space-y-2">
                <template v-for="day in parseAvailableDays(location.available_days)" :key="day">
                  <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input type="radio" v-model="selectedMeetupSchedule" 
                           :value="`${location.id}_${getDayNumber(day)}`"
                           class="form-radio text-black mr-3">
                    <div>
                      <span class="font-medium">{{ day }}</span>
                      <span class="text-gray-600 text-sm ml-2">
                        {{ formatTime(location.available_from) }} - {{ formatTime(location.available_until) }}
                      </span>
                    </div>
                  </label>
                </template>
              </div>

              <!-- Location Details with Google Maps -->
              <div class="text-sm text-gray-600 mt-2">
                <p>{{ location.custom_location }}</p>
                <template v-if="location.latitude && location.longitude">
                  <a :href="`https://maps.google.com/?q=${location.latitude},${location.longitude}`"
                     target="_blank"
                     class="inline-flex items-center text-primary-color hover:text-primary-color/80 mt-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    View on Maps
                  </a>
                </template>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Payment Method Section -->
      <div class="py-5 space-y-4">
        <h3 class="font-Satoshi-bold">Payment Method</h3>
        <div class="space-y-2">
          <label class="flex items-center space-x-3">
            <input type="radio" v-model="selectedPaymentMethod" value="cash" class="form-radio text-black">
            <span>Cash on Meetup</span>
          </label>
          <label class="flex items-center space-x-3">
            <input type="radio" v-model="selectedPaymentMethod" value="gcash" class="form-radio text-black">
            <span>GCash</span>
          </label>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Button -->
  <div class="p-6 border-t bg-white">
    <button type="submit"
      class="w-full bg-black text-white py-3 rounded-full font-Satoshi-bold hover:bg-gray-800 transition-colors">
      Place Order
    </button>
  </div>
</div>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { useToast } from '@/Components/ui/toast/use-toast'

export default {
  components: {
    Link,
    Button,
    Input,
    Label,
    RadioGroup,
    RadioGroupItem
  },

  props: {
    product: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      required: true
    },
    meetupLocations: {
      type: Array,
      default: () => []
    }
  },

  setup(props) {
    const quantity = ref(1)
    const selectedVariant = ref(null)
    const selectedPaymentMethod = ref('cash')
    const selectedMeetupSchedule = ref(null)

    const calculateSubtotal = computed(() => {
      return props.product.discounted_price * quantity.value
    })

    const calculateDiscount = computed(() => {
      return props.product.price - props.product.discounted_price
    })

    const calculateTotal = computed(() => {
      return calculateSubtotal.value
    })

    const incrementQuantity = () => {
      if (quantity.value < props.product.stock) {
        quantity.value++
      }
    }

    const decrementQuantity = () => {
      if (quantity.value > 1) {
        quantity.value--
      }
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat().format(price)
    }

    const capitalizeFirst = (string) => {
      return string.charAt(0).toUpperCase() + string.slice(1)
    }

    const form = ref({
      email: props.user?.wmsu_email || '',
      phone: props.user?.phone || '',
    })

    const parseAvailableDays = (days) => {
      if (!days) return []
      return typeof days === 'string' ? JSON.parse(days) : days
    }

    const getDayNumber = (dayName) => {
      const days = {
        'Sunday': 0,
        'Monday': 1,
        'Tuesday': 2,
        'Wednesday': 3,
        'Thursday': 4,
        'Friday': 5,
        'Saturday': 6
      }
      return days[dayName] || 0
    }

    const formatTime = (time) => {
      if (!time) return ''
      return new Date(`2000-01-01 ${time}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      }).toLowerCase()
    }

    const submitForm = () => {
      if (!selectedMeetupSchedule.value) {
        alert('Please select a meetup schedule')
        return
      }

      router.post(route('checkout.process'), {
        product_id: props.product.id,
        quantity: quantity.value,
        sub_total: calculateTotal.value,
        variant_id: selectedVariant.value,
        payment_method: selectedPaymentMethod.value,
        meetup_schedule: selectedMeetupSchedule.value,
        email: form.value.email,
        phone: form.value.phone
      })
    }

    return {
      quantity,
      selectedVariant,
      selectedPaymentMethod,
      selectedMeetupSchedule,
      calculateSubtotal,
      calculateDiscount,
      calculateTotal,
      incrementQuantity,
      decrementQuantity,
      formatPrice,
      capitalizeFirst,
      submitForm,
      form,
      parseAvailableDays,
      formatTime,
      getDayNumber
    }
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>