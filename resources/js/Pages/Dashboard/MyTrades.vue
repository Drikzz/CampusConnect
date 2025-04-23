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
            <div class="space-y-4"> <!-- Changed from grid to vertical spacing -->
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
              <Card v-else v-for="trade in trades" :key="trade.id" class="flex flex-col mb-4 bg-card text-card-foreground">
                <CardHeader class="py-6">
                  <CardTitle class="flex justify-between mb-3">
                    <span class="text-lg">Trade #{{ trade.id }}</span>
                    <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusColor(trade.status)]">
                      {{ trade.status.charAt(0).toUpperCase() + trade.status.slice(1) }}
                    </span>
                  </CardTitle>
                  <CardDescription class="text-sm text-gray-500">
                    Offered on {{ formatDateTime(trade.created_at) }}
                  </CardDescription>
                </CardHeader>
                <CardContent class="py-4 space-y-4 flex-1 min-h-0"> <!-- Changed py-6 to py-4 and space-y-6 to space-y-4 -->
                  <div class="grid grid-cols-2 gap-6"> <!-- Keep this internal grid -->
                    <!-- Left Column -->
                    <div class="space-y-4"> <!-- Changed from space-y-6 -->
                      <!-- Product Being Traded For -->
                      <div>
                        <h4 class="font-medium text-base mb-3">Trading for:</h4>
                        <h5 class="text-sm text-muted-foreground mb-2">Items:</h5>
                        <div class="bg-gray-50 p-4 rounded-lg">
                          <div class="space-y-2">
                            <p class="font-medium">{{ trade.seller_product.name }}</p>
                            <p class="text-sm text-gray-500">Owner: {{ trade.seller ? `${trade.seller.first_name} ${trade.seller.last_name}` : 'Unknown Seller' }}</p>
                            <p class="font-semibold text-primary-color">Value: {{ formatPrice(trade.seller_product.price) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="space-y-4"> <!-- Changed from space-y-6 -->
                      <h4 class="font-medium text-base mb-3">Your Offer:</h4>
                      <!-- Offered Items -->
                      <div v-if="trade.offered_items && trade.offered_items.length">
                        <h5 class="text-sm text-muted-foreground mb-2">Items:</h5>
                        <div class="space-y-3">
                          <div v-for="item in trade.offered_items" :key="item.id" 
                               class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-2">
                              <h5 class="font-medium">{{ item.name }}</h5>
                              <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                              <p class="font-semibold text-primary-color">{{ formatPrice(item.estimated_value) }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Additional Cash -->
                      <div v-if="trade.additional_cash > 0">
                        <h5 class="text-sm text-muted-foreground mb-2">Additional Cash:</h5>
                        <div class="bg-muted p-4 rounded-lg">
                          <p class="font-semibold text-primary">{{ formatPrice(trade.additional_cash) }}</p>
                        </div>
                      </div>

                      <!-- Notes if any -->
                      <div v-if="trade.notes">
                        <h5 class="text-sm text-muted-foreground mb-2">Notes:</h5>
                        <div class="bg-gray-50 p-4 rounded-lg">
                          <p class="text-sm text-gray-600 leading-relaxed">{{ trade.notes }}</p>
                        </div>
                      </div>
                      
                    </div>
                  </div> <!-- End of grid -->

                  <!-- Meetup Schedule - Now full width -->
                  <div v-if="trade.meetup_schedule" class="mt-4 border-t pt-4">
                    <div class="flex items-start gap-3">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <div class="space-y-1">
                        <p class="font-medium text-base">Meetup Scheduled</p>
                        <p class="text-sm text-gray-600">
                          {{ formatDateTime(trade.meetup_schedule, false) }}
                          <span v-if="trade.meetup_location_name"> at {{ trade.meetup_location_name }}</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="py-6 flex justify-between items-center border-t">
                  <div class="font-semibold text-base">
                    Total Value: {{ formatPrice(calculateTradeValue(trade)) }}
                  </div>
                  <div class="space-x-3 flex items-center"> <!-- Added flex and items-center -->
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
                    <Button v-if="trade.status === 'pending'" variant="destructive" @click="promptCancelTrade(trade.id)">
                      Cancel
                    </Button>
                    <Button v-if="isTradeEligibleForDelete(trade)" variant="outline" class="text-red-500" @click="promptDeleteTrade(trade.id)">
                      Delete
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
            <span class="text-2xl font-semibold">Trade #{{ selectedTrade?.id }}</span>
            <Badge :variant="getStatusVariant(selectedTrade?.status)">
              {{ selectedTrade?.status?.charAt(0).toUpperCase() + selectedTrade?.status?.slice(1) }}
            </Badge>
          </DialogTitle>
          <DialogDescription>
            Created on {{ formatDateTime(selectedTrade?.created_at, true) }}
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedTrade" class="space-y-6 py-4">
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
                    <!-- Product Image - Updated for better handling -->
                    <div class="w-24 aspect-square">
                      <img 
                        v-if="selectedTrade.sellerProduct && selectedTrade.sellerProduct.images && selectedTrade.sellerProduct.images.length"
                        :src="getOptimizedImageUrl(selectedTrade.sellerProduct.images[0])"
                        :alt="selectedTrade.sellerProduct ? selectedTrade.sellerProduct.name : 'Product'"
                        class="w-full h-full object-cover backface-hidden transform-gpu antialiased"
                        style="image-rendering: -webkit-optimize-contrast"
                        @error="handleImageError"
                      />
                      <div v-else class="flex items-center justify-center h-full bg-muted rounded-md">
                        <ImageIcon class="h-8 w-8 text-muted-foreground" />
                      </div>
                    </div>

                    <!-- Product Details -->
                    <div class="flex-1">
                      <div class="flex justify-between">
                        <h5 class="font-semibold">{{ selectedTrade.sellerProduct ? selectedTrade.sellerProduct.name : 'Product' }}</h5>
                        <p class="font-semibold text-primary">
                          {{ formatPrice(selectedTrade.sellerProduct ? selectedTrade.sellerProduct.price : 0) }}
                        </p>
                      </div>
                      <div class="flex items-center gap-1 text-sm text-muted-foreground mt-1">
                        <UserIcon class="h-4 w-4" />
                        <span>Seller: {{ selectedTrade.seller?.first_name }} {{ selectedTrade.seller?.last_name }}</span>
                      </div>
                      <p v-if="selectedTrade.sellerProduct && selectedTrade.sellerProduct.description" class="text-sm text-muted-foreground mt-2">
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
                    <!-- Item Image -->
                    <div class="w-24 aspect-square">
                      <img 
                        v-if="item.images && item.images.length"
                        :src="getOptimizedImageUrl(item.images[0])"
                        :alt="item.name"
                        class="w-full h-full object-cover backface-hidden transform-gpu antialiased"
                        style="image-rendering: -webkit-optimize-contrast"
                        @error="handleImageError"
                      />
                      <div v-else class="flex items-center justify-center h-full bg-muted rounded-md">
                        <ImageIcon class="h-8 w-8 text-muted-foreground" />
                      </div>
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
                  <p class="font-medium">
                    {{ selectedTrade.meetup_schedule ? formatDateTime(selectedTrade.meetup_schedule, false) : 'Date not set' }}
                  </p>
                  <p class="text-sm text-muted-foreground">
                    {{ selectedTrade.meetup_location_name || 'Location not specified' }}
                  </p>
                  <p v-if="selectedTrade.preferred_time" class="text-sm text-muted-foreground">
                    Time: {{ selectedTrade.preferred_time_formatted || formatTime(selectedTrade.preferred_time) }}
                  </p>
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
              v-if="selectedTrade?.status === 'pending'"
              variant="destructive"
              @click="promptCancelTrade(selectedTrade.id)"
            >
              Cancel Trade
            </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Cancel Trade Alert Dialog -->
    <AlertDialog :open="showCancelAlert" @update:open="showCancelAlert = false">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Cancel Trade Offer</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to cancel this trade offer? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showCancelAlert = false">No, Keep it</AlertDialogCancel>
          <AlertDialogAction @click="confirmCancelTrade">Yes, Cancel Trade</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Delete Trade Alert Dialog -->
    <AlertDialog :open="showDeleteAlert" @update:open="showDeleteAlert = false">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Trade</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this trade? It will be removed from your view but still stored in the database.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showDeleteAlert = false">No, Keep it</AlertDialogCancel>
          <AlertDialogAction @click="confirmDeleteTrade">Yes, Delete Trade</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Bulk Delete Alert Dialog -->
    <AlertDialog :open="showBulkDeleteAlert" @update:open="showBulkDeleteAlert = false">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Bulk Delete Trades</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete all completed, cancelled, and rejected trades? 
            This will remove them from your view but they will still be stored in the database.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showBulkDeleteAlert = false">No, Keep them</AlertDialogCancel>
          <AlertDialogAction @click="confirmBulkDelete">Yes, Delete All</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Replace the Edit Trade Dialog with TradeForm -->
    <TradeForm 
      v-if="showEditTradeDialog && tradeToEdit && tradeToEdit.sellerProduct"
      :product="tradeToEdit.sellerProduct"
      :open="showEditTradeDialog === true"
      :existing-trade="tradeToEdit"
      :edit-mode="true"
      @close="closeEditTradeModal"
      @update:open="closeEditTradeModal"
    />

    <!-- Fix the image preview dialog -->
    <Dialog :open="!!previewImageUrl" @update:open="previewImageUrl = null" class="image-preview-dialog">
      <DialogContent class="max-w-5xl max-h-[90vh] p-2 bg-white/98 backdrop-blur rounded-lg">
        <div class="relative">
          <Button 
            class="absolute top-2 right-2 rounded-full bg-black/50 hover:bg-black/70"
            size="sm"
            @click="previewImageUrl = null"
          >
            <X class="h-4 w-4" />
          </Button>
          <div class="flex items-center justify-center bg-white rounded-lg overflow-hidden p-2">
            <img 
              :src="previewImageUrl"
              class="max-h-[80vh] max-w-full object-contain backface-hidden transform-gpu antialiased" 
              style="image-rendering: -webkit-optimize-contrast"
              @error="handleImageError"
            />
          </div>
        </div>
      </DialogContent>
    </Dialog>

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
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick, reactive } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import DashboardLayout from './DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Toaster } from '@/Components/ui/toast';
import { 
  Card, 
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { 
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/Components/ui/tabs';
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription } from '@/Components/ui/dialog';
import { useToast } from '@/Components/ui/toast/use-toast';
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
import { 
  UserIcon, 
  ImageIcon, 
  StarIcon, 
  CalendarIcon, 
  X 
} from 'lucide-vue-next';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { Input } from "@/Components/ui/input";
import { format } from "date-fns";
// Import the SellerReviews component
import SellerReviews from '@/Components/SellerReviews.vue';
// Replace the TradeCalendar import with MeetupDate
import MeetupDate from '@/Components/ui/trade-calendar/meetup-date.vue';
// Add import for ScrollArea
import { ScrollArea } from '@/Components/ui/scroll-area';
// Add the TradeForm import
import TradeForm from '@/Components/TradeForm.vue';

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
  },
  flash: {
    type: Object,
    default: () => ({
      success: null,
      error: null
    })
  }
});

