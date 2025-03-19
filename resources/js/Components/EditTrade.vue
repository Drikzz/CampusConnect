<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
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
import { Calendar } from "@/Components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { format } from "date-fns";
import { CalendarIcon } from "lucide-vue-next";

const props = defineProps({
    trade: {
        type: Object,
        required: true
    },
    open: {
        type: Boolean,
        required: true
    },
    availableMeetupLocations: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'update:open']);
const { toast } = useToast();
const loading = ref(false);
const errors = ref({});

// Initialize the form with the trade data
const form = reactive({
    additional_cash: props.trade?.additional_cash || 0,
    meetup_location_id: props.trade?.meetup_location?.id || '',
    meetup_date: props.trade?.meetup_schedule ? new Date(props.trade?.meetup_schedule) : null,
    notes: props.trade?.notes || '',
    offered_items: []
});

// Initialize offered items from props
onMounted(() => {
    if (props.trade?.offered_items?.length) {
        form.offered_items = props.trade.offered_items.map(item => ({
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            estimated_value: item.estimated_value,
            description: item.description || '',
            images: [], // Will hold new images to upload
            current_images: item.images || [] // Store current images separately
        }));
    }
});

// Watch for changes in the trade prop to update the form
watch(() => props.trade, (newTrade) => {
    if (newTrade) {
        form.additional_cash = newTrade.additional_cash || 0;
        form.meetup_location_id = newTrade.meetup_location?.id || '';
        form.meetup_date = newTrade.meetup_schedule ? new Date(newTrade.meetup_schedule) : null;
        form.notes = newTrade.notes || '';
        
        if (newTrade.offered_items?.length) {
            form.offered_items = newTrade.offered_items.map(item => ({
                id: item.id,
                name: item.name,
                quantity: item.quantity,
                estimated_value: item.estimated_value,
                description: item.description || '',
                images: [], // Will hold new images to upload
                current_images: item.images || [] // Store current images separately
            }));
        } else {
            form.offered_items = [];
        }
    }
}, { deep: true });

// Format date for display
const formatMeetupDate = (date) => {
    if (!date) return 'Select Date';
    return format(new Date(date), 'MMMM d, yyyy');
};

// Add or remove offered items
const addOfferedItem = () => {
    form.offered_items.push({
        name: '',
        quantity: 1,
        estimated_value: 0,
        description: '',
        images: [],
        current_images: []
    });
};

const removeOfferedItem = (index) => {
    form.offered_items.splice(index, 1);
};

// Handle file uploads for item images
const handleFileUpload = (files, index) => {
    if (files && files.length > 0) {
        form.offered_items[index].images = files;
    }
};

// Remove a specific image from current images
const removeCurrentImage = (itemIndex, imageIndex) => {
    form.offered_items[itemIndex].current_images.splice(imageIndex, 1);
};

// Format price display
const formatPrice = (price) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(price);
};

// Validate the form before submission
const validate = () => {
    const validationErrors = {};
    let isValid = true;
    
    // Validate each offered item
    form.offered_items.forEach((item, index) => {
        if (!item.name.trim()) {
            validationErrors[`item_${index}_name`] = 'Item name is required';
            isValid = false;
        }
        
        if (item.quantity <= 0) {
            validationErrors[`item_${index}_quantity`] = 'Quantity must be greater than 0';
            isValid = false;
        }
        
        if (item.estimated_value <= 0) {
            validationErrors[`item_${index}_value`] = 'Estimated value must be greater than 0';
            isValid = false;
        }
        
        // Require at least one image (either current or new)
        if ((!item.current_images || item.current_images.length === 0) && 
            (!item.images || item.images.length === 0)) {
            validationErrors[`item_${index}_images`] = 'At least one image is required';
            isValid = false;
        }
    });
    
    // Validate additional cash (can be zero)
    if (form.additional_cash < 0) {
        validationErrors.additional_cash = 'Additional cash cannot be negative';
        isValid = false;
    }
    
    // Validate meetup schedule and location consistency
    if ((form.meetup_location_id && !form.meetup_date) || (!form.meetup_location_id && form.meetup_date)) {
        if (!form.meetup_date) {
            validationErrors.meetup_date = 'Please select a meetup date';
        }
        if (!form.meetup_location_id) {
            validationErrors.meetup_location_id = 'Please select a meetup location';
        }
        isValid = false;
    }
    
    errors.value = validationErrors;
    return isValid;
};

