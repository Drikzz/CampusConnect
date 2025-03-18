<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Updated import path

const props = defineProps({
    stats: {
        type: Object,
        required: true
    }
});


const dropdownStates = ref({
  dropdown1: false,
  dropdown2: false
});

const toggleDropdown = (id) => {
  dropdownStates.value[id] = !dropdownStates.value[id];
};

onMounted(() => {
  // You can replace this with another charting library or simpler HTML/CSS elements
});
</script>

<template>
  <AdminLayout>
    <div class="bg-white p-4 rounded-lg flex justify-between items-center shadow-md navbar">
      <h1 class="text-2xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-red-600 to-red-400">DASHBOARD</h1>
    </div>

    <!-- Stats Cards -->
    <div class="flex gap-4">
      <div class="bg-white p-2 rounded-lg shadow-md transition-all duration-300 relative overflow-hidden hover:transform hover:-translate-y-1 hover:shadow-lg flex-1">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-600 to-red-400"></div>
        <h3 class="text-gray-500 text-sm mb-2 font-medium">Listed Products</h3>
        <div class="text-lg font-semibold text-gray-800 flex items-baseline gap-2">
          {{ stats.listedProducts }}
          <span class="text-sm text-green-500 font-medium p-1 bg-green-100 rounded">+45%</span>
        </div>
      </div>
      <!-- Similar stats cards for Total Users and Total Orders -->
    </div>

    <!-- Charts -->
    <div class="flex gap-4">
      <div class="relative flex flex-col rounded-xl bg-white bg-clip-border text-gray-700 shadow-md flex-1">
        <!-- Transaction Overview Chart -->
        <div id="line-chart"></div>
      </div>
      <div class="relative flex flex-col rounded-xl bg-white bg-clip-border text-gray-700 shadow-md flex-1">
        <!-- User Registrations Chart -->
        <div id="bar-chart"></div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="flex gap-4">
      <div class="relative flex flex-col rounded-xl bg-white bg-clip-border text-gray-700 shadow-md flex-1">
        <!-- Products table implementation -->
        <table class="min-w-full bg-white">
          <!-- Table header and body implementation -->
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
