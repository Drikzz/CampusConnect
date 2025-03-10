<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Left side navigation -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-gray-900">Admin Dashboard</h1>
            </div>
          </div>
          
          <!-- Right side -->
          <div class="flex items-center">
            <button 
              @click="logout" 
              class="ml-4 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Sidebar Navigation -->
    <div class="flex">
      <div class="w-64 min-h-screen bg-white shadow-sm">
        <nav class="mt-5 px-2">
          <Link 
            v-for="(item, index) in navigationItems" 
            :key="index"
            :href="item.route"
            class="mb-1 px-3 py-2 text-sm font-medium text-gray-900 rounded-md hover:bg-gray-100 hover:text-gray-900 group flex items-center"
            :class="{ 'bg-gray-100': route().current(item.routeName) }"
          >
            <component :is="item.icon" class="w-5 h-5 mr-2" />
            {{ item.name }}
          </Link>
        </nav>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 min-h-screen bg-gray-50">
        <div class="py-6">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { 
  UsersIcon, ShoppingBagIcon, CurrencyDollarIcon, 
  ChartBarIcon, WalletIcon, MapPinIcon, TagIcon,
  HomeIcon, BanknotesIcon // Remove duplicate BanknotesIcon
} from '@heroicons/vue/24/outline'

const navigationItems = [
  { name: 'Dashboard', icon: HomeIcon, route: route('admin.dashboard'), routeName: 'admin.dashboard' },
  { name: 'User Management', icon: UsersIcon, route: route('admin.users'), routeName: 'admin.users' },
  { name: 'Product Management', icon: ShoppingBagIcon, route: route('admin.products'), routeName: 'admin.products' },
  { name: 'Transactions', icon: CurrencyDollarIcon, route: route('admin.orders'), routeName: 'admin.orders' },
  { name: 'Wallet Requests', icon: WalletIcon, route: route('admin.wallet-requests'), routeName: 'admin.wallet-requests' },
  { name: 'Wallet & Fees', icon: BanknotesIcon, route: route('admin.wallet'), routeName: 'admin.wallet' },
  { name: 'Reports & Complaints', icon: ChartBarIcon, route: '#', routeName: 'admin.reports' },
  { name: 'Meetup Locations', icon: MapPinIcon, route: '#', routeName: 'admin.locations' },
  { name: 'Categories & Tags', icon: TagIcon, route: '#', routeName: 'admin.categories' }
]

const logout = () => {
  router.post(route('admin.logout'))
}
</script>