// Update the user reference to use auth.user
const user = computed(() => props.auth.user);

// Toast handling
const page = usePage();
const { toast } = useToast();

// Watch for flash messages from the server and show toasts
watch(() => page.props.flash, (flash) => {
  // console.log('Flash message detected:', flash); // Debug log
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

// Also watch for flash messages at the root level
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
const showDeleteAlert = ref(false);
const showBulkDeleteAlert = ref(false);
const tradeToCancel = ref(null);
const tradeToDelete = ref(null);
// Add these missing ref declarations
const tradeToEdit = ref(null);
const availableMeetupLocations = ref([]);
const showEditTradeDialog = ref(false);
const loading = ref(false);
// Add these refs for schedule selection
const selectedDate = ref(null);

// Simplified tradeSchedule reactive object
const tradeSchedule = reactive({
  meetup_location_id: '',
  selectedDay: '',
  meetingSelection: ''
});

const groupedTrades = computed(() => {
  // First, filter trades where the current user is the buyer
  const buyerTrades = (props.trades?.data || []).filter(trade => 
    trade.buyer_id === props.auth.user.id
  );

  // Then group the filtered trades by status
  const groups = {
    all: buyerTrades.filter(t => t.status !== 'canceled'),
    pending: buyerTrades.filter(t => t.status === 'pending'),
    accepted: buyerTrades.filter(t => t.status === 'accepted'),
    rejected: buyerTrades.filter(t => t.status === 'rejected'),
    completed: buyerTrades.filter(t => t.status === 'completed'),
    canceled: buyerTrades.filter(t => t.status === 'canceled'),
  };

  // Filter by search query if present
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    Object.keys(groups).forEach(key => {
      groups[key] = groups[key].filter(trade => 
        trade.id.toString().includes(query) || 
        (trade.seller_product && trade.seller_product.name.toLowerCase().includes(query)) ||
        (trade.offered_items && trade.offered_items.some(item => item.name.toLowerCase().includes(query)))
      );
    });
  }

  return groups;
});

const formatPrice = (price) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);

