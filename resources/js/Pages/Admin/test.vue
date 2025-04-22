<template>
  <AdminLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-bold">Wallet & Fees</h2>
      
      <!-- Platform Fees Settings -->
      <div class="bg-white border rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Platform Fees Settings</h3>
        <form @submit.prevent="updateDeductionRate" class="space-y-4">
          <div class="flex items-center gap-4">
            <div class="w-64">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Wallet Deduction Rate
              </label>
              <div class="flex items-center gap-2">
                <input 
                  type="number" 
                  v-model="walletDeductionRate" 
                  min="0" 
                  max="100" 
                  step="0.1"
                  class="w-full p-2 border rounded focus:ring-2 focus:ring-indigo-500"
                />
                <span>%</span>
              </div>
            </div>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
      
      <!-- API Status Notification -->
      <div v-if="apiError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">API Error:</strong>
        <span class="block sm:inline"> {{ apiError }}</span>
        <button 
          class="absolute top-0 bottom-0 right-0 px-4 py-3" 
          @click="apiError = null"
        >
          <span class="sr-only">Dismiss</span>
          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
      
      <!-- Wallet Stats Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Platform Revenue</p>
          <p class="text-2xl font-bold text-indigo-600">
            ₱{{ formatCurrency(totalRevenue) }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Active Seller Wallets</p>
          <p class="text-2xl font-bold text-green-600">
            {{ activeWallets }}
          </p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow">
          <p class="text-sm text-gray-500">Total Profit From Revenue</p>
          <p class="text-2xl font-bold text-yellow-600">
            ₱{{ formatCurrency(totalProfit) }}
          </p>
        </div>
      </div>
      
      <!-- Admin Wallet Table Component -->
      <AdminWalletTable
        :wallets="sellers || []"
        :loading="isLoadingWallets"
        :error="walletError"
        @refresh="fetchSellerWallets"
        @update-wallet="updateWalletInState"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminWalletTable from '@/Pages/Admin/Components/AdminWalletTable.vue';
import axios from 'axios';

// Data properties - initialize with proper default values
const walletDeductionRate = ref(5);
const totalRevenue = ref(0);
const totalProfit = ref(0);
const activeWallets = ref(0);
const isSubmitting = ref(false);
const isLoadingWallets = ref(true);
const walletError = ref(null);
const apiError = ref(null);
const sellers = ref([]);

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    console.log('Fetching dashboard data...');
    const response = await axios.get('/admin/wallet/dashboard-data');
    console.log('Dashboard API response:', response.data);
    
    // Check if the response has the expected properties
    if (!response.data) {
      throw new Error('Empty response from server');
    }
    
    // Update values
    walletDeductionRate.value = parseFloat(response.data.walletDeductionRate) || 5;
    totalRevenue.value = response.data.totalRevenue || 0;
    totalProfit.value = response.data.totalProfit || 0;
    activeWallets.value = response.data.activeWallets || 0;
    
    console.log('Dashboard data loaded successfully');
    
    // Fetch seller wallets after dashboard data
    await fetchSellerWallets();
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    // Set default values on error
    walletDeductionRate.value = 5;
    totalRevenue.value = 0;
    totalProfit.value = 0;
    activeWallets.value = 0;
    
    // Show user-friendly error
    apiError.value = `Failed to load dashboard data: ${error.message || 'Unknown error'}`;
    
    // Still try to fetch wallets
    await fetchSellerWallets();
  }
};

// Fetch seller wallets
const fetchSellerWallets = async () => {
  isLoadingWallets.value = true;
  walletError.value = null;
  
  try {
    console.log('Making request to /admin/wallet/seller-wallets');
    const response = await axios.get('/admin/wallet/seller-wallets');
    console.log('API response received, status:', response.status);
    console.log('Response data:', response.data);
    
    // More robust error handling
    if (!response.data) {
      throw new Error('Empty response data from server');
    }
    
    // Check if response has wallets property, if not create an empty default
    if (!response.data.hasOwnProperty('wallets')) {
      console.warn('Missing wallets property in response, using empty array instead');
      sellers.value = [];
    } else {
      // Ensure wallets is an array
      sellers.value = Array.isArray(response.data.wallets) ? response.data.wallets : [];
      console.log('Seller wallets loaded successfully:', sellers.value.length);
    }
  } catch (error) {
    console.error('Error fetching seller wallets:', error);
    
    // More detailed error logging
    if (error.response) {
      console.error('Error response status:', error.response.status);
      console.error('Error response data:', error.response.data);
    }
    
    walletError.value = 'Failed to load seller wallets: ' + (error.response?.data?.message || error.message || 'Unknown error');
    sellers.value = []; // Reset to empty array on error
  } finally {
    isLoadingWallets.value = false;
  }
};

// Update wallet deduction rate
const updateDeductionRate = async () => {
  isSubmitting.value = true;
  
  try {
    console.log('Updating deduction rate to:', walletDeductionRate.value);
    const response = await axios.post('/admin/wallet/update-deduction-rate', {
      rate: parseFloat(walletDeductionRate.value)
    });
    
    console.log('Rate update response:', response.data);
    
    // Refresh data after update
    await nextTick();
    await fetchDashboardData();
  } catch (error) {
    console.error('Error updating deduction rate:', error);
    apiError.value = 'Failed to update deduction rate: ' + (error.response?.data?.message || error.message || 'Unknown error');
  } finally {
    isSubmitting.value = false;
  }
};

// Update wallet in state after adjustment
const updateWalletInState = (updatedWallet) => {
  if (!updatedWallet || !updatedWallet.id) {
    console.warn('Attempted to update wallet with invalid data:', updatedWallet);
    return;
  }
  
  const index = sellers.value.findIndex(wallet => wallet.id === updatedWallet.id);
  if (index !== -1) {
    console.log('Updating wallet in state:', updatedWallet.id);
    sellers.value[index] = updatedWallet;
  } else {
    console.warn('Wallet not found in state:', updatedWallet.id);
  }
};

// Helper to format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0);
};

// Add this helper function to verify API routes
const verifyApiRoute = async (url) => {
  try {
    console.log(`Verifying API route: ${url}`);
    const response = await axios.head(url);
    console.log(`Route ${url} is valid, status: ${response.status}`);
    return true;
  } catch (error) {
    console.error(`Route ${url} verification failed:`, error.message);
    if (error.response) {
      console.log(`Response status: ${error.response.status}`);
    }
    return false;
  }
};

// Fetch data on component mount
onMounted(async () => {
  console.log('Admin wallet page mounted');
  
  // Try to verify the API routes first
  await verifyApiRoute('/admin/wallet/dashboard-data');
  await verifyApiRoute('/admin/wallet/seller-wallets');
  
  // Then proceed with data fetching
  await fetchDashboardData();
});
</script>