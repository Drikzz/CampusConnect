<template>
  <DashboardLayout 
    :user="auth.user" 
    :stats="stats" 
    :flash="flash || {}"
    class="bg-background text-foreground"
  >
    <!-- Add toast container for notifications -->
    <div class="fixed top-4 right-4 z-[100]">
      <Toaster />
    </div>

    <div class="space-y-2">
      <!-- Search Bar -->
      <div class="w-full mb-4">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search trades..."
          class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
        />
      </div>

      <!-- Trades Tabs -->
      <Tabs default-value="all" class="w-full">
        <TabsList class="grid w-full grid-cols-6">
          <TabsTrigger value="all">
            All ({{ groupedTrades.all.length }})
          </TabsTrigger>
          <TabsTrigger value="pending">
            Pending ({{ groupedTrades.pending.length }})
          </TabsTrigger>
          <TabsTrigger value="accepted">
            Accepted ({{ groupedTrades.accepted.length }})
          </TabsTrigger>
          <TabsTrigger value="rejected">
            Rejected ({{ groupedTrades.rejected.length }})
          </TabsTrigger>
          <TabsTrigger value="completed">
            Completed ({{ groupedTrades.completed.length }})
          </TabsTrigger>
          <TabsTrigger value="canceled">
            Cancelled ({{ groupedTrades.canceled.length }})
          </TabsTrigger>
        </TabsList>

        <template v-for="(trades, status) in groupedTrades" :key="status">
          <TabsContent :value="status">
            <div class="space-y-4">
              <!-- Empty State -->
              <div v-if="trades.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <h3 class="text-lg font-medium mb-2">No trades found</h3>
                <p class="text-sm text-gray-400 text-center">
                  {{ getEmptyStateMessage(status) }}
                </p>
              </div>

              <!-- Trade Cards -->
              <Card v-else v-for="(trade, tradeIndex) in trades" :key="trade.id" class="flex flex-col mb-4 bg-card text-card-foreground">
                <CardHeader class="py-6">
                  <CardTitle class="flex justify-between mb-3">
                    <span class="text-lg">Trade #{{ getUserTradeNumber(tradeIndex, status) }}</span>
                    <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusColor(trade.status)]">
                      {{ trade.status.charAt(0).toUpperCase() + trade.status.slice(1) }}
                    </span>
                  </CardTitle>
                  <CardDescription class="text-sm text-gray-500">
                    Offered on {{ formatDateTime(trade.created_at) }}
                  </CardDescription>
                </CardHeader>
                <CardContent class="py-4 space-y-4 flex-1 min-h-0">
                  <div class="grid grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                      <!-- Product Being Traded For -->
                      <div>
                        <h4 class="font-medium text-base mb-3">Trading for:</h4>
                        <div class="flex gap-4 items-start">
                          <div class="w-16 h-16 overflow-hidden rounded-md border">
                            <img 
                              v-if="getImageSrc(trade.seller_product?.images || [])" 
                              :src="getImageSrc(trade.seller_product?.images || [])"
                              class="w-full h-full object-cover"
                              alt="Product image"
                              @error="handleImageError"
                            />
                            <div v-else class="w-full h-full bg-muted flex items-center justify-center">
                              <ImageIcon class="h-6 w-6 text-muted-foreground" />
                            </div>
                          </div>
                          <div>
                            <h5 class="font-medium">{{ trade.seller_product?.name || 'Product Name' }}</h5>
                            <p class="text-sm text-muted-foreground">{{ formatPrice(trade.seller_product?.price || 0) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="space-y-4">
                      <h4 class="font-medium text-base mb-3">Your Offer:</h4>
                      <!-- Offered Items -->
                      <div v-if="trade.offered_items && trade.offered_items.length" class="space-y-2">
                        <div v-for="item in trade.offered_items.slice(0, 2)" :key="item.id" class="flex gap-2 items-start">
                          <div class="w-12 h-12 overflow-hidden rounded-md border flex-shrink-0">
                            <ImagePreview
                              :images="processImagesForPreview(item.images || [])"
                              :alt="item.name"
                            />
                          </div>
                          <div>
                            <p class="text-sm font-medium">{{ item.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatPrice(item.estimated_value) }} x {{ item.quantity }}</p>
                          </div>
                        </div>
                        <div v-if="trade.offered_items.length > 2" class="text-xs text-muted-foreground">
                          +{{ trade.offered_items.length - 2 }} more item(s)
                        </div>
                      </div>
                      
                      <!-- Additional Cash -->
                      <div v-if="trade.additional_cash > 0">
                        <p class="text-sm">
                          <span class="font-medium">Additional Cash:</span> 
                          <span class="text-primary">{{ formatPrice(trade.additional_cash) }}</span>
                        </p>
                      </div>

                      <!-- Notes if any -->
                      <div v-if="trade.notes">
                        <p class="text-xs text-muted-foreground line-clamp-2">
                          <span class="font-medium">Notes:</span> {{ trade.notes }}
                        </p>
                      </div>
                    </div>
                  </div> <!-- End of grid -->

                  <!-- Meetup Schedule - Now full width -->
                  <div v-if="trade.meetup_schedule" class="mt-4 border-t pt-4">
                    <div class="flex items-start gap-3">
                      <CalendarIcon class="h-5 w-5 text-muted-foreground mt-0.5" />
                      <div class="space-y-1">
                        <p class="font-medium">Meetup Details</p>
                        <div class="flex items-center gap-4 text-sm text-muted-foreground">
                          <div class="flex items-center">
                            <CalendarDaysIcon class="h-4 w-4 mr-1" />
                            {{ formatDateTime(trade.meetup_schedule, false, true) }}
                          </div>
                          <div class="flex items-center">
                            <ClockIcon class="h-4 w-4 mr-1" />
                            {{ formatTime(trade.preferred_time) }}
                          </div>
                        </div>
                        <div class="flex items-center text-sm text-muted-foreground">
                          <MapPinIcon class="h-4 w-4 mr-1" />
                          {{ trade.meetup_location_name || 'Location not specified' }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="mt-4 border-t pt-4">
                    <div class="flex items-start gap-3">
                      <CalendarOffIcon class="h-5 w-5 text-muted-foreground mt-0.5" />
                      <div>
                        <p class="font-medium">Meetup Details (not scheduled)</p>
                        <p class="text-sm text-muted-foreground">No meetup scheduled yet</p>
                      </div>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="py-6 flex justify-between items-center border-t">
                  <div class="font-semibold text-base">
                    Total Value: {{ formatPrice(calculateTradeValue(trade)) }}
                  </div>
                  <div class="space-x-3 flex items-center">
                    <Button variant="outline" class="hover:bg-gray-100" @click="viewTradeDetails(trade)">
                      View Details
                    </Button>
                    <Button 
                      v-if="trade.status === 'pending'" 
                      variant="outline"
                      class="hover:bg-gray-100"
                      @click="editTrade(trade)"
                    >
                      Edit Details
                    </Button>
                    <Button v-if="['pending', 'accepted'].includes(trade.status)" variant="destructive" @click="promptCancelTrade(trade.id)">
                      Cancel
                    </Button>
                  </div>
                </CardFooter>
              </Card>
            </div>
          </TabsContent>
        </template>
      </Tabs>
    </div>

    <!-- Trade Details Dialog -->
    <Dialog :open="showTradeDetails" @update:open="closeTradeDetails">
      <DialogContent class="mx-4 w-[75%] sm:max-w-20xl overflow-y-auto max-h-[90vh] p-6 md:p-8 lg:p-10 bg-background dark:bg-gray-900 border-border dark:border-gray-700">
        <DialogHeader>
          <DialogTitle class="flex items-center justify-between">
            <span class="text-2xl font-semibold">Trade #{{ selectedTrade?.id || '' }}</span>
            <Badge v-if="selectedTrade?.status" :variant="getStatusVariant(selectedTrade.status)">
              {{ selectedTrade.status ? selectedTrade.status.charAt(0).toUpperCase() + selectedTrade.status.slice(1) : '' }}
            </Badge>
            <div v-else class="h-6 w-24 bg-muted animate-pulse rounded"></div>
          </DialogTitle>
          <DialogDescription>
            <template v-if="selectedTrade?.created_at">
              Created on {{ formatDateTime(selectedTrade.created_at, true) }}
            </template>
            <div v-else class="h-4 w-32 bg-muted animate-pulse rounded"></div>
          </DialogDescription>
        </DialogHeader>

        <!-- Add skeleton loading state -->
        <div v-if="!selectedTrade" class="space-y-6 py-4">
          <div class="flex flex-col space-y-6">
            <Skeleton class="h-[150px] w-full rounded-lg" />
            <div class="space-y-3">
              <Skeleton class="h-6 w-full" />
              <Skeleton class="h-6 w-[80%]" />
            </div>
            <Skeleton class="h-[100px] w-full rounded-lg" />
            <Skeleton class="h-[200px] w-full rounded-lg" />
          </div>
        </div>

        <div v-else class="space-y-6 py-4">
          <!-- Seller's Product Section -->
          <Card class="flex flex-col mb-4 bg-card text-card-foreground">
            <CardHeader>
              <CardTitle>Product Details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-6 flex-1 min-h-0">
              <div class="space-y-4">
                <h4 class="font-semibold text-sm text-muted-foreground">Trading for:</h4>
                <div class="border rounded-lg p-4">
                  <div class="flex gap-4">
                    <!-- Use ImagePreview component instead of raw img -->
                    <div class="w-24 h-24 aspect-square rounded-md overflow-hidden">
                      <ImagePreview
                        :images="selectedTrade.sellerProduct && selectedTrade.sellerProduct.images ? 
                          processImagesForPreview(selectedTrade.sellerProduct.images) : 
                          ['/images/placeholder-product.jpg']"
                        :alt="selectedTrade.sellerProduct?.name || 'Product'"
                      />
                    </div>

                    <!-- Product Details -->
                    <div class="flex-1">
                      <div class="flex justify-between">
                        <h3 class="font-medium">{{ selectedTrade.sellerProduct?.name }}</h3>
                        <p class="font-semibold text-primary">
                          {{ formatPrice(selectedTrade.sellerProduct?.price || 0) }}
                        </p>
                      </div>
                      <div class="flex items-center gap-1 text-sm text-muted-foreground mt-1">
                        <UserIcon class="h-3.5 w-3.5" />
                        <span>{{ selectedTrade.seller?.name || 'Unknown Seller' }}</span>
                      </div>
                      <p v-if="selectedTrade.sellerProduct && selectedTrade.sellerProduct.description" 
                         class="text-sm text-muted-foreground mt-2 line-clamp-3">
                        {{ selectedTrade.sellerProduct.description }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Your Offer Section -->
          <Card class="flex flex-col mb-4 bg-card text-card-foreground">
            <CardHeader>
              <CardTitle>Your Offer</CardTitle>
            </CardHeader>
            <CardContent class="space-y-6 flex-1 min-h-0">
              <!-- Offered Items -->
              <div v-if="selectedTrade.offered_items?.length" class="space-y-4">
                <h4 class="font-semibold text-sm text-muted-foreground">Offered Items</h4>
                <div v-for="item in selectedTrade.offered_items" :key="item.id" class="border rounded-lg p-4">
                  <div class="flex gap-4">
                    <!-- Use ImagePreview component here instead of raw img with click handler -->
                    <div class="w-24 h-24 aspect-square rounded-md overflow-hidden">
                      <ImagePreview
                        :images="processImagesForPreview(item.images || [])"
                        :alt="item.name"
                      />
                    </div>

                    <!-- Item Details -->
                    <div class="flex-1">
                      <div class="flex justify-between">
                        <h5 class="font-semibold">{{ item.name }}</h5>
                        <p class="font-semibold text-primary">{{ formatPrice(item.estimated_value) }}</p>
                      </div>
                      <p class="text-sm text-muted-foreground mt-1">Quantity: {{ item.quantity }}</p>
                      <p v-if="item.description" class="text-sm text-muted-foreground mt-2">
                        {{ item.description }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Additional Cash -->
              <div v-if="selectedTrade.additional_cash > 0">
                <h4 class="font-semibold text-sm text-muted-foreground mb-2">Additional Cash</h4>
                <div class="bg-muted rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm">Cash Amount</span>
                    <span class="font-semibold text-primary">{{ formatPrice(selectedTrade.additional_cash) }}</span>
                  </div>
                </div>
              </div>

              <!-- Total Value -->
              <Separator />
              <div class="flex items-center justify-between pt-2">
                <span class="font-semibold">Total Offer Value</span>
                <span class="text-lg font-bold text-primary">{{ formatPrice(calculateTotalValue(selectedTrade)) }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Meetup Schedule Section -->
          <Card v-if="selectedTrade.meetup_schedule || selectedTrade.meetup_location" class="flex flex-col mb-4 bg-card text-card-foreground">
            <CardHeader>
              <CardTitle>Meetup Details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4 flex-1 min-h-0">
              <div class="flex items-start gap-3">
                <CalendarIcon class="h-5 w-5 text-muted-foreground mt-0.5" />
                <div class="space-y-1">
                  <p class="font-medium">Meetup Details</p>
                  <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center">
                      <CalendarDaysIcon class="h-4 w-4 mr-1" />
                      {{ formatDateTime(selectedTrade.meetup_schedule, false, true) }}
                    </div>
                    <div class="flex items-center">
                      <ClockIcon class="h-4 w-4 mr-1" />
                      {{ formatTime(selectedTrade.preferred_time || '') }}
                    </div>
                  </div>
                  <div class="flex items-center text-sm text-muted-foreground">
                    <MapPinIcon class="h-4 w-4 mr-1" />
                    {{ selectedTrade.meetup_location_name || 'Location not specified' }}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Chat Section -->
          <Card class="flex flex-col mb-4 bg-card text-card-foreground">
            <CardHeader>
              <CardTitle>Chat History</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="chatState.isLoading" class="flex justify-center p-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
              </div>
              <div v-else-if="chatState.messages.length === 0" class="text-center py-8 text-muted-foreground">
                No messages yet
              </div>
              <div v-else ref="messagesContainer" class="space-y-4 max-h-[400px] overflow-y-auto scroll-smooth">
                <div v-for="message in chatState.messages" :key="message.id" 
                     :class="['flex', message.user_id === auth.user.id ? 'justify-end' : 'justify-start']">
                  <div :class="[
                    'max-w-[80%] rounded-lg p-3',
                    message.user_id === auth.user.id ? 'bg-primary text-primary-foreground' : 'bg-muted'
                  ]">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="text-xs font-medium">
                        {{ message.user.first_name }} {{ message.user.last_name }}
                      </span>
                      <span class="text-xs opacity-70">
                        {{ formatMessageTime(message.created_at) }}
                      </span>
                    </div>
                    <p class="text-sm">{{ message.message }}</p>
                  </div>
                </div>
              </div>
              <form @submit.prevent="sendMessage" class="mt-4 flex gap-2">
                <Input 
                  v-model="newMessage" 
                  placeholder="Type your message..."
                  :disabled="isSendingMessage"
                />
                <Button type="submit" :disabled="isSendingMessage">
                  Send
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="closeTradeDetails">Close</Button>
          <div class="space-x-2">
            <Button 
              v-if="selectedTrade?.status === 'completed'"
              variant="secondary"
              @click="openReviewDialog"
            >
              <StarIcon class="w-4 h-4 mr-2" />
              View/Write Review
            </Button>
            <Button 
              v-if="selectedTrade?.status === 'pending'"
              @click="editTrade(selectedTrade)"
            >
              Edit Trade
            </Button>
            <Button 
              v-if="['pending', 'accepted'].includes(selectedTrade?.status)"
              variant="destructive"
              @click="promptCancelTrade(selectedTrade.id)"
            >
              Cancel Trade
            </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Cancel Trade Alert Dialog - updated with reason field -->
    <AlertDialog :open="showCancelAlert" @update:open="showCancelAlert = false">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Cancel Trade Offer</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to cancel this trade offer? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <div class="my-4">
          <Label for="cancel-reason">Reason for cancellation (optional)</Label>
          <Textarea id="cancel-reason" v-model="cancellationReason" placeholder="Please provide a reason..." class="mt-2" />
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showCancelAlert = false">No, Keep it</AlertDialogCancel>
          <AlertDialogAction @click="confirmCancelTrade">Yes, Cancel Trade</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Add the Reviews Dialog -->
    <Dialog :open="!!showReviewsDialog" @update:open="showReviewsDialog = $event">
      <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto scroll-smooth animate-in fade-in-0 zoom-in-95 slide-in-from-left-1/2 slide-in-from-top-[48%]">
        <DialogHeader>
          <DialogTitle>Seller Reviews</DialogTitle>
          <DialogDescription v-if="selectedTrade">
            Reviews for {{ selectedTrade.seller?.name || 'Seller' }}
          </DialogDescription>
        </DialogHeader>
        <SellerReviews
          v-if="selectedTrade"
          :seller-code="selectedTrade.seller_code"
          :seller-name="selectedTrade.seller?.name || 'Seller'"
          :transaction-id="selectedTrade.id"
          transaction-type="trade"
          :is-completed="selectedTrade.status === 'completed'"
          @review-submitted="handleReviewSubmitted"
        />
        <DialogFooter>
          <Button variant="outline" @click="showReviewsDialog = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Add image preview dialog -->
    <Dialog :open="!!previewImageUrl" @update:open="previewImageUrl = null">
      <DialogContent class="p-0 sm:max-w-3xl overflow-hidden">
        <img 
          v-if="previewImageUrl" 
          :src="previewImageUrl" 
          class="max-w-full max-h-[80vh] object-contain" 
          @error="$event.target.src = '/images/placeholder-product.jpg'"
        />
        <Button 
          variant="ghost" 
          size="icon" 
          class="absolute top-2 right-2 rounded-full bg-black/30 hover:bg-black/50 text-white"
          @click="previewImageUrl = null"
        >
          <X class="h-4 w-4" />
        </Button>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick, reactive } from 'vue';
import { usePage, router, Deferred } from '@inertiajs/vue3';
import DashboardLayout from './DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Toaster } from '@/Components/ui/toast';
import { useToast } from '@/Components/ui/toast/use-toast';
import { 
  Card, 
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { 
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import axios from 'axios';
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription } from '@/Components/ui/dialog';
import { 
  UserIcon, 
  ImageIcon, 
  StarIcon, 
  CalendarIcon,
  CalendarDaysIcon,
  ClockIcon,
  MapPinIcon,
  CalendarOffIcon,
  X
} from 'lucide-vue-next';
import { Input } from "@/Components/ui/input";
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { format } from "date-fns";
import { Skeleton } from '@/Components/ui/Skeleton';
import SellerReviews from '@/Components/SellerReviews.vue';
import MeetupDate from '@/Components/ui/trade-calendar/meetup-date.vue';
import { ScrollArea } from '@/Components/ui/scroll-area';
import TradeForm from '@/Components/TradeForm.vue';
import { ImagePreview } from '@/Components/ui/image-preview';
import UserAvatar from '@/Components/ui/user-avatar.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';

// Define a helper function to optimize image URLs
const getOptimizedImageUrl = (url) => {
  if (!url) return '/images/placeholder-product.jpg';
  
  // If it's already a full URL, return it as is
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }
  
  // Clean up URL path
  if (url.startsWith('/storage/')) {
    return url;
  } else if (url.startsWith('storage/')) {
    return '/' + url;
  } else {
    return '/storage/' + url;
  }
};

// Create a stub for editForm if it doesn't exist
const editForm = reactive({
  meetup_location_id: '',
  meetup_date: null,
  meetup_time: '',
  offered_items: []
});

// Define cancellationReason which is used in the template
const cancellationReason = ref('');

const props = defineProps({
  auth: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  },
  trades: {
    type: Object,
    default: () => ({
      data: [],
    }),
  },
  flash: {
    type: Object,
    default: () => ({
      success: null,
      error: null,
    }),
  },
});

const user = computed(() => props.auth.user);
const page = usePage();
const { toast } = useToast();

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    toast({
      title: 'Success',
      description: flash.success,
      variant: 'default'
    });
  } else if (flash?.error) {
    toast({
      title: 'Error',
      description: flash.error,
      variant: 'destructive'
    });
  }
}, { deep: true, immediate: true });