const formatDateTime = (date, includeTime = false) => {
  if (!date) return '';
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

// Update the status color function to use shadcn theme colors
const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-100',
    'accepted': 'bg-muted dark:bg-muted text-muted-foreground dark:text-muted-foreground',
    'rejected': 'bg-destructive/10 text-destructive dark:bg-destructive/20',
    'canceled': 'bg-secondary text-secondary-foreground',
    'completed': 'bg-primary/10 text-primary dark:bg-primary/20',
  };
  return colors[status] || 'bg-secondary text-secondary-foreground';
};
  
const calculateTradeValue = (trade) => {
  let totalValue = 0;
  // Calculate offered items value
  if (trade.offered_items && trade.offered_items.length > 0) {
    totalValue = trade.offered_items.reduce((sum, item) => {
      return sum + (parseFloat(item.estimated_value) * parseInt(item.quantity));
    }, 0);
  }
  // Add additional cash if present
  if (trade.additional_cash) {
    totalValue += parseFloat(trade.additional_cash);
  }
  return totalValue;
}

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

// Add new functions for image handling
const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg';
};

// Enhanced getOptimizedImageUrl to better handle different image formats
const getOptimizedImageUrl = (image, fallbackImage = '/images/placeholder-product.jpg') => {
  if (!image) {
    return fallbackImage;
  }
  
  // If the image is already a full URL
  if (typeof image === 'string' && (image.startsWith('http://') || image.startsWith('https://'))) {
    const baseUrl = image.split('?')[0];
    return `${baseUrl}?quality=100&t=${Date.now()}`;
  }
  
  // If the image is a path to storage
  if (typeof image === 'string') {
    // If the image path starts with 'storage/', add the leading slash
    if (image.startsWith('storage/')) {
      return '/' + image;
    }
    
    // If it already has a leading slash, return as is
    if (image.startsWith('/')) {
      return image;
    }
    
    // Otherwise assume it needs the storage prefix
    return `/storage/${image}`;
  }
  
  // Handle case where image might be an object with url property
  if (typeof image === 'object' && image !== null && image.url) {
    return getOptimizedImageUrl(image.url, fallbackImage);
  }
  
  return fallbackImage;
};

