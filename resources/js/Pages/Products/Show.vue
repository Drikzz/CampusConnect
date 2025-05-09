<script setup>
import { ref, computed } from 'vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Button } from "@/Components/ui/button";
import { Card, CardContent } from "@/Components/ui/card";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/Components/ui/tabs";
import { Link, router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/Components/ui/dialog";
import { useToast } from "@/Components/ui/toast/use-toast";

const { toast } = useToast();

const props = defineProps({
    product: Object,
    randomProducts: Array,
    userProducts: Array // Add this to receive user's products for trade
});

const currentImageIndex = ref(0);
const activeTab = ref('ProductDetails');

const changeImage = (index) => {
    currentImageIndex.value = index;
};

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % props.product.images.length;
};

const prevImage = () => {
    currentImageIndex.value = currentImageIndex.value === 0 
        ? props.product.images.length - 1 
        : currentImageIndex.value - 1;
};

const formattedPrice = computed(() => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(props.product.price);
});

const formattedDiscountedPrice = computed(() => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(props.product.discounted_price);
});

// Add toggleBookmark functionality
const isBookmarked = ref(false);
const toggleBookmark = () => {
    isBookmarked.value = !isBookmarked.value;
};

// Add tab functionality
const currentTab = ref('ProductDetails');

// Add trade modal state and form
const showTradeModal = ref(false);
const tradeForm = ref({
    offered_product_id: null,
    message: '',
    target_product_id: props.product?.id
});

const selectedTradeProduct = ref(null);

const openTradeModal = () => {
    if (!props.userProducts || props.userProducts.length === 0) {
        toast({
            title: "No Products Available",
            description: "You don't have any products to offer for trade",
            variant: "destructive"
        });
        return;
    }
    
    showTradeModal.value = true;
};

const selectTradeProduct = (product) => {
    tradeForm.value.offered_product_id = product.id;
    selectedTradeProduct.value = product;
};

const submitTradeOffer = () => {
    if (!tradeForm.value.offered_product_id) {
        toast({
            title: "Selection Required",
            description: "Please select a product to offer for trade",
            variant: "destructive"
        });
        return;
    }

    router.post(route('product.trade.submit'), tradeForm.value, {
        onSuccess: () => {
            showTradeModal.value = false;
            toast({
                title: "Trade Offer Sent",
                description: "Your trade offer has been submitted successfully",
            });
        },
        onError: (errors) => {
            toast({
                title: "Error",
                description: Object.values(errors)[0] || "Failed to submit trade offer",
                variant: "destructive"
            });
        }
    });
};

const getImageUrl = (image) => {
    if (!image) return '/images/placeholder.jpg';
    return image.startsWith('http') ? image : `/storage/${image}`;
};

const handleImageError = (e) => {
    e.target.src = '/images/placeholder.jpg';
};
</script>

