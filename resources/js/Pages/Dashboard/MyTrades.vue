<template>
  <DashboardLayout :user="user" :stats="stats" :flash="null">
    <!-- Add toast container for notifications -->
    <div class="fixed top-4 right-4 z-[100]">
      <Toaster />
    </div>

    <div class="space-y-6">
      <!-- Search Bar -->
      <div class="w-full">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search trades..."
          class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-primary"
        />
      </div>

      <!-- Bulk Actions for eligible trades -->
      <div class="flex justify-between items-center">
        <div>
          <Button 
            v-if="hasDeleteEligibleTrades" 
            variant="destructive" 
            size="sm"
            @click="showBulkDeleteAlert = true"
          >
            Delete Completed/Cancelled Trades
          </Button>
        </div>
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
            <div class="grid gap-4">
              <Card v-for="trade in trades" :key="trade.id" class="w-full">
                <CardHeader>
                  <CardTitle class="flex justify-between">
                    <span>Trade #{{ trade.id }}</span>
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusColor(trade.status)]">
                      {{ trade.status.charAt(0).toUpperCase() + trade.status.slice(1) }}
                    </span>
                  </CardTitle>
                  <CardDescription>
                    Offered on {{ formatDateTime(trade.created_at) }}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="space-y-4">
                    <!-- Product Being Traded For -->
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                      <div>
                        <h4 class="font-medium">Trading for: {{ trade.seller_product.name }}</h4>
                        <p class="text-sm text-gray-500">Owner: {{ trade.seller ? trade.seller.name : 'Unknown Seller' }}</p>
                      </div>
                      <div class="text-right">
                        <p class="font-semibold">Value: {{ formatPrice(trade.seller_product.price) }}</p>
                      </div>
                    </div>
                    
                    <!-- Offered Items -->
                    <div v-if="trade.offered_items && trade.offered_items.length" class="mt-2">
                      <h4 class="font-medium mb-2">Your Offered Items:</h4>
                      <div v-for="item in trade.offered_items" :key="item.id" class="flex justify-between items-center py-2 border-b last:border-0">
                        <div>
                          <h5 class="font-medium">{{ item.name }}</h5>
                          <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                        </div>
                        <div class="text-right">
                          <p class="font-semibold">{{ formatPrice(item.estimated_value) }}</p>
                        </div>
                      </div>
                      
                      <!-- Additional Cash -->
                      <div v-if="trade.additional_cash > 0" class="flex justify-between items-center mt-2 pt-2 border-t">
                        <p class="font-medium">Additional Cash</p>
                        <p class="font-semibold">{{ formatPrice(trade.additional_cash) }}</p>
                      </div>
                    </div>
                    
                    <!-- Meetup Schedule - Updated to show date only -->
                    <div v-if="trade.meetup_schedule" class="mt-2 pt-2 border-t">
                      <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                          <p class="font-medium">Meetup Scheduled</p>
                          <p class="text-sm text-gray-600">{{ trade.formatted_meetup_date || formatDateTime(trade.meetup_schedule, false) }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Notes -->
                    <div v-if="trade.notes" class="mt-4 p-4 bg-gray-50 rounded-lg">
                      <h4 class="font-medium">Notes</h4>
                      <p class="text-sm mt-1">{{ trade.notes }}</p>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                  <div class="font-semibold">
                    Total Value: {{ formatPrice(calculateTotalValue(trade)) }}
                  </div>
                  <div class="space-x-2">
                    <Button variant="outline" @click="viewTradeDetails(trade)">
                      View Details
                    </Button>
                    <Button 
                      v-if="trade.status === 'pending'"
                      variant="destructive"
                      @click="promptCancelTrade(trade.id)"
                    >
                      Cancel
                    </Button>
                    <!-- Add Edit button for pending trades -->
                    <Button 
                      v-if="trade.status === 'pending'"
                      variant="default"
                      @click="editTrade(trade)"
                    >
                      Edit
                    </Button>
                    <Button 
                      v-if="isTradeEligibleForDelete(trade)"
                      variant="outline"
                      class="text-red-500"
                      @click="promptDeleteTrade(trade.id)"
                    >
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
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
        <div v-if="selectedTrade" class="space-y-6">
          <!-- Loading state -->
          <div v-if="selectedTrade.loading" class="flex flex-col items-center justify-center py-8">
            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin mb-4"></div>
            <p class="text-gray-500">Loading trade details...</p>
          </div>
          
          <!-- Trade content (only show when not loading) -->
          <template v-else>
            <div class="flex justify-between items-center">
              <h2 class="text-2xl font-semibold">Trade #{{ selectedTrade.id }}</h2>
              <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusColor(selectedTrade.status)]">
                {{ selectedTrade.status.charAt(0).toUpperCase() + selectedTrade.status.slice(1) }}
              </span>
            </div>
            
            <div class="space-y-6">
              <!-- Seller's Product -->
              <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold mb-2">Seller's Product</h3>
                <div class="flex flex-col md:flex-row gap-4">
                  <!-- Product images - improved rendering quality -->
                  <div class="w-full md:w-1/3 space-y-2">
                    <div v-if="selectedTrade.seller_product?.images?.length" 
                         class="aspect-square rounded-md overflow-hidden border border-gray-200 shadow-sm bg-white">
                      <img 
                        :src="getOptimizedImageUrl(selectedTrade.seller_product.images[0])" 
                        :alt="selectedTrade.seller_product?.name"
                        class="w-full h-full object-contain bg-white"
                        @error="handleImageError"
                        @click="previewImage(selectedTrade.seller_product.images[0])"
                        style="image-rendering: -webkit-optimize-contrast; transform: translateZ(0);"
                      />
                    </div>
                    <!-- Thumbnail grid with improved rendering -->
                    <div v-if="selectedTrade.seller_product?.images?.length > 1" class="grid grid-cols-4 gap-2">
                      <div
                        v-for="(image, idx) in selectedTrade.seller_product.images.slice(1, 5)"
                        :key="idx"
                        class="aspect-square rounded-md overflow-hidden border border-gray-200 cursor-pointer bg-white"
                        @click="previewImage(image)"
                      > 
                        <img 
                          :src="getOptimizedImageUrl(image)" 
                          :alt="`${selectedTrade.seller_product?.name} thumbnail`" 
                          class="w-full h-full object-contain"
                          @error="handleImageError"
                          style="image-rendering: -webkit-optimize-contrast; transform: translateZ(0);"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Product details with improved typography -->
                  <div class="w-full md:w-2/3">
                    <h4 class="font-semibold text-lg leading-tight">{{ selectedTrade.seller_product?.name }}</h4>
                    <p class="text-primary font-semibold my-2 text-lg">{{ formatPrice(selectedTrade.seller_product?.price) }}</p>
                    <div v-if="selectedTrade.seller_product?.description" class="prose prose-sm mt-2 text-gray-700 leading-relaxed">
                      <p>{{ selectedTrade.seller_product?.description }}</p>
                    </div>
                    <div class="mt-4">
                      <p class="text-sm text-gray-500">
                        Owner: <span class="font-medium">{{ selectedTrade.seller?.name }}</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Offered Items with improved rendering -->
              <div class="space-y-4">
                <h3 class="text-lg font-semibold">Offered Items</h3>
                <div v-for="item in selectedTrade.offered_items" :key="item.id" class="bg-gray-50 p-4 rounded-lg shadow-sm">
                  <div class="flex flex-col md:flex-row gap-4">
                    <!-- Item images with improved quality -->
                    <div class="w-full md:w-1/3 space-y-2">
                      <div class="aspect-square rounded-md overflow-hidden bg-white border border-gray-200 shadow-sm">
                        <img 
                          v-if="item.images && item.images.length" 
                          :src="getOptimizedImageUrl(item.images[0])" 
                          :alt="item.name"
                          class="w-full h-full object-contain"
                          @error="handleImageError"
                          @click="previewImage(item.images[0])"
                          style="image-rendering: -webkit-optimize-contrast; transform: translateZ(0);"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                          <span>No image available</span>
                        </div>
                      </div>
                      <!-- Thumbnail grid -->
                      <div v-if="item.images && item.images.length > 1" class="grid grid-cols-4 gap-2">
                        <div
                          v-for="(image, idx) in item.images.slice(1, 5)" 
                          :key="idx"
                          class="aspect-square rounded-md overflow-hidden bg-white border border-gray-200 cursor-pointer"
                          @click="previewImage(image)"
                        >
                          <img 
                            :src="getOptimizedImageUrl(image)" 
                            :alt="`${item.name} thumbnail`" 
                            class="w-full h-full object-contain"
                            @error="handleImageError"
                            style="image-rendering: -webkit-optimize-contrast; transform: translateZ(0);"
                          />
                        </div>
                      </div>
                    </div>
                    
                    <!-- Item details with improved typography -->
                    <div class="w-full md:w-2/3">
                      <div class="flex justify-between">
                        <h4 class="font-semibold text-lg leading-tight">{{ item.name }}</h4>
                        <p class="text-primary font-semibold text-lg">{{ formatPrice(item.estimated_value) }}</p>
                      </div>
                      <p class="text-sm text-gray-500 mt-1">Quantity: <span class="font-medium">{{ item.quantity }}</span></p>
                      <div v-if="item.description" class="prose prose-sm mt-2 text-gray-700 leading-relaxed">
                        <p>{{ item.description }}</p>
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
              </div>
              
              <!-- Notes -->
              <div v-if="selectedTrade.notes" class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-2">Notes</h3>
                <p class="italic">{{ selectedTrade.notes }}</p>
              </div>
              
              <!-- Trade Timeline -->
              <div>
                <h3 class="text-lg font-semibold mb-2">Trade Timeline</h3>
                <div class="space-y-2">
                  <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500">Created:</span>
                    <span class="font-medium">{{ formatDateTime(selectedTrade.created_at, true) }}</span>
                  </div>
                  <div v-if="selectedTrade.updated_at > selectedTrade.created_at" class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500">Last Updated:</span>
                    <span class="font-medium">{{ formatDateTime(selectedTrade.updated_at, true) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Meetup Details -->
              <div v-if="selectedTrade.meetup_location || selectedTrade.meetup_schedule" class="space-y-3">
                <h3 class="text-lg font-semibold">Meetup Details</h3>
                <div class="bg-blue-50 p-4 rounded-lg">
                  <div v-if="selectedTrade.meetup_location" class="flex flex-col gap-1 mb-2">
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
                        <p class="text-xs text-gray-500" v-if="selectedTrade.meetup_location.phone">Contact: {{ selectedTrade.meetup_location.phone }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="selectedTrade.meetup_schedule" class="flex items-start gap-2">
                    <div class="rounded-full p-1 bg-blue-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium">{{ selectedTrade.formatted_meetup_date || formatDateTime(selectedTrade.meetup_schedule, false) }}</p>
                      <p class="text-xs text-gray-500">Scheduled meetup date</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Trade Negotiations -->
              <div class="space-y-3">
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
                        <!-- Message from other user -->
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
                        
                        <!-- Message from current user -->
                        <template v-else>
                          <div class="flex-1 max-w-[75%]">
                            <div class="flex justify-end items-baseline">
                              <span class="text-xs text-gray-500 mr-2">{{ formatMessageTime(message.created_at) }}</span>
                              <span class="font-medium text-sm">You</span>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 mt-1 shadow-sm border-blue-100 border text-right">
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
              
            </div>

            <div class="flex justify-end space-x-4 pt-4">
              <Button variant="outline" @click="closeTradeDetails">Close</Button>
              <!-- Add Edit button in trade details view -->
              <Button 
                v-if="selectedTrade.status === 'pending'"
                variant="default"
                @click="editTrade(selectedTrade)"
              >
                Edit Trade
              </Button>
              <Button 
                v-if="selectedTrade.status === 'pending'"
                variant="destructive"
                @click="promptCancelTrade(selectedTrade.id)"
              >
                Cancel Trade
              </Button>
            </div>
          </template>
        </div>
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

    <!-- Add Edit Trade Dialog -->
    <EditTrade 
      v-if="tradeToEdit"
      :trade="tradeToEdit"
      :open="showEditTradeDialog"
      :availableMeetupLocations="availableMeetupLocations"
      @close="closeEditTradeModal"
      @update:open="showEditTradeDialog = $event"
    />

    <!-- Add an Image Preview Dialog -->
    <Dialog :open="!!previewImageUrl" @update:open="previewImageUrl = null" class="image-preview-dialog">
      <DialogContent class="max-w-5xl max-h-[90vh]">
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
              class="max-h-[80vh] max-w-full object-contain"
              style="image-rendering: -webkit-optimize-contrast; transform: translateZ(0); backface-visibility: hidden;"
              @error="handleImageError"
            />
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Toaster } from '@/Components/ui/toast'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/Components/ui/tabs'
import { Dialog, DialogContent } from '@/Components/ui/dialog'
import { useToast } from '@/Components/ui/toast/use-toast'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/Components/ui/alert-dialog'
import axios from 'axios';

// Add new imports for edit form
import { Calendar } from "@/Components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover"
import { Select } from "@/Components/ui/select"
import { Label } from "@/Components/ui/label"
import { Textarea } from "@/Components/ui/textarea"
import { CalendarIcon } from "lucide-vue-next"
import { format } from "date-fns"
import { X } from "lucide-vue-next"

// Import the EditTrade component
import EditTrade from '@/Components/EditTrade.vue'

const props = defineProps({
  user: Object,
  stats: Object,
  trades: {
    type: Object,
    required: true
  }
})

// Toast handling
const page = usePage()
const { toast } = useToast()

// Watch for flash messages from the server and show toasts
watch(() => page.props.flash, (flash) => {
  console.log('Flash message detected:', flash) // Debug log
  
  if (flash?.success) {
    toast({
      title: 'Success',
      description: flash.success,
      variant: 'default'
    })
  } else if (flash?.error) {
    toast({
      title: 'Error',
      description: flash.error,
      variant: 'destructive'
    })
  }
}, { deep: true, immediate: true })

// Also watch for flash messages at the root level
watch(() => page.props, (props) => {
  if (props.success) {
    toast({
      title: 'Success',
      description: props.success,
      variant: 'default'
    })
  }
  
  if (props.error) {
    toast({
      title: 'Error',
      description: props.error,
      variant: 'destructive'
    })
  }
}, { immediate: true })

const searchQuery = ref('')
const selectedTrade = ref(null)
const showTradeDetails = ref(false)
const showCancelAlert = ref(false)
const showDeleteAlert = ref(false)
const showBulkDeleteAlert = ref(false)
const tradeToCancel = ref(null)
const tradeToDelete = ref(null)

const groupedTrades = computed(() => {
  // First, filter trades where the current user is the buyer
  const buyerTrades = (props.trades.data || []).filter(trade => 
    trade.buyer_id === props.user.id
  );
  
  // Then group the filtered trades by status
  const groups = {
    all: buyerTrades.filter(t => t.status !== 'canceled'),
    pending: buyerTrades.filter(t => t.status === 'pending'),
    accepted: buyerTrades.filter(t => t.status === 'accepted'),
    rejected: buyerTrades.filter(t => t.status === 'rejected'),
    completed: buyerTrades.filter(t => t.status === 'completed'),
    canceled: buyerTrades.filter(t => t.status === 'canceled'),
  }
  
  // Filter by search query if present
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    Object.keys(groups).forEach(key => {
      groups[key] = groups[key].filter(trade => 
        trade.id.toString().includes(query) ||
        (trade.seller_product && trade.seller_product.name.toLowerCase().includes(query)) ||
        (trade.offered_items && trade.offered_items.some(item => item.name.toLowerCase().includes(query)))
      )
    })
  }
  
  return groups
})

const formatPrice = (price) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price)

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

