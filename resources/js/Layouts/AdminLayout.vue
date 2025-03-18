<template>
  <div>
   
    <!-- Main Content Layout -->
    <div class="w-full mt-10 mb-64 px-4 md:px-16">
      <!-- Flash Messages (can be added if needed) -->
      <div v-if="$page.props.flash?.success" 
           class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error"
           class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
        {{ $page.props.flash.error }}
      </div>

      <!-- Main Content Grid -->
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 md:sticky md:top-4 md:h-[calc(100vh-8rem)]">
          <nav class="bg-white rounded-lg shadow p-4">
            <!-- Dashboard Section -->
            <div class="mb-4">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Dashboard</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.dashboard')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.dashboard') }">
                    <HomeIcon class="w-5 h-5" />
                    <span>Dashboard</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- User Management Section -->
            <div class="mb-4">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">User Management</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.users')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.users') }">
                    <UsersIcon class="w-5 h-5" />
                    <span>Users</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Product Management Section -->
            <div class="mb-4">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Product Management</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.products')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.products') }">
                    <ShoppingBagIcon class="w-5 h-5" />
                    <span>Products</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="'#'"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.categories') }">
                    <TagIcon class="w-5 h-5" />
                    <span>Categories & Tags</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Financial Section -->
            <div class="mb-4">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Financial</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.orders')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.orders') }">
                    <CurrencyDollarIcon class="w-5 h-5" />
                    <span>Transactions</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="route('admin.wallet-requests')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.wallet-requests') }">
                    <WalletIcon class="w-5 h-5" />
                    <span>Wallet Requests</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="route('admin.wallet')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.wallet') }">
                    <BanknotesIcon class="w-5 h-5" />
                    <span>Wallet & Fees</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Platform Section -->
            <div class="mb-4">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Platform</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="'#'"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.reports') }">
                    <ChartBarIcon class="w-5 h-5" />
                    <span>Reports & Complaints</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="'#'"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red': route().current('admin.locations') }">
                    <MapPinIcon class="w-5 h-5" />
                    <span>Meetup Locations</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- System Section -->
            <div class="mb-4">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">System</h3>
              <ul class="space-y-1">
                <li>
                  <form @submit.prevent="logout">
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                      <ArrowRightOnRectangleIcon class="w-5 h-5" />
                      <span>Logout</span>
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
          <div class="bg-white rounded-lg shadow">
            <div class="p-6">
              <slot />
            </div>
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
  HomeIcon, BanknotesIcon, ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const logout = () => {
  router.post(route('admin.logout'))
}
</script>
