<script setup>
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import ImagePreview from '@/Components/ui/image-preview/ImagePreview.vue';

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

// Improved normalizeImageUrl function that handles blob URLs correctly
const normalizeImageUrl = (url) => {
  console.log("Normalizing URL input:", url);
  
  if (!url) return '/images/placeholder-product.jpg';
  
  try {
    // Handle blob URLs directly - these are temporary browser-generated URLs for preview
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
    
    // If we got here, return placeholder
    return '/images/placeholder-product.jpg';
  } catch (error) {
    console.error('Error normalizing image URL:', error);
    return '/images/placeholder-product.jpg';
  }
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)" modal>
        <DialogContent class="mx-4 w-[75%] sm:max-w-20xl overflow-y-auto max-h-[90vh] p-6 md:p-8 lg:p-10 bg-background dark:bg-gray-900 border-border dark:border-gray-700">
            <DialogHeader>
                <DialogTitle>{{ dialogTitle }}</DialogTitle>
                <DialogDescription>
                    Review your trade offer details before submitting
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Product Information -->
                <div class="flex items-center p-4 rounded-lg bg-accent/5 dark:bg-gray-800/50 border border-border dark:border-gray-700">
                    <!-- Replace simple image with ImagePreview component -->
                    <div class="flex-shrink-0 w-16 h-16 sm:w-24 sm:h-24 overflow-hidden rounded-md border border-border dark:border-gray-700 mr-4">
                        <ImagePreview 
                            :images="[normalizeImageUrl(productImageUrl)]" 
                            :alt="product?.name"
                            class="h-full w-full"
                        />
                    </div>
                    <div>
                        <h3 class="text-lg font-medium">{{ product?.name }}</h3>
                        <p class="text-base text-primary-color">
                            ₱{{ formatPrice(productEffectivePrice) }}
                        </p>
                    </div>
                </div>

                <!-- Your Offer -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Your Offer</h3>
                    
                    <!-- Offered Items -->
                    <div class="space-y-3 p-4 border border-border dark:border-gray-700 rounded-lg bg-accent/5 dark:bg-gray-800/50">
                        <h4 class="font-medium">Offered Items</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div v-for="(item, index) in offer?.offered_items" :key="index" 
                                 class="border border-border dark:border-gray-700 rounded-md overflow-hidden flex flex-col">
                                <div class="h-24 md:h-32 flex-shrink-0 w-full relative">
                                    <ImagePreview 
                                        :images="item.images" 
                                        :alt="`Item ${index + 1}`"
                                        class="h-full w-full"
                                    />
                                </div>
                                <div class="p-2 text-xs sm:text-sm space-y-1 bg-background dark:bg-gray-800/40">
                                    <p class="font-medium truncate">{{ item.name }}</p>
                                    <p>Qty: {{ item.quantity }}</p>
                                    <p>₱{{ formatPrice(item.estimated_value) }} each</p>
                                    <p class="text-xs text-muted-foreground">
                                        Condition: {{ getConditionName(item.condition) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Cash -->
                    <div v-if="parseFloat(offer.additional_cash || 0) > 0" class="mt-4 pt-4 border-t border-border dark:border-gray-700">
                        <h4 class="font-medium mb-2">Additional Cash</h4>
                        <div class="flex justify-between items-center bg-accent/5 dark:bg-gray-800/50 p-3 rounded border border-border dark:border-gray-700">
                            <span>Cash Amount</span>
                            <span class="font-medium text-primary-color">₱{{ formatPrice(offer.additional_cash) }}</span>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div v-if="offer.notes" class="mt-4 pt-4 border-t border-border dark:border-gray-700">
                        <h4 class="font-medium mb-2">Your Notes</h4>
                        <div class="bg-accent/5 dark:bg-gray-800/50 p-3 rounded text-sm border border-border dark:border-gray-700">
                            {{ offer.notes }}
                        </div>
                    </div>
                </div>
                
                <!-- Trade Value Summary -->
                <div class="p-4 border border-border dark:border-gray-700 rounded-lg bg-accent/5 dark:bg-gray-800/50">
                    <h4 class="font-medium mb-2">Meetup Details</h4>
                    <div class="flex items-start gap-2 mb-3">
                        
                        <div class="text-sm">
                            <div class="flex items-center gap-2 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                    <line x1="16" x2="16" y1="2" y2="6"></line>
                                    <line x1="8" x2="8" y1="2" y2="6"></line>
                                    <line x1="3" x2="21" y1="10" y2="10"></line>
                                </svg>
                                <p><span class="font-medium">Date:</span> {{ selectedDay }} {{ formattedMeetupDate }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <!-- Add clock icon for time -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <p><span class="font-medium">Time:</span> {{ selectedTimeDisplay }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <!-- Add location icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <p><span class="font-medium">Location:</span> {{ selectedLocationName }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-border dark:border-gray-700 pt-3 mt-3">
                        <div class="font-medium">Total Offer Value</div>
                        <div class="text-lg font-medium text-primary-color">₱{{ formatPrice(totalOfferedValue) }}</div>
                    </div>
                </div>

                <!-- New Comparison Section -->
                <div class="mt-6 p-4 border border-border dark:border-gray-700 rounded-lg bg-accent/5 dark:bg-gray-800/50">
                    <h4 class="font-medium mb-3 text-center">Trade Comparison</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Product Side -->
                        <div class="p-3 bg-background dark:bg-gray-800 rounded-lg">
                            <h5 class="font-medium text-sm mb-2 text-center">You'll Receive</h5>
                            <div class="flex items-center justify-center mb-2">
                                <div class="w-20 h-20 relative overflow-hidden rounded-md">
                                    <ImagePreview 
                                        :images="[normalizeImageUrl(productImageUrl)]" 
                                        :alt="product?.name"
                                        class="h-full w-full"
                                    />
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="font-medium">{{ product?.name }}</p>
                                <p class="text-primary-color">₱{{ formatPrice(productEffectivePrice) }}</p>
                            </div>
                        </div>

                        <!-- Offered Items Side -->
                        <div class="p-3 bg-background dark:bg-gray-800 rounded-lg">
                            <h5 class="font-medium text-sm mb-2 text-center">You'll Trade</h5>
                            <div class="flex justify-center flex-wrap gap-2 mb-2">
                                <template v-if="offer?.offered_items && offer.offered_items.length > 0">
                                    <div v-for="(item, index) in offer.offered_items.slice(0, 3)" :key="index" class="w-16 h-16 relative overflow-hidden rounded-md">
                                        <ImagePreview 
                                            :images="item.images" 
                                            :alt="item.name"
                                            class="h-full w-full"
                                        />
                                        <div class="absolute bottom-0 right-0 bg-black/60 text-white text-xs px-1 rounded-tl">
                                            {{ item.quantity > 1 ? `x${item.quantity}` : '' }}
                                        </div>
                                    </div>
                                    <div v-if="offer.offered_items.length > 3" class="w-16 h-16 flex items-center justify-center bg-muted rounded-md">
                                        <span class="text-sm font-medium">+{{ offer.offered_items.length - 3 }}</span>
                                    </div>
                                </template>
                                <div v-else class="w-full text-center text-muted-foreground">
                                    No items
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-border dark:border-gray-700">
                                <span class="text-sm">Items Value:</span>
                                <span class="text-primary-color">₱{{ formatPrice(totalOfferedValue - parseFloat(offer.additional_cash || 0)) }}</span>
                            </div>
                            <div v-if="parseFloat(offer.additional_cash || 0) > 0" class="flex items-center justify-between mt-1">
                                <span class="text-sm">Additional Cash:</span>
                                <span class="text-primary-color">₱{{ formatPrice(offer.additional_cash) }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-1 pt-1 border-t border-border dark:border-gray-700 font-medium">
                                <span class="text-sm">Total Value:</span>
                                <span class="text-primary-color">₱{{ formatPrice(totalOfferedValue) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:gap-0 sm:justify-between pt-4 border-t border-border dark:border-gray-700">
                <Button @click="emit('cancel')" variant="outline" :disabled="loading">
                    Back
                </Button>
                <Button @click="emit('confirm')" :loading="loading" :disabled="loading">
                    {{ editMode ? 'Update Trade' : 'Confirm & Submit' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>

/* Custom styles for image previews */
:deep(.image-navigation-controls) {
  /* Make controls less obtrusive in the grid view */
  opacity: 0;
  transition: opacity 0.2s;
}

:deep(:hover .image-navigation-controls) {
  opacity: 1;
}

/* Ensure proper sizing of the ImagePreview component */
:deep(img) {
  width: 100%;
  height: 100%;
  object-fit: contain;
  background-color: var(--background);
}
</style>