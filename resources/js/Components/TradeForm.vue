<script setup>
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { useToast } from "@/Components/ui/toast/use-toast";
import FileUpload from '@/Components/FileUpload.vue';

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

const emit = defineEmits(['close']);
const { toast } = useToast();
const loading = ref(false);
const errors = ref({});

// Form for trade submission using Inertia
const form = useForm({
    seller_product_id: props.product.id,
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
    form.offered_items[index].images = files;
};

// Basic validation
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
    });
    
    if (form.additional_cash < 0) {
        errors.value.additional_cash = 'Additional cash cannot be negative';
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
    
    // Debug info about the product being traded for
    console.log('Product details:', {
        id: props.product.id,
        name: props.product.name,
        seller: props.product.seller
    });
    
    // Create FormData for file uploads
    const formData = new FormData();
    formData.append('seller_product_id', form.seller_product_id);
    formData.append('additional_cash', form.additional_cash);
    formData.append('notes', form.notes);
    
    // Add offered items
    form.offered_items.forEach((item, index) => {
        formData.append(`offered_items[${index}][name]`, item.name);
        formData.append(`offered_items[${index}][quantity]`, item.quantity);
        formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
        formData.append(`offered_items[${index}][description]`, item.description || '');
        
        // Add images
        if (item.images && item.images.length) {
            item.images.forEach((image, imageIndex) => {
                formData.append(`offered_items[${index}][images][${imageIndex}]`, image);
            });
        }
    });
    
    // Use Inertia.js to post the form data
    router.post(route('product.trade.submit'), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            // Check for success in the response
            if (page && page.props && page.props.flash) {
                const success = page.props.flash.success;
                const tradeId = page.props.flash.trade_id;
                
                if (success) {
                    toast({
                        title: "Success!",
                        description: success
                    });
                    
                    console.log('Trade submitted successfully with ID:', tradeId);
                }
            } else {
                // Generic success message if flash messages aren't available
                toast({
                    title: "Success!",
                    description: "Trade offer submitted successfully"
                });
            }
            
            // Close modal and reset form
            emit('close');
            form.reset();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            
            toast({
                title: "Error",
                description: "Failed to submit trade offer. Please check the form.",
                variant: "destructive"
            });
            
            // Set form errors
            errors.value = errors;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

<template>
    <Dialog 
        :open="open" 
        @update:open="emit('close')"
        modal
    >
        <DialogContent class="sm:max-w-3xl overflow-y-auto max-h-[90vh]">
            <DialogHeader>
                <DialogTitle>Trade for {{ product.name }}</DialogTitle>
                <DialogDescription>
                    Offer your items in exchange for this product. The seller will review your offer.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitTradeOffer" class="space-y-6">
                <!-- Product being traded for -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-20 h-20 overflow-hidden rounded-md shrink-0">
                        <img 
                            :src="product.images?.[0] || '/images/placeholder-product.jpg'" 
                            :alt="product.name" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div>
                        <h3 class="font-Satoshi-bold text-lg">{{ product.name }}</h3>
                        <p class="text-gray-600">₱{{ product.price }}</p>
                        <p class="text-sm text-gray-500">Owned by: {{ product.seller?.name || 'Unknown Seller' }}</p>
                    </div>
                </div>

                <!-- Additional cash -->
                <div class="space-y-2">
                    <Label for="additional_cash">Additional Cash (₱)</Label>
                    <Input 
                        id="additional_cash" 
                        type="number" 
                        v-model="form.additional_cash" 
                        min="0" 
                        step="0.01"
                    />
                    <p v-if="errors.additional_cash" class="text-red-500 text-sm">{{ errors.additional_cash }}</p>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <Label for="notes">Notes for Seller</Label>
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
                            <Label :for="`item_description_${index}`">Description</Label>
                            <Textarea 
                                :id="`item_description_${index}`" 
                                v-model="item.description" 
                                placeholder="Describe your item..."
                                rows="3"
                            />
                        </div>

                        <!-- Images upload using FileUpload component -->
                        <div class="space-y-2">
                            <Label>Item Images</Label>
                            <FileUpload 
                                :files="item.images" 
                                @update:files="files => handleFileUpload(files, index)" 
                                multiple
                                accept="image/*"
                            />
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('close')">Cancel</Button>
                    <Button 
                        type="submit" 
                        :disabled="loading"
                        class="ml-2"
                    >
                        {{ loading ? 'Submitting...' : 'Submit Trade Offer' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
    
</template>
