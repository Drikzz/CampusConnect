<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { useToast } from "@/Components/ui/toast/use-toast";
import { ScrollArea } from '@/Components/ui/scroll-area';
import { Toaster } from '@/Components/ui/toast';
import { format } from "date-fns";
import MeetupDate from '@/Components/ui/trade-calendar/meetup-date.vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    open: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['close', 'update:open']);
const { toast } = useToast();
const loading = ref(false);
const errors = ref({});
const meetupLocations = ref([]);
const isLoadingMeetupLocations = ref(false);
const selectedSchedule = ref(null);

// Form data with reactive state
const form = useForm({
    seller_product_id: props.product.id,
    meetup_location_id: '',
    meetup_schedule: '', // This will hold the schedule ID in format: location_id_dayname
    meetup_date: '', // This will hold the actual date string
    additional_cash: 0,
    notes: '',
    offered_items: [
        {
            name: '',
            quantity: 1,
            estimated_value: 0,
            description: '',
            images: []
        }
    ]
});

// Computed properties for product and seller information
const productImageUrl = computed(() => {
  return props.product?.images?.[0] 
    ? `/storage/${props.product.images[0]}` 
    : '/images/placeholder-product.jpg';
});

const sellerName = computed(() => {
  return props.product?.seller 
    ? `${props.product.seller.first_name} ${props.product.seller.last_name}`
    : 'Unknown Seller';
});

const sellerProfilePicture = computed(() => {
  return props.product?.seller?.profile_picture
    ? `/storage/${props.product.seller.profile_picture}`
    : '/images/placeholder-avatar.jpg';
});

// Computed property to get available schedules from meetup locations
const availableSchedules = computed(() => {
    const schedules = [];
    if (meetupLocations.value.length > 0) {
        meetupLocations.value.forEach(location => {
            const availableDays = location.available_days || [];
            availableDays.forEach(day => {
                schedules.push({
                    id: `${location.id}_${day}`,
                    location: location.name || 'Location Not Available',
                    day: day,
                    timeFrom: formatTime(location.available_from),
                    timeUntil: formatTime(location.available_until),
                    description: location.description || '',
                    maxMeetups: location.max_daily_meetups
                });
            });
        });
    }
    return schedules;
});

// Computed property to get selected day from meetup schedule
const selectedScheduleDay = computed(() => {
    if (!form.meetup_schedule) return '';
    const schedule = availableSchedules.value.find(s => s.id === form.meetup_schedule);
    return schedule?.day || '';
});

// Fetch meetup locations for the product's seller
const fetchMeetupLocations = async () => {
    if (!props.product?.id || isLoadingMeetupLocations.value) return;
    
    try {
        isLoadingMeetupLocations.value = true;
        const response = await fetch(`/trades/product/${props.product.id}/meetup-locations`);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || `HTTP error! Status: ${response.status}`);
        }
        
        if (data.success === false) {
            throw new Error(data.message || "Failed to load meetup locations");
        }
        
        meetupLocations.value = data.meetup_locations || [];
        
        if (meetupLocations.value.length === 0) {
            toast({
                title: "Notice",
                description: "This seller has no meetup locations set up.",
                variant: "warning"
            });
        }
    } catch (error) {
        console.error('Failed to fetch meetup locations:', error);
        toast({
            title: "Error",
            description: error.message || "Failed to load meetup locations",
            variant: "destructive"
        });
    } finally {
        isLoadingMeetupLocations.value = false;
    }
};

// Watch for dialog open/close and product changes
watch(
    [() => props.open, () => props.product?.id], 
    ([isOpen, productId]) => {
        if (isOpen && productId) {
            form.seller_product_id = productId;
            fetchMeetupLocations();
        }
    },
    { immediate: true }
);

// Handle dialog close
const handleDialogClose = (isOpen) => {
    if (!isOpen) {
        closeDialog();
    }
};

// Update schedule when meetup location selection changes
watch(() => form.meetup_schedule, (newSchedule) => {
    // Reset the date when schedule changes
    form.meetup_date = ''; 
    
    if (newSchedule) {
        const schedule = availableSchedules.value.find(s => s.id === newSchedule);
        selectedSchedule.value = schedule || null;
        
        if (schedule) {
            // Extract location_id from the schedule ID (format: location_id_day)
            const [locationId] = schedule.id.split('_');
            form.meetup_location_id = locationId;
        }
    } else {
        selectedSchedule.value = null;
        form.meetup_location_id = '';
    }
});

