<template>
  <AdminLayout>
    <div class="py-4 sm:py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-xl sm:text-2xl font-bold text-foreground mb-6">Wallet & Fees</h1>
        
        <!-- Platform Fees Settings -->
        <div class="bg-card shadow rounded-lg p-4 sm:p-6 mb-6 sm:mb-8 hover:shadow-md transition-shadow duration-300">
          <h3 class="text-lg font-medium text-foreground mb-4">Platform Fees Settings</h3>
          <form @submit.prevent="updateDeductionRate" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
              <div class="w-full sm:w-64">
                <label class="block text-sm font-medium text-muted-foreground mb-1">
                  Wallet Deduction Rate
                </label>
                <div class="flex items-center gap-2">
                  <input 
                    type="number" 
                    v-model="walletDeductionRate" 
                    min="0" 
                    max="100" 
                    step="0.1"
                    class="w-full h-10 px-3 py-2 border rounded focus:ring-2 focus:ring-primary bg-background text-foreground"
                  />
                  <span class="text-muted-foreground">%</span>
                </div>
              </div>
              <button 
                type="submit" 
                class="h-10 px-4 bg-primary text-primary-foreground rounded hover:bg-primary/90 transition-colors duration-200"
                :disabled="isSubmitting"
              >
                <span v-if="isSubmitting" class="flex items-center gap-2">
                  <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-r-transparent align-[-0.125em]"></span>
                  Saving...
                </span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </form>
        </div>
        
        <!-- API Status Notification -->
        <div v-if="apiError" class="bg-destructive/10 border border-destructive text-destructive px-4 py-3 rounded-lg relative mb-6 sm:mb-8" role="alert">
          <strong class="font-bold">API Error:</strong>
          <span class="block sm:inline"> {{ apiError }}</span>
          <button 
            class="absolute top-0 bottom-0 right-0 px-4 py-3 text-destructive hover:text-destructive/80" 
            @click="apiError = null"
          >
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
        
        <!-- Wallet Stats Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <div class="bg-card shadow rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
            <p class="text-sm text-muted-foreground">Total Platform Revenue</p>
            <p class="text-xl sm:text-2xl font-bold text-primary mt-1">
              ₱{{ formatCurrency(totalRevenue) }}
            </p>
          </div>
          <div class="bg-card shadow rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
            <p class="text-sm text-muted-foreground">Active Seller Wallets</p>
            <p class="text-xl sm:text-2xl font-bold text-primary mt-1">
              {{ activeWallets }}
            </p>
          </div>
          <div class="bg-card shadow rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
            <p class="text-sm text-muted-foreground">Total Profit From Revenue</p>
            <p class="text-xl sm:text-2xl font-bold text-primary mt-1">
              ₱{{ formatCurrency(totalProfit) }}
            </p>
          </div>
        </div>
        
        <!-- Admin Wallet Table Section -->
        <div class="bg-card shadow rounded-lg overflow-hidden">
          <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-border">
            <h3 class="text-base sm:text-lg font-medium text-foreground">Seller Wallets</h3>
            <p class="mt-1 text-xs sm:text-sm text-muted-foreground">Manage wallet balances and transactions for all sellers.</p>
          </div>
          
          <!-- Loading State -->
          <div v-if="isLoadingWallets" class="flex items-center justify-center h-40 sm:h-64 p-4 sm:p-6">
            <div class="text-center">
              <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent align-[-0.125em]"></div>
              <p class="mt-2 text-muted-foreground">Loading seller wallets...</p>
            </div>
          </div>
          
          <!-- Error State -->
          <div v-else-if="walletError" class="p-4 sm:p-6 text-center text-destructive">
            <p>{{ walletError }}</p>
            <button 
              @click="fetchSellerWallets" 
              class="mt-4 px-4 py-2 bg-primary text-primary-foreground rounded hover:bg-primary/90 transition-colors duration-200"
            >
              Retry
            </button>
          </div>
          
          <!-- Admin Wallet Table Component -->
          <AdminWalletTable
            v-else
            :wallets="sellers || []"
            :loading="isLoadingWallets"
            :error="walletError"
            @refresh="fetchSellerWallets"
            @update-wallet="updateWalletInState"
          />
        </div>
      </div>
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