// Add state for image preview
const previewImageUrl = ref(null);

// Preview image function with proper high-resolution handling
const previewImage = (imageUrl) => {
  let url = getOptimizedImageUrl(imageUrl);
  previewImageUrl.value = url;
  // Preload the image for better display
  const img = new Image();
  img.onload = () => {
    if (img.complete) {
      const currentUrl = previewImageUrl.value;
      previewImageUrl.value = null;
      previewImageUrl.value = url;
      setTimeout(() => {
        previewImageUrl.value = currentUrl;
      }, 10);
    }
  };
  img.src = url;
}

// Format time in 12-hour format
const formatTime = (time) => {
  if (!time) return '';
  try {
    const [hours, minutes] = time.split(':');
    const hourNum = parseInt(hours);
    const suffix = hourNum >= 12 ? 'PM' : 'AM';
    const hour12 = hourNum % 12 || 12;
    return `${hour12}:${minutes} ${suffix}`;
  } catch (e) {
    console.error('Error formatting time:', e);
    return time;
  }
};

/**
 * Opens the trade details dialog and sets trade data
 */
const viewTradeDetails = async (trade) => {
  try {
    selectedTrade.value = trade;
    showTradeDetails.value = true;
    chatState.messages = [];
    chatState.error = null;

    // First fetch complete trade details
    const response = await axios.get(route('trades.details', trade.id));
    if (response.data && response.data.success && response.data.trade) {
      selectedTrade.value = response.data.trade;

      // Now always fetch complete product details to ensure we have images and seller info
      try {
        const productResponse = await axios.get(`/trade/products/${selectedTrade.value.seller_product_id}/details`);
        if (productResponse.data) {
          // Merge the product details into the sellerProduct property
          selectedTrade.value.sellerProduct = {
            ...selectedTrade.value.sellerProduct,
            ...productResponse.data,
            // Ensure images array is properly formatted for rendering
            images: Array.isArray(productResponse.data.images) 
              ? productResponse.data.images 
              : (typeof productResponse.data.images === 'string' 
                ? [productResponse.data.images] 
                : [])
          };

          // Store the seller information correctly
          if (productResponse.data.seller) {
            selectedTrade.value.seller = {
              ...selectedTrade.value.seller,
              ...productResponse.data.seller
            };
          }
          
          // console.log("Updated product with complete details:", selectedTrade.value.sellerProduct);
        }
      } catch (productError) {
        console.error("Failed to load additional product details:", productError);
      }

      // Format the preferred time in 12-hour format if it exists
      if (selectedTrade.value.preferred_time) {
        selectedTrade.value.preferred_time_formatted = formatTime(selectedTrade.value.preferred_time);
      }

      // Process offered items to ensure images are properly formatted
      if (selectedTrade.value.offered_items) {
        selectedTrade.value.offered_items = selectedTrade.value.offered_items.map(item => {
          // Ensure images is correctly formatted
          if (typeof item.images === 'string') {
            try {
              // Try to parse JSON string
              item.images = JSON.parse(item.images);
            } catch (e) {
              // If not valid JSON, treat as a single image path
              item.images = [item.images];
            }
          }
          
          // If still not an array, convert to array
          if (!Array.isArray(item.images)) {
            item.images = item.images ? [item.images] : [];
          }
          
          // Make sure each image is a properly formatted URL
          item.images = item.images.map(img => getOptimizedImageUrl(img));
          
          return item;
        });
      }
    }
    
    await fetchMessages(trade.id);
  } catch (error) {
    console.error('Error loading trade details:', error);
    toast({
      title: 'Error',
      description: 'Failed to load complete trade details',
      variant: 'destructive'
    });
  }
}

