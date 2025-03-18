<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3'; // Added router import

const activeButton = ref('');

const setActive = (buttonName) => {
    activeButton.value = buttonName;
    localStorage.setItem('activeButton', buttonName);
};

onMounted(() => {
    const stored = localStorage.getItem('activeButton');
    if (stored) {
        activeButton.value = stored;
    }
});

// Updated navigationItems to match routes from the main AdminLayout
const navigationItems = [
    { name: 'Dashboard', icon: 'bx-home', route: 'admin.dashboard' },
    { name: 'Users', icon: 'bx-user', route: 'admin.users' },
    { name: 'Products', icon: 'bx-package', route: 'admin.products' },
    { name: 'Wallet', icon: 'bx-wallet', route: 'admin.wallet' },
    { name: 'Wallet Requests', icon: 'bx-credit-card', route: 'admin.wallet-requests' },
    { name: 'Orders', icon: 'bx-transfer', route: 'admin.orders' },
    // Add a logout action that uses router
    { name: 'Logout', icon: 'bx-log-out', action: () => router.post(route('admin.logout')) }
];
</script>

<template>
    <!-- This component is deprecated. Please use @/Layouts/AdminLayout.vue instead -->
    <div class="bg-gray-100 min-h-screen text-gray-800">
        <div class="flex">
            <!-- Sidebar -->
            <div class="bg-white rounded-lg p-5 flex flex-col items-center gap-4 shadow-md sidebar">
                <template v-for="item in navigationItems" :key="item.name">
                    <Link v-if="!item.action"
                      :href="route(item.route)"
                      class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1"
                      :class="{ 'active': activeButton === item.name }"
                      :data-tooltip="item.name"
                      @click="setActive(item.name)">
                        <i :class="['bx', item.icon]"></i>
                    </Link>
                    <button v-else
                      class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1"
                      :class="{ 'active': activeButton === item.name }"
                      :data-tooltip="item.name"
                      @click="item.action">
                        <i :class="['bx', item.icon]"></i>
                    </button>
                </template>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-6 z-0">
                <div class="flex flex-col gap-4">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.active {
    background-color: #e54646;
    color: white;
    transform: translateY(-0.25rem);
}

.active i {
    color: white;
}

.sidebar div:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 100%;
    margin-left: 10px;
    padding: 5px 10px;
    background-color: #e54646;
    color: white;
    border-radius: 5px;
    white-space: nowrap;
    z-index: 50;
}

.sidebar {
    z-index: 50;
    position: sticky;
    top: 0;
    height: 100vh;
}

.navbar {
    position: sticky;
    top: 0;
    z-index: 40;
}
</style>
