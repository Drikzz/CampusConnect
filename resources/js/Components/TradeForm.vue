<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { useToast } from "@/Components/ui/toast/use-toast";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { ScrollArea } from '@/Components/ui/scroll-area';
import FileUpload from '@/Components/FileUpload.vue';
import { Toaster } from '@/Components/ui/toast';
// Import date picker components
import { Calendar } from "@/Components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { format } from "date-fns";
import { CalendarIcon } from "lucide-vue-next";

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
const sellerData = ref(null); 
const selectedDate = ref(null); // Add selected date ref
const selectedSchedule = ref(null); // Track selected schedule for availability

// Update the computed properties for seller information
const sellerName = computed(() => {
    // First try from seller data fetched from API
    if (sellerData.value?.name || (sellerData.value?.first_name && sellerData.value?.last_name)) {
        return sellerData.value.name || `${sellerData.value.first_name} ${sellerData.value.last_name}`.trim();
    }
    
    // Then try from product.seller
    const seller = props.product?.seller;
    if (seller) {
        if (seller.first_name && seller.last_name) {
            return `${seller.first_name} ${seller.last_name}`.trim();
        }
        if (seller.username) {
            return seller.username;
        }
    }
    
    return 'Unknown Seller';
});

const sellerLocation = computed(() => {
    // First try from enhanced seller data from API
    if (sellerData.value?.location) {
        return sellerData.value.location;
    }
    
    // Then try from product.seller
    if (props.product?.seller?.location) {
        return props.product.seller.location;
    }
    
    return 'Location N/A';
});

const sellerProfilePicture = computed(() => {
    const profilePic = sellerData.value?.profile_picture || props.product?.seller?.profile_picture;
    
    if (profilePic) {
        return profilePic.startsWith('http') ? profilePic : `/storage/${profilePic}`;
    }
    
    return '/images/placeholder-avatar.jpg';
});

// Properly format the product image URL
const productImageUrl = computed(() => {
    if (!props.product || !props.product.images || !props.product.images[0]) {
        return '/images/placeholder-product.jpg';
    }
    
    const image = props.product.images[0];
    
    // Check if image is already a full URL
    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    }
    
    // Check if image starts with storage/
    if (image.startsWith('storage/')) {
        return '/' + image;
    }
    
    // Otherwise assume it needs /storage/ prefix
    return `/storage/${image}`;
});

