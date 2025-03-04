<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Page Not Found" />
  
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
    <div class="text-center">
      <h1 class="text-9xl font-bold text-primary-color">404</h1>
      <h2 class="text-3xl font-Satoshi-bold mt-4 mb-6">Page Not Found</h2>
      <p class="text-gray-600 mb-8">The page you are looking for might have been removed or is temporarily unavailable.</p>
      
      <Link 
        :href="route('index')" 
        class="px-6 py-3 bg-primary-color text-white rounded-md hover:bg-primary-color/90 transition-colors"
      >
        Return Home
      </Link>
    </div>
  </div>
</template>