<template>
  <Head title="Product" />

  <div class="mt-10 mb-28 px-4 sm:px-8 lg:px-16">
    <!-- Product Grid - Updated for responsiveness -->
    <div class="grid grid-cols-1 lg:grid-cols-2 place-items-center w-full gap-8">
      <!-- Image Gallery -->
      <div class="h-full w-full flex justify-between items-center gap-4 sm:gap-8">
        <!-- Thumbnail Gallery -->
        <div class="h-full w-[20%] hidden sm:flex flex-col justify-start items-center gap-4">
          <div v-for="(image, index) in product.images" 
               :key="index"
               @click="changeImage(index)"
               class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 cursor-pointer">
            <img :src="getImageUrl(image)" 
                 :class="['w-full h-full aspect-square object-cover hover:outline hover:outline-black',
                         { 'outline outline-black': currentImageIndex === index }]"
                 :alt="product.name"
                 @error="handleImageError">
          </div>
        </div>

        <!-- Main Image -->
        <div class="h-full w-full sm:w-[80%] bg-black relative">
          <img :src="product.images[currentImageIndex]" 
               class="object-contain aspect-square w-full h-full max-h-[300px] sm:max-h-[400px] lg:max-h-[500px]"
               :alt="product.name">
          
          <!-- Navigation Buttons - Made more visible on mobile -->
          <button @click="prevImage"
              class="absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white p-3 sm:p-4 rounded-full z-10">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24">
                  <path d="M17.17,24a1,1,0,0,1-.71-.29L8.29,15.54a5,5,0,0,1,0-7.08L16.46.29a1,1,0,1,1,1.42,1.42L9.71,9.88a3,3,0,0,0,0,4.24l8.17,8.17a1,1,0,0,1,0,1.42A1,1,0,0,1,17.17,24Z" />
              </svg>
          </button>
          <button @click="nextImage"
              class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white p-3 sm:p-4 rounded-full z-10">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24">
                  <path d="M7,24a1,1,0,0,1-.71-.29,1,1,0,0,1,0-1.42l8.17-8.17a3,3,0,0,0,0-4.24L6.29,1.71A1,1,0,0,1,7.71.29l8.17,8.17a5,5,0,0,1,0,7.08L7.71,23.71A1,1,0,0,1,7,24Z" />
              </svg>
          </button>

          <!-- Mobile thumbnail indicators -->
          <div class="flex justify-center gap-2 mt-2 sm:hidden">
            <div v-for="(_, index) in product.images" 
                 :key="index"
                 @click="changeImage(index)"
                 class="w-2 h-2 rounded-full cursor-pointer"
                 :class="currentImageIndex === index ? 'bg-primary-color' : 'bg-gray-300'">
            </div>
          </div>
        </div>
      </div>

      <!-- Product Info -->
      <div class="w-full mt-6 lg:mt-0">
        <!-- Navigation and Tags -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4 sm:gap-0">
          <div>
            <!-- Breadcrumb -->
            <div class="flex flex-wrap justify-start items-center gap-2">
              <Link :href="route('index')" class="font-Satoshi text-sm sm:text-base">Home</Link>
              <span class="font-Satoshi text-sm sm:text-base">/</span>
              <Link :href="route('products')" class="font-Satoshi text-sm sm:text-base">Products</Link>
              <span class="font-Satoshi text-sm sm:text-base">/</span>
              <span class="font-Satoshi-bold text-sm sm:text-base">{{ product.category.name }}</span>
            </div>

            <!-- Product Tags -->
            <div class="flex flex-wrap justify-start items-center gap-2 mt-4">
              <span class="font-Satoshi-bold text-sm sm:text-base">Tags:</span>
              <div v-for="tag in product.tags" 
                   :key="tag"
                   class="flex items-center px-2 sm:px-4 py-1 sm:py-2 ring-2 ring-gray-400 rounded-full gap-1 sm:gap-2">
                <span class="text-gray-400 text-xs sm:text-sm">{{ tag }}</span>
              </div>
            </div>
          </div>

          <!-- Bookmark Button -->
          <div class="w-fit bg-white p-2 rounded-full hover:shadow-md hover:ring-2 hover:ring-black hover:transition-all">
            <svg v-if="!isBookmarked" @click="toggleBookmark" 
                 class="w-5 h-5 fill-black cursor-pointer"
                 viewBox="0 0 24 24">
                <path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Zm-3.585,18.4a2.973,2.973,0,0,1-3.83,0C4.947,16.006,2,11.87,2,8.967a4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,11,8.967a1,1,0,0,0,2,0,4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,22,8.967C22,11.87,19.053,16.006,13.915,20.313Z" />
            </svg>
            <svg v-else @click="toggleBookmark"
                 class="w-5 h-5 fill-black cursor-pointer"
                 viewBox="0 0 24 24">
                <path d="M2.849,23.55a2.954,2.954,0,0,0,3.266-.644L12,17.053l5.885,5.853a2.956,2.956,0,0,0,2.1.881,3.05,3.05,0,0,0,1.17-.237A2.953,2.953,0,0,0,23,20.779V5a5.006,5.006,0,0,0-5-5H6A5.006,5.006,0,0,0,1,5V20.779A2.953,2.953,0,0,0,2.849,23.55Z" />
            </svg>
          </div>
        </div>

        <!-- Product Title -->
        <div class="mt-4">
          <p class="font-Satoshi-bold text-xl sm:text-2xl">{{ product.name }}</p>
        </div>

        <!-- Product Price, Sale, Stock -->
        <div class="flex flex-wrap justify-start items-center gap-2 sm:gap-4 mt-4">
          <p class="font-Satoshi-bold text-xl sm:text-2xl">{{ formattedDiscountedPrice }}</p>
          <template v-if="product.discount > 0">
            <p class="font-Satoshi-bold text-lg sm:text-2xl line-through text-gray-400">{{ formattedPrice }}</p>
            <p class="font-Satoshi-bold text-lg sm:text-2xl text-red">-{{ Math.round(product.discount * 100) }}%</p>
          </template>
          <p class="font-Satoshi text-base sm:text-xl mt-1 sm:mt-0">In stock: <span>{{ product.stock }}</span> pc</p>
        </div>

        <!-- Product Owner Info -->
        <div class="flex flex-wrap justify-start items-center gap-2 sm:gap-4 mt-4">
          <div v-if="product.seller.profile_picture" 
               class="w-10 h-10 rounded-full overflow-hidden">
              <img :src="product.seller.profile_picture" 
                   :alt="product.seller.username"
                   class="w-full h-full object-cover">
          </div>
          <div v-else
               class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center border-2 border-white">
              <span class="text-white text-lg font-medium">{{ product.seller.first_name ? product.seller.first_name[0] : (product.seller.username ? product.seller.username[0] : 'U') }}</span>
          </div>
          <div>
              <p class="font-Satoshi-bold text-base">{{ product.seller.username }}</p>
              <p class="text-xs text-gray-500">{{ product.seller.first_name }} {{ product.seller.last_name }}</p>
          </div>
          <!-- Rating Stars -->
          <div class="flex justify-center items-center gap-1">
              <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-yellow-400" viewBox="0 0 24 24">
                  <path d="M19.467,23.316,12,17.828,4.533,23.316,7.4,14.453-.063,9H9.151L12,.122,14.849,9h9.213L16.6,14.453Z" />
              </svg>
          </div>

          <!-- Rating Score -->
          <div class="flex justify-center items-center">
              <p class="font-Satoshi underline">{{ product.seller.rating }} <span>/ 5</span></p>
          </div>

          <!-- Review Count -->
          <div class="flex justify-center items-center">
              <p class="font-Satoshi">{{ product.seller.reviews_count }} <span>Reviews</span></p>
          </div>

          <!-- Chat Button -->
          <div>
              <button class="font-Satoshi-bold text-base bg-black text-white px-4 py-2 rounded-full hover:bg-gray-800 hover:transition-all">
                  Chat Seller
              </button>
          </div>
        </div>

        <!-- Extra Info -->
        <div class="mt-4">
          <div class="flex justify-start items-center gap-2">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                  <path d="M12,0A9.013,9.013,0,0,0,3,9c0,7.1,8.1,14.4,8.4,14.7a1,1,0,0,0,1.2,0C13,23.4,21,16.1,21,9A9.013,9.013,0,0,0,12,0Zm0,13a4,4,0,1,1,4-4A4,4,0,0,1,12,13Z" />
              </svg>
              <p class="font-Satoshi-bold">{{ product.seller.location || 'Location not specified' }}</p>
          </div>

          <div class="flex justify-start items-center gap-2 mt-4">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                  <path d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z" />
              </svg>
              <p class="font-Satoshi-bold">Verified User</p>
          </div>

          <div class="flex justify-start items-center gap-2 mt-4">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                  <path d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z" />
              </svg>
              <p class="font-Satoshi-bold">No Hassle Refunds</p>
          </div>

          <div class="flex justify-start items-center gap-2 mt-4">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                  <path d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z" />
              </svg>
              <p class="font-Satoshi-bold">Secure Payments</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-evenly items-center gap-4 sm:gap-0 mt-6 sm:mt-4 w-full">
          <Link 
            v-if="product.is_buyable" 
            :href="route('checkout.show', product.id)"
            class="w-full sm:w-auto sm:px-20">
            <Button variant="default" size="lg" class="w-full">
              Buy Now
            </Button>
          </Link>
          <Button v-if="product.is_tradable" 
                  variant="default" 
                  size="lg" 
                  @click="openTradeModal"
                  class="w-full sm:w-auto sm:px-20">
            Trade Now
          </Button>
          <p v-if="!product.is_buyable && !product.is_tradable" class="font-Satoshi text-gray-500">
            Not available for purchase or trade
          </p>
        </div>
      </div>
    </div>

    <!-- Product Description Tabs -->
    <Tabs defaultValue="details" class="w-full mt-8">
      <TabsList>
          <TabsTrigger value="details">Product Details</TabsTrigger>
          <TabsTrigger value="reviews">Reviews</TabsTrigger>
      </TabsList>
      
      <TabsContent value="details">
          <Card>
              <CardContent class="space-y-4 pt-4">
                  <h3 class="font-Satoshi-bold text-xl">Product Description</h3>
                  <p>{{ product.description }}</p>
              </CardContent>
          </Card>
      </TabsContent>
      
      <TabsContent value="reviews">
          <Card>
              <CardContent class="space-y-4 pt-4">
                  <h3 class="font-Satoshi-bold text-xl">Customer Reviews</h3>
                  <!-- Add reviews content -->
              </CardContent>
          </Card>
      </TabsContent>
    </Tabs>

    <!-- Separator -->
    <div class="w-full h-1 bg-black"></div>

    <!-- Similar Products -->
    <div v-if="randomProducts?.length" class="w-full mt-14">
      <div class="flex justify-center items-center mb-4">
        <p class="font-Footer italic text-2xl sm:text-3xl lg:text-4xl text-center">YOU MIGHT ALSO LIKE</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <ProductCard v-for="product in randomProducts" 
                    :key="product.id" 
                    :product="product" />
      </div>
    </div>
    
    <!-- Trade Modal -->
    <Dialog :open="showTradeModal" @update:open="showTradeModal = false">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Trade Offer</DialogTitle>
            </DialogHeader>
            
            <div class="grid gap-4 py-4">
                <div class="space-y-2">
                    <h3 class="text-sm font-medium">You want to trade for:</h3>
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-md">
                        <img 
                            :src="product.images?.[0]" 
                            :alt="product.name" 
                            class="w-16 h-16 object-cover rounded-md"
                        />
                        <div>
                            <p class="font-medium">{{ product.name }}</p>
                            <p class="text-sm text-gray-500">{{ formattedDiscountedPrice }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-sm font-medium">Select a product to offer:</h3>
                    <div class="max-h-[300px] overflow-y-auto space-y-2">
                        <div 
                            v-for="userProduct in userProducts" 
                            :key="userProduct.id"
                            @click="selectTradeProduct(userProduct)"
                            :class="[
                                'flex items-center space-x-4 p-3 rounded-md cursor-pointer border',
                                tradeForm.offered_product_id === userProduct.id 
                                    ? 'border-primary-color bg-primary-color/5' 
                                    : 'border-gray-200 hover:bg-gray-50'
                            ]"
                        >
                            <img 
                                :src="userProduct.images?.[0]" 
                                :alt="userProduct.name" 
                                class="w-16 h-16 object-cover rounded-md"
                            />
                            <div>
                                <p class="font-medium">{{ userProduct.name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ new Intl.NumberFormat('en-PH', {
                                        style: 'currency',
                                        currency: 'PHP'
                                    }).format(userProduct.discounted_price) }}
                                </p>
                            </div>
                        </div>
                        
                        <p v-if="!userProducts || userProducts.length === 0" class="text-center py-8 text-gray-500">
                            You don't have any products available for trade
                        </p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="trade-message" class="text-sm font-medium">Message (Optional):</label>
                    <textarea
                        id="trade-message"
                        v-model="tradeForm.message"
                        rows="3"
                        placeholder="Add a message to the product owner..."
                        class="w-full p-2 border rounded-md"
                    ></textarea>
                </div>
            </div>
            
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="showTradeModal = false"
                    class="mr-2"
                >
                    Cancel
                </Button>
                <Button
                    variant="default"
                    @click="submitTradeOffer"
                    :disabled="!tradeForm.offered_product_id"
                >
                    Submit Trade Offer
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
    
    <!-- View Order Details Link (hidden but available for programmatic navigation) -->
    <Link 
        :href="route('orders.details', { order: 0 })" 
        class="hidden"
        id="order-details-link"
    />
  </div>
</template>

<style scoped>
.tab {
    transition: border-color 0.3s ease;
}

.tab:hover {
    border-color: rgba(0, 0, 0, 0.3);
}
</style>
