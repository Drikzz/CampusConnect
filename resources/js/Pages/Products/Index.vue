<script setup>
import { ref } from 'vue';
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
    matchingType: 'any',
    category: props.filters.category || 'all', // Change default value to 'all'
    minPrice: props.filters.price?.min || '',
    maxPrice: props.filters.price?.max || ''
});

const applyFilters = () => {
    router.get('/products', {
        // Convert 'all' back to empty string when sending to server
        category: filters.value.category === 'all' ? '' : filters.value.category,
        price: {
            min: filters.value.minPrice,
            max: filters.value.maxPrice
        }
    }, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Products" />

    <div class="flex flex-col w-full mt-10 mb-28">
        <!-- Breadcrumb -->
        <div class="flex justify-start items-center gap-2 w-full pt-4 px-16">
            <Link :href="route('index')" class="font-Satoshi text-base">Home</Link>
            <span class="font-Satoshi text-base">/</span>
            <span class="font-Satoshi-bold text-base">Products</span>
        </div>

        <!-- Title -->
        <div class="flex justify-center items-center w-full py-8">
            <h1 class="font-Footer italic text-4xl">ALL PRODUCTS</h1>
        </div>

        <div class="flex px-16">
            <!-- Sidebar Filters -->
            <div class="w-1/4 pr-6">
                <Card>
                    <CardContent class="p-6">
                        <h3 class="font-Satoshi-bold text-lg mb-4">Filters</h3>

                        <!-- Matching Type -->
                        <div class="mb-6">
                            <p class="font-Satoshi-bold mb-2">Matching Type</p>
                            <div class="flex flex-col gap-2">
                                <label class="flex items-center gap-2">
                                    <input type="radio" v-model="filters.matchingType" value="any" class="form-radio">
                                    <span class="font-Satoshi">Any</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" v-model="filters.matchingType" value="all" class="form-radio">
                                    <span class="font-Satoshi">All</span>
                                </label>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="mb-6">
                            <p class="font-Satoshi-bold mb-2">Categories</p>
                            <Select v-model="filters.category">
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
                                <Input type="number" v-model="filters.minPrice" placeholder="Min Price" />
                                <Input type="number" v-model="filters.maxPrice" placeholder="Max Price" />
                            </div>
                        </div>

                        <Button @click="applyFilters" variant="default" class="w-full">
                            Apply Filters
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Products Grid -->
            <div class="w-3/4">
                <div v-if="products.data?.length > 0" 
                     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ProductCard v-for="product in products.data" 
                               :key="product.id" 
                               :product="product" />
                </div>
                
                <!-- Pagination -->
                <div v-if="products.links?.length > 3" class="mt-6 flex justify-center gap-2">
                    <Link v-for="link in products.links" 
                          :key="link.label"
                          :href="link.url"
                          v-html="link.label"
                          class="px-4 py-2 border rounded-lg"
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