const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'accepted': 'bg-blue-100 text-blue-800',
    'rejected': 'bg-red-100 text-red-800',
    'canceled': 'bg-gray-100 text-gray-800',
    'completed': 'bg-green-100 text-green-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const calculateTotalValue = (trade) => {
  // Calculate total value of offered items
  const itemsTotal = trade.offered_items ? trade.offered_items.reduce((sum, item) => {
    return sum + (item.estimated_value * item.quantity);
  }, 0) : 0;

  // Add additional cash
  return itemsTotal + (trade.additional_cash || 0);
};

// Add new functions for image handling
const getImageUrl = (image, fallbackImage = '/images/placeholder-product.jpg') => {
  return getOptimizedImageUrl(image, fallbackImage);
};

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg';
};

// Improved image URL handling to ensure quality
const getOptimizedImageUrl = (image, fallbackImage = '/images/placeholder-product.jpg') => {
  if (!image) {
    return fallbackImage;
  }
  
  // Add cache-busting for remotely served images
  if (image.startsWith('http://') || image.startsWith('https://')) {
    // Add timestamp to prevent caching if needed
    return image.includes('?') ? image : `${image}?t=${Date.now()}`;
  }
  
  // If the image path starts with 'storage/', add the leading slash
  if (image.startsWith('storage/')) {
    return '/' + image;
  }
  
  // Otherwise assume it needs the storage prefix
  return `/storage/${image}`;
};

