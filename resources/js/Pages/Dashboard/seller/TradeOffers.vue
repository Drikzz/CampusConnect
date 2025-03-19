<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h2 class="text-2xl font-bold">Trade Offers</h2>
      </div>
      
      <!-- Filters and Search Bar - Combined in one consistent row -->
      <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search Input -->
        <div class="w-full sm:w-[280px]">
          <Input 
            type="text" 
            v-model="searchQuery"
            placeholder="Search trades..."
            @input="filterTradeOffers"
            class="w-full"
          />
        </div>
        
        <div class="flex flex-wrap gap-4">
          <div class="flex gap-2 items-center">
            <span class="text-sm font-medium">Status:</span>
            <Select v-model="statusFilter" @update:value="filterTradeOffers">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="accepted">Accepted</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="canceled">Canceled</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div class="flex gap-2 items-center">
            <span class="text-sm font-medium">Date:</span>
            <Select v-model="dateFilter" @update:value="filterTradeOffers">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Select period" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Time</SelectItem>
                <SelectItem value="today">Today</SelectItem>
                <SelectItem value="week">This Week</SelectItem>
                <SelectItem value="month">This Month</SelectItem>
                <SelectItem value="year">This Year</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <!-- Trade Offers Table -->
      <div v-if="paginatedTradeOffers.length > 0">
        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>ID</TableHead>
                <TableHead>Buyer</TableHead>
                <TableHead>Product</TableHead>
                <TableHead>Offered Items</TableHead>
                <TableHead>Created</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="trade in paginatedTradeOffers" :key="trade.id" class="hover:bg-muted/50">
                <TableCell>{{ trade.id }}</TableCell>
                <TableCell>{{ trade.buyer_name }}</TableCell>
                <TableCell>{{ trade.product_name }}</TableCell>
                <TableCell>{{ trade.offered_items_count }} items</TableCell>
                <TableCell>{{ formatDate(trade.created_at) }}</TableCell>
                <TableCell>
                  <span :class="getStatusClass(trade.status)">{{ trade.status }}</span>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-2">
                    <Button variant="outline" size="sm" @click="viewTradeDetails(trade)">
                      View
                    </Button>
                    <Button 
                      v-if="trade.status === 'pending'" 
                      variant="success" 
                      size="sm" 
                      @click="acceptTrade(trade.id)"
                    >
                      Accept
                    </Button>
                    <Button 
                      v-if="trade.status === 'pending'" 
                      variant="destructive" 
                      size="sm" 
                      @click="rejectTrade(trade.id)"
                    >
                      Reject
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="mt-4 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing {{ startItem }} to {{ endItem }} of {{ filteredTradeOffers.length }} entries
          </p>
          
          <div class="flex space-x-2">
            <Pagination>
              <PaginationFirst @click="goToPage(1)" :disabled="currentPage === 1" />
              <PaginationPrev @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" />
              
              <PaginationList v-if="totalPages <= 7">
                <PaginationListItem 
                  v-for="page in totalPages" 
                  :key="page" 
                  :value="page"
                  :active="currentPage === page"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </PaginationListItem>
              </PaginationList>
              
              <PaginationList v-else>
                <!-- First page -->
                <PaginationListItem :value="1" :active="currentPage === 1" @click="goToPage(1)">1</PaginationListItem>
                
                <!-- Ellipsis if needed -->
                <PaginationEllipsis v-if="currentPage > 3" />
                
                <!-- Pages around current page -->
                <template v-for="page in visiblePages" :key="page">
                  <PaginationListItem 
                    :value="page" 
                    :active="currentPage === page"
                    @click="goToPage(page)"
                  >
                    {{ page }}
                  </PaginationListItem>
                </template>
                
                <!-- Ellipsis if needed -->
                <PaginationEllipsis v-if="currentPage < totalPages - 2" />
                
                <!-- Last page -->
                <PaginationListItem 
                  :value="totalPages" 
                  :active="currentPage === totalPages"
                  @click="goToPage(totalPages)"
                >
                  {{ totalPages }}
                </PaginationListItem>
              </PaginationList>
              
              <PaginationNext @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" />
              <PaginationLast @click="goToPage(totalPages)" :disabled="currentPage === totalPages" />
            </Pagination>
          </div>
        </div>
      </div>
      <div v-else class="bg-gray-50 p-10 text-center rounded-lg">
        <p class="text-gray-500">No trade offers found matching your filters.</p>
      </div>

      <!-- Trade Details Dialog -->
      <Dialog :open="showTradeDetails" @update:open="closeTradeDetails">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
          <div v-if="selectedTrade" class="space-y-6">
            <div class="flex justify-between items-center">
              <h2 class="text-2xl font-semibold">Trade #{{ selectedTrade.id }}</h2>
              <span :class="getStatusClass(selectedTrade.status)">
                {{ selectedTrade.status }}
              </span>
            </div>

            <!-- Seller's Product -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Seller's Product</h3>
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex flex-col md:flex-row gap-4">
                  <!-- Product images -->
                  <div class="w-full md:w-1/3 space-y-2">
                    <div class="aspect-square rounded-md overflow-hidden bg-gray-100">
                      <img 
                        v-if="selectedTrade.seller_product?.images?.length" 
                        :src="selectedTrade.seller_product.images[0]" 
                        :alt="selectedTrade.seller_product?.name"
                        class="w-full h-full object-cover"
                        @error="handleImageError"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                        <span>No image</span>
                      </div>
                    </div>
                    
                    <!-- Additional images thumbnails -->
                    <div v-if="selectedTrade.seller_product?.images?.length > 1" 
                         class="grid grid-cols-4 gap-2">
                      <div 
                        v-for="(image, idx) in selectedTrade.seller_product.images.slice(1, 5)" 
                        :key="idx"
                        class="aspect-square rounded-md overflow-hidden bg-gray-100">
                        <img 
                          :src="image" 
                          :alt="`${selectedTrade.seller_product.name} image ${idx + 2}`"
                          class="w-full h-full object-cover"
                          @error="handleImageError"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Product details -->
                  <div class="w-full md:w-2/3">
                    <div class="flex justify-between">
                      <h4 class="font-semibold text-lg">{{ selectedTrade.seller_product?.name }}</h4>
                      <p class="text-primary font-semibold">{{ formatPrice(selectedTrade.seller_product?.price || 0) }}</p>
                    </div>
                    <p v-if="selectedTrade.seller_product?.description" class="mt-2 text-gray-700">
                      {{ selectedTrade.seller_product?.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Buyer's Offered Items -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Offered Items</h3>
              <div v-for="(item, index) in selectedTrade.offered_items" :key="index" class="bg-gray-50 p-4 rounded-lg">
                <div class="flex flex-col md:flex-row gap-4">
                  <!-- Item images -->
                  <div class="w-full md:w-1/3 space-y-2">
                    <div class="aspect-square rounded-md overflow-hidden bg-gray-100">
                      <img 
                        v-if="item.image_url" 
                        :src="item.image_url" 
                        :alt="item.name"
                        class="w-full h-full object-cover"
                        @error="handleImageError"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                        <span>No image</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Item details -->
                  <div class="w-full md:w-2/3">
                    <div class="flex justify-between">
                      <h4 class="font-semibold text-lg">{{ item.name }}</h4>
                      <p class="text-primary font-semibold">{{ formatPrice(item.estimated_value) }}</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Quantity: {{ item.quantity }}</p>
                    <p v-if="item.description" class="mt-2 text-gray-700">{{ item.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Cash -->
            <div v-if="selectedTrade.additional_cash > 0" class="bg-blue-50 p-4 rounded-lg">
              <div class="flex justify-between items-center">
                <h4 class="font-semibold">Additional Cash Offered</h4>
                <p class="text-primary font-semibold">{{ formatPrice(selectedTrade.additional_cash) }}</p>
              </div>
            </div>

            <!-- Total Value -->
            <div class="border-t pt-4 mt-4">
              <div class="flex justify-between items-center">
                <h4 class="font-semibold">Total Offer Value</h4>
                <p class="text-primary font-semibold">{{ formatPrice(calculateTotalValue(selectedTrade)) }}</p>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="selectedTrade.notes" class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold mb-2">Notes</h3>
              <p class="italic">{{ selectedTrade.notes }}</p>
            </div>

            <!-- Meetup Details -->
            <div v-if="selectedTrade.meetup_location || selectedTrade.meetup_schedule" class="space-y-4">
              <h3 class="text-lg font-semibold">Meetup Details</h3>
              <div class="bg-blue-50 p-4 rounded-lg">
                <!-- Meetup Location -->
                <div v-if="selectedTrade.meetup_location" class="flex flex-col gap-1 mb-3">
                  <h4 class="font-medium">Location:</h4>
                  <div class="flex items-start gap-2">
                    <div class="rounded-full p-1 bg-blue-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium">{{ selectedTrade.meetup_location.full_name }}</p>
                      <p class="text-xs text-gray-500">{{ selectedTrade.meetup_location.location?.name || 'No specific location' }}</p>
                      <p class="text-xs text-gray-500" v-if="selectedTrade.meetup_location.description">{{ selectedTrade.meetup_location.description }}</p>
                      <p class="text-xs text-gray-500 mt-1" v-if="selectedTrade.meetup_location.phone">Contact: {{ selectedTrade.meetup_location.phone }}</p>
                    </div>
                  </div>
                </div>

                <!-- Meetup Schedule -->
                <div v-if="selectedTrade.meetup_schedule || selectedTrade.formatted_meetup_date" class="flex flex-col gap-1">
                  <h4 class="font-medium">Schedule:</h4>
                  <div class="flex items-start gap-2">
                    <div class="rounded-full p-1 bg-blue-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium">{{ selectedTrade.formatted_meetup_date || formatDate(selectedTrade.meetup_schedule, false) }}</p>
                      <p class="text-xs text-gray-500">Scheduled meetup date</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Chat Messages Section -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Chat</h3>
              <div class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 p-3 border-b flex justify-between items-center">
                  <h4 class="font-semibold">Messages</h4>
                  <Button 
                    variant="outline" 
                    size="sm" 
                    @click="refreshMessages"
                    class="h-8 px-2 text-xs"
                  >
                    Refresh
                  </Button>
                </div>
                
                <!-- Messages container with auto-scroll -->
                <div 
                  ref="messagesContainer" 
                  class="p-4 h-64 overflow-y-auto space-y-4 bg-gray-50"
                >
                  <div v-if="isLoadingMessages" class="flex justify-center items-center h-full">
                    <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin"></div>
                  </div>
                  
                  <div v-else-if="messages.length === 0" class="flex justify-center items-center h-full">
                    <p class="text-gray-500">No messages yet. Start the conversation!</p>
                  </div>
                  
                  <template v-else>
                    <div 
                      v-for="message in messages" 
                      :key="message.id" 
                      class="flex gap-3"
                      :class="message.user.id === user.id ? 'justify-end' : ''"
                    >
                      <!-- Message from other user (buyer) -->
                      <template v-if="message.user.id !== user.id">
                        <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                          <img 
                            v-if="message.user.profile_picture" 
                            :src="message.user.profile_picture" 
                            :alt="message.user.name" 
                            class="w-full h-full object-cover"
                            @error="handleImageError"
                          />
                        </div>
                        <div class="flex-1 max-w-[75%]">
                          <div class="flex justify-between items-baseline">
                            <span class="font-medium text-sm">{{ message.user.name }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ formatMessageTime(message.created_at) }}</span>
                          </div>
                          <div class="bg-white rounded-lg p-3 mt-1 shadow-sm border">
                            <p class="text-sm whitespace-pre-wrap">{{ message.message }}</p>
                          </div>
                        </div>
                      </template>
                      
                      <!-- Message from current user (seller) -->
                      <template v-else>
                        <div class="flex-1 max-w-[75%]">
                          <div class="flex justify-end items-baseline">
                            <span class="text-xs text-gray-500 mr-2">{{ formatMessageTime(message.created_at) }}</span>
                            <span class="font-medium text-sm">You</span>
                          </div>
                          <div class="bg-green-50 rounded-lg p-3 mt-1 shadow-sm border-green-100 border text-right">
                            <p class="text-sm whitespace-pre-wrap">{{ message.message }}</p>
                          </div>
                        </div>
                      </template>
                    </div>
                  </template>
                </div>
                
                <!-- Message input -->
                <div class="p-3 border-t bg-white">
                  <form @submit.prevent="sendMessage" class="flex gap-2">
                    <Textarea
                      v-model="newMessage"
                      placeholder="Type your message here..."
                      class="flex-1 min-h-[60px] max-h-[120px] resize-none"
                      :disabled="isSendingMessage"
                      @keydown.enter.exact.prevent="sendMessage"
                    />
                    <Button 
                      type="submit" 
                      class="self-end"
                      :disabled="isSendingMessage || !newMessage.trim()"
                    >
                      <span v-if="isSendingMessage" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending
                      </span>
                      <span v-else>Send</span>
                    </Button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="selectedTrade.status === 'pending'" class="flex justify-end space-x-4">
              <Button variant="outline" @click="closeTradeDetails">Close</Button>
              <Button variant="destructive" @click="rejectTrade(selectedTrade.id)">Reject Offer</Button>
              <Button variant="success" @click="acceptTrade(selectedTrade.id)">Accept Offer</Button>
            </div>
            <div v-else-if="selectedTrade.status === 'accepted'" class="flex justify-end space-x-4">
              <Button variant="outline" @click="closeTradeDetails">Close</Button>
              <Button variant="success" @click="completeTrade(selectedTrade.id)">Mark as Completed</Button>
            </div>
            <div v-else class="flex justify-end">
              <Button variant="outline" @click="closeTradeDetails">Close</Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Confirmation Dialogs -->
      <AlertDialog :open="showAcceptDialog" @update:open="showAcceptDialog = false">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Accept Trade Offer</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to accept this trade offer? This will initiate the trade process.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmAcceptTrade">Accept</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <AlertDialog :open="showRejectDialog" @update:open="showRejectDialog = false">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Reject Trade Offer</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to reject this trade offer? This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmRejectTrade">Reject</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <AlertDialog :open="showCompleteDialog" @update:open="showCompleteDialog = false">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Complete Trade</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to mark this trade as completed? This indicates that the transaction has been successfully finalized.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmCompleteTrade">Complete</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '../../Dashboard/DashboardLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/Components/ui/select'
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/Components/ui/table'
import { Dialog, DialogContent } from '@/Components/ui/dialog'
import { 
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle
} from '@/Components/ui/alert-dialog'
import { useToast } from '@/Components/ui/toast/use-toast'
// Import pagination components
import { 
  Pagination, 
  PaginationListItem, 
  PaginationList 
} from '@/Components/ui/pagination'
import {
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationNext,
  PaginationPrev
} from '@/Components/ui/pagination'
import { Textarea } from '@/Components/ui/textarea'
import axios from 'axios'
import { nextTick } from 'vue'

const props = defineProps({
  user: Object,
  stats: Object,
  tradeOffers: {
    type: Array,
    default: () => []
  }
})

const { toast } = useToast()
const showTradeDetails = ref(false)
const selectedTrade = ref(null)
const tradeIdToAccept = ref(null)
const tradeIdToReject = ref(null)
const tradeIdToComplete = ref(null)
const showAcceptDialog = ref(false)
const showRejectDialog = ref(false)
const showCompleteDialog = ref(false)

// Filter and search state
const searchQuery = ref('')
const statusFilter = ref('all')
const dateFilter = ref('all')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Computed property for filtered trade offers
const filteredTradeOffers = computed(() => {
  let filtered = [...props.tradeOffers]
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(trade => 
      trade.buyer_name.toLowerCase().includes(query) || 
      trade.product_name.toLowerCase().includes(query) ||
      String(trade.id).includes(query)
    )
  }
  
  // Filter by status
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(trade => trade.status.toLowerCase() === statusFilter.value)
  }
  
  // Filter by date
  if (dateFilter.value !== 'all') {
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    
    filtered = filtered.filter(trade => {
      const tradeDate = new Date(trade.created_at)
      
      if (dateFilter.value === 'today') {
        return tradeDate >= today
      } 
      else if (dateFilter.value === 'week') {
        const weekStart = new Date(now)
        weekStart.setDate(now.getDate() - now.getDay()) // Start of week (Sunday)
        return tradeDate >= weekStart
      }
      else if (dateFilter.value === 'month') {
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1)
        return tradeDate >= monthStart
      }
      else if (dateFilter.value === 'year') {
        const yearStart = new Date(now.getFullYear(), 0, 1)
        return tradeDate >= yearStart
      }
      
      return true
    })
  }
  
  return filtered
})