watch(() => page.props, (props) => {
  if (props.success) {
    toast({
      title: 'Success',
      description: props.success,
      variant: 'default'
    });
  }
  if (props.error) {
    toast({
      title: 'Error',
      description: props.error,
      variant: 'destructive'
    });
  }
}, { immediate: true });

const searchQuery = ref('');
const selectedTrade = ref(null);
const showTradeDetails = ref(false);
const showCancelAlert = ref(false);
const showEditTradeDialog = ref(false);
const tradeToCancel = ref(null);
const tradeToEdit = ref(null);
const availableMeetupLocations = ref([]);
const loading = ref(false);
const selectedDate = ref(null);
const tradeSchedule = reactive({
  meetup_location_id: '',
  selectedDay: '',
  meetingSelection: ''
});

const groupedTrades = computed(() => {
  const buyerTrades = (props.trades?.data || []).filter(trade => 
    trade.buyer_id === props.auth.user.id
  );
  const groups = {
    all: buyerTrades.filter(t => t.status !== 'canceled'),
    pending: buyerTrades.filter(t => t.status === 'pending'),
    accepted: buyerTrades.filter(t => t.status === 'accepted'),
    rejected: buyerTrades.filter(t => t.status === 'rejected'),
    completed: buyerTrades.filter(t => t.status === 'completed'),
    canceled: buyerTrades.filter(t => t.status === 'canceled'),
  };
  Object.keys(groups).forEach(key => {
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      groups[key] = groups[key].filter(trade => 
        trade.id.toString().includes(query) || 
        (trade.seller_product && trade.seller_product.name.toLowerCase().includes(query)) ||
        (trade.offered_items && trade.offered_items.some(item => item.name.toLowerCase().includes(query)))
      );
    }
  });
  return groups;
});

