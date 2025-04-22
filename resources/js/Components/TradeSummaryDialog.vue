<script setup>
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { ImagePreview } from "@/Components/ui/image-preview";
import { XIcon, ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next';

const props = defineProps({
    open: {
        type: Boolean,
        required: true
    },
    product: {
        type: Object,
        required: true
    },
    offer: {
        type: Object,
        required: true
    },
    totalOfferedValue: {
        type: Number,
        required: true
    },
    selectedLocationName: {
        type: String,
        default: ''
    },
    selectedDay: {
        type: String,
        default: ''
    },
    selectedTimeDisplay: {
        type: String,
        default: ''
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['confirm', 'cancel']);

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
</script>

<template>
    <Dialog :open="open" @update:open="emit('cancel')" modal>
        <DialogContent class="mx-4 w-[75%] sm:max-w-20xl overflow-y-auto max-h-[90vh] p-6 md:p-8 lg:p-10 bg-background dark:bg-gray-900 border-border dark:border-gray-700">
            <DialogHeader>
                <DialogTitle class="text-foreground dark:text-white font-medium text-xl">Review Your Trade Offer</DialogTitle>
                <DialogDescription class="text-muted-foreground dark:text-gray-400">
                    Please review your trade offer details before submitting.
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-6 my-4">
                <!-- Product Information -->
                <div class="flex items-center gap-4 p-3 sm:p-4 bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 overflow-hidden rounded-md shrink-0 border border-border dark:border-gray-700">
                        <ImagePreview :images="product.images || []" :alt="product.name" />
                    </div>
                    <div>
                        <h3 class="font-medium text-base sm:text-lg text-foreground dark:text-white">Trade for {{ capitalizeFirst(product.name) }}</h3>
                        <p class="text-muted-foreground dark:text-gray-400 text-sm sm:text-base">
                            <span v-if="product.discounted_price && product.discounted_price < product.price">
                                <span class="line-through text-muted-foreground mr-1">₱{{ formatPrice(product.price) }}</span>
                                <span class="text-primary-color dark:text-primary-color">₱{{ formatPrice(product.discounted_price) }}</span>
                            </span>
                            <span v-else class="text-primary-color dark:text-primary-color">₱{{ formatPrice(product.price) }}</span>
                        </p>
                    </div>
                </div>

                <!-- Meetup Details -->
                <div class="p-4 border border-border dark:border-gray-700 rounded-lg bg-accent/5 dark:bg-gray-800/50">
                    <h3 class="font-medium text-base mb-3 text-foreground dark:text-white">Meetup Details</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-white">Location</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400">{{ selectedLocationName || 'Not specified' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-white">Day</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400">{{ capitalizeFirst(selectedDay) || 'Not specified' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-white">Date</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400">
                                {{ formattedMeetupDate || 'Not specified' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-white">Time</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400">{{ selectedTimeDisplay || 'Not specified' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Offered Items -->
                <div class="p-4 border border-border dark:border-gray-700 rounded-lg bg-accent/5 dark:bg-gray-800/50">
                    <h3 class="font-medium text-base mb-3 text-foreground dark:text-white">Your Offered Items</h3>
                    
                    <div v-for="(item, index) in offer.offered_items" :key="index" class="mb-4 border-b border-border dark:border-gray-700 pb-3 last:border-b-0 last:pb-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-2">
                            <h4 class="font-medium text-foreground dark:text-white">{{ item.name }}</h4>
                            <span class="text-sm text-primary-color dark:text-primary-color">
                                ₱{{ formatPrice(item.estimated_value) }} × {{ item.quantity }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm mt-2">
                            <div>
                                <span class="text-muted-foreground dark:text-gray-400">Condition: </span>
                                <span class="text-foreground dark:text-white">{{ getConditionName(item.condition) }}</span>
                            </div>
                            
                            <div v-if="item.description">
                                <span class="text-muted-foreground dark:text-gray-400">Description: </span>
                                <span class="text-foreground dark:text-white">{{ item.description }}</span>
                            </div>
                        </div>
                        
                        <div v-if="item.images && item.images.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <div 
                                v-for="(image, imgIndex) in item.images.slice(0, 3)" 
                                :key="`${index}-${imgIndex}`" 
                                class="w-16 h-16 sm:w-20 sm:h-20 aspect-square relative rounded-md overflow-hidden border border-border dark:border-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                            >
                                <ImagePreview :images="[image]" :alt="`${item.name} - Image ${imgIndex + 1}`" />
                            </div>
                            <div 
                                v-if="item.images.length > 3" 
                                class="w-16 h-16 sm:w-20 sm:h-20 aspect-square flex items-center justify-center text-xs border border-border dark:border-gray-700 rounded-md text-muted-foreground bg-muted dark:bg-gray-800 cursor-pointer hover:bg-muted/70 transition-colors"
                            >
                                +{{ item.images.length - 3 }} more
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Cash -->
                    <div v-if="parseFloat(offer.additional_cash) > 0" class="mt-3 pt-3 border-t border-border dark:border-gray-700">
                        <div class="flex justify-between">
                            <p class="text-sm font-medium text-foreground dark:text-white">Additional Cash:</p>
                            <p class="text-sm text-primary-color dark:text-primary-color">₱{{ formatPrice(offer.additional_cash) }}</p>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div v-if="offer.notes" class="mt-3 pt-3 border-t border-border dark:border-gray-700">
                        <p class="text-sm font-medium text-foreground dark:text-white">Notes:</p>
                        <p class="text-sm text-muted-foreground dark:text-gray-400">{{ offer.notes }}</p>
                    </div>
                </div>
                
                <!-- Trade Value Summary -->
                <div class="p-4 border border-border dark:border-gray-700 rounded-lg bg-accent/5 dark:bg-gray-800/50">
                    <div class="flex justify-between mb-2">
                        <p class="text-foreground dark:text-white font-medium">Product Value:</p>
                        <p class="text-foreground dark:text-white">₱{{ formatPrice(productEffectivePrice) }}</p>
                    </div>
                    <div class="flex justify-between mb-2">
                        <p class="text-foreground dark:text-white font-medium">Your Offer Value:</p>
                        <p class="text-foreground dark:text-white">₱{{ formatPrice(totalOfferedValue) }}</p>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-border dark:border-gray-700">
                        <p class="text-foreground dark:text-white font-medium">Difference:</p>
                        <p :class="[ 
                            totalOfferedValue >= productEffectivePrice ? 'text-green-600 dark:text-green-500' : 'text-destructive',
                            'font-medium'
                        ]">
                            {{ totalOfferedValue >= productEffectivePrice ? '+' : '' }}₱{{ formatPrice(totalOfferedValue - productEffectivePrice) }}
                        </p>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex justify-between pt-2 sm:pt-4 border-t border-border dark:border-gray-700">
                <Button 
                    type="button" 
                    variant="outline" 
                    @click="emit('cancel')"
                    :disabled="loading"
                    class="text-xs sm:text-sm bg-white dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                >
                    <template v-if="loading">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-foreground dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </template>
                    <template v-else>
                        Go Back
                    </template>
                </Button>
                <Button 
                    type="button" 
                    @click="emit('confirm')"
                    :disabled="loading"
                    class="text-xs sm:text-sm bg-primary-color hover:bg-opacity-90 text-white min-w-[150px]"
                >
                    <template v-if="loading">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </template>
                    <template v-else>
                        Confirm & Submit
                    </template>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