// Submit the edited trade
const submitEditedTrade = () => {
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
    
    // Add method spoofing - THIS IS THE KEY CHANGE
    // Laravel will interpret this as a PATCH request even though we're using POST
    formData.append('_method', 'PATCH');
    
    // Add basic fields
    formData.append('additional_cash', form.additional_cash);
    formData.append('notes', form.notes || '');
    
    // Add meetup details
    if (form.meetup_location_id) {
        formData.append('meetup_location_id', form.meetup_location_id);
    }
    
    if (form.meetup_date) {
        formData.append('meetup_date', format(new Date(form.meetup_date), 'yyyy-MM-dd'));
    }
    
    // Add offered items
    form.offered_items.forEach((item, index) => {
        formData.append(`offered_items[${index}][id]`, item.id || '');
        formData.append(`offered_items[${index}][name]`, item.name);
        formData.append(`offered_items[${index}][quantity]`, item.quantity);
        formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
        formData.append(`offered_items[${index}][description]`, item.description || '');
        
        // Add any new images
        if (item.images && item.images.length) {
            item.images.forEach((image, imageIndex) => {
                if (image instanceof File) {
                    formData.append(`offered_items[${index}][images][${imageIndex}]`, image);
                }
            });
        }
        
        // Keep track of current images to retain
        if (item.current_images && item.current_images.length) {
            formData.append(`offered_items[${index}][current_images]`, JSON.stringify(item.current_images));
        }
    });
    
    // Use POST instead of PATCH but with method spoofing for proper file handling
    router.post(route('trades.update', props.trade.id), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            toast({
                title: "Success",
                description: "Trade updated successfully",
                variant: "success",
                duration: 3000,
            });
            
            closeDialog();
        },
        onError: (errorResponse) => {
            toast({
                title: "Error",
                description: "Failed to update trade. Please check the form.",
                variant: "destructive",
                duration: 3000,
            });
            
            errors.value = errorResponse;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Dialog handling
const handleDialogClose = (isOpen) => {
    if (!isOpen) {
        closeDialog();
    }
};

const closeDialog = () => {
    errors.value = {};
    emit('close');
    emit('update:open', false);
};

// Helper to get optimized image URL 
const getOptimizedImageUrl = (image, fallbackImage = '/images/placeholder-product.jpg') => {
    if (!image) {
        return fallbackImage;
    }
    
    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    }
    
    if (image.startsWith('storage/')) {
        return '/' + image;
    }
    
    return `/storage/${image}`;
};

// Handle image errors
const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg';
};
</script>

