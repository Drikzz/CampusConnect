<template>
  <div>
    <Head title="Checkout" />

    <form @submit.prevent="submitForm" id="checkout-form" class="min-h-screen bg-gray-50 pb-24 pt-12">
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
                    <img :src="product.images[0]" 
                         :alt="product.name" 
                         class="w-16 h-16 object-cover rounded-md flex-shrink-0"
                         @error="handleImageError">
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
                        <div class="font-medium">{{ schedule.location || 'Location Not Available' }}</div>
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
              <Button 
                type="submit" 
                class="w-full"
                :disabled="loading"
              >
                <span v-if="loading" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processing...
                </span>
                <span v-else>
                  Place Order
                </span>
              </Button>
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
import { useToast } from '@/Components/ui/toast/use-toast';
import axios from 'axios';

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

console.log('Meetup Locations:', props.meetupLocations); // Debug log

const { toast } = useToast();

const quantity = ref(1);
const loading = ref(false); // Add loading state
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
  // Set loading state to true
  loading.value = true;

  // Make sure all required fields are filled
  if (!form.value.meetup_schedule) {
    toast({
      title: 'Validation Error',
      description: 'Please select a meetup schedule',
      variant: 'destructive'
    });
    loading.value = false; // Reset loading state
    return;
  }

  // Make sure we have all the required data
  const requiredFields = {
    'product_id': form.value.product_id,
    'sub_total': calculateTotal.value,
    'quantity': quantity.value,
    'phone': form.value.phone,
    'payment_method': form.value.payment_method,
    'meetup_schedule': form.value.meetup_schedule
  };

  // Check if any required field is missing
  const missingFields = Object.entries(requiredFields)
    .filter(([key, value]) => !value)
    .map(([key]) => key);

  if (missingFields.length > 0) {
    toast({
      title: 'Missing Required Fields',
      description: `Please fill in: ${missingFields.join(', ')}`,
      variant: 'destructive'
    });
    loading.value = false; // Reset loading state
    return;
  }

  // Prepare the data object with all fields properly set
  const submitData = {
    product_id: form.value.product_id,
    quantity: quantity.value,
    sub_total: calculateTotal.value, // Use computed value for consistency
    email: form.value.email,
    phone: form.value.phone,
    payment_method: form.value.payment_method,
    meetup_schedule: form.value.meetup_schedule
  };

  console.log("Submitting checkout data:", submitData);

  // Use direct axios call instead of Inertia router for better debugging
  axios.post(route('checkout.process'), submitData)
    .then(response => {
      console.log("Checkout response:", response.data);
      toast({
        title: 'Order Placed',
        description: 'Your order has been placed successfully!',
        variant: 'default'
      });
      
      // Short delay before redirecting to ensure toast is visible
      setTimeout(() => {
        window.location.href = route('dashboard.orders');
      }, 1000);
    })
    .catch(error => {
      console.error('Checkout error:', error);
      console.error('Error response:', error.response?.data);
      
      let errorMessage = 'There was a problem processing your order. Please try again.';
      
      // Check for validation errors
      if (error.response?.data?.errors) {
        const firstError = Object.values(error.response.data.errors)[0];
        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
      }
      
      toast({
        title: 'Error',
        description: errorMessage,
        variant: 'destructive'
      });
    })
    .finally(() => {
      loading.value = false; // Reset loading state
    });
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
        console.log('Processing location:', location); // Debug log
        const availableDays = location.available_days || [];
        availableDays.forEach(day => {
            schedules.push({
                id: `${location.id}_${day}`,
                location: location.location?.name || 'Location Not Available', // Use the relationship
                day: day,
                timeFrom: formatTime(location.available_from),
                timeUntil: formatTime(location.available_until),
                description: location.description || '',
                maxMeetups: location.max_daily_meetups
            });
        });
    });

    console.log('Generated schedules:', schedules); // Debug log
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

// Add in script setup section
const handleImageError = (e) => {
    e.target.src = '/images/placeholder.jpg';
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>