<script setup>
import { useForm } from '@inertiajs/vue3';
import { inject } from 'vue';
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handling
        }
    });
};

const page = usePage();
const toast = inject('globalToast', null);

// Add this watcher for flash messages
watch(() => page.props.flash.message, (message) => {
    if (message && toast) {
        toast({
            variant: 'default',
            title: 'Notification',
            description: message,
        });
    }
}, { immediate: true }); // immediate: true ensures it runs on mount too

// Add this form for logout
const logoutForm = useForm({});

const handleLogout = () => {
    logoutForm.post(route('logout'));
};
</script>

<template>
    <!-- Add toast container at the top level -->
    <div class="relative">

        <!-- Background and main layout -->
        <div class="background w-full h-full absolute z-0 dark:bg-black dark:bg-opacity-80"></div>

        <div class="w-full min-h-screen px-4 sm:px-8 md:px-16 pt-8 sm:pt-16 pb-16 sm:pb-32 flex flex-col md:flex-row justify-center items-center relative z-10">
            <!-- Logo Container -->
            <div class="w-full md:w-1/2 flex justify-center mb-8 md:mb-0">
                <img class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
            </div>

            <!-- Verification Card Container -->
            <div class="flex flex-col justify-center items-center w-full md:w-1/2 px-4">
                <Card class="w-full max-w-[40rem] shadow-lg bg-background dark:bg-gray-900 dark:border-gray-700">
                    <CardContent class="p-6 sm:p-10">
                        <div class="text-center">
                            <div class="mb-6 sm:mb-8">
                                <p class="font-FontSpring-bold text-2xl sm:text-3xl text-primary-color">Verify Your Email</p>
                            </div>
                            
                            <div class="flex justify-center mb-6">
                                <div class="w-16 h-16 sm:w-24 sm:h-24 flex items-center justify-center rounded-full bg-primary-color/10 dark:bg-primary-color/20 text-primary-color">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                    </svg>
                                </div>
                            </div>

                            <p class="text-foreground dark:text-gray-300 mb-4 sm:mb-6 text-sm sm:text-base">
                                Thanks for signing up! Before getting started, could you verify your email address by clicking on 
                                the link we just emailed to you?
                            </p>

                            <p class="text-muted-foreground dark:text-gray-400 mb-6 sm:mb-8 text-sm sm:text-base">
                                If you didn't receive the email, click the button below to request another.
                            </p>

                            <form @submit.prevent="submit">
                                <Button 
                                    type="submit" 
                                    class="w-full bg-primary-color text-white hover:bg-opacity-90 transition-all"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
                                </Button>
                            </form>

                            <!-- Logout Button -->
                            <div class="mt-6 pt-6 border-t border-border dark:border-gray-700">
                                <Button 
                                    type="button" 
                                    variant="outline"
                                    class="w-full border-border dark:border-gray-700 text-foreground dark:text-gray-300"
                                    @click="handleLogout"
                                    :disabled="logoutForm.processing"
                                >
                                    {{ logoutForm.processing ? 'Logging out...' : 'Log Out' }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
