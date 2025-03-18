<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { useToast } from '@/Components/ui/toast/use-toast'
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue'

const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

// Formatting helpers
const formatPrice = (price) => new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
}).format(price)

const formatDate = (date) => new Date(date).toLocaleString()

// Add additional formatting helpers
const formatDateTime = (date) => {
    if (!date) return 'Not set'
    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short'
    }).format(new Date(date))
}

const getStatusDescription = (status) => {
    const descriptions = {
        'Pending': 'Your order is waiting for seller confirmation',
        'Accepted': 'The seller has accepted your order',
        'Meetup Scheduled': 'Meetup time and location confirmed',
        'Delivered': 'Order has been delivered',
        'Completed': 'Transaction completed successfully',
        'Cancelled': 'Order has been cancelled',
        'Disputed': 'Order is under review'
    }
    return descriptions[status] || status
}

// Cancel order functionality
const showCancelDialog = ref(false)
const cancelForm = useForm({
    cancellation_reason: ''
})

const cancelOrder = () => {
    cancelForm.patch(route('orders.cancel', props.order.id), {
        onSuccess: () => {
            showCancelDialog.value = false
            emit('close')
            toast({
                title: "Order Cancelled",
                description: "Your order has been cancelled successfully."
            })
        }
    })
}

// Add image error handler
const handleImageError = (e) => {
    e.target.src = '/images/placeholder.jpg'; // Fallback image
};
</script>

<template>
    <div class="space-y-6">
        <DialogHeader>
            <DialogTitle class="text-2xl">Order Details #{{ order.id }}</DialogTitle>
            <p class="text-gray-500 mt-1">Placed on {{ formatDateTime(order.created_at) }}</p>
        </DialogHeader>

        <!-- Order Status Timeline -->
        <div class="rounded-lg border bg-card p-6">
            <h3 class="font-semibold mb-4">Order Status</h3>
            <div class="flex items-center mb-4">
                <OrderStatusBadge :status="order.status" class="mr-2" />
                <p class="text-gray-600">{{ getStatusDescription(order.status) }}</p>
            </div>
            
            <!-- Status Timeline -->
            <div class="space-y-3 mt-4">
                <div v-if="order.accepted_at" class="flex items-start">
                    <div class="w-24 text-sm text-gray-500">Accepted</div>
                    <div>{{ formatDateTime(order.accepted_at) }}</div>
                </div>
                <div v-if="order.meetup_schedule" class="flex items-start">
                    <div class="w-24 text-sm text-gray-500">Meetup</div>
                    <div>{{ formatDateTime(order.meetup_schedule) }}</div>
                </div>
                <div v-if="order.completed_at" class="flex items-start">
                    <div class="w-24 text-sm text-gray-500">Completed</div>
                    <div>{{ formatDateTime(order.completed_at) }}</div>
                </div>
                <div v-if="order.cancelled_at" class="flex items-start text-red-600">
                    <div class="w-24 text-sm">Cancelled</div>
                    <div>
                        {{ formatDateTime(order.cancelled_at) }}
                        <p v-if="order.cancellation_reason" class="text-sm mt-1">
                            Reason: {{ order.cancellation_reason }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items with better presentation -->
        <div class="rounded-lg border bg-card p-6">
            <h3 class="font-semibold mb-4">Order Items</h3>
            <div class="space-y-4">
                <div v-for="item in order.items" :key="item.id" 
                     class="flex items-center justify-between border-b pb-4">
                    <div class="flex items-center gap-4">
                        <img 
                            :src="item.product.image_url || '/images/placeholder.jpg'"
                            :alt="item.product.name"
                            class="h-16 w-16 object-cover rounded-md"
                            @error="handleImageError"
                        />
                        <div>
                            <h4 class="font-medium">{{ item.product.name }}</h4>
                            <p class="text-sm text-gray-500">
                                {{ formatPrice(item.price) }} × {{ item.quantity }}
                            </p>
                        </div>
                    </div>
                    <span class="font-semibold">
                        {{ formatPrice(item.price * item.quantity) }}
                    </span>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="mt-6 border-t pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>{{ formatPrice(order.sub_total) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg">
                        <span>Total</span>
                        <span>{{ formatPrice(order.total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meetup Details with Map Preview if available -->
        <div v-if="order.meetup_location" class="rounded-lg border bg-card p-6">
            <h3 class="font-semibold mb-4">Meetup Details</h3>
            <div class="grid gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Location</span>
                        <p class="font-medium">{{ order.meetup_location.full_name }}</p>
                        <p v-if="order.meetup_location.description" class="text-sm text-gray-600 mt-1">
                            {{ order.meetup_location.description }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Schedule</span>
                        <p class="font-medium">{{ formatDateTime(order.meetup_schedule) }}</p>
                        <p v-if="order.meetup_confirmation_code" class="text-sm text-gray-600 mt-1">
                            Confirmation Code: <span class="font-medium">{{ order.meetup_confirmation_code }}</span>
                        </p>
                    </div>
                </div>
                <div v-if="order.meetup_notes" class="mt-2">
                    <span class="text-sm text-gray-500">Additional Notes</span>
                    <p class="mt-1">{{ order.meetup_notes }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
            <Button variant="outline" @click="emit('close')">Close</Button>
            <Button 
                v-if="order.status === 'Pending'"
                variant="destructive"
                @click="showCancelDialog = true"
            >
                Cancel Order
            </Button>
        </div>

        <!-- Cancel Order Dialog -->
        <Dialog :open="showCancelDialog" @update:open="showCancelDialog = false">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Cancel Order</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to cancel this order? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="cancelOrder" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Reason for Cancellation</label>
                        <textarea
                            v-model="cancelForm.cancellation_reason"
                            class="mt-1 w-full rounded-md border border-input px-3 py-2"
                            required
                            rows="3"
                            placeholder="Please provide a reason for cancellation"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <Button 
                            variant="outline" 
                            type="button"
                            @click="showCancelDialog = false"
                        >
                            Cancel
                        </Button>
                        <Button 
                            variant="destructive"
                            type="submit"
                            :disabled="cancelForm.processing"
                        >
                            Confirm Cancellation
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