const formatPrice = (price) => {
  const numericPrice = Number(price);
  const formatter = new Intl.NumberFormat('en-PH', { 
    style: 'currency', 
    currency: 'PHP',
    currencyDisplay: 'symbol'
  });
  return formatter.format(numericPrice);
};

const formatDateTime = (date, includeTime = false, dateOnly = false) => {
  if (!date) return '';
  
  if (dateOnly) {
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    };
    return new Date(date).toLocaleDateString('en-PH', options);
  }
  
  const options = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  };
  if (includeTime) {
    options.hour = '2-digit';
    options.minute = '2-digit';
  }
  return new Date(date).toLocaleDateString('en-PH', options);
};

const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-100',
    'accepted': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100',
    'rejected': 'bg-destructive/10 text-destructive dark:bg-destructive/60 dark:text-red',
    'canceled': 'bg-secondary text-secondary-foreground',
    'completed': 'bg-primary/10 text-primary dark:bg-primary/20',
  };
  return colors[status] || 'bg-secondary text-secondary-foreground';
};

const calculateTradeValue = (trade) => {
  let totalValue = 0;
  if (trade.offered_items && trade.offered_items.length > 0) {
    totalValue = trade.offered_items.reduce((sum, item) => {
      return sum + (parseFloat(item.estimated_value) * parseInt(item.quantity));
    }, 0);
  }
  if (trade.additional_cash) {
    totalValue += parseFloat(trade.additional_cash);
  }
  return totalValue;
};