// Form for trade submission using Inertia
const form = useForm({
    seller_product_id: props.product.id,
    meetup_location_id: '',
    meetup_schedule: '',
    meetup_date: '', // Add meetup_date field
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

// Enhanced meetup location fetching using the product ID directly
const fetchMeetupLocations = async () => {
    // Make sure we have a product ID
    if (!props.product?.id) {
        console.error("Product ID is missing");
        toast({
            title: "Error",
            description: "Could not identify the product. Please refresh the page.",
            variant: "destructive"
        });
        return;
    }
    
    // Avoid redundant API calls if already loading
    if (isLoadingMeetupLocations.value) return;
    
    try {
        isLoadingMeetupLocations.value = true;
        console.log("Fetching meetup locations for product ID:", props.product.id);
        
        // Use the product ID to fetch meetup locations - similar to Checkout.vue approach
        const response = await fetch(`/trades/product/${props.product.id}/meetup-locations`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log("Meetup locations response:", data);
        
        if (data?.success === false) {
            throw new Error(data.message || "Failed to load meetup locations");
        }
        
        // Store enhanced seller data from the API if available
        if (data?.product?.seller) {
            sellerData.value = data.product.seller;
            console.log("Enhanced seller data loaded:", sellerData.value);
        }
        
        if (data && data.meetup_locations) {
            if (data.meetup_locations.length === 0) {
                toast({
                    title: "Cannot Trade",
                    description: "This seller has no meetup locations set up. Trading is not possible at this time.",
                    variant: "destructive",
                    duration: 5000
                });
                closeDialog(); // Close the dialog since trading isn't possible
            }
            
            meetupLocations.value = data.meetup_locations;
            console.log("Meetup locations loaded:", meetupLocations.value);
            
            // If there's a default meetup location, select it automatically
            const defaultLocation = data.default_location || data.meetup_locations.find(location => location.is_default);
            if (defaultLocation) {
                form.meetup_location_id = defaultLocation.id.toString();
                console.log("Selected default meetup location:", defaultLocation.id);
            }
        } else {
            meetupLocations.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch meetup locations:', error);
        toast({
            title: "Error",
            description: `Failed to load meetup locations: ${error.message}`,
            variant: "destructive"
        });
        meetupLocations.value = [];
    } finally {
        isLoadingMeetupLocations.value = false;
    }
};

// Watch both product changes AND dialog open state
watch(
    [() => props.product?.id, () => props.open], 
    ([newProductId, isOpen]) => {
        console.log("TradeForm watchers triggered:", {newProductId, isOpen});
        if (isOpen && newProductId) {
            fetchMeetupLocations();
        }
    },
    { immediate: true } // Important: Changed to true to run immediately when component mounts
);

// Load meetup locations when component mounts if dialog is open
onMounted(() => {
    console.log("TradeForm mounted, open state:", props.open);
    console.log("Product data:", props.product);
    console.log("Seller data:", props.product?.seller);
    if (props.open) {
        fetchMeetupLocations();
    }
});

// Watch for open prop changes and initialize form
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            console.log("Dialog opened, product:", props.product);
            // Reset form when opening
            form.seller_product_id = props.product.id;
            // Initialize form with product data
            fetchMeetupLocations();
        }
    }
);

// Watch selected schedule to reset date when schedule changes
watch(
    () => form.meetup_schedule,
    (newSchedule) => {
        if (newSchedule) {
            // Reset the date when schedule changes
            selectedDate.value = null;
            form.meetup_date = '';
            
            // Find and store the selected schedule details
            const [locationId, day] = newSchedule.split('_');
            selectedSchedule.value = availableSchedules.value.find(s => s.id === newSchedule);
            
            console.log("Selected new schedule:", selectedSchedule.value);
        }
    }
);

// Watch selected date to update form
watch(
    () => selectedDate.value,
    (newDate) => {
        if (newDate) {
            form.meetup_date = format(newDate, 'yyyy-MM-dd');
            console.log("Selected date:", form.meetup_date);
        } else {
            form.meetup_date = '';
        }
    }
);

// Methods to manipulate offered items
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

// Handle file uploads for each offered item
const handleFileUpload = (files, index) => {
    if (!Array.isArray(files)) {
        console.error('Files must be an array');
        return;
    }
    
    console.log(`Received ${files.length} files for item ${index}:`, files);
    
    // Make sure we're setting a valid array of files
    form.offered_items[index].images = files;
    
    // Add this debugging line to verify files were properly set
    console.log(`After setting: item ${index} now has ${form.offered_items[index].images.length} images`);
};

// Basic validation - updated to require images and date
const validate = () => {
    errors.value = {};
    let isValid = true;
    
    // Check if all required fields are filled
    form.offered_items.forEach((item, index) => {
        if (!item.name.trim()) {
            errors.value[`item_${index}_name`] = 'Item name is required';
            isValid = false;
        }
        
        if (item.quantity < 1) {
            errors.value[`item_${index}_quantity`] = 'Quantity must be at least 1';
            isValid = false;
        }
        
        if (item.estimated_value < 0) {
            errors.value[`item_${index}_value`] = 'Value cannot be negative';
            isValid = false;
        }
        
        // Check if images exist and have at least one item
        if (!item.images || item.images.length === 0) {
            errors.value[`item_${index}_images`] = 'At least one image is required';
            isValid = false;
        }
    });
    
    // Additional cash can be zero now (optional)
    if (form.additional_cash < 0) {
        errors.value.additional_cash = 'Additional cash cannot be negative';
        isValid = false;
    }
    
    // Validate meetup schedule
    if (meetupLocations.value.length > 0 && !form.meetup_schedule) {
        errors.value.meetup_schedule = 'Please select a meetup schedule';
        isValid = false;
    }
    
    // Validate meetup date
    if (form.meetup_schedule && !form.meetup_date) {
        errors.value.meetup_date = 'Please select a meetup date';
        isValid = false;
    }
    
    return isValid;
};

// Submit the trade offer using Inertia.js
const submitTradeOffer = () => {
    if (!validate()) {
        toast({
            title: "Validation Error",
            description: "Please check the form for errors",
            variant: "destructive"
        });
        return;
    }
    
    loading.value = true;
    
    // Create FormData for file uploads
    const formData = new FormData();
    formData.append('seller_product_id', form.seller_product_id);
    formData.append('additional_cash', form.additional_cash);
    formData.append('notes', form.notes);
    
    // Parse the meetup schedule ID to extract location ID and day
    if (form.meetup_schedule) {
        // The meetup_schedule value is in format "location_id_day"
        const [locationId, day] = form.meetup_schedule.split('_');
        
        // Add the parsed location ID
        formData.append('meetup_location_id', locationId);
        
        // Add the meetup schedule day
        formData.append('meetup_schedule', day);
        
        // Add the meetup date (new field)
        formData.append('meetup_date', form.meetup_date);
        
        console.log('Sending location ID:', locationId, 'day:', day, 'date:', form.meetup_date);
    }
    
    // Add offered items
    form.offered_items.forEach((item, index) => {
        formData.append(`offered_items[${index}][name]`, item.name);
        formData.append(`offered_items[${index}][quantity]`, item.quantity);
        formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
        formData.append(`offered_items[${index}][description]`, item.description || '');
        
        // Add images - ensure we're only appending File objects
        if (item.images && item.images.length) {
            item.images.forEach((image, imageIndex) => {
                if (image instanceof File) {
                    formData.append(`offered_items[${index}][images][${imageIndex}]`, image);
                }
            });
        }
    });
    
    // Use Inertia.js to post the form data
    router.post(route('product.trade.submit'), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log("Trade submission response:", page?.props?.flash);
            
            // Always show success toast, with enhanced information if available
            let successMessage = "Trade offer submitted successfully!";
            let tradeIdInfo = "";
            
            // Try to get enhanced info from flash message
            if (page?.props?.flash?.success) {
                successMessage = page.props.flash.success;
            }
            
            if (page?.props?.flash?.trade_id) {
                tradeIdInfo = ` (Trade ID: #${page.props.flash.trade_id})`;
            }
            
            // Show the success toast with the most information available
            toast({
                title: "Trade Offer Submitted!",
                description: successMessage + tradeIdInfo,
                variant: "success",
                duration: 6000, // Show for 6 seconds for better visibility
            });
            
            // Close modal and reset form
            closeDialog();
            form.reset();
        },
        onError: (errorResponse) => {
            console.error("Trade submission error:", errorResponse);
            
            toast({
                title: "Error",
                description: "Failed to submit trade offer. Please check the form.",
                variant: "destructive",
                duration: 6000,
            });
            
            // Set form errors
            errors.value = errorResponse;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Improved dialog closing mechanism with multiple methods
const handleDialogClose = (isOpen) => {
    console.log("Dialog update:open event received:", isOpen);
    if (!isOpen) {
        closeDialog();
    }
};

// Separate method to ensure consistent closing behavior
const closeDialog = () => {
    console.log("Closing trade dialog explicitly");
    form.reset();
    errors.value = {};
    emit('close');
    emit('update:open', false);
};

// Format time from 24h to 12h format - copied from Checkout.vue
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

// Computed property to get available schedules from meetup locations - same as Checkout.vue
const availableSchedules = computed(() => {
    const schedules = [];
    
    meetupLocations.value.forEach(location => {
        const availableDays = location.available_days || [];
        availableDays.forEach(day => {
            schedules.push({
                id: `${location.id}_${day}`,
                location: location.location?.name || 'Location Not Available', // Changed to match Checkout.vue
                day: day,
                timeFrom: formatTime(location.available_from),
                timeUntil: formatTime(location.available_until),
                description: location.description || '',
                maxMeetups: location.max_daily_meetups
            });
        });
    });

    console.log('Generated schedules:', schedules);
    return schedules;
});

// Format price helper function - copied from Checkout.vue
const formatPrice = (price) => {
    return new Intl.NumberFormat().format(price);
};

// Capitalize first letter helper - copied from Checkout.vue
const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

// Calculate disabled dates for the date picker based on selected schedule
const disabledDates = computed(() => {
    if (!selectedSchedule.value) return [];
    
    const disabledDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        .filter(day => day !== selectedSchedule.value.day.toLowerCase());
        
    // Function to determine if a date is disabled
    return (date) => {
        // Get day name from date
        const dayName = format(date, 'EEEE').toLowerCase();
        
        // Disable dates in the past
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Disable dates more than 30 days in the future
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + 30);
        
        return date < today || date > maxDate || disabledDays.includes(dayName);
    };
});

// Format date function
const formatSelectedDate = (date) => {
    if (!date) return '';
    return format(date, 'MMMM d, yyyy');
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
                        <p class="text-gray-600">₱{{ formatPrice(product.price) }}</p>
                    </div>
                </div>

                <!-- Seller Information - Now exactly like Checkout.vue -->
                <div class="border-t border-gray-100 py-4">
                    <h4 class="font-Satoshi-bold mb-2">Seller Information</h4>
                    <div class="flex items-center gap-3">
                        <img :src="sellerProfilePicture" class="w-8 h-8 rounded-full object-cover">
                        <div>
                            <p class="font-medium">{{ sellerName }}</p>
                            <p class="text-sm text-gray-500">{{ sellerLocation }}</p>
                        </div>
                    </div>
                </div>

                <!-- Meetup Schedule - Updated to match Checkout.vue -->
                <div class="mb-6">
                    <h3 class="font-Satoshi-bold mb-4">Available Meetup Schedules</h3>
                    <ScrollArea class="h-[200px] rounded-md border p-4">
                        <div v-if="isLoadingMeetupLocations" class="flex justify-center items-center h-[160px]">
                            <div class="text-gray-400">Loading available meetup schedules...</div>
                        </div>
                        <div v-else-if="availableSchedules.length === 0" class="flex justify-center items-center h-[160px]">
                            <div class="text-amber-600">No meetup locations available. Contact the seller for arrangements.</div>
                        </div>
                        <div v-else class="space-y-2">
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
                    <p v-if="errors.meetup_schedule" class="text-red-500 text-sm mt-2">{{ errors.meetup_schedule }}</p>
                </div>

                <!-- Meetup Date Selection - New Component -->
                <div class="mb-6" v-if="form.meetup_schedule">
                    <h3 class="font-Satoshi-bold mb-2">Select Meetup Date</h3>
                    <p class="text-sm text-gray-500 mb-2">
                        Choose a specific date for your meetup. 
                        Only {{ selectedSchedule?.day || '' }} dates are available.
                    </p>
                    
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button variant="outline" class="w-full justify-start text-left font-normal">
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                {{ selectedDate ? formatSelectedDate(selectedDate) : 'Select a date' }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar 
                                v-model="selectedDate" 
                                :disabled-dates="disabledDates" 
                                initialFocus
                                mode="single"
                            />
                        </PopoverContent>
                    </Popover>
                    <p v-if="errors.meetup_date" class="text-red-500 text-sm mt-2">{{ errors.meetup_date }}</p>
                </div>

                <!-- Additional cash - now clearly marked as optional -->
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

                <!-- Notes - now clearly marked as optional -->
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
                                class="text-red-500"
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
                            <p v-if="errors[`item_${index}_value`]" class="text-red-500 text-sm">
                                {{ errors[`item_${index}_value`] }}
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

                        <!-- File Upload - now required with asterisk -->
                        <div class="space-y-2">
                            <Label>Upload Images*</Label>
                            <FileUpload 
                                :multiple="true" 
                                :onUpload="(files) => handleFileUpload(files, index)"
                                :key="`file-upload-${index}`"
                            />
                            <p v-if="errors[`item_${index}_images`]" class="text-red-500 text-sm">
                                {{ errors[`item_${index}_images`] }}
                            </p>
                            <div v-if="item.images && item.images.length > 0" class="mt-2">
                                <p class="text-sm text-green-600">{{ item.images.length }} image(s) selected</p>
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
    
    <!-- Add Toaster component to display toast notifications -->
    <Toaster />
</template>