// Preview image function with proper high-resolution handling
const previewImage = (imageUrl) => {
  // Clean the URL to ensure we're not using a cached thumbnail version
  let url = getOptimizedImageUrl(imageUrl);
  
  // For remote images, remove any size parameters that might be limiting quality
  if (url.includes('?')) {
    // Remove common size limiting parameters
    url = url.split('?')[0];
  }
  
  previewImageUrl.value = url;
  
  // Preload the image for better display
  const img = new Image();
  img.src = url;
};

// Add state for image preview
const previewImageUrl = ref(null)

/**
 * Function to fetch detailed information about a trade
 */
const fetchTradeDetails = (tradeId) => {
  // Show loading state first
  selectedTrade.value = { ...selectedTrade.value, loading: true };
  
  router.visit(route('trades.details', tradeId), {
    preserveState: true,
    preserveScroll: true,
    only: ['trade'],
    onSuccess: page => {
      // Get trade data from the page props
      const tradeData = page.props.trade;
      
      console.log('Inertia response received:', page.props);
      
      if (tradeData) {
        // Update the selectedTrade with the fetched detailed data
        selectedTrade.value = tradeData;
        
        // Ensure images arrays are properly initialized to prevent rendering errors
        if (selectedTrade.value.seller_product && !selectedTrade.value.seller_product.images) {
          selectedTrade.value.seller_product.images = [];
        }
        if (selectedTrade.value.offered_items) {
          selectedTrade.value.offered_items.forEach(item => {
            if (!item.images) {
              item.images = [];
            }
          });
        }
      } else {
        toast({
          title: "Error",
          description: "Failed to load trade details - data not found in response",
          variant: "destructive"
        });
        closeTradeDetails();
      }
    },
    onError: errors => {
      console.error("Error fetching trade details:", errors);
      toast({
        title: "Error",
        description: "Failed to load trade details. Please try again later.",
        variant: "destructive"
      });
      closeTradeDetails();
    }
  });
};

