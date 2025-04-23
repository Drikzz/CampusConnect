<template>
    <div class="w-full mt-10 mb-64 px-4 md:px-16">
      <!-- Flash Messages -->
      <div v-if="props.flash?.success" 
           class="mb-4 p-4 text-sm bg-primary/10 text-primary rounded-lg">
        {{ props.flash.success }}
      </div>
      <div v-if="props.flash?.error"
           class="mb-4 p-4 text-sm bg-destructive/10 text-destructive rounded-lg">
        {{ props.flash.error }}
      </div>

      <!-- Header with Role Badge -->
      <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-bold">My Dashboard</h1>
          <span v-if="isSeller" class="px-3 py-1 text-sm font-medium bg-primary/10 text-primary rounded-full">
            Seller
          </span>
        </div>
        <Link v-if="!isSeller" 
              :href="route('dashboard.become-seller')" 
              class="text-primary hover:text-primary/90 hover:underline">
          Become a Seller
        </Link>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <template v-if="isSeller">
          <StatCard title="Total Sales" :value="stats.totalSales" icon="money" type="money" />
          <StatCard title="Active Products" :value="stats.activeProducts" icon="box" />
          <StatCard title="Pending Orders" :value="stats.pendingOrders" icon="clock" />
        </template>
        <template v-else>
          <StatCard title="Total Orders" :value="stats.totalOrders" icon="shopping-bag" />
          <StatCard title="Wishlist Items" :value="stats.wishlistCount" icon="heart" />
          <StatCard title="Active Orders" :value="stats.activeOrders" icon="truck" />
        </template>
      </div>

      <!-- Main Content Grid -->
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 md:sticky md:top-4 md:h-[calc(100vh-8rem)]">
          <nav class="bg-card text-card-foreground rounded-lg shadow p-4">
            <NavSection title="Profile">
              <li>
                <Link :href="route('dashboard.profile')" 
                  :preserve-scroll="true"
                  class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                  :class="{ 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/profile') }">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>Profile</span>
                </Link>
              </li>
            </NavSection>

            <NavSection title="Shopping">
              <li>
                <Link :href="route('dashboard.orders')"
                  class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                  :class="{ 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/orders') }">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                  </svg>
                  <span>My Orders</span>
                </Link>
              </li>

              <li>
                <Link :href="route('dashboard.trades')"
                  class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                  :class="{ 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/trades') }">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                  </svg>
                  <span>My Trades</span>
                </Link>
              </li>

              <li>
                <Link :href="route('dashboard.wishlist')"
                  class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                  :class="{ 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/wishlist') }">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                  <span>Wishlist</span>
                </Link>
              </li>
            </NavSection>

            <template v-if="isSeller">
              <NavSection title="Seller Management">
                <li>
                  <Link :href="route('seller.meetup-locations')"
                    class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/seller/meetup-locations') }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Meetup Locations</span>
                  </Link>
                </li>
                
                <!-- Add Trade Offers Link -->
                <li>
                  <Link :href="route('seller.trade-offers')"
                    class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/seller/trade-offers') }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Trade Offers</span>
                  </Link>
                </li>

                <li>
                  <Link :href="route('seller.wallet.index')"
                    class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 
                      'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/seller/wallet'),
                      'cursor-pointer': true // Always allow clicking
                    }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span>My Wallet</span>
                    <span v-if="needsWalletSetup" 
                          class="ml-auto text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">
                      Setup Required
                    </span>
                  </Link>
                </li>
                
                <li>
                  <div class="relative group">
                    <Link 
                      :href="!needsWalletSetup ? route('seller.products') : '#'"
                      :class="[
                        'flex items-center gap-3 px-4 py-2 rounded-lg transition-colors',
                        !needsWalletSetup 
                          ? 'text-foreground hover:bg-muted' 
                          : 'text-muted-foreground cursor-not-allowed',
                        { 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/seller/products') }
                      ]">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                      <span>Products</span>
                    </Link>
                    <div v-if="needsWalletSetup"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded 
                                invisible group-hover:visible whitespace-nowrap">
                      Complete wallet setup to manage products
                    </div>
                  </div>
                </li>
                
                <li>
                  <div class="relative group">
                    <Link :href="hasActivatedWallet ? route('seller.orders') : '#'"
                          :class="[
                            'flex items-center gap-3 px-4 py-2 rounded-lg transition-colors',
                            hasActivatedWallet 
                              ? 'text-foreground hover:bg-muted' 
                              : 'text-muted-foreground cursor-not-allowed',
                            { 'bg-primary/10 text-primary': $page.url.startsWith('/dashboard/seller/orders') }
                          ]">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                      </svg>
                      <span>Orders</span>
                    </Link>
                    <div v-if="!hasActivatedWallet"
                         class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded 
                                invisible group-hover:visible whitespace-nowrap">
                      Activate your wallet to manage orders
                    </div>
                  </div>
                </li>
                
                <li>
                  <Link :href="route('seller.reviews')"
                    class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': route().current('seller.reviews') }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976-2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span>My Reviews</span>
                  </Link>
                </li>
              </NavSection>
            </template>
            <template v-else>
              <NavSection title="Seller">
                <li>
                  <Link :href="route('dashboard.become-seller')"
                    class="flex items-center gap-3 px-4 py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': route().current('dashboard.become-seller') }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Become a Seller</span>
                  </Link>
                </li>
              </NavSection>
            </template>

            <NavSection title="System">
              <form @submit.prevent="logout">
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  <span>Logout</span>
                </button>
              </form>
            </NavSection>
          </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
          <div class="bg-card text-card-foreground rounded-lg shadow">
            <div class="p-6">
              <slot />
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import StatCard from './Components/StatCard.vue'
import NavSection from './Components/NavSection.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true,
    default: () => ({})
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

// Add null checks for user properties
const isSeller = computed(() => props.user?.is_seller ?? false)

const page = usePage()

function formatCurrency(value) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(value)
}

function logout() {
  router.post(route('logout'))
}

// Update the wallet status computed properties
const hasActivatedWallet = computed(() => {
  return props.user?.is_seller && props.stats?.wallet?.is_activated || false
})

const needsWalletSetup = computed(() => {
  return props.user?.is_seller && (!props.stats?.wallet?.is_activated)
})
</script>

<style scoped>
/* Update any hardcoded colors to use theme variables */
:deep(.bg-primary-color\/10) {
  @apply bg-primary/10;
}

:deep(.text-primary-color) {
  @apply text-primary;
}

:deep(.hover\:bg-gray-50) {
  @apply hover:bg-muted;
}

:deep(.text-gray-700) {
  @apply text-foreground;
}

:deep(.text-gray-500) {
  @apply text-muted-foreground;
}
</style>
