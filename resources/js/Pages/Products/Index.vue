<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Card, CardContent } from "@/Components/ui/card";

const props = defineProps({
    products: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            links: [],
            total: 0
        })
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const filters = ref({
    matchingType: props.filters?.matchingType || 'any',
    category: props.filters?.category || 'all',
    minPrice: props.filters?.price?.min || '',
    maxPrice: props.filters?.price?.max || ''
});

const applyFilters = () => {
    router.get('/products', {
        matchingType: filters.value.matchingType,
        category: filters.value.category === 'all' ? '' : filters.value.category,
        price: {
            min: filters.value.minPrice || null,
            max: filters.value.maxPrice || null
        }
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Add a watcher to apply filters when they change
watch(filters.value, () => {
    applyFilters();
}, { deep: true });

// Simplify the formattedProducts computed property
const formattedProducts = computed(() => {
    if (!props.products.data) return [];
    
    return props.products.data.map(product => {
        // Create a shallow copy of the product
        const formattedProduct = { ...product };
        
        // Handle images more simply
        if (formattedProduct.images) {
            // If images is a string that looks like JSON, parse it
            if (typeof formattedProduct.images === 'string' && 
                (formattedProduct.images.startsWith('[') || formattedProduct.images.startsWith('{'))) {
                try {
                    formattedProduct.images = JSON.parse(formattedProduct.images);
                } catch (e) {
                    formattedProduct.images = [formattedProduct.images];
                }
            }
            
            // Ensure images is always an array
            if (!Array.isArray(formattedProduct.images)) {
                formattedProduct.images = [formattedProduct.images];
            }
        } else {
            formattedProduct.images = [];
        }
        
        return formattedProduct;
    });
});

const handleBuyNow = (productId) => {
    window.location.href = `/checkout/${productId}`;
};

// Add toggle for mobile filters
const showFilters = ref(false);

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};
</script>

<template>
    <Head title="Products" />

    <div class="flex flex-col w-full mt-6 sm:mt-10 mb-16 sm:mb-28">
        <!-- Breadcrumb -->
        <div class="flex justify-start items-center gap-2 w-full pt-4 px-4 sm:px-8 lg:px-16">
            <Link :href="route('index')" class="font-Satoshi text-sm sm:text-base">Home</Link>
            <span class="font-Satoshi text-sm sm:text-base">/</span>
            <span class="font-Satoshi-bold text-sm sm:text-base">Products</span>
        </div>

        <!-- Title -->
        <div class="flex justify-center items-center w-full py-4 sm:py-8">
            <h1 class="font-Footer italic text-2xl sm:text-3xl md:text-4xl">ALL PRODUCTS</h1>
        </div>

        <div class="flex flex-col lg:flex-row px-4 sm:px-8 lg:px-16">
            <!-- Sidebar Filters - Make collapsible on mobile -->
            <div class="w-full lg:w-1/4 lg:pr-6 mb-6 lg:mb-0">
                <Button 
                    variant="outline" 
                    class="w-full mb-4 flex justify-between items-center lg:hidden"
                    @click="toggleFilters"
                >
                    <span>Filters</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" 
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                         stroke-linejoin="round" 
                         :class="{'rotate-180': showFilters}">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </Button>
                
                <div :class="{'hidden lg:block': !showFilters, 'block': showFilters}">
                    <Card>
                        <CardContent class="p-6">
                            <h3 class="font-Satoshi-bold text-lg mb-4">Filters</h3>

                            <!-- Matching Type -->
                            <div class="mb-6">
                                <p class="font-Satoshi-bold mb-2">Matching Type</p>
                                <div class="flex flex-col gap-2">
                                    <label class="flex items-center gap-2">
                                        <input 
                                            type="radio" 
                                            v-model="filters.matchingType" 
                                            value="any" 
                                            class="form-radio"
                                            @change="applyFilters"
                                        >
                                        <span class="font-Satoshi">Any (OR)</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input 
                                            type="radio" 
                                            v-model="filters.matchingType" 
                                            value="all" 
                                            class="form-radio"
                                            @change="applyFilters"
                                        >
                                        <span class="font-Satoshi">All (AND)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-6">
                                <p class="font-Satoshi-bold mb-2">Categories</p>
                                <Select v-model="filters.category" @update:value="applyFilters">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <!-- Change empty value to "all" -->
                                        <SelectItem value="all">All Categories</SelectItem>
                                        <SelectItem value="Electronics">Electronics</SelectItem>
                                        <SelectItem value="Books">Books</SelectItem>
                                        <SelectItem value="Uniforms">Uniforms</SelectItem>
                                        <SelectItem value="School Supplies">School Supplies</SelectItem>
                                        <SelectItem value="Clothing">Clothing</SelectItem>
                                        <SelectItem value="On Sale">On Sale</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-6">
                                <p class="font-Satoshi-bold mb-2">Price Range</p>
                                <div class="flex flex-col gap-2">
                                    <Input 
                                        type="number" 
                                        v-model="filters.minPrice" 
                                        placeholder="Min Price" 
                                        @change="applyFilters"
                                    />
                                    <Input 
                                        type="number" 
                                        v-model="filters.maxPrice" 
                                        placeholder="Max Price"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <Button @click="applyFilters" variant="default" class="w-full">
                                Apply Filters
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="w-full lg:w-3/4">
                <div v-if="formattedProducts.length > 0" 
                     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <ProductCard v-for="product in formattedProducts" 
                               :key="product.id" 
                               :product="product"
                               :disableWishlistCheck="false"
                               @buyNow="handleBuyNow" />
                </div>
                
                <!-- Pagination -->
                <div v-if="products.links?.length > 3" class="mt-6 flex flex-wrap justify-center gap-2">
                    <Link v-for="link in products.links" 
                          :key="link.label"
                          :href="link.url"
                          v-html="link.label"
                          class="px-2 sm:px-4 py-2 border rounded-lg text-sm sm:text-base"
                          :class="[
                              link.active ? 'bg-primary-color text-white' : 'bg-white',
                              !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'
                          ]" />
                </div>

                <!-- Empty State -->
                <div v-else-if="!products.data?.length" class="text-center py-12">
                    <p class="text-gray-500 text-lg">No products found</p>
                    <p class="text-gray-400 mt-2">Try adjusting your search criteria</p>
                </div>
            </div>
        </div>
    </div>
</template>
