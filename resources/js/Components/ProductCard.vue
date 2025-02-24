<script setup>
import { route } from 'ziggy-js';

defineProps({
    product: Object
});
</script>

<template>
    <div class="w-60 h-[30rem] p-4 flex flex-col justify-between items-start gap-4 hover:shadow-lg rounded">
        <div class="relative">
            <!-- Use Inertia Link component -->
            <Link :href="route('products.show', product.id)">
                <img :src="product.images[0]" alt="" class="w-52 h-64 object-cover">
            </Link>
            <div v-if="product.discount > 0.0" class="absolute bottom-2 right-2 rounded-2xl bg-white px-3 py-1">
                <p class="font-Satoshi-bold text-sm text-black">
                    {{ Math.round(product.discount * 100) }}%
                </p>
            </div>

            <div class="absolute top-3 right-3 bg-white p-2 rounded-full hover:shadow-md hover:ring-2 hover:ring-black hover:transition-all">
                <svg class="w-5 h-5 fill-black cursor-pointer bookmarked" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M2.849,23.55a2.954,2.954,0,0,0,3.266-.644L12,17.053l5.885,5.853a2.956,2.956,0,0,0,2.1.881,3.05,3.05,0,0,0,1.17-.237A2.953,2.953,0,0,0,23,20.779V5a5.006,5.006,0,0,0-5-5H6A5.006,5.006,0,0,0,1,5V20.779A2.953,2.953,0,0,0,2.849,23.55Z" />
                </svg>
            </div>
        </div>

        <div>
            <p class="font-Satoshi-bold text-xl">{{ product.name }}</p>
        </div>

        <div class="flex justify-center items-center w-fit gap-2">
            <p class="font-Satoshi-bold text-2xl">₱{{ Number(product.discounted_price).toLocaleString() }}</p>
            <p class="font-Satoshi-bold line-through text-2xl text-gray-400">₱{{ Number(product.price).toLocaleString() }}</p>
        </div>

        <div class="flex justify-between items-center w-full">
            <Link v-if="product.is_buyable" :href="`/summary/${product.id}`"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 hover:transition-all focus:opacity-60 focus:transition-all">
                <button class="font-Satoshi text-white">Buy Now!</button>
            </Link>
            <Link v-if="product.is_tradable" href="#"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 hover:transition-all focus:opacity-60 focus:transition-all">
                <button class="font-Satoshi text-white">Trade Now!</button>
            </Link>
            <p v-if="!product.is_buyable && !product.is_tradable" class="font-Satoshi text-gray-500">
                Not available for purchase or trade
            </p>
        </div>
    </div>
</template>
