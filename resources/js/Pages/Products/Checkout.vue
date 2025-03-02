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
            <!-- Form sections here -->
            <!-- Similar structure to the blade template but with Vue syntax -->
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export default {
  props: {
    product: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      required: true
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

    const submitForm = () => {
    router.post(route('checkout.process'), {
        product_id: props.product.id,
        quantity: quantity.value,
        sub_total: calculateTotal.value,
        variant_id: selectedVariant.value,
        payment_method: selectedPaymentMethod.value,
        meetup_schedule: selectedMeetupSchedule.value,
        // Add any other form fields you need
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
      submitForm
    }
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>