/**
 * Closes the trade details dialog and resets data
 */
const closeTradeDetails = () => {
  showTradeDetails.value = false;
  selectedTrade.value = null;
};

/**
 * Opens the edit trade dialog and prepares data
 */
const editTrade = async (trade) => {
  showTradeDetails.value = false;
  
  // Initialize with basic data we already have
  tradeToEdit.value = {
    ...trade,
    sellerProduct: trade.seller_product || null
  };
  
  // Load trade details with all related data
  try {
    const response = await axios.get(route('trades.details', trade.id));
    if (response.data && response.data.success && response.data.trade) {
      // Update with complete data from API
      tradeToEdit.value = {
        ...response.data.trade,
        sellerProduct: response.data.trade.sellerProduct || null
      };
      
      // Always fetch full product details to ensure we have complete data
      try {
        const productResponse = await axios.get(`/trade/products/${tradeToEdit.value.seller_product_id}/details`);
        if (productResponse.data) {
          tradeToEdit.value.sellerProduct = {
            ...tradeToEdit.value.sellerProduct,
            ...productResponse.data
          };

          // Store the seller information correctly
          if (productResponse.data.seller) {
            tradeToEdit.value.seller = {
              ...tradeToEdit.value.seller,
              ...productResponse.data.seller
            };
          }
        }
      } catch (productError) {
        console.error("Failed to load additional product details for editing:", productError);
        
        // Try alternative API endpoint if the first attempt failed
        try {
          const fallbackResponse = await axios.get(`/api/products/${tradeToEdit.value.seller_product_id}`);
          if (fallbackResponse.data) {
            tradeToEdit.value.sellerProduct = {
              ...tradeToEdit.value.sellerProduct,
              ...fallbackResponse.data
            };
            
            // Store the seller information correctly if available
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
      
      // Make sure offered items have the correct image format
      if (tradeToEdit.value.offered_items) {
        tradeToEdit.value.offered_items = tradeToEdit.value.offered_items.map(item => {
          // Process images properly
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

          // Map each image to a full URL
          images = images.map(img => getOptimizedImageUrl(img));

          return {
            ...item,
            // Store properly formatted images as current_images for editing
            current_images: images,
            // Initialize empty array for new images
            images: []
          };
        });
      }
      
      // console.log("Trade data loaded for editing:", tradeToEdit.value);
      
      if (!tradeToEdit.value.sellerProduct) {
        toast({
          title: 'Warning',
          description: 'Product information is missing for this trade',
          variant: 'warning'
        });
        return;
      }

      // Ensure it's a boolean, not the trade ID
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

// Add new state variables for chat
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

/**
 * Opens the cancel trade confirmation dialog
 */
const promptCancelTrade = (tradeId) => {
  tradeToCancel.value = tradeId;
  showCancelAlert.value = true;
}

/**
 * Confirms and actually cancels the trade after dialog confirmation
 */
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

// Check if there are any trades eligible for deletion
const hasDeleteEligibleTrades = computed(() => {
  return props.trades.data?.some(trade => isTradeEligibleForDelete(trade)) || false;
});

// Function to get all eligible trade IDs for bulk deletion
const getEligibleTradeIds = () => {
  return props.trades.data
    ?.filter(trade => isTradeEligibleForDelete(trade))
    ?.map(trade => trade.id) || [];
};

// Delete trade functions
const promptDeleteTrade = (tradeId) => {
  tradeToDelete.value = tradeId;
  showDeleteAlert.value = true;
};

const confirmDeleteTrade = () => {
  if (tradeToDelete.value) {
    showDeleteAlert.value = false;
    router.delete(route('trades.delete', tradeToDelete.value), {
      onSuccess: (page) => {
        toast({
          title: 'Success',
          description: page.props.flash?.success || 'Trade deleted successfully',
          variant: 'default'
        });
        tradeToDelete.value = null;
      },
      onError: (errors) => {
        toast({
          title: 'Error',
          description: errors.message || 'Failed to delete trade',
          variant: 'destructive'
        });
        tradeToDelete.value = null;
      },
    });
  }
};

// Bulk delete function
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

/**
 * Close edit trade dialog and reset form
 */
const closeEditTradeModal = () => {
  tradeToEdit.value = null;
  showEditTradeDialog.value = false;
  if (selectedTrade.value && showTradeDetails.value) {
    fetchMessages(selectedTrade.value.id);
  }
};

// Check if a trade is eligible for deletion (completed, cancelled, or rejected)
const isTradeEligibleForDelete = (trade) => {
  return ['completed', 'canceled', 'rejected'].includes(trade.status);
};

// Add state for reviews dialog
const showReviewsDialog = ref(false);

/**
 * Opens the reviews dialog
 */
const openReviewDialog = () => {
  showReviewsDialog.value = true;
};

/**
 * Handle review submission completion
 */
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

/**
 * Opens the reviews dialog directly from a trade card
 */
const openReviewDialogForTrade = (trade) => {
  if (!selectedTrade.value || selectedTrade.value.id !== trade.id) {
    selectedTrade.value = trade;
  }
  showReviewsDialog.value = true;
};

// Add this function in your script setup section, before the component props
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

// Add these helper functions after the other functions in the script setup section:
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

// Add computed property for available days
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

// Add method to filter locations by day
const getLocationsForDay = (day) => {
  return availableMeetupLocations.value?.filter(location => 
    location.available_days?.includes(day)
  ) || [];
};

// Add computed property for available schedules
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

// Add function to load meetup locations
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

// Add function to select a schedule
const selectSchedule = (schedule) => {
  editForm.meetup_location_id = schedule.id.split('_')[0];
  editForm.meetup_date = schedule.day;
  editForm.meetup_time = `${schedule.timeFrom} - ${schedule.timeUntil}`;
}

// Add this computed property after other computed properties
const getSelectedLocationSchedule = computed(() => {
  if (!tradeSchedule.meetup_location_id) return null;
  return availableMeetupLocations.value.find(
    location => location.id === tradeSchedule.meetup_location_id
  );
});

// Add this computed property to get selected day from meetup location
const selectedDay = computed(() => {
  const location = availableMeetupLocations.value?.find(
    loc => loc.id === tradeSchedule.meetup_location_id
  );
  
  // Return all available days for the selected location
  return location?.available_days || [];
});

// Add this helper function to format available days
const formatAvailableDays = (days) => {
  if (!days || !days.length) return '';
  if (days.length === 1) return days[0];
  const lastDay = days[days.length - 1];
  const otherDays = days.slice(0, -1);
  return `${otherDays.join(', ')} and ${lastDay}`;
};

// Fix the form reference error by using editForm instead
watch(() => tradeSchedule.meetup_location_id, (newVal) => {
  if (newVal) {
    // Reset the selected date when location changes
    selectedDate.value = null;
    editForm.meetup_date = null; // Changed from form.meetup_date to editForm.meetup_date
  }
});

// Fix the watch handlers for selected date
watch(
  () => selectedDate.value,
  (newDate) => {
    if (!newDate) {
      editForm.meetup_date = '';
      return;
    }
    
    try {
      // Convert any date input to a proper date object
      const dateObj = newDate instanceof Date ? newDate : new Date(newDate);
      if (isNaN(dateObj.getTime())) throw new Error('Invalid date');
      
      editForm.meetup_date = format(dateObj, 'yyyy-MM-dd');
      // console.log("Selected date:", editForm.meetup_date);
    } catch (error) {
      console.error('Date conversion error:', error);
      editForm.meetup_date = '';
    }
  }
);

// Add a watch for selectedDay to reset date when day changes
watch(
  () => selectedDay.value,
  (newDay) => {
    if (newDay !== undefined) {
      selectedDate.value = null;
      editForm.meetup_date = null;
    }
  }
);

// Update the handleMeetupSelection function
const handleMeetupSelection = (location, day) => {
    // Reset date selections
    selectedDate.value = null;
    editForm.meetup_date = null;

    // Update schedule info with proper casing and validation
    try {
        tradeSchedule.meetup_location_id = location.id;
        tradeSchedule.selectedDay = day;  // Keep original casing
        tradeSchedule.meetingSelection = `${location.id}_${day}`;

        console.debug('Selected meetup schedule:', {
            locationId: location.id,
            day: tradeSchedule.selectedDay,
            selection: tradeSchedule.meetingSelection
        });
    } catch (error) {
        console.error('Error in handleMeetupSelection:', error);
        toast({
            title: "Error",
            description: "Failed to set meetup schedule",
            variant: "destructive"
        });
    }
}

// Enhanced handleDateSelection function
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
            console.debug('Date selected:', editForm.meetup_date);
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

// Add watch to reset date when schedule changes
watch(() => [tradeSchedule.meetup_location_id, tradeSchedule.selectedDay], () => {
    selectedDate.value = null;
    editForm.meetup_date = null;
}, { deep: true });

// // Add debugging to check days being loaded from meetup locations
// watch(() => availableMeetupLocations.value, (locations) => {
//   if (locations && locations.length) {
//     console.log('Available days from meetup locations:', 
//       locations.flatMap(loc => loc.available_days || [])
//         .filter((value, index, self) => self.indexOf(value) === index)
//     );
//   }
// }, { immediate: true });

</script>