<template>
  <div>
    <!-- Main Content Layout -->
    <div class="w-full mt-6 sm:mt-8 md:mt-10 mb-24 sm:mb-32 md:mb-64 px-4 sm:px-6 md:px-16">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" 
           class="mb-4 p-3 sm:p-4 text-xs sm:text-sm rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error"
           class="mb-4 p-3 sm:p-4 text-xs sm:text-sm rounded-lg bg-destructive/10 text-destructive">
        {{ $page.props.flash.error }}
      </div>

      <!-- Main Content Grid -->
      <div class="flex flex-col md:flex-row gap-4 sm:gap-6">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 md:sticky md:top-4 md:h-[calc(100vh-8rem)] mb-4 md:mb-0">
          <nav class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-3 sm:p-4 overflow-auto max-h-[50vh] md:max-h-full">
            <!-- Dashboard Section -->
            <div class="mb-3 sm:mb-4">
              <h3 class="px-3 sm:px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Dashboard</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.dashboard')"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/Dashboard') }">
                    <HomeIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Dashboard</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- User Management Section -->
            <div class="mb-3 sm:mb-4">
              <h3 class="px-3 sm:px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">User Management</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.users')"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/admin-userManagement') }">
                    <UsersIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Users</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Product Management Section -->
            <div class="mb-3 sm:mb-4">
              <h3 class="px-3 sm:px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Product Management</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.products')"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/admin-productManagement') }">
                    <ShoppingBagIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Products</span>
                  </Link>
                </li>
                 <li>
                  <Link 
                    :href="route('admin.categories-tags-management')"
                    class="flex items-center gap-3 px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-gray-50 text-red-600': $page.component.includes('Admin/admin-categoriestagsManagement') }">
                    <TagIcon class="w-5 h-5" />
                    <span>Categories & Tags</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Financial Section -->
            <div class="mb-3 sm:mb-4">
              <h3 class="px-3 sm:px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Financial</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="route('admin.transactions')"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/admin-transactions') }">
                    <CurrencyDollarIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Transactions</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="route('admin.wallet-requests')"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/WalletRequests') }">
                    <WalletIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Wallet Requests</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="route('admin.test')" 
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/test') || $page.component.includes('Admin/Wallet') }">
                    <BanknotesIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Wallet & Fees</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- Platform Section -->
            <div class="mb-3 sm:mb-4">
              <h3 class="px-3 sm:px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Platform</h3>
              <ul class="space-y-1">
                <li>
                  <Link 
                    :href="'#'"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/reports') }">
                    <ChartBarIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Reports & Complaints</span>
                  </Link>
                </li>
                <li>
                  <Link 
                    :href="route('admin.locations')"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground rounded-lg hover:bg-muted transition-colors"
                    :class="{ 'bg-primary/10 text-primary': $page.component.includes('Admin/admin-meetuploc') }">
                    <MapPinIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    <span class="text-sm">Meetup Locations</span>
                  </Link>
                </li>
              </ul>
            </div>

            <!-- System Section -->
            <div>
              <h3 class="px-3 sm:px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">System</h3>
              <ul class="space-y-1">
                <li>
                  <form @submit.prevent="logout">
                    <button 
                      type="submit" 
                      class="w-full flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                    >
                      <ArrowRightOnRectangleIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                      <span class="text-sm">Logout</span>
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
          <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border">
            <div class="p-4 sm:p-5 md:p-6">
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
import { computed } from 'vue'

const logout = () => {
  router.post(route('admin.logout'))
}

const adminAvatarUrl = computed(() => {
  try {
    // Try to use the user's avatar if available
    if (props.auth?.user?.profile_picture) {
      return `/storage/${props.auth.user.profile_picture}`;
    }
    // Fall back to default avatar from our public route
    return '/admin-avatar';
  } catch (e) {
    console.error('Error getting admin avatar URL', e);
    return '/admin-avatar';
  }
});

</script>