/**
 * Opens the trade details dialog and fetches complete information
 */
const viewTradeDetails = (trade) => {
  // Set basic trade info first so we can show a loading state
  selectedTrade.value = trade;
  showTradeDetails.value = true;
  
  // Reset messages when viewing a different trade
  messages.value = [];
  
  // Then fetch the complete details with images
  fetchTradeDetails(trade.id);
  
  // Fetch messages for this trade
  fetchMessages(trade.id);
};

/**
 * Closes the trade details dialog and resets state
 */
const closeTradeDetails = () => {
  selectedTrade.value = null;
  showTradeDetails.value = false;
}

// Check if there are any trades eligible for deletion
const hasDeleteEligibleTrades = computed(() => {
  return props.trades.data?.some(trade => isTradeEligibleForDelete(trade)) || false
})

// Function to get all eligible trade IDs for bulk deletion
const getEligibleTradeIds = () => {
  return props.trades.data
    ?.filter(trade => isTradeEligibleForDelete(trade))
    ?.map(trade => trade.id) || []
}

// Delete trade functions
const promptDeleteTrade = (tradeId) => {
  tradeToDelete.value = tradeId
  showDeleteAlert.value = true
}

const confirmDeleteTrade = () => {
  if (tradeToDelete.value) {
    // Close the dialog immediately to prevent it from staying visible
    showDeleteAlert.value = false
    
    router.delete(route('trades.delete', tradeToDelete.value), {
      onSuccess: (page) => {
        toast({
          title: 'Success',
          description: page.props.flash?.success || 'Trade deleted successfully',
          variant: 'default'
        })
        
        // Reset state
        tradeToDelete.value = null
      },
      onError: (errors) => {
        toast({
          title: 'Error',
          description: errors.message || 'Failed to delete trade',
          variant: 'destructive'
        })
        tradeToDelete.value = null
      }
    })
  }
}

