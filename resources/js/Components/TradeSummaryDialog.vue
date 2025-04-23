<script setup>
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";

const props = defineProps({
  open: Boolean,
  product: Object,
  productImageUrl: String,
  offer: Object,
  totalOfferedValue: Number,
  selectedLocationName: String,
  selectedDay: String,
  selectedTimeDisplay: String,
  loading: Boolean,
  editMode: Boolean
});

// Add new emit event for summary display
const emit = defineEmits(['close', 'update:open', 'cancel', 'showSummary', 'updateTrade']);

const formatPrice = (price) => {
    return new Intl.NumberFormat().format(price || 0);
};

const productEffectivePrice = computed(() => {
    if (!props.product) return 0;
    return (props.product.discounted_price && props.product.discounted_price < props.product.price)
        ? parseFloat(props.product.discounted_price)
        : parseFloat(props.product.price);
});

const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const getConditionName = (conditionValue) => {
    const conditions = {
        'new': 'New',
        'used_like_new': 'Used - Like New',
        'used_good': 'Used - Good',
        'used_fair': 'Used - Fair'
    };
    return conditions[conditionValue] || conditionValue;
};

const formattedMeetupDate = computed(() => {
    if (!props.offer.meetup_date) return '';
    const dateParts = props.offer.meetup_date.split('-');
    if (dateParts.length !== 3) return props.offer.meetup_date;
    
    const date = new Date(dateParts[0], parseInt(dateParts[1]) - 1, dateParts[2]);
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric' 
    });
});

const dialogTitle = computed(() => {
    return props.editMode ? 'Confirm Trade Update' : 'Confirm Trade Offer';
});

// Improved normalizeImageUrl function that handles both blob URLs and storage paths
const normalizeImageUrl = (url) => {
  if (!url) return '';
  
  try {
    // Handle blob objects with isBlob flag (from prepareOfferForSummary)
    if (typeof url === 'object' && url !== null && url.isBlob && url.url) {
      return url.url;
    }
    
    // Handle blob URLs directly
    if (typeof url === 'string' && url.startsWith('blob:')) {
      return url;
    }
    
    // Handle fully-qualified URLs
    if (typeof url === 'string' && (url.startsWith('http://') || url.startsWith('https://'))) {
      return url;
    }
    
    // Handle storage paths with leading slash
    if (typeof url === 'string' && url.startsWith('/storage/')) {
      return url;
    }
    
    // Handle storage paths with no leading slash
    if (typeof url === 'string' && url.startsWith('storage/')) {
      return '/' + url;
    }
    
    // For all other paths, assume they need /storage/ prefix
    if (typeof url === 'string') {
      return `/storage/${url}`;
    }
    
    return '';
  } catch (error) {
    console.error('Error normalizing image URL:', error);
    return '';
  }
};

// Handle image load error
const handleImageError = (event) => {
  // Use a data URI for placeholder instead of referencing a file
  event.target.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)" modal>
        <DialogContent class="mx-4 w-[75%] sm:max-w-20xl overflow-y-auto max-h-[90vh] p-6 md:p-8 lg:p-10 bg-background dark:bg-gray-900 border-border dark:border-gray-700">
            <DialogHeader>
                <DialogTitle class="text-foreground dark:text-white font-medium text-xl">{{ dialogTitle }}</DialogTitle>
                <DialogDescription class="text-muted-foreground dark:text-gray-400">
                    Please confirm your trade offer details below.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Product and Offer Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Product being traded for -->
                    <div class="space-y-4">
                        <h3 class="font-medium text-lg">You are trading for:</h3>
                        <div class="flex bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700 p-3">
                            <div class="flex-shrink-0 w-16 h-16 mr-3 rounded-md overflow-hidden border border-border dark:border-gray-700">
                                <img 
                                    :src="normalizeImageUrl(productImageUrl)" 
                                    :alt="props.product?.name || 'Product'"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                            </div>
                            <div>
                                <h4 class="font-medium text-foreground dark:text-white">{{ capitalizeFirst(props.product?.name || '') }}</h4>
                                <p class="text-primary dark:text-primary">
                                    {{ formatPrice(productEffectivePrice) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Your offer summary -->
                    <div class="space-y-4">
                        <h3 class="font-medium text-lg">Your offer:</h3>
                        <div class="bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700 p-3">
                            <div v-if="props.offer.offered_items && props.offer.offered_items.length" class="space-y-2">
                                <div v-for="(item, index) in props.offer.offered_items" :key="index" class="flex items-center gap-2">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-md overflow-hidden border border-border dark:border-gray-700">
                                        <img 
                                            :src="normalizeImageUrl(item.images && item.images.length ? item.images[0] : null)" 
                                            :alt="item.name"
                                            class="w-full h-full object-cover"
                                            @error="handleImageError"
                                        />
                                    </div>
                                    <div class="flex-1 text-sm">
                                        <span class="font-medium block">{{ item.name }}</span>
                                        <span class="text-xs">{{ item.quantity }} × {{ formatPrice(item.estimated_value) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="props.offer.additional_cash > 0" class="mt-2 pt-2 border-t border-border dark:border-gray-700">
                                <span class="font-medium">Additional Cash: </span>
                                <span class="text-primary dark:text-primary">{{ formatPrice(props.offer.additional_cash) }}</span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-border dark:border-gray-700">
                                <span class="font-medium">Total Value: </span>
                                <span class="text-primary-color dark:text-primary-color font-bold">{{ formatPrice(props.totalOfferedValue) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meetup details -->
                <div class="mt-4 pt-4 border-t border-border dark:border-gray-700">
                    <h3 class="font-medium text-lg mb-3">Meetup Details:</h3>
                    <div class="bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="font-medium block">Location:</span>
                            <span class="text-muted-foreground dark:text-gray-400">{{ props.selectedLocationName || 'Not specified' }}</span>
                        </div>
                        <div>
                            <span class="font-medium block">Date & Time:</span>
                            <span class="text-muted-foreground dark:text-gray-400">
                                {{ formattedMeetupDate }} at {{ props.selectedTimeDisplay || 'Not specified' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notes if any -->
                <div v-if="props.offer.notes" class="mt-4 pt-4 border-t border-border dark:border-gray-700">
                    <h3 class="font-medium text-lg mb-3">Notes:</h3>
                    <div class="bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700 p-4">
                        <p class="text-foreground dark:text-white whitespace-pre-wrap">{{ props.offer.notes }}</p>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:gap-0 sm:justify-between pt-4 border-t border-border dark:border-gray-700">
                <Button 
                    type="button" 
                    variant="outline" 
                    @click="emit('cancel')"
                    class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                    :disabled="props.loading"
                >
                    Cancel
                </Button>
                <Button 
                    type="button" 
                    @click="emit('updateTrade')" 
                    :disabled="props.loading"
                    class="bg-primary-color hover:bg-opacity-90 text-white"
                >
                    {{ props.loading ? 'Processing...' : (props.editMode ? 'Update Trade' : 'Submit Trade') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Custom styles for image display */
img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  background-color: var(--background);
}

/* Fade-in animation for images */
img {
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
}

img[src] {
  opacity: 1;
}

/* Fix for dark mode */
.dark img {
  filter: brightness(0.95);
}
</style>