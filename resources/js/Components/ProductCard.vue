<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { HeartIcon as HeartIconOutline } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';
import TradeForm from '@/Components/TradeForm.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    disableWishlistCheck: {
        type: Boolean,
        default: false
    },
    showTradeButton: {
        type: Boolean, 
        default: false
    }
});

// Initialize isInWishlist but don't check status automatically
const isInWishlist = ref(false);
const wishlistChecked = ref(false);

// Only check wishlist status when needed
const checkWishlistStatus = async () => {
    if (props.disableWishlistCheck || wishlistChecked.value) return;
    
    try {
        const response = await axios.get(`/dashboard/wishlist/check/${props.product.id}`);
        isInWishlist.value = response.data.inWishlist;
        wishlistChecked.value = true;
    } catch (error) {
        console.error('Error checking wishlist status:', error);
    }
};

const toggleWishlist = async (event) => {
    event.preventDefault();
    event.stopPropagation();
    
    if (props.disableWishlistCheck) return;
    
    // Check status first if not already done
    if (!wishlistChecked.value) {
        await checkWishlistStatus();
    }
    
    try {
        const response = await axios.post('/dashboard/wishlist', {
            product_id: props.product.id
        });
        
        // Update the wishlist state
        isInWishlist.value = response.data.status === 'added';
        wishlistChecked.value = true;
    } catch (error) {
        // Handle authentication errors
        if (error.response && error.response.status === 401) {
            // Redirect to login without showing the full URL in the address bar
            window.location.href = '/login';
            return;
        }
        
        console.error('Error toggling wishlist:', error);
    }
};

// Handle image loading errors
const handleImageError = (e) => {
    e.target.src = '/images/placeholder.jpg';
};

// Get formatted price
const formatPrice = (price) => {
    return Number(price).toLocaleString();
};

// Get the main image URL or properly parse it if needed
const getImageUrl = computed(() => {
    if (!props.product.images || props.product.images.length === 0) {
        return '/images/placeholder.jpg';
    }
    
    let image = props.product.images[0];
    
    // Check if image is a string that looks like JSON
    if (typeof image === 'string' && (image.startsWith('{') || image.startsWith('['))) {
        try {
            const parsed = JSON.parse(image);
            // If it's an array, take the first element
            if (Array.isArray(parsed) && parsed.length > 0) {
                return parsed[0].startsWith('http') ? parsed[0] : `/storage/${parsed[0]}`;
            }
            // If it's an object with a path property
            else if (parsed && parsed.path) {
                return parsed.path.startsWith('http') ? parsed.path : `/storage/${parsed.path}`;
            }
            // If it's an object with a name property (fallback)
            else if (parsed && parsed.name) {
                return `/storage/${parsed.name}`;
            }
        } catch (e) {
            console.error('Error parsing image JSON:', e);
        }
    }
    
    // If it's a plain string path
    return image.startsWith('http') ? image : `/storage/${image}`;
});

// Calculate discount percentage
const discountPercentage = computed(() => {
    if (!props.product.price || !props.product.discounted_price) return 0;
    
    const discount = ((props.product.price - props.product.discounted_price) / props.product.price) * 100;
    return Math.round(discount);
});

// Check if product has a discount
const hasDiscount = computed(() => {
    return props.product.price > props.product.discounted_price;
});

// Add this computed property to handle category display
const categoryName = computed(() => {
    if (!props.product.category) return 'Uncategorized';
    
    // If category is an object with a name property
    if (typeof props.product.category === 'object' && props.product.category !== null) {
        return props.product.category.name || 'Uncategorized';
    }
    
    // If it's a string, use it directly
    return props.product.category;
});

// Add state for trade modal
const showTradeModal = ref(false);

// Function to open trade modal
const openTradeModal = () => {
    showTradeModal.value = true;
};
</script>

<template>
    <div class="w-full h-full p-4 flex flex-col justify-between items-start gap-4 hover:shadow-lg rounded transition-shadow">
        <div class="relative w-full">
            <Link :href="route('products.show', product.id)" class="group">
                <div class="relative aspect-square overflow-hidden rounded-lg">
                    <img 
                        :src="getImageUrl"
                        :alt="product.name"
                        class="h-full w-full object-cover object-center transition-all duration-300 group-hover:scale-105"
                        @error="handleImageError"
                    />
                </div>
            </Link>
            
            <!-- Discount Badge -->
            <div v-if="hasDiscount" class="absolute bottom-2 right-2 rounded-2xl bg-white px-3 py-1">
                <p class="font-Satoshi-bold text-sm text-black">
                    {{ discountPercentage }}%
                </p>
            </div>

            <!-- Wishlist Button - Only shown if wishlist check is not disabled -->
            <div v-if="!disableWishlistCheck" 
                 @click="toggleWishlist"
                 class="absolute top-3 right-3 bg-white p-2 rounded-full hover:shadow-md hover:ring-2 hover:ring-black transition-all cursor-pointer">
                <HeartIconSolid v-if="isInWishlist" class="w-5 h-5 text-red-500" aria-hidden="true" />
                <HeartIconOutline v-else class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="w-full">
            <Link :href="route('products.show', product.id)" class="hover:text-primary-color transition-colors">
                <p class="font-Satoshi-bold text-xl line-clamp-2">{{ product.name }}</p>
            </Link>
        </div>

        <div class="flex justify-start items-center w-fit gap-2">
            <p class="font-Satoshi-bold text-xl text-primary-color">₱{{ formatPrice(product.discounted_price) }}</p>
            <p v-if="hasDiscount" class="font-Satoshi-bold line-through text-lg text-gray-400">₱{{ formatPrice(product.price) }}</p>
        </div>

        <div class="flex justify-between items-center w-full">
            <Link v-if="product.is_buyable" :href="route('summary', product.id)"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 transition-all focus:opacity-60">
                <button class="font-Satoshi text-white">Buy Now!</button>
            </Link>
            <button v-if="product.is_tradable && showTradeButton" 
                @click.prevent="openTradeModal"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 transition-all focus:opacity-60">
                <span class="font-Satoshi text-white">Trade Now!</span>
            </button>
            <Link v-else-if="product.is_tradable" :href="route('products.trade')"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 transition-all focus:opacity-60">
                <button class="font-Satoshi text-white">Trade Now!</button>
            </Link>
            <span v-if="!product.is_buyable && !product.is_tradable" class="font-Satoshi text-gray-500">
                Not available for purchase or trade
            </span>
            
            <span class="text-xs text-gray-500">{{ categoryName }}</span>
        </div>

        <!-- Add Trade Form Modal -->
        <TradeForm 
            v-if="showTradeModal" 
            :product="product" 
            :open="showTradeModal"
            @close="showTradeModal = false"
        />
    </div>
</template>