// Bulk delete function
const confirmBulkDelete = () => {
  const tradeIds = getEligibleTradeIds()
  
  if (tradeIds.length > 0) {
    showBulkDeleteAlert.value = false
    
    router.delete(route('trades.bulk-delete'), {
      data: { trade_ids: tradeIds },
      onSuccess: (page) => {
        toast({
          title: 'Success',
          description: page.props.flash?.success || 'Trades deleted successfully',
          variant: 'default'
        })
      },
      onError: (errors) => {
        toast({
          title: 'Error',
          description: errors.message || 'Failed to delete trades',
          variant: 'destructive'
        })
      }
    })
  }
}

// Add edit form state variables
const showEditTradeDialog = ref(false)
const tradeToEdit = ref(null)
const availableMeetupLocations = ref([])

/**
 * Open edit trade dialog and initialize form with trade data
 */
const editTrade = async (trade) => {
  // Close other dialogs if open
  showTradeDetails.value = false
  
  // Set the trade to edit
  tradeToEdit.value = trade
  
  // Load available meetup locations for this seller
  try {
    const response = await axios.get(`/trades/product/${trade.seller_product_id}/meetup-locations`)
    if (response.data.success) {
      availableMeetupLocations.value = response.data.meetup_locations || []
    } else {
      availableMeetupLocations.value = []
      toast({
        title: 'Warning',
        description: 'Could not load meetup locations',
        variant: 'default'
      })
    }
  } catch (error) {
    console.error('Error loading meetup locations:', error)
    availableMeetupLocations.value = []
  }
  
  // Show the edit trade dialog
  showEditTradeDialog.value = true
}