// Handle file uploads with validation
const handleDateSelection = (date) => {
  try {
    console.log('TradeForm received date:', date);
    
    if (!date) {
      form.meetup_date = '';
      return;
    }

    // If date is already a string in YYYY-MM-DD format, use it directly
    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
      form.meetup_date = date;
      console.log("Selected date (from string):", form.meetup_date);
      return;
    }
    
    // Otherwise create a date object and format it
    const selectedDate = new Date(date);
    
    // Additional validation
    if (isNaN(selectedDate.getTime())) {
      console.error('Invalid date selected', date);
      form.meetup_date = '';
      return;
    }
    
    // Format as ISO date string for form submission (YYYY-MM-DD)
    const year = selectedDate.getFullYear();
    const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const day = String(selectedDate.getDate()).padStart(2, '0');
    form.meetup_date = `${year}-${month}-${day}`;
    
    console.log("Selected date (converted):", form.meetup_date);
  } catch (error) {
    console.error('Error formatting date:', error);
    form.meetup_date = '';
    toast({
      title: "Error",
      description: "Invalid date selection. Please try again.",
      variant: "destructive"
    });
  }
};

// Methods for offered items
const addOfferedItem = () => {
    form.offered_items.push({
        name: '',
        quantity: 1,
        estimated_value: 0,
        description: '',
        images: []
    });
};

const removeOfferedItem = (index) => {
    if (form.offered_items.length > 1) {
        form.offered_items.splice(index, 1);
    }
};

// Update the removeImage method to be more robust
const removeImage = (itemIndex, imageIndex) => {
  try {
    if (!form.offered_items[itemIndex] || !form.offered_items[itemIndex].images) {
      console.error(`No images array found for item ${itemIndex}`);
      return;
    }
    
    // Create a new array without the removed image
    const updatedImages = [...form.offered_items[itemIndex].images];
    updatedImages.splice(imageIndex, 1);
    form.offered_items[itemIndex].images = updatedImages;
    
    console.log(`Image ${imageIndex} removed from item ${itemIndex}`);
  } catch (error) {
    console.error('Error removing image:', error);
  }
};

// Image handling methods
const getImagePreviewUrl = (image) => {
  try {
    if (typeof image === 'string') {
      return image;
    }
    
    // Safely access global URL object 
    if (window.URL && image instanceof File) {
      return window.URL.createObjectURL(image);
    }
    
    return '/images/placeholder-product.jpg';
  } catch (error) {
    console.error('Error creating object URL:', error);
    return '/images/placeholder-product.jpg';
  }
};

const handleImageError = (e) => {
  e.target.src = '/images/placeholder-product.jpg';
};

// Handle file uploads for offered items
const handleFileUpload = (files, itemIndex) => {
  try {
    if (!files || files.length === 0) {
      return;
    }
    
    // Validate each file
    const validFiles = Array.from(files).filter(file => {
      // Check file type (only images)
      if (!file.type.startsWith('image/')) {
        toast({
          title: "Invalid file type",
          description: "Please upload only image files",
          variant: "destructive"
        });
        return false;
      }
      
      // Check file size (max 5MB)
      const maxSizeMB = 5;
      const maxSizeBytes = maxSizeMB * 1024 * 1024;
      
      if (file.size > maxSizeBytes) {
        toast({
          title: "File too large",
          description: `Maximum file size is ${maxSizeMB}MB`,
          variant: "destructive"
        });
        return false;
      }
      
      return true;
    });
    
    if (validFiles.length === 0) {
      return;
    }
    
    // Add the files to the appropriate item
    if (!form.offered_items[itemIndex]) {
      console.error(`Item index ${itemIndex} not found`);
      return;
    }
    
    // Initialize images array if it doesn't exist
    if (!form.offered_items[itemIndex].images) {
      form.offered_items[itemIndex].images = [];
    }
    
    // Add new files to the images array
    form.offered_items[itemIndex].images = [
      ...form.offered_items[itemIndex].images,
      ...validFiles
    ];
    
    // Reset the file input to allow selecting the same files again if needed
    document.getElementById(`file-upload-${itemIndex}`).value = '';
    
    console.log(`Added ${validFiles.length} images to item ${itemIndex}`);
  } catch (error) {
    console.error('Error handling file upload:', error);
    toast({
      title: "Error",
      description: "Failed to process uploaded files",
      variant: "destructive"
    });
  }
};

