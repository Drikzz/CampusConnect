<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { useToast } from '@/Components/ui/toast/use-toast';
import { 
  UserIcon, 
  PhoneIcon, 
  MailIcon, 
  MapPinIcon, 
  CalendarIcon, 
  TicketIcon 
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  user: {
    type: Object,
    required: true
  }
});

const { toast } = useToast();

defineEmits(['close', 'order-updated']);

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatTime = (datetime) => {
  if (!datetime) return 'N/A';
  return new Date(datetime).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatPrice = (price) => {
  const numericPrice = parseFloat(price);
  if (isNaN(numericPrice) || numericPrice === null || numericPrice === undefined) {
    return '₱0.00';
  }
  return new Intl.NumberFormat('en-PH', { 
    style: 'currency', 
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numericPrice);
};

const getStatusVariant = (status) => {
  const statusMap = {
    'Pending': 'warning',
    'Processing': 'primary',
    'Shipped': 'info',
    'Delivered': 'success',
    'Completed': 'success',
    'Cancelled': 'destructive',
    'Refunded': 'secondary',
    'Meetup Scheduled': 'info'
  };
  return statusMap[status] || 'default';
};

const getProductImageUrl = (product) => {
  if (!product || !product.images) return '/images/placeholder-product.jpg';
  if (typeof product.images === 'string') {
    try {
      const images = JSON.parse(product.images);
      if (Array.isArray(images) && images.length > 0) {
        return formatImagePath(images[0]);
      }
      return formatImagePath(product.images);
    } catch (e) {
      return formatImagePath(product.images);
    }
  } else if (Array.isArray(product.images) && product.images.length > 0) {
    return formatImagePath(product.images[0]);
  }
  return '/images/placeholder-product.jpg';
};

const formatImagePath = (path) => {
  if (!path) return '/images/placeholder-product.jpg';
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  if (path.startsWith('storage/')) {
    return '/' + path;
  } else if (path.startsWith('/storage/')) {
    return path;
  } else {
    return '/storage/' + path;
  }
};

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg';
};

const cancelOrder = () => {
  if (confirm('Are you sure you want to cancel this order?')) {
    router.patch(route('orders.cancel', props.order.id), {}, {
      onSuccess: () => {
        toast({
          title: 'Order Cancelled',
          description: 'Your order has been cancelled successfully.',
          variant: 'default'
        });
      },
      onError: () => {
        toast({
          title: 'Error',
          description: 'Failed to cancel order. Please try again.',
          variant: 'destructive'
        });
      }
    });
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between gap-4 md:items-center">
      <div>
        <h2 class="text-2xl font-bold">Order #{{ order.id }}</h2>
        <p class="text-gray-500">Placed on {{ formatDate(order.created_at) }}</p>
      </div>
      <Badge :variant="getStatusVariant(order.status)">{{ order.status }}</Badge>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Order Items</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div v-for="item in order.items" :key="item.id" class="flex flex-col sm:flex-row gap-4 border-b pb-4 last:border-b-0 last:pb-0 items-start">
            <div class="w-24 h-24 rounded-md overflow-hidden border bg-gray-50 flex-shrink-0">
              <img 
                :src="getProductImageUrl(item.product)"
                :alt="item.product.name"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-medium truncate">{{ item.product.name }}</h3>
              <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
              <p class="text-sm text-gray-500">
                Seller: {{ item.product.seller?.first_name }} {{ item.product.seller?.last_name }}
              </p>
              <p v-if="item.product.description" class="text-sm text-gray-500 mt-2 line-clamp-2">
                {{ item.product.description }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm">{{ formatPrice(parseFloat(item.price)) }} each</p>
              <p class="font-medium mt-1">{{ formatPrice(parseFloat(item.price) * parseInt(item.quantity)) }}</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card v-if="order.meetup_location">
      <CardHeader>
        <CardTitle>Meetup Details</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-2">
          <div class="flex gap-2">
            <MapPinIcon class="h-5 w-5 text-gray-500" />
            <div>
              <p class="font-medium">{{ order.meetup_location.name }}</p>
              <p class="text-sm text-gray-500">{{ order.meetup_location.address }}</p>
            </div>
          </div>
          <div v-if="order.meetup_schedule" class="flex gap-2">
            <CalendarIcon class="h-5 w-5 text-gray-500" />
            <div>
              <p class="font-medium">{{ formatDate(order.meetup_schedule) }}</p>
              <p class="text-sm text-gray-500">{{ formatTime(order.meetup_schedule) }}</p>
            </div>
          </div>
          <div v-if="order.meetup_confirmation_code" class="flex gap-2">
            <TicketIcon class="h-5 w-5 text-gray-500" />
            <div>
              <p class="font-medium">Confirmation Code</p>
              <p class="text-sm font-mono">{{ order.meetup_confirmation_code }}</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Pricing Summary</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>{{ formatPrice(parseFloat(order.sub_total || 0)) }}</span>
          </div>
          <div class="flex justify-between">
            <span>Transaction Fee</span>
            <span>{{ formatPrice(order.transaction_fee || 0) }}</span>
          </div>
          <div class="border-t pt-2 mt-2 flex justify-between font-bold">
            <span>Total</span>
            <span>{{ formatPrice(parseFloat(order.total || order.sub_total || 0)) }}</span>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Contact Information</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-2">
          <div class="flex gap-2">
            <UserIcon class="h-5 w-5 text-gray-500" />
            <div>
              <p class="font-medium">{{ order.contact_name || user.first_name + ' ' + user.last_name }}</p>
            </div>
          </div>
          <div class="flex gap-2">
            <PhoneIcon class="h-5 w-5 text-gray-500" />
            <div>
              <p class="font-medium">{{ order.contact_phone || user.phone }}</p>
            </div>
          </div>
          <div class="flex gap-2">
            <MailIcon class="h-5 w-5 text-gray-500" />
            <div>
              <p class="font-medium">{{ order.contact_email || user.email }}</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="flex justify-end gap-4">
      <Button variant="outline" @click="$emit('close')">Close</Button>
      <Button 
        v-if="['Pending', 'Processing'].includes(order.status)" 
        variant="destructive"
        @click="cancelOrder"
      >
        Cancel Order
      </Button>
    </div>
  </div>
</template>
