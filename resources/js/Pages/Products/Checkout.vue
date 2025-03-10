<template>
  <div>
    <Head title="Checkout" />

    <form @submit.prevent="submitForm" id="checkout-form" class="min-h-screen bg-gray-50 pb-24 pt-12">
      <input type="hidden" name="product_id" :value="product.id">
      <input type="hidden" name="sub_total" id="form-total" :value="product.discounted_price">
      <input type="hidden" name="quantity" id="form-quantity" :value="quantity">

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
            <div class="p-6 border-b">
              <h2 class="text-2xl font-Satoshi-bold">Checkout Details</h2>
            </div>

            <div class="p-6 flex-1 overflow-y-auto">
              <!-- Contact Information -->
              <div class="mb-6">
                <h3 class="font-Satoshi-bold mb-4">Contact Information</h3>
                <div class="space-y-4">
                  <div>
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" readonly />
                  </div>
                  <div>
                    <Label for="phone">Phone Number</Label>
                    <Input id="phone" type="tel" v-model="form.phone" required />
                  </div>
                </div>
              </div>

              <!-- Meetup Schedule -->
              <div class="mb-6">
                <h3 class="font-Satoshi-bold mb-4">Available Meetup Schedules</h3>
                <ScrollArea class="h-[200px] rounded-md border p-4">
                  <div class="space-y-2">
                    <label v-for="schedule in availableSchedules" 
                           :key="schedule.id"
                           class="flex p-3 border rounded hover:bg-gray-50 cursor-pointer">
                      <input type="radio" 
                             v-model="form.meetup_schedule"
                             :value="schedule.id"
                             class="mr-3">
                      <div>
                        <div class="font-medium">{{ schedule.location }}</div>
                        <div class="text-sm text-gray-600">
                          {{ schedule.day }} | {{ schedule.timeFrom }} - {{ schedule.timeUntil }}
                        </div>
                        <div v-if="schedule.description" class="text-sm text-gray-500 mt-1">
                          {{ schedule.description }}
                        </div>
                      </div>
                    </label>
                  </div>
                </ScrollArea>
              </div>

              <!-- Payment Method -->
              <div class="mb-6">
                <h3 class="font-Satoshi-bold mb-4">Payment Method</h3>
                <div class="space-y-2">
                  <label class="flex items-center space-x-3">
                    <input type="radio" v-model="form.payment_method" value="cash" class="form-radio">
                    <span>Cash on Meetup</span>
                  </label>
                  <label class="flex items-center space-x-3">
                    <input type="radio" v-model="form.payment_method" value="gcash" class="form-radio">
                    <span>GCash</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Order Button -->
            <div class="p-6 border-t">
              <Button type="submit" class="w-full">Place Order</Button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Label } from "@/Components/ui/label"
import { ScrollArea } from '@/Components/ui/scroll-area'
import { Head } from '@inertiajs/vue3';

const props = defineProps({
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
        required: true
    }
});

const quantity = ref(1);
const form = ref({
    product_id: props.product.id,
    quantity: 1,
    sub_total: props.product.discounted_price,
    phone: props.user.phone || '',
    email: props.user.wmsu_email || '',
    payment_method: 'cash',
    meetup_schedule: '', // Will be in format: locationId_dayNumber
    delivery_estimate: `${new Date().toLocaleDateString()} - ${new Date(Date.now() + 7*24*60*60*1000).toLocaleDateString()}`,
    address: '',
    city: 'Zamboanga City',
    postal_code: '7000'
});

// Calculate subtotal
const calculateSubtotal = computed(() => {
    return props.product.discounted_price * quantity.value;
});

const formattedSubtotal = computed(() => {
    return new Intl.NumberFormat().format(calculateSubtotal.value);
});

// Update quantity and form values
const updateQuantity = (value) => {
    if (value < 1) value = 1;
    if (value > props.product.stock) value = props.product.stock;
    quantity.value = value;
    form.value.quantity = value;
    form.value.sub_total = calculateSubtotal.value;
};

const submitForm = () => {
    router.post(route('checkout.process'), form.value);
};

// Function to format time from 24h to 12h format
const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const date = new Date(2000, 0, 1, hours, minutes);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric',
        minute: '2-digit',
        hour12: true 
    }).toLowerCase();
};

// Computed property to get available schedules from meetup locations
const availableSchedules = computed(() => {
    const schedules = [];
    
    props.meetupLocations.forEach(location => {
        const availableDays = location.available_days || [];
        availableDays.forEach(day => {
            schedules.push({
                id: `${location.id}_${day}`,
                location: location.full_name,
                day: day,
                timeFrom: formatTime(location.available_from),
                timeUntil: formatTime(location.available_until),
                description: location.description || '',
                maxMeetups: location.max_daily_meetups
            });
        });
    });

    return schedules;
});

// Add utility functions
const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat().format(price);
};

// Add these computed properties for price calculations
const calculateDiscount = computed(() => {
    return (props.product.price - props.product.discounted_price) * quantity.value;
});

const calculateTotal = computed(() => {
    return props.product.discounted_price * quantity.value;
});

// Add increment/decrement functions
const incrementQuantity = () => {
    if (quantity.value < props.product.stock) {
        updateQuantity(quantity.value + 1);
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        updateQuantity(quantity.value - 1);
    }
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>