<template>
    <Dialog :open="open" @update:open="handleDialogClose">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Edit Trade Offer</DialogTitle>
                <DialogDescription>
                    Make changes to your trade offer. The seller will be notified of your updates.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitEditedTrade" class="space-y-6">
                <!-- Trade ID indicator -->
                <div class="p-4 bg-gray-50 rounded-lg space-y-2">
                    <h3 class="font-semibold text-sm text-gray-600">EDITING TRADE #{{ trade.id }}</h3>
                    <p class="text-sm text-gray-500">You can modify all aspects of your trade offer.</p>
                </div>
                
                <!-- Offered Items section -->
                <div class="space-y-4 border-t pt-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-lg">Items to Offer</h3>
                        <Button type="button" variant="outline" size="sm" @click="addOfferedItem">
                            Add Another Item
                        </Button>
                    </div>

                    <div v-for="(item, index) in form.offered_items" :key="index" class="p-4 border rounded-lg space-y-4">
                        <div class="flex justify-between">
                            <h4 class="font-medium">Item {{ index + 1 }}</h4>
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
                            <!-- Item name -->
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

                            <!-- Quantity -->
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

                        <!-- Estimated value -->
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

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label :for="`item_description_${index}`">Description <span class="text-gray-400 text-sm">(Optional)</span></Label>
                            <Textarea 
                                :id="`item_description_${index}`" 
                                v-model="item.description"
                                placeholder="Describe the item..."
                            />
                        </div>

                        <!-- Current Images -->
                        <div v-if="item.current_images && item.current_images.length" class="space-y-2">
                            <Label>Current Images</Label>
                            <div class="grid grid-cols-4 gap-2">
                                <div v-for="(image, imageIndex) in item.current_images" :key="imageIndex" 
                                     class="relative group">
                                    <img 
                                        :src="getOptimizedImageUrl(image)" 
                                        :alt="`Item ${index+1} image ${imageIndex+1}`"
                                        class="h-24 w-24 object-cover rounded-md border"
                                        @error="handleImageError"
                                    />
                                    <button 
                                        type="button" 
                                        @click="removeCurrentImage(index, imageIndex)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 
                                               opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload New Images -->
                        <div class="space-y-2">
                            <Label>Upload New Images</Label>
                            <FileUpload 
                                :multiple="true" 
                                :onUpload="(files) => handleFileUpload(files, index)"
                                :key="`file-upload-${index}`"
                            />
                            <p v-if="errors[`item_${index}_images`]" class="text-red-500 text-sm">
                                {{ errors[`item_${index}_images`] }}
                            </p>
                            <div v-if="item.images && item.images.length > 0" class="mt-2">
                                <p class="text-sm text-green-600">{{ item.images.length }} new image(s) selected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional cash -->
                <div class="space-y-2 border-t pt-4">
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

                <!-- Meetup Information -->
                <div v-if="availableMeetupLocations.length > 0" class="space-y-4 border-t pt-4">
                    <h3 class="font-semibold">Meetup Information</h3>
                    
                    <!-- Meetup Location Selection -->
                    <div class="space-y-2">
                        <Label>Meetup Location</Label>
                        <Select v-model="form.meetup_location_id">
                            <option value="">Select a location</option>
                            <option 
                                v-for="location in availableMeetupLocations" 
                                :key="location.id" 
                                :value="location.id"
                            >
                                {{ location.full_name }}
                            </option>
                        </Select>
                        <p v-if="errors.meetup_location_id" class="text-red-500 text-sm">{{ errors.meetup_location_id }}</p>
                    </div>
                    
                    <!-- Meetup Date Selection -->
                    <div class="space-y-2">
                        <Label>Meetup Date</Label>
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button variant="outline" class="w-full justify-start text-left">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ form.meetup_date ? formatMeetupDate(form.meetup_date) : 'Select Date' }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <Calendar 
                                    v-model="form.meetup_date" 
                                    initialFocus 
                                    mode="single"
                                />
                            </PopoverContent>
                        </Popover>
                        <p v-if="errors.meetup_date" class="text-red-500 text-sm">{{ errors.meetup_date }}</p>
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2 border-t pt-4">
                    <Label for="notes">Notes for Seller <span class="text-gray-400 text-sm">(Optional)</span></Label>
                    <Textarea
                        id="notes"
                        v-model="form.notes"
                        placeholder="Add any details about your trade offer here..."
                        rows="4"
                    />
                </div>

                <DialogFooter class="flex justify-between pt-4 border-t border-gray-100">
                    <Button type="button" variant="outline" @click="closeDialog">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="loading">
                        {{ loading ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
    
    <Toaster />
</template>

<style scoped>
.image-preview img {
    max-width: 100%;
    height: auto;
}
</style>