// Submit the trade offer
const submitTradeOffer = () => {
    // Validate meetup date
    if (!form.meetup_date) {
        errors.value = { 
            ...errors.value,
            meetup_date: ['Please select a valid meetup date']
        };
        toast({
            title: "Error",
            description: "Please select a valid meetup date",
            variant: "destructive"
        });
        return;
    }

    loading.value = true;
    const formData = new FormData();
    
    // Append form fields to FormData
    formData.append('meetup_date', form.meetup_date);
    formData.append('seller_product_id', form.seller_product_id);
    formData.append('meetup_location_id', form.meetup_location_id);
    formData.append('meetup_schedule', form.meetup_schedule);
    formData.append('additional_cash', form.additional_cash || 0);
    formData.append('notes', form.notes || '');

    // Add offered items
    form.offered_items.forEach((item, index) => {
        formData.append(`offered_items[${index}][name]`, item.name);
        formData.append(`offered_items[${index}][quantity]`, item.quantity);
        formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
        formData.append(`offered_items[${index}][description]`, item.description);
        
        // Append image files
        item.images.forEach((image, imageIndex) => {
            formData.append(`offered_items[${index}][images][${imageIndex}]`, image);
        });
    });

    // Submit the form using Inertia
    router.post(route('product.trade.submit'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: "Success",
                description: "Trade offer submitted successfully!",
                variant: "success"
            });
            closeDialog();
        },
        onError: (validationErrors) => {
            errors.value = validationErrors;
            toast({
                title: "Error",
                description: "Please check the form for errors",
                variant: "destructive"
            });
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Close dialog and reset form
const closeDialog = () => {
    form.reset();
    errors.value = {};
    selectedSchedule.value = null;
    emit('close');
    emit('update:open', false);
};

// Utility functions
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

const formatPrice = (price) => {
    return new Intl.NumberFormat().format(price);
};

const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};
</script>

