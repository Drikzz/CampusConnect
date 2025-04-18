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

// Also check for simple message
if (page.props.flash.message) {
    toast({
        variant: 'default',
        title: 'Notification',
        description: page.props.flash.message,
    });
}

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
        <div class="background w-full h-full absolute z-0"></div>

        <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">
            <!-- Logo Container -->
            <div class="w-1/2">
                <img class="w-[30rem] h-[30rem]" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
            </div>

            <!-- Verification Card Container -->
            <div class="flex flex-col justify-center items-center w-1/2">
                <Card class="w-[40rem] shadow-lg">
                    <CardContent class="p-10">
                        <div class="text-center">
                            <div class="mb-8">
                                <p class="font-FontSpring-bold text-3xl text-primary-color">Verify Your Email</p>
                            </div>
                            
                            <div class="flex justify-center mb-6">
                                <div class="w-24 h-24 flex items-center justify-center rounded-full bg-blue-50 text-primary-color">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                    </svg>
                                </div>
                            </div>

                            <p class="text-gray-600 mb-6">
                                Thanks for signing up! Before getting started, could you verify your email address by clicking on 
                                the link we just emailed to you?
                            </p>

                            <p class="text-gray-600 mb-8">
                                If you didn't receive the email, click the button below to request another.
                            </p>

                            <form @submit.prevent="submit">
                                <Button 
                                    type="submit" 
                                    class="w-full bg-primary-color hover:bg-opacity-90 transition-all"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
                                </Button>
                            </form>

                            <!-- Logout Button -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <Button 
                                    type="button" 
                                    variant="outline"
                                    class="w-full border-gray-300 text-gray-700"
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