// Calculate total pages
const totalPages = computed(() => {
  return Math.ceil(filteredTradeOffers.value.length / itemsPerPage.value)
})

// Get paginated trade offers
const paginatedTradeOffers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredTradeOffers.value.slice(start, end)
})

// Calculate visible pages for pagination
const visiblePages = computed(() => {
  let start = Math.max(2, currentPage.value - 1)
  let end = Math.min(totalPages.value - 1, currentPage.value + 1)
  
  // Adjust to show 3 pages minimum
  if (end - start < 2) {
    if (start === 2) {
      end = Math.min(totalPages.value - 1, start + 2)
    } else {
      start = Math.max(2, end - 2)
    }
  }
  
  const pages = []
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

// Calculate start and end items for display
const startItem = computed(() => {
  if (filteredTradeOffers.value.length === 0) return 0
  return (currentPage.value - 1) * itemsPerPage.value + 1
})

const endItem = computed(() => {
  return Math.min(currentPage.value * itemsPerPage.value, filteredTradeOffers.value.length)
})

// Filter function
const filterTradeOffers = () => {  
  // Reset to first page when filters change
  currentPage.value = 1
}
 
// Navigation function  
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const formatDate = (date, includeTime = false) => {
  if (!date) return '';
  
  const options = {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  };
  
  if (includeTime) {
    options.hour = '2-digit';
    options.minute = '2-digit';
  }
  
  return new Date(date).toLocaleDateString('en-US', options);
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-PH', { 
    style: 'currency', 
    currency: 'PHP' 
  }).format(price)
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-semibold',
    'accepted': 'bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold',
    'rejected': 'bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold',
    'completed': 'bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold',
    'canceled': 'bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold'
  }
  return classes[status.toLowerCase()] || classes['pending']
}