<template>
    <Dialog 
        :open="open" 
        @update:open="handleDialogClose"
        modal
    >
        <DialogContent class="sm:max-w-3xl overflow-y-auto max-h-[90vh]">
            <DialogHeader>
                <DialogTitle>Trade for {{ capitalizeFirst(product.name) }}</DialogTitle>
                <DialogDescription>
                    Offer your items in exchange for this product. The seller will review your offer.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitTradeOffer" class="space-y-6">
                <!-- Product being traded for -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-20 h-20 overflow-hidden rounded-md shrink-0">
                        <img 
                            :src="productImageUrl" 
                            :alt="product.name" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div>
                        <h3 class="font-Satoshi-bold text-lg">{{ capitalizeFirst(product.name) }}</h3>
                        <p class="text-gray-600">
                          <span v-if="product.discounted_price && product.discounted_price < product.price">
                            <span class="line-through text-gray-400 mr-1">₱{{ formatPrice(product.price) }}</span>
                            ₱{{ formatPrice(product.discounted_price) }}
                          </span>
                          <span v-else>₱{{ formatPrice(product.price) }}</span>
                        </p>
                    </div>
                </div>

                <!-- Seller Information Section -->
                <div class="border-t border-gray-100 py-4">
                    <h4 class="font-Satoshi-bold mb-2">Seller Information</h4>
                    <div v-if="props.product?.seller" class="flex items-start gap-3">
                        <img :src="sellerProfilePicture" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                        <div class="space-y-1">
                            <p class="font-medium">{{ sellerName }}</p>
                            <div class="text-sm text-gray-500 space-y-0.5">
                                <p class="flex items-center gap-2">
                                    <span class="font-medium">@{{ props.product.seller.username }}</span>
                                </p>
                                <p v-if="props.product.seller.phone" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    {{ props.product.seller.phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">
                        Seller information not available
                    </div>
                </div>

                <!-- Meetup Schedule Section -->
                <div class="mb-6">
                    <h3 class="font-Satoshi-bold mb-4">Available Meetup Schedules</h3>
                    <p class="text-sm text-gray-500 mb-2">Select a meetup schedule first to enable date selection.</p>
                    <ScrollArea class="h-[200px] rounded-md border p-4">
                        <div v-if="isLoadingMeetupLocations" class="flex justify-center items-center h-[160px]">
                            <div class="text-gray-400">Loading available meetup schedules...</div>
                        </div>
                        <div v-else-if="availableSchedules.length === 0" class="flex justify-center items-center h-[160px]">
                            <div class="text-amber-600">No meetup locations available. Contact the seller for arrangements.</div>
                        </div>
                        <div v-else class="space-y-2">
                            <div 
                                v-for="schedule in availableSchedules" 
                                :key="schedule.id"
                                class="relative"
                            >
                            <label 
                                :class="[
                                    form.meetup_schedule === schedule.id ? 'bg-accent/10' : 'hover:bg-gray-50',
                                    'flex p-3 border rounded cursor-pointer select-none'
                                ]"
                            >
                                <input 
                                    type="radio" 
                                    v-model="form.meetup_schedule"
                                    :value="schedule.id"
                                    class="mr-3 mt-1"
                                />
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ schedule.location }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ capitalizeFirst(schedule.day) }} | {{ schedule.timeFrom }} - {{ schedule.timeUntil }}
                                    </div>
                                    <div v-if="schedule.description" class="text-sm text-gray-500 mt-1">
                                        {{ schedule.description }}
                                    </div>
                                </div>
                            </label>
                            </div>
                        </div>
                    </ScrollArea>
                    <p v-if="errors.meetup_schedule" class="text-red-500 text-sm mt-2">{{ errors.meetup_schedule }}</p>
                </div>

                <!-- Meetup Date Selection -->
                <div class="mb-6">
                    <h3 class="font-Satoshi-bold mb-2">Select Meetup Date</h3>
                    <p class="text-sm text-gray-500 mb-2">
                        {{ form.meetup_schedule 
                            ? `Choose a ${selectedScheduleDay} date for your meetup.`
                            : 'Please select a meetup schedule first to enable date selection.' }}
                    </p>
                    <div class="relative">
                        <MeetupDate
                            :model-value="form.meetup_date"
                            :selected-day="selectedScheduleDay"
                            :is-date-disabled="!form.meetup_schedule"
                            @update:model-value="handleDateSelection"
                        />
                    </div>
                    <p v-if="errors.meetup_date" class="text-red-500 text-sm mt-2">{{ errors.meetup_date }}</p>
                </div>

                <!-- Additional cash - clearly marked as optional -->
                <div class="space-y-2">
                    <Label for="additional_cash">Additional Cash (₱) <span class="text-gray-400 text-sm">(Optional)</span></Label>
                    <Input 
                        id="additional_cash"
                        type="number"
                        v-model="form.additional_cash"
                        min="0"
                        step="0.01"
                    />
                    <p v-if="errors.additional_cash" class="text-red-500 text-sm">{{ errors.additional_cash }}</p>
                </div>

                <!-- Notes - clearly marked as optional -->
                <div class="space-y-2">
                    <Label for="notes">Notes for Seller <span class="text-gray-400 text-sm">(Optional)</span></Label>
                    <Textarea 
                        id="notes"
                        v-model="form.notes"
                        placeholder="Add any details about your trade offer here..."
                    />
                </div>

                <!-- Offered Items -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-Satoshi-bold text-lg">Items to Offer</h3>
                        <Button type="button" variant="outline" size="sm" @click="addOfferedItem">
                            Add Another Item
                        </Button>
                    </div>
                    <div v-for="(item, index) in form.offered_items" :key="index" class="p-4 border rounded-lg space-y-4">
                        <div class="flex justify-between">
                            <h4 class="font-Satoshi-medium">Item {{ index + 1 }}</h4>
                            <Button 
                                v-if="form.offered_items.length > 1" 
                                type="button"
                                variant="ghost" 
                                size="sm" 
                                @click="removeOfferedItem(index)"
                            >
                                Remove
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Item name - required field -->
                            <div class="space-y-2">
                                <Label :for="`item_name_${index}`">Item Name*</Label>
                                <Input 
                                    :id="`item_name_${index}`"
                                    v-model="item.name"
                                    required
                                />
                                <p v-if="errors[`item_${index}_name`]" class="text-red-500 text-sm">
                                    {{ errors[`item_${index}_name`] }}
                                </p>
                            </div>
                            <!-- Quantity - required field -->
                            <div class="space-y-2">
                                <Label :for="`item_quantity_${index}`">Quantity*</Label>
                                <Input 
                                    :id="`item_quantity_${index}`" 
                                    type="number" 
                                    v-model="item.quantity"
                                    min="1" 
                                    required
                                />
                                <p v-if="errors[`item_${index}_quantity`]" class="text-red-500 text-sm">
                                    {{ errors[`item_${index}_quantity`] }}
                                </p>
                            </div>
                            <!-- Estimated value - required field -->
                            <div class="space-y-2">
                                <Label :for="`item_value_${index}`">Estimated Value (₱)*</Label>
                                <Input 
                                    :id="`item_value_${index}`" 
                                    type="number" 
                                    v-model="item.estimated_value"
                                    min="0" 
                                    step="0.01" 
                                    required
                                />
                                <p v-if="errors['item_' + index + '_value']" class="text-red-500 text-sm">
                                    {{ errors['item_' + index + '_value'] }}
                                </p>
                            </div>
                            <!-- Description - clearly marked as optional -->
                            <div class="space-y-2">
                                <Label :for="`item_description_${index}`">Description <span class="text-gray-400 text-sm">(Optional)</span></Label>
                                <Textarea 
                                    :id="`item_description_${index}`" 
                                    v-model="item.description"
                                    placeholder="Describe the item..."
                                />
                            </div>
                            <!-- File Upload -->
                            <div class="space-y-2 md:col-span-2">
                                <Label>Upload Images*</Label>
                                <!-- Replace the FileUpload component with native file input -->
                                <div class="border rounded-md p-2 bg-background">
                                    <input 
                                        type="file"
                                        :id="`file-upload-${index}`"
                                        @change="(e) => handleFileUpload(e.target.files, index)"
                                        multiple
                                        accept="image/*"
                                        class="block w-full text-sm text-gray-500 
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-accent file:text-accent-foreground
                                            hover:file:bg-accent/80"
                                    />
                                </div>

                                <p v-if="errors[`item_${index}_images`]" class="text-red-500 text-sm">
                                    {{ errors[`item_${index}_images`] }}
                                </p>
                                                            
                                <!-- Keep your custom image previews - this part is good -->
                                <div v-if="item.images && item.images.length > 0" class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                    <div v-for="(image, imgIndex) in item.images" :key="`img-${index}-${imgIndex}`" 
                                        class="relative group border rounded-md overflow-hidden h-24">
                                        <img 
                                            :src="getImagePreviewUrl(image)" 
                                            class="w-full h-full object-cover"
                                            @error="handleImageError"
                                        />
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                            <button 
                                                type="button" 
                                                class="bg-white/70 p-1 rounded-full hover:bg-white"
                                                @click.prevent="removeImage(index, imgIndex)"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="flex justify-between">
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="closeDialog"
                    >
                        Cancel
                    </Button>
                    <Button 
                        type="submit" 
                        :disabled="loading"
                    >
                        {{ loading ? 'Submitting...' : 'Submit Trade Offer' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
    <Toaster />
</template>

<style scoped>
.select-none {
  user-select: none !important;
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
}

/* Update the label styles to ensure clickability */
label.select-none {
  cursor: pointer !important;
  pointer-events: auto !important;
}

/* Prevent any transform or filter effects that could cause blur */
.hover\:bg-gray-50:hover,
.hover\:bg-gray-50 {
  background-color: rgb(249 250 251);
  backdrop-filter: none !important;
  transform: none !important;
  transition: background-color 0.2s ease;
}

/* Ensure text remains sharp */
* {
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
  -moz-osx-font-smoothing: grayscale;
}
</style>