const calculateTotalValue = (trade) => {
  return calculateTradeValue(trade);
};

const getEmptyStateMessage = (status) => {
  const messages = {
    all: "You haven't made any trade offers yet",
    pending: "No pending trade offers",
    accepted: "No accepted trades at the moment",
    rejected: "No rejected trades",
    completed: "No completed trades yet",
    canceled: "No cancelled trades"
  };
  return messages[status] || "No trades found";
};

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg';
};

/**
 * Ensures the path starts with /storage/ correctly
 * @param {String} path - The image path
 * @returns {String} - Corrected path to storage file
 */
const ensureStoragePath = (path) => {
  if (!path || typeof path !== 'string') return '/images/placeholder-product.jpg';
  
  // If it's already a full URL, return it as is
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  
  // Clean path of any domain references that might be mixed in
  const cleanPath = path.replace(/^https?:\/\/[^\/]+\//, '');
  
  // Handle storage paths
  if (cleanPath.startsWith('/storage/')) {
    return cleanPath;
  } else if (cleanPath.startsWith('storage/')) {
    return '/' + cleanPath;
  } else {
    // For any other path, assume it should be in storage
    return `/storage/${cleanPath}`;
  }
};

/**
 * Gets the first image from an array or string of images - specifically for product images
 * Completely separate from user profile pictures
 * @param {Array|String} images - The images array or string
 * @returns {String} - URL to the image file
 */
const getImageSrc = (images) => {
  // Early exit for empty input
  if (!images || (Array.isArray(images) && images.length === 0) || (typeof images === 'string' && !images.trim())) {
    return '/images/placeholder-product.jpg';
  }

  try {
    // Handle JSON string format
    if (typeof images === 'string' && (images.startsWith('[') || images.startsWith('{'))) {
      try {
        const parsed = JSON.parse(images);
        // Array of images - use first valid one
        if (Array.isArray(parsed) && parsed.length > 0) {
          for (const img of parsed) {
            if (img) return ensureStoragePath(img);
          }
        }
        // Single image as string
        else if (typeof parsed === 'string' && parsed) {
          return ensureStoragePath(parsed);
        }
      } catch (e) {
        // If JSON parsing fails but there's content, use it as a path
        if (images.trim()) {
          return ensureStoragePath(images);
        }
      }
    }
    // Direct string path input
    else if (typeof images === 'string') {
      return ensureStoragePath(images);
    }
    // Array of image paths
    else if (Array.isArray(images)) {
      for (const img of images) {
        if (img) return ensureStoragePath(img);
      }
    }
  } catch (error) {
    console.error('Error processing product image:', error);
  }
  // Default fallback
  return '/images/placeholder-product.jpg';
};

const previewImageUrl = ref(null);
const previewImage = (imageUrl) => {
  let url = imageUrl; // Already normalized by getProductImagePath or getOfferedItemImagePath
  previewImageUrl.value = url;

  // Preload the image
  const img = new Image();
  img.onload = () => {
    if (img.complete) {
      // Quick fix for rerendering the image if needed
      const currentUrl = previewImageUrl.value;
      previewImageUrl.value = null;
      setTimeout(() => {
        previewImageUrl.value = currentUrl;
      }, 10);
    }
  };
  img.src = url;
};

const formatTime = (time) => {
  if (!time) return 'Time not specified';
  try {
    // Handle various time formats
    let hours, minutes;
    if (time.includes(':')) {
      [hours, minutes] = time.split(':');
    } else {
      return time; // Return as is if format is unknown
    }
    const hourNum = parseInt(hours);
    if (isNaN(hourNum)) return time;
    const suffix = hourNum >= 12 ? 'PM' : 'AM';
    const hour12 = hourNum % 12 || 12;
    return `${hour12}:${minutes} ${suffix}`;
  } catch (e) {
    console.error('Error formatting time:', e, 'Time value:', time);
    return time || 'Time not specified';
  }
};

const getUserTradeNumber = (index, status) => {
  if (status === 'all') {
    return index + 1;
  }
  const allTrades = groupedTrades.value.all;
  const trade = groupedTrades.value[status][index];
  const allTradesIndex = allTrades.findIndex(t => t.id === trade.id);
  return allTradesIndex !== -1 ? allTradesIndex + 1 : index + 1;
};

const viewTradeDetails = async (trade) => {
  try {
    selectedTrade.value = null;
    showTradeDetails.value = true;
    chatState.messages = []; 
    chatState.error = null;
    const response = await axios.get(route('trades.details', trade.id));
    if (response.data && response.data.success && response.data.trade) {
      selectedTrade.value = response.data.trade;
      // Check if sellerProduct field exists and create it from seller_product if needed
      if (!selectedTrade.value.sellerProduct && selectedTrade.value.seller_product) {
        selectedTrade.value.sellerProduct = selectedTrade.value.seller_product;
      }
      // Format images consistently for both sellerProduct and offered_items
      if (selectedTrade.value.sellerProduct && selectedTrade.value.sellerProduct.images) {
        // Images handling already taken care of by getProductImagePath function
      }
      // Ensure preferred_time is available
      if (!selectedTrade.value.preferred_time && trade.preferred_time) {
        selectedTrade.value.preferred_time = trade.preferred_time;
      }
      // Format time for display
      if (selectedTrade.value.preferred_time) {
        selectedTrade.value.preferred_time_formatted = formatTime(selectedTrade.value.preferred_time);
      }
      // Process offered items - no need to transform the actual image structure
      // as our getOfferedItemImagePath will handle all the formatting
    }
    await fetchMessages(trade.id);
  } catch (error) {
    console.error('Error loading trade details:', error);
    toast({
      title: 'Error',
      description: 'Failed to load complete trade details',
      variant: 'destructive'
    });
    selectedTrade.value = null;
    showTradeDetails.value = false;
  }
};

const closeTradeDetails = () => {
  showTradeDetails.value = false;
  selectedTrade.value = null;
};

const editTrade = async (trade) => {
  showTradeDetails.value = false;
  tradeToEdit.value = {
    ...trade,
    sellerProduct: trade.seller_product || null
  };
  try {
    const response = await axios.get(route('trades.details', trade.id));
    if (response.data && response.data.success && response.data.trade) {
      tradeToEdit.value = {
        ...response.data.trade,
        sellerProduct: response.data.trade.sellerProduct || null
      };
      try {
        const productResponse = await axios.get(`/trade/products/${tradeToEdit.value.seller_product_id}/details`);
        if (productResponse.data) {
          tradeToEdit.value.sellerProduct = {
            ...tradeToEdit.value.sellerProduct,
            ...productResponse.data
          };
          if (productResponse.data.seller) {
            tradeToEdit.value.seller = {
              ...tradeToEdit.value.seller,
              ...productResponse.data.seller
            };
          }
        }
      } catch (productError) {
        console.error("Failed to load additional product details for editing:", productError);
        try {
          const fallbackResponse = await axios.get(`/api/products/${tradeToEdit.value.seller_product_id}`);
          if (fallbackResponse.data) {
            tradeToEdit.value.sellerProduct = {
              ...tradeToEdit.value.sellerProduct,
              ...fallbackResponse.data
            };
            if (fallbackResponse.data.seller) {
              tradeToEdit.value.seller = {
                ...tradeToEdit.value.seller,
                ...fallbackResponse.data.seller
              };
            }
          }
        } catch (fallbackError) {
          console.error("All attempts to load product details failed:", fallbackError);
        }
      }
      if (tradeToEdit.value.offered_items) {
        tradeToEdit.value.offered_items = tradeToEdit.value.offered_items.map(item => {
          let images = [];
          if (item.images) {
            if (typeof item.images === 'string') {
              try {
                images = JSON.parse(item.images);
                if (!Array.isArray(images)) {
                  images = [images];
                }
              } catch (e) {
                images = [item.images];
              }
            } else if (Array.isArray(item.images)) {
              images = item.images;
            }
          }
          images = images.map(img => getOptimizedImageUrl(img));
          return {
            ...item,
            current_images: images,
            images: []
          };
        });
      }
      if (!tradeToEdit.value.sellerProduct) {
        toast({
          title: 'Warning',
          description: 'Product information is missing for this trade',
          variant: 'warning'
        });
        return;
      }
      showEditTradeDialog.value = true;
    } else {
      throw new Error('Invalid response format');
    }
  } catch (error) {
    console.error('Error loading trade details:', error);
    toast({
      title: 'Error',
      description: 'Failed to load trade details for editing',
      variant: 'destructive'
    });
  }
};

const newMessage = ref('');
const chatState = reactive({
  messages: [],
  isLoading: false,
  error: null
});
const isSendingMessage = ref(false);
const messagesContainer = ref(null);

const formatMessageTime = (timestamp) => {
  if (!timestamp) return '';
  const date = new Date(timestamp);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  if (date.toDateString() === today.toDateString()) {
    return date.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  }
  if (date.toDateString() === yesterday.toDateString()) {
    return `Yesterday, ${date.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })}`;
  }
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

const scrollToBottom = () => {
  if (messagesContainer.value) {
    nextTick(() => {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    });
  }
};

const fetchMessages = async (tradeId) => {
  if (!tradeId) return;
  chatState.isLoading = true;
  chatState.error = null;
  try {
    // Simplified: Just one API call to get messages (which will create a welcome message if needed)
    const response = await axios.get(route('trades.messages.get', tradeId));
    if (response.data.success) {
      chatState.messages = response.data.data;
      nextTick(() => {
        scrollToBottom();
      });
    } else {
      chatState.error = response.data.message || 'Failed to load messages';
      toast({
        title: 'Error',
        description: chatState.error,
        variant: 'destructive'
      });
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
    chatState.error = 'Failed to load messages. Please try again later.';
    toast({
      title: 'Error',
      description: chatState.error,
      variant: 'destructive'
    });
  } finally {
    chatState.isLoading = false;
  }
};

const refreshMessages = () => {
  if (selectedTrade.value?.id) {
    fetchMessages(selectedTrade.value.id);
  }
};

const sendMessage = async () => {
  if (!selectedTrade.value?.id || !newMessage.value.trim()) return;
  isSendingMessage.value = true;
  try {
    const response = await axios.post(route('trades.message.send', selectedTrade.value.id), {
      message: newMessage.value.trim()
    });
    if (response.data.success) {
      chatState.messages.push(response.data.data);
      newMessage.value = '';
      scrollToBottom();
    } else {
      toast({
        title: 'Error',
        description: response.data.message || 'Failed to send message',
        variant: 'destructive'
      });
    }
  } catch (error) {
    console.error('Error sending message:', error);
    toast({
      title: 'Error',
      description: 'Failed to send message. Please try again later.',
      variant: 'destructive'
    });
  } finally {
    isSendingMessage.value = false;
  }
};

const promptCancelTrade = (tradeId) => {
  tradeToCancel.value = tradeId;
  showCancelAlert.value = true;
}

const confirmCancelTrade = () => {
  if (tradeToCancel.value) {
    showCancelAlert.value = false;
    showTradeDetails.value = false;
    router.patch(route('trades.cancel', tradeToCancel.value), {}, {
      onSuccess: (page) => {
        if (page.props.flash?.success) {
          toast({
            title: 'Success',
            description: page.props.flash.success,
            variant: 'default'
          });
        } else {
          toast({
            title: 'Success',
            description: 'Trade offer cancelled successfully',
            variant: 'default'
          });
        }
        selectedTrade.value = null;
        tradeToCancel.value = null;
      },
      onError: (errors) => {
        toast({
          title: 'Error',
          description: 'Failed to cancel trade offer',
          variant: 'destructive'
        });
        tradeToCancel.value = null;
      },
    });
  }
};

const hasDeleteEligibleTrades = computed(() => {
  return props.trades.data?.some(trade => isTradeEligibleForDelete(trade)) || false;
});

const getEligibleTradeIds = () => {
  return props.trades.data
    ?.filter(trade => isTradeEligibleForDelete(trade))
    ?.map(trade => trade.id) || [];
};

const confirmBulkDelete = () => {
  const tradeIds = getEligibleTradeIds();
  if (tradeIds.length > 0) {
    showBulkDeleteAlert.value = false;
    router.delete(route('trades.bulk-delete'), {
      data: { trade_ids: tradeIds },
      onSuccess: (page) => {
        toast({
          title: 'Success',
          description: page.props.flash?.success || 'Trades deleted successfully',
          variant: 'default'
        });
      },
      onError: (errors) => {
        toast({
          title: 'Error',
          description: errors.message || 'Failed to delete trades',
          variant: 'destructive'
        });
      },
    });
  }
};

const closeEditTradeModal = () => {
  tradeToEdit.value = null;
  showEditTradeDialog.value = false;
  if (selectedTrade.value && showTradeDetails.value) {
    fetchMessages(selectedTrade.value.id);
  }
};

const isTradeEligibleForDelete = (trade) => {
  return ['completed', 'canceled', 'rejected'].includes(trade.status);
};

const showReviewsDialog = ref(false);
const openReviewDialog = () => {
  showReviewsDialog.value = true;
};

const handleReviewSubmitted = () => {
  toast({
    title: 'Review Submitted',
    description: 'Thank you for your feedback!',
    variant: 'default'
  });
  setTimeout(() => {
    showReviewsDialog.value = false;
  }, 1500);
};

const openReviewDialogForTrade = (trade) => {
  if (!selectedTrade.value || selectedTrade.value.id !== trade.id) {
    selectedTrade.value = trade;
  }
  showReviewsDialog.value = true;
};

const getStatusVariant = (status) => {
  const variants = {
    'pending': 'warning',
    'accepted': 'success',
    'rejected': 'destructive',
    'canceled': 'secondary',
    'completed': 'default',
  };
  return variants[status?.toLowerCase()] || 'default';
};

const formatDate = (date) => {
  if (!date) return 'Select date';
  return format(new Date(date), 'MMMM d, yyyy');
};

const addOfferedItem = () => {
  editForm.offered_items.push({
    name: '',
    quantity: 1,
    estimated_value: 0,
    description: '',
    images: [],
    current_images: []
  });
};

const removeOfferedItem = (index) => {
  editForm.offered_items.splice(index, 1);
};

const formatSelectedTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const date = new Date();
  date.setHours(parseInt(hours), parseInt(minutes));
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
};

const getSelectedLocationName = () => {
  const location = availableMeetupLocations.value.find(
    loc => loc.id === editForm.meetup_location_id
  );
  return location?.full_name || '';
};

const availableDays = computed(() => {
  const days = new Set();
  availableMeetupLocations.value?.forEach(location => {
    location.available_days?.forEach(day => days.add(day));
  });
  return Array.from(days).sort((a, b) => {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    return days.indexOf(a) - days.indexOf(b);
  });
});

const getLocationsForDay = (day) => {
  return availableMeetupLocations.value?.filter(location => 
    location.available_days?.includes(day)
  ) || [];
};

const availableSchedules = computed(() => {
  const schedules = [];
  if (availableMeetupLocations.value) {
    availableMeetupLocations.value.forEach(location => {
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

const loadMeetupLocations = async (productId) => {
  try {
    const response = await axios.get(`/trades/product/${productId}/meetup-locations`);
    if (response.data.success) {
      availableMeetupLocations.value = response.data.meetup_locations;
    }
  } catch (error) {
    console.error('Error loading meetup locations:', error);
    toast({
      title: 'Error',
      description: 'Failed to load meetup locations',
      variant: 'destructive'
    });
  }
};

const selectSchedule = (schedule) => {
  editForm.meetup_location_id = schedule.id.split('_')[0];
  editForm.meetup_date = schedule.day;
  editForm.meetup_time = `${schedule.timeFrom} - ${schedule.timeUntil}`;
}

const getSelectedLocationSchedule = computed(() => {
  if (!tradeSchedule.meetup_location_id) return null;
  return availableMeetupLocations.value.find(
    location => location.id === tradeSchedule.meetup_location_id
  );
});

const selectedDay = computed(() => {
  const location = availableMeetupLocations.value?.find(
    loc => loc.id === tradeSchedule.meetup_location_id
  );
  return location?.available_days || [];
});

const formatAvailableDays = (days) => {
  if (!days || !days.length) return '';
  if (days.length === 1) return days[0];
  const lastDay = days[days.length - 1];
  const otherDays = days.slice(0, -1);
  return `${otherDays.join(', ')} and ${lastDay}`;
};

watch(() => tradeSchedule.meetup_location_id, (newVal) => {
  if (newVal) {
    selectedDate.value = null;
    editForm.meetup_date = null;
  }
});

watch(() => selectedDate.value, (newDate) => {
  if (!newDate) {
    editForm.meetup_date = '';
    return;
  }
  try {
    const dateObj = newDate instanceof Date ? newDate : new Date(newDate);
    if (isNaN(dateObj.getTime())) throw new Error('Invalid date');
    editForm.meetup_date = format(dateObj, 'yyyy-MM-dd');
  } catch (error) {
    console.error('Date conversion error:', error);
    editForm.meetup_date = '';
  }
});

watch(() => selectedDay.value, (newDay) => {
  if (newDay !== undefined) {
    selectedDate.value = null;
    editForm.meetup_date = null;
  }
});

const handleMeetupSelection = (location, day) => {
  selectedDate.value = null; 
  editForm.meetup_date = null;

  try {
    tradeSchedule.meetup_location_id = location.id;
    tradeSchedule.selectedDay = day;
    tradeSchedule.meetingSelection = `${location.id}_${day}`;
  } catch (error) {
    console.error('Error in handleMeetupSelection:', error);
    toast({
      title: "Error",
      description: "Failed to set meetup schedule",
      variant: "destructive"
    });
  }
};

const handleDateSelection = (date) => {
  if (!tradeSchedule.meetup_location_id || !date) {
    editForm.meetup_date = null;
    return;
  }

  try {
    const selectedDate = new Date(date);
    if (isNaN(selectedDate.getTime())) {
      throw new Error('Invalid date');
    }
    const selectedDay = format(selectedDate, 'EEEE').toLowerCase();
    const scheduleDay = tradeSchedule.selectedDay.toLowerCase();

    if (selectedDay === scheduleDay) {
      editForm.meetup_date = format(selectedDate, 'yyyy-MM-dd');
    } else {
      editForm.meetup_date = null;
      toast({
        title: "Invalid Day Selected",
        description: `Please select a ${tradeSchedule.selectedDay} for this meetup location.`,
        variant: "destructive"
      });
    }
  } catch (error) {
    console.error('Date selection error:', error);
    editForm.meetup_date = null;
    toast({
      title: "Error",
      description: "Invalid date selected",
      variant: "destructive"
    });
  }
};

watch(() => [tradeSchedule.meetup_location_id, tradeSchedule.selectedDay], () => {
  selectedDate.value = null;
  editForm.meetup_date = null;
}, { deep: true });

/**
 * Gets a clean image path for a product directly from storage
 * @param {Object} product - The product object
 * @returns {String} - Path to the image in storage folder
 */
const getProductImagePath = (product) => {
  if (!product) return '/images/placeholder-product.jpg';
  let images = product.images;
  
  // If images is null or undefined, return placeholder
  if (!images) return '/images/placeholder-product.jpg';
  
  // Handle case where images might be a JSON string
  if (typeof images === 'string') {
    try {
      images = JSON.parse(images);
    } catch (e) {
      // If parsing fails but it's a valid path, use it directly
      if (images) {
        return ensureStoragePath(images);
      }
      return '/images/placeholder-product.jpg';
    }
  }
  
  // If images is an array, use the first one
  if (Array.isArray(images) && images.length > 0) {
    return ensureStoragePath(images[0]);
  }
  
  return '/images/placeholder-product.jpg';
};

/**
 * Gets a clean image path for an offered item directly from storage
 * @param {Object} item - The offered item object
 * @returns {String} - Path to the image in storage folder
 */
const getOfferedItemImagePath = (item) => {
  if (!item || !item.images) return '/images/placeholder-product.jpg';
  let images = item.images;
  
  // Handle case where images might be a JSON string
  if (typeof images === 'string') {
    try {
      images = JSON.parse(images);
    } catch (e) {
      // If parsing fails but it's a valid path, use it directly
      if (images) {
        return ensureStoragePath(images);
      }
      return '/images/placeholder-product.jpg';
    }
  }
  
  // If images is an array, use the first one
  if (Array.isArray(images) && images.length > 0) {
    return ensureStoragePath(images[0]);
  }
  
  return '/images/placeholder-product.jpg';
};

/**
 * Process different image formats for the ImagePreview component
 * @param {Array|String} images - The images to process
 * @returns {Array} - Array of image URLs
 */
const processImagesForPreview = (images) => {
  // Always return an array for the ImagePreview component
  if (!images) return ['/images/placeholder-product.jpg'];
  if (Array.isArray(images)) {
    if (images.length === 0) return ['/images/placeholder-product.jpg'];
    return images.map(img => {
      if (typeof img === 'string') {
        // Process string URLs
        return ensureStoragePath(img);
      }
      return '/images/placeholder-product.jpg';
    });
  }
  // If images is a string, try to parse as JSON if it looks like JSON
  if (typeof images === 'string') {
    if (images.startsWith('[') || images.startsWith('{')) {
      try {
        const parsed = JSON.parse(images);
        if (Array.isArray(parsed)) {
          return parsed.map(img => ensureStoragePath(img));
        }
        return [ensureStoragePath(parsed)];
      } catch (e) {
        // Not valid JSON, treat as a single URL
        return [ensureStoragePath(images)];
      }
    }
    // Regular string path
    return [ensureStoragePath(images)];
  }
  
  // Fallback
  return ['/images/placeholder-product.jpg'];
};
</script>