const calculateTotalValue = (trade) => {
  // Calculate total value of offered items
  const itemsTotal = trade.offered_items ? trade.offered_items.reduce((sum, item) => {
    return sum + (item.estimated_value * item.quantity);
  }, 0) : 0;

  // Add additional cash
  return itemsTotal + (trade.additional_cash || 0);
}

const viewTradeDetails = (trade) => {
  // Set basic trade info first
  selectedTrade.value = trade
  showTradeDetails.value = true
  
  // Reset messages when viewing a different trade
  messages.value = [];
  
  // Fetch full trade details
  router.get(route('seller.trades.details', trade.id), {}, {
    preserveState: true,
    preserveScroll: true,
    only: ['trade'],
    onSuccess: (page) => {
      if (page.props.trade) {
        selectedTrade.value = page.props.trade
        
        // Fetch messages after trade details are loaded
        fetchMessages(selectedTrade.value.id);
      } else {
        toast({
          title: 'Error',
          description: 'Failed to load complete trade details',
          variant: 'destructive'
        })
      }
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to load trade details',
        variant: 'destructive'
      })
    }
  })
}

// Modify closeTradeDetails to reset chat state
const closeTradeDetails = () => {
  showTradeDetails.value = false
  selectedTrade.value = null
  messages.value = []
  newMessage.value = ''
}