/**
 * Show modal for editing trade from a button click
 */
const showEditTradeModal = (trade) => {
  editTrade(trade);
}

/**
 * Close edit trade dialog and reset form
 */
const closeEditTradeModal = () => {
  tradeToEdit.value = null;
  showEditTradeDialog.value = false;
  
  // Refresh trade details if we're viewing the trade details
  if (selectedTrade.value && showTradeDetails.value) {
    fetchTradeDetails(selectedTrade.value.id);
  }
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

/**
 * Opens the cancel trade confirmation dialog
 */
const promptCancelTrade = (tradeId) => {
  tradeToCancel.value = tradeId;
  showCancelAlert.value = true;
};

/**
 * Confirms and actually cancels the trade after dialog confirmation
 */
const confirmCancelTrade = () => {
  if (tradeToCancel.value) {
    // Close both dialogs immediately to prevent them from staying visible
    showCancelAlert.value = false;
    showTradeDetails.value = false;
    
    router.patch(route('trades.cancel', tradeToCancel.value), {}, {
      onSuccess: (page) => {
        // Show success toast if we have a message in the response
        if (page.props.flash?.success) {
          toast({
            title: 'Success',
            description: page.props.flash.success,
            variant: 'default'
          })
        } else {
          // Default success message if none provided
          toast({
            title: 'Success',
            description: 'Trade offer cancelled successfully',
            variant: 'default'
          })
        }
        
        // Reset all dialog-related state
        selectedTrade.value = null;
        tradeToCancel.value = null;
      },
      onError: (errors) => {
        // Show error toast
        toast({
          title: 'Error',
          description: 'Failed to cancel trade offer',
          variant: 'destructive'
        })
        tradeToCancel.value = null;
      }
    })
  }
}

// Check if a trade is eligible for deletion (completed, cancelled, or rejected)
const isTradeEligibleForDelete = (trade) => {
  return ['completed', 'canceled', 'rejected'].includes(trade.status)
}
</script>

<style scoped>
.bg-card {
  background-color: white;
}

/* Improve image rendering quality */
img {
  -webkit-backface-visibility: hidden;
  -ms-transform: translateZ(0); /* IE 9 */
  -webkit-transform: translateZ(0); /* Chrome, Safari, Opera */
  transform: translateZ(0);
}

/* Fix Safari image rendering */
img {
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

/* Smooth scrolling for better user experience */
.overflow-y-auto {
  scroll-behavior: smooth;
}

/* Improve image preview dialog */
.image-preview-dialog :deep(.DialogContent) {
  padding: 8px;
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(5px);
}

/* Fix dialog rendering */
:deep(.DialogOverlay),
:deep(.AlertDialogOverlay) {
  animation: overlayShow 150ms cubic-bezier(0.16, 1, 0.3, 1);
}

:deep(.DialogContent),
:deep(.AlertDialogContent) {
  animation: contentShow 150ms cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes overlayShow {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes contentShow {
  from {
    opacity: 0;
    transform: translate(-50%, -48%) scale(0.96);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}

/* Enhanced image quality settings */
img {
  -webkit-backface-visibility: hidden;
  -ms-transform: translateZ(0); /* IE 9 */
  -webkit-transform: translateZ(0); /* Chrome, Safari, Opera */
  transform: translateZ(0);
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
  max-height: 100%;
  width: auto;
  margin: 0 auto;
}

/* Use higher quality rendering for 2x displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  img {
    image-rendering: auto;
  }
}

/* Better preview dialog styling */
.image-preview-dialog :deep(.DialogContent) {
  padding: 8px;
  background-color: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(5px);
  max-width: 90vw;
  width: auto;
}
</style>