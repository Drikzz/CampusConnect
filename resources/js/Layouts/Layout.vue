<script setup lang="ts">
import { ref, provide, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger, SheetDescription } from '@/Components/ui/sheet';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu';
import { useColorMode } from '@vueuse/core';
import RegistrationGuard from '@/Components/RegistrationGuard.vue';
import { Input } from '@/Components/ui/input';
import { Bars3Icon, SunIcon, MoonIcon, ComputerDesktopIcon } from '@heroicons/vue/24/outline';
import { SearchIcon } from 'lucide-vue-next';
import { Separator } from '@/Components/ui/separator';
import { Toaster } from '@/Components/ui/toast'; // Import Toaster directly
import { ToastProvider } from '@/Components/ui/toast';
import { useToast } from '@/Components/ui/toast/use-toast'; // Import useToast

const props = defineProps({
  auth: Object
});

// Add toast functionality
const { toast } = useToast();
provide('globalToast', toast); // Make toast available to all child components

const isOpen = ref(false);

// Add function to close sidebar
const closeSidebar = () => {
  isOpen.value = false;
};

const mode = useColorMode();
const page = usePage();
const searchQuery = ref('');
const newsletterEmail = ref('');

// Enhance theme toggle with system option
const themeOptions = {
  light: { icon: SunIcon, label: 'Light' },
  dark: { icon: MoonIcon, label: 'Dark' },
  system: { icon: ComputerDesktopIcon, label: 'System' }
};

// Update theme toggle to support system preference
const toggleTheme = () => {
  const values = ['light', 'dark', 'system'];
  const currentIndex = values.indexOf(mode.value);
  mode.value = values[(currentIndex + 1) % values.length];
};

const handleSearch = () => {
  console.log('Searching:', searchQuery.value);
};

// Updated footer sections with proper links
const footerSections = [
  {
    title: 'Company',
    links: [
      { label: 'About', href: '/about' },
      { label: 'Features', href: '/features' },
      { label: 'Careers', href: '/careers' },
    ]
  },
  {
    title: 'Help',
    links: [
      { label: 'Support', href: '/support' },
      { label: 'Terms', href: '/terms' },
      { label: 'Privacy', href: '/privacy' }
    ]
  },
  {
    title: 'Resources',
    links: [
      { label: 'Blog', href: '/blog' },
      { label: 'Documentation', href: '/docs' },
      { label: 'Contact', href: '/contact' }
    ]
  }
];

// Add social icons data
const socialIcons = [
  {
    name: 'facebook',
    component: 'FacebookIcon',
    href: '#'
  },
  {
    name: 'twitter',
    component: 'TwitterIcon',
    href: '#'
  },
  {
    name: 'instagram',
    component: 'InstagramIcon',
    href: '#'
  }
];

// Add flash message handling
watch(() => page.props.flash.toast, (flashToast) => {
  if (flashToast) {
    toast({
      variant: flashToast.variant || 'default',
      title: flashToast.title || '',
      description: flashToast.description || '',
    });
  }
}, { immediate: true });
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Add the registration guard component -->
    <RegistrationGuard />
    
    <!-- Add ToastWrapper with high z-index -->
    <Toaster />

    <!-- Sticky Header -->
    <header class="sticky top-0 z-50 w-full bg-primary">
      <div class="container flex h-16 items-center justify-between px-4 md:px-16">
        <!-- Logo -->
        <Link :href="route('index')" class="flex items-center space-x-2">
          <div class="flex flex-col items-start">
            <span class="font-Header italic text-white text-xl leading-none">Campus</span>
            <span class="font-Header italic text-white text-xl leading-none">Connect</span>
          </div>
        </Link>

        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center space-x-4">
          <Button 
            variant="ghost" 
            asChild
            class="text-white hover:text-white hover:bg-white/10"
          >
            <Link :href="route('products')">Shop Now</Link>
          </Button>
          <Button 
            variant="ghost"
            asChild
            class="text-white hover:text-white hover:bg-white/10"
          >
            <Link href="/trade">Trade Now</Link>
          </Button>
        </nav>

        <!-- Right side items -->
        <div class="flex items-center space-x-4">
          <!-- Theme Toggle with Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button 
                variant="ghost" 
                size="icon"
                class="text-white hover:bg-white/10 hover:text-white"
              >
                <SunIcon v-if="mode === 'light'" class="h-5 w-5" />
                <MoonIcon v-if="mode === 'dark'" class="h-5 w-5" />
                <component 
                  :is="themeOptions.system.icon" 
                  v-if="mode === 'system'" 
                  class="h-5 w-5"
                />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem @click="mode = 'light'">
                <SunIcon class="mr-2 h-4 w-4" />
                <span>Light</span>
              </DropdownMenuItem>
              <DropdownMenuItem @click="mode = 'dark'">
                <MoonIcon class="mr-2 h-4 w-4" />
                <span>Dark</span>
              </DropdownMenuItem>
              <DropdownMenuItem @click="mode = 'system'">
                <ComputerDesktopIcon class="mr-2 h-4 w-4" />
                <span>System</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <!-- Guest Navigation for desktop -->
          <div v-if="!auth?.user" class="hidden lg:flex items-center space-x-4">
            <Button 
              variant="ghost"
              asChild
              class="text-white hover:text-white hover:bg-white/10"
            >
              <Link :href="route('login')">Login</Link>
            </Button>
            <Button 
              variant="secondary"
              asChild
              class="bg-white text-primary hover:bg-white/90"
            >
              <Link :href="route('register.personal-info')">Sign Up</Link>
            </Button>
          </div>

          <!-- User Menu for desktop -->
          <div v-if="auth?.user" class="hidden lg:flex items-center space-x-4">
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
          </div>

          <!-- Mobile/Tablet Menu Button -->
          <Sheet v-model:open="isOpen">
            <SheetTrigger asChild>
              <Button 
                variant="ghost" 
                size="icon" 
                class="lg:hidden text-white hover:bg-white/10"
              >
                <Bars3Icon class="h-6 w-6" />
                <span class="sr-only">Open menu</span>
              </Button>
            </SheetTrigger>
            <SheetContent side="right" class="w-[300px] sm:w-[400px]">
              <SheetHeader>
                <SheetTitle>Menu</SheetTitle>
                <SheetDescription>
                  Access your account and navigation
                </SheetDescription>
              </SheetHeader>
              
              <!-- Move search to top of sheet -->
              <div class="pt-6 pb-4">
                <form @submit.prevent="handleSearch">
                  <div class="relative">
                    <Input 
                      v-model="searchQuery"
                      type="search"
                      placeholder="Search products..."
                      class="w-full pr-10"
                    />
                    <Button 
                      type="submit"
                      variant="ghost" 
                      size="icon"
                      class="absolute right-0 top-0"
                    >
                      <SearchIcon class="h-4 w-4" />
                    </Button>
                  </div>
                </form>
              </div>

              <div class="grid gap-4 py-4">
                <!-- Mobile Navigation - Update to close sidebar on click -->
                <Button 
                  variant="ghost" 
                  asChild 
                  class="w-full justify-start"
                  @click="closeSidebar"
                >
                  <Link :href="route('products')">Shop Now</Link>
                </Button>
                <Button 
                  variant="ghost" 
                  asChild 
                  class="w-full justify-start"
                  @click="closeSidebar"
                >
                  <Link href="/trade">Trade Now</Link>
                </Button>

                <!-- Auth links for mobile - Update to close sidebar on click -->
                <Separator />
                <div class="space-y-4">
                  <template v-if="!auth?.user">
                    <Button variant="outline" asChild class="w-full" @click="closeSidebar">
                      <Link :href="route('login')">Login</Link>
                    </Button>
                    <Button variant="default" asChild class="w-full" @click="closeSidebar">
                      <Link :href="route('register.personal-info')">Sign Up</Link>
                    </Button>
                  </template>
                  <template v-else>
                    <Button variant="outline" asChild @click="closeSidebar">
                      <Link href="/dashboard/profile">My Profile</Link>
                    </Button>
                  </template>
                </div>
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-background border-t px-4 sm:px-8 md:px-16">
      <!-- Newsletter Section -->
      <div class="container py-12">
        <div class="relative">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-muted"></div>
          </div>
          <div class="relative flex justify-center">
            <span class="bg-background px-4 text-sm text-muted-foreground">Stay updated</span>
          </div>
        </div>

        <div class="mt-8 mx-auto max-w-xl text-center">
          <h2 class="text-2xl font-Header italic text-foreground">Subscribe to our newsletter</h2>
          <p class="mt-4 text-muted-foreground">Get the latest updates about our products and services</p>
          <form class="mt-6 sm:flex sm:max-w-md sm:mx-auto">
            <Input 
              type="email"
              v-model="newsletterEmail"
              placeholder="Enter your email"
              class="w-full"
            />
            <div class="mt-3 sm:mt-0 sm:ml-3">
              <Button type="submit">Subscribe</Button>
            </div>
          </form>
        </div>
      </div>

      <!-- Links Section -->
      <div class="container py-8">
        <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
          <div class="space-y-6">
            <div class="flex flex-col">
              <span class="font-Header italic text-2xl text-foreground">Campus</span>
              <span class="font-Header italic text-2xl text-foreground">Connect</span>
            </div>
            <p class="text-sm text-muted-foreground">
              The trusted marketplace for students and alumni.
            </p>
            <div class="flex space-x-4">
              <!-- Social Links -->
              <Button v-for="icon in socialIcons" 
                      :key="icon.name"
                      variant="ghost" 
                      size="icon"
              >
                <component :is="icon.component" class="h-4 w-4" />
              </Button>
            </div>
          </div>

          <div v-for="section in footerSections" :key="section.title">
            <h3 class="text-sm font-semibold text-foreground mb-4">{{ section.title }}</h3>
            <ul class="space-y-3">
              <li v-for="link in section.links" :key="link">
                <Link :href="link.href" 
                      class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                  {{ link.label }}
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Copyright -->
      <div class="border-t">
        <div class="container py-6">
          <p class="text-center text-sm text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} CampusConnect. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Update header styles */
.bg-primary {
  background-color: hsl(var(--primary));
}

/* Remove any border styles */
header {
  border: none;
}

/* Update input styles for better contrast */
:deep(.bg-white\/10) {
  background-color: rgb(255 255 255 / 0.1);
}

:deep(.placeholder\:text-white\/70::placeholder) {
  color: rgb(255 255 255 / 0.7);
}

/* Improve backdrop blur */
@supports (backdrop-filter: blur(20px)) {
  .supports-backdrop-blur {
    background: hsl(var(--primary)/.8);
    backdrop-filter: blur(20px);
  }
}

/* Add these styles to ensure proper text colors */
:deep(.text-white) {
  color: white !important;
}

:deep(.hover\:bg-white\/10:hover) {
  background-color: rgb(255 255 255 / 0.1) !important;
}

:deep(.hover\:text-white:hover) {
  color: white !important;
}

/* Add other existing styles as needed */
.active {
  background-color: #e54646;
  color: white;
  transform: translateY(-0.25rem);
}

/* Add styles for the stacked logo text */
.font-Header {
  letter-spacing: -0.02em;
}

/* Update search input styles */
:deep(.pl-10) {
  padding-left: 2.5rem;
}

:deep(.pointer-events-none) {
  pointer-events: none;
}
</style>