const acceptTrade = (tradeId) => {
  tradeIdToAccept.value = tradeId
  showAcceptDialog.value = true
}

const rejectTrade = (tradeId) => {
  tradeIdToReject.value = tradeId
  showRejectDialog.value = true
}

const confirmAcceptTrade = () => {
  if (tradeIdToAccept.value) {
    // Show loading toast immediately
    const loadingToast = toast({
      title: 'Processing',
      description: 'Accepting trade offer...',
      variant: 'default'
    })
    
    router.post(route('seller.trades.accept', tradeIdToAccept.value), {}, {
      onSuccess: () => {
        // Dismiss the loading toast
        loadingToast.dismiss()
        
        toast({
          title: 'Success',
          description: 'Trade offer accepted successfully',
          variant: 'default'
        })
        showAcceptDialog.value = false
        closeTradeDetails()
      },
      onError: () => {
        // Dismiss the loading toast
        loadingToast.dismiss()
        
        toast({
          title: 'Error',
          description: 'Failed to accept trade offer',
          variant: 'destructive'
        })
        showAcceptDialog.value = false
      }
    })
  }
}

const confirmRejectTrade = () => {
  if (tradeIdToReject.value) {
    // Show loading toast immediately
    const loadingToast = toast({
      title: 'Processing',
      description: 'Rejecting trade offer...',
      variant: 'default'
    })
    
    router.post(route('seller.trades.reject', tradeIdToReject.value), {}, {
      onSuccess: () => {
        // Dismiss the loading toast
        loadingToast.dismiss()
        
        toast({
          title: 'Success',
          description: 'Trade offer rejected successfully',
          variant: 'default'
        })
        showRejectDialog.value = false
        closeTradeDetails()
      },
      onError: () => {
        // Dismiss the loading toast
        loadingToast.dismiss()
        
        toast({
          title: 'Error',
          description: 'Failed to reject trade offer',
          variant: 'destructive'
        })
        showRejectDialog.value = false
      }
    })
  }
}

