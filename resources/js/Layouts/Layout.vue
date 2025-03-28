<script setup>
import { onMounted, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from '@/Components/ui/toast/use-toast';
import { Toaster } from '@/Components/ui/toast';
import RegistrationGuard from '@/Components/RegistrationGuard.vue';

const props = defineProps({
  auth: Object
});

const mobileMenuOpen = ref(false);
const searchText = ref('');

const handleLogoClick = () => {
    const loadingBar = document.getElementById('top-loading-bar');
    if (loadingBar) {
        loadingBar.style.transform = 'translateX(-100%)';
        loadingBar.style.opacity = '1';
        requestAnimationFrame(() => {
            loadingBar.style.transform = 'translateX(0)';
        });
    }
};

const handleSearch = () => {
    // Implement search functionality
    console.log('Searching for:', searchText.value);
};

onMounted(() => {
    // Initialize Alpine.js and other scripts
    if (typeof Alpine !== 'undefined') {
        Alpine.start();
    }
});

function getSocialIcon(platform) {
    switch (platform) {
        case 'facebook':
            return 'svg-facebook' // You'll need to implement these components or use an icon library
        case 'twitter':
            return 'svg-twitter'
        case 'instagram':
            return 'svg-instagram'
        case 'tiktok':
            return 'svg-tiktok'
        default:
            return null
    }
}

const newsletterEmail = ref('');
const socialIcons = {
    facebook: {
        viewBox: "0 0 512 512",
        path: "M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"
    },
    twitter: {
        viewBox: "0 0 512 512",
        path: "M459.4 151.7c.3 4.5 .3 9.1 .3 13.6 0 138.7-105.6 298.6-298.6 298.6-59.5 0-114.7-17.2-161.1-47.1 8.4 1 16.6 1.3 25.3 1.3 49.1 0 94.2-16.6 130.3-44.8-46.1-1-84.8-31.2-98.1-72.8 6.5 1 13 1.6 19.8 1.6 9.4 0 18.8-1.3 27.6-3.6-48.1-9.7-84.1-52-84.1-103v-1.3c14 7.8 30.2 12.7 47.4 13.3-28.3-18.8-46.8-51-46.8-87.4 0-19.5 5.2-37.4 14.3-53 51.7 63.7 129.3 105.3 216.4 109.8-1.6-7.8-2.6-15.9-2.6-24 0-57.8 46.8-104.9 104.9-104.9 30.2 0 57.5 12.7 76.7 33.1 23.7-4.5 46.5-13.3 66.6-25.3-7.8 24.4-24.4 44.8-46.1 57.8 21.1-2.3 41.6-8.1 60.4-16.2-14.3 20.8-32.2 39.3-52.6 54.3z"
    },
    instagram: {
        viewBox: "0 0 448 512",
        path: "M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"
    },
    tiktok: {
        viewBox: "0 0 448 512",
        path: "M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z"
    }
};

const footerSections = [
    {
        title: 'Company',
        titleSize: 'md',
        links: ['About', 'Features', 'Works', 'Career']
    },
    {
        title: 'Help',
        links: ['Customer Support', 'Delivery Details', 'Terms & Conditions', 'Privacy Policy']
    },
    {
        title: 'FAQ',
        links: ['Account', 'Manage Deliveries', 'Orders', 'Payments']
    }
];

const handleNewsletterSubmit = () => {
    // Implement newsletter submission logic
    console.log('Newsletter subscription:', newsletterEmail.value);
};

const page = usePage()
const { toast } = useToast()

// Watch for flash messages
watch(() => page.props.flash.toast, (flashMessage) => {
    if (flashMessage) {
        toast({
            variant: flashMessage.variant,
            title: flashMessage.title,
            description: flashMessage.description,
        })
    }
}, { immediate: true })
</script>

<template>
    <div>
        <!-- Add the registration guard component -->
        <RegistrationGuard />
        
        <div class="fixed top-4 right-4 w-full z-101">
            <Toaster></Toaster>
        </div>
        <header class="relative bg-primary-color shadow-sm">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 sm:h-24 items-center justify-between gap-4">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <Link :href="route('index')" class="flex flex-col items-center" @click="handleLogoClick">
                            <img src="/storage/app/public/imgs/campusconnect_btn.png" alt="logo" class="w-[180px] h-auto">
                        </Link>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex md:items-center space-x-6">
                        <Link :href="route('products')"
                            class="text-white hover:text-gray-200 px-5 py-2.5 text-base font-medium transition-colors rounded-lg hover:bg-white/10">
                            Shop Now
                        </Link>
                        <Link href="/trade"
                            class="text-white hover:text-gray-200 px-5 py-2.5 text-base font-medium transition-colors rounded-lg hover:bg-white/10">
                            Trade Now
                        </Link>
                    </div>

                    <!-- Search Bar -->
                    <div class="hidden md:block flex-1 max-w-md mx-4">
                        <form @submit.prevent="handleSearch" class="relative">
                            <input type="text" v-model="searchText" placeholder="Search for products..."
                                class="w-full rounded-full pl-12 pr-4 py-3 border-transparent focus:border-white focus:ring-white text-base bg-white/10 text-white placeholder-gray-300">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </form>
                    </div>

                    <!-- Right Navigation -->
                    <div class="flex items-center gap-2 sm:gap-4">
                        <!-- Wishlist -->
                        <Link v-if="auth?.user" href="/dashboard/wishlist"
                            class="text-white hover:text-gray-200 p-2.5 rounded-lg transition-colors hover:bg-white/10 fill-white">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Z" />
                            </svg>
                        </Link>

                        <!-- Messages -->
                        <Link v-if="auth?.user" href="/dashboard/messages"
                            class="text-white hover:text-gray-200 p-2.5 rounded-lg transition-colors hover:bg-white/10 fill-white">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M19,4h-1.101c-.465-2.279-2.485-4-4.899-4H5C2.243,0,0,2.243,0,5v12.854c0,.794.435,1.52,1.134,1.894.318.171.667.255,1.015.255.416,0,.831-.121,1.19-.36l2.95-1.967c.691,1.935,2.541,3.324,4.711,3.324h5.697l3.964,2.643c.36.24.774.361,1.19.361.348,0,.696-.085,1.015-.256.7-.374,1.134-1.1,1.134-1.894v-12.854c0-2.757-2.243-5-5-5Z" />
                            </svg>
                        </Link>

                        <!-- Profile/Auth Section -->
                        <div class="relative">
                            <template v-if="auth?.user">
                                <Link href="/dashboard/profile" 
                                    class="flex items-center gap-3 text-white hover:text-gray-200 p-2 rounded-lg transition-colors hover:bg-white/10">
                                    <div v-if="auth.user.profile_picture" class="h-10 w-10 rounded-full overflow-hidden">
                                        <img :src="`/storage/${auth.user.profile_picture}`" :alt="auth.user.first_name" 
                                            class="h-full w-full object-cover border-2 border-white">
                                    </div>
                                    <div v-else
                                        class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center border-2 border-white">
                                        <span class="text-white text-lg font-medium">{{ auth.user.first_name[0] }}</span>
                                    </div>
                                    <div class="hidden md:block">
                                        <p class="text-sm font-medium leading-tight">
                                            {{ auth.user.first_name }} {{ auth.user.last_name }}
                                        </p>
                                        <p class="text-xs text-white/70">{{ auth.user.username }}</p>
                                    </div>
                                </Link>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-2">
                                    <!-- Fix the route name from register.personal-info to register.details -->
                                    <Link :href="'/register'"
                                        class="text-white hover:text-gray-200 px-4 py-2 text-sm font-medium transition-colors rounded-lg hover:bg-white/10">
                                        Sign Up
                                    </Link>
                                    <Link :href="route('login')"
                                        class="text-primary-color bg-white hover:bg-gray-100 px-4 py-2 text-sm font-medium transition-colors rounded-lg shadow-sm">
                                        Login
                                    </Link>
                                </div>
                            </template>
                        </div>

                        <!-- Mobile menu button -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2.5 rounded-lg text-white hover:text-gray-200 hover:bg-white/10">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M18.71,8.21a1,1,0,0,0-1.42,0l-4.58,4.58a1,1,0,0,1-1.42,0L6.71,8.21a1,1,0,0,0-1.42,0,1,1,0,0,0,0,1.41l4.59,4.59a3,3,0,0,0,4.24,0l4.59-4.59A1,1,0,0,0,18.71,8.21Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Search -->
                <div class="md:hidden py-4">
                    <form @submit.prevent="handleSearch" class="relative">
                        <input type="text" v-model="searchText" placeholder="Search for products..."
                            class="w-full rounded-full pl-12 pr-4 py-3 border-transparent focus:border-white focus:ring-white text-base bg-white/10 text-white placeholder-gray-300">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </form>
                </div>

                <!-- Mobile menu -->
                <div v-show="mobileMenuOpen" class="md:hidden bg-white/5 rounded-lg mt-2">
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <Link href="/products"
                            class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-white/10">
                            Shop Now
                        </Link>
                        <Link href="/trade"
                            class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-white/10">
                            Trade Now
                        </Link>
                    </div>
                </div>
            </nav>
        </header>

        <!-- This is important - it renders the page content -->
        <slot />

        <Toaster />

        <footer class="w-full bg-footer flex flex-col items-center justify-center relative py-10 px-16">
            <!-- Newsletter Section -->
            <div class="flex justify-center items-center max-w-screen-lg py-6 px-8 bg-black rounded-xl absolute top-[-4rem]">
                <div>
                    <p class="font-Footer italic text-2xl text-white w-[26rem]">STAY UP TO DATE ABOUT OUR LATEST OFFERS</p>
                </div>

                <form @submit.prevent="handleNewsletterSubmit">
                    <div class="relative flex items-center focus-within:fill-black transition-colors mb-2">
                        <svg class="w-5 h-5 absolute ml-3 pointer-events-none" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path d="M19,1H5A5.006,5.006,0,0,0,0,6V18a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V6A5.006,5.006,0,0,0,19,1ZM5,3H19a3,3,0,0,1,2.78,1.887l-7.658,7.659a3.007,3.007,0,0,1-4.244,0L2.22,4.887A3,3,0,0,1,5,3ZM19,21H5a3,3,0,0,1-3-3V7.5L8.464,13.96a5.007,5.007,0,0,0,7.072,0L22,7.5V18A3,3,0,0,1,19,21Z" />
                        </svg>

                        <input type="email" v-model="newsletterEmail" autocomplete="off"
                            placeholder="Enter your Email Address"
                            class="pl-12 pr-3 px-3 py-2 w-[16rem] rounded-full text-black border-none outline-none font-Satoshi text-base">
                    </div>

                    <button type="submit"
                        class="px-3 py-2 w-[16rem] bg-white rounded-full text-black border-none outline-none font-Satoshi text-base">
                        Subscribe to Newsletter
                    </button>
                </form>
            </div>

            <!-- Footer Grid -->
            <div class="w-full h-64 mt-4 grid grid-cols-4 gap-16">
                <!-- Brand Column -->
                <div class="blackbox flex flex-col justify-center items-start h-full gap-4">
                    <svg class="w-32 h-auto fill-black" xmlns="http://www.w3.org/2000/svg" width="421" height="179"
                        viewBox="0 0 421 179">
                        <text id="Campus_Connect" data-name="Campus Connect" class="text-[72px] font-Header italic" y="58">Campus<tspan x="0"
                                dy="86.4">Connect</tspan></text>
                    </svg>

                    <div>
                        <p class="font-Satoshi text-sm">We have clothes that suits your style and which you're proud to wear. From women to men.</p>
                    </div>

                    <!-- Social Icons -->
                    <div class="flex justify-start items-center w-full gap-4">
                        <a v-for="(icon, index) in socialIcons" :key="index" href="#"
                            class="focus-within:opacity-35 focus-within:ring-2 focus-within:ring-black rounded-full">
                            <div class="w-10 h-10 bg-white rounded-full flex justify-center items-center hover:ring-2 hover:ring-black hover:transition-all">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" :viewBox="icon.viewBox">
                                    <path :d="icon.path" />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Footer Links -->
                <template v-for="(section, index) in footerSections" :key="index">
                    <div class="flex flex-col justify-center items-start h-full gap-4">
                        <div>
                            <p :class="`font-Satoshi text-${section.titleSize || '[18px]'}`">{{ section.title }}</p>
                        </div>
                        <div v-for="(link, linkIndex) in section.links" :key="linkIndex">
                            <a href="#" class="hover:underline focus:underline">
                                <p class="font-Satoshi text-[16px]">{{ link }}</p>
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Copyright -->
            <div class="w-full items-start mt-4">
                <p class="font-Satoshi text-[14px]">CampusConnect &copy; {{ new Date().getFullYear() }}, All Rights Reserved</p>
            </div>
        </footer>
    </div>
</template>

<style>
/* Add any necessary styles here */
.active {
    background-color: #e54646;
    color: white;
    transform: translateY(-0.25rem);
}

/* Add other existing styles as needed */
</style>