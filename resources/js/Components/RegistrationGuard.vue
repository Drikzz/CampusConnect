<script setup>
import { onMounted } from 'vue';
import { clearRegistrationData, isRegistrationRoute } from '@/Utils/registrationManager';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    forceCleanup: {
        type: Boolean,
        default: false
    }
});

onMounted(() => {
    // Clear immediately if forced
    if (props.forceCleanup) {
        clearRegistrationData();
    }
    
    // Add listener for navigation
    router.on('navigate', (event) => {
        const currentPath = window.location.pathname;
        const targetPath = new URL(event.detail.page.url, window.location.origin).pathname;
        
        // Only clear when navigating from registration to non-registration
        if (isRegistrationRoute(currentPath) && !isRegistrationRoute(targetPath)) {
            clearRegistrationData();
        }
    });
});
</script>

<template>
    <!-- This is an invisible component that doesn't render anything -->
</template>