const completeTrade = (tradeId) => {
  tradeIdToComplete.value = tradeId
  showCompleteDialog.value = true
}

const confirmCompleteTrade = () => {
  if (tradeIdToComplete.value) {
    // Show loading toast immediately
    const loadingToast = toast({
      title: 'Processing',
      description: 'Marking trade as completed...',
      variant: 'default'
    })
    
    router.post(route('seller.trades.complete', tradeIdToComplete.value), {}, {
      onSuccess: () => {
        // Dismiss the loading toast
        loadingToast.dismiss()
        
        toast({
          title: 'Success',
          description: 'Trade marked as completed successfully',
          variant: 'default'
        })
        showCompleteDialog.value = false
        closeTradeDetails()
      },
      onError: () => {
        // Dismiss the loading toast
        loadingToast.dismiss()
        
        toast({
          title: 'Error',
          description: 'Failed to mark trade as completed',
          variant: 'destructive'
        })
        showCompleteDialog.value = false
      }
    })
  }
}

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg'
}

// Add new state variables for chat
const newMessage = ref('')
const messages = ref([])
const isLoadingMessages = ref(false)
const isSendingMessage = ref(false)
const messagesContainer = ref(null)

// Function to format message time
const formatMessageTime = (timestamp) => {
  if (!timestamp) return '';
  
  const date = new Date(timestamp);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  // Check if the message is from today
  if (date.toDateString() === today.toDateString()) {
    return date.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit'
    });
  }
  
  // Check if the message is from yesterday
  if (date.toDateString() === yesterday.toDateString()) {
    return `Yesterday, ${date.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })}`;
  }
  
  // Otherwise show the full date
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Function to auto-scroll to the bottom of the messages container
const scrollToBottom = () => {
  if (messagesContainer.value) {
    nextTick(() => {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    });
  }
};

// Function to fetch messages for the selected trade
const fetchMessages = async (tradeId) => {
  if (!tradeId) return;
  
  isLoadingMessages.value = true;
  
  try {
    const response = await axios.get(route('trades.messages.get', tradeId));
    
    if (response.data.success) {
      messages.value = response.data.data;
      scrollToBottom();
    } else {
      toast({
        title: 'Error',
        description: response.data.message || 'Failed to load messages',
        variant: 'destructive'
      });
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
    toast({
      title: 'Error',
      description: 'Failed to load messages. Please try again later.',
      variant: 'destructive'
    });
  } finally {
    isLoadingMessages.value = false;
  }
};

// Function to refresh messages
const refreshMessages = () => {
  if (selectedTrade.value?.id) {
    fetchMessages(selectedTrade.value.id);
  }
};

// Function to send a new message
const sendMessage = async () => {
  if (!selectedTrade.value?.id || !newMessage.value.trim()) return;
  
  isSendingMessage.value = true;
  
  try {
    const response = await axios.post(route('trades.message.send', selectedTrade.value.id), {
      message: newMessage.value.trim()
    });
    
    if (response.data.success) {
      // Add the new message to the messages array for immediate display
      messages.value.push(response.data.data);
      newMessage.value = ''; // Clear the input
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
</script>