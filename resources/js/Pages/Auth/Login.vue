<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, inject } from 'vue';

const form = useForm({
    username: '',
    wmsu_email: '',
    password: '',
    remember: false
});

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// Add watchers for form fields to clear errors when values change
watch(() => form.username, () => {
    if (form.errors.username) delete form.errors.username;
});

watch(() => form.wmsu_email, () => {
    if (form.errors.wmsu_email) delete form.errors.wmsu_email;
});

watch(() => form.password, () => {
    if (form.errors.password) delete form.errors.password;
});

// Helper function to clear a specific field error
const clearFieldError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const submit = () => {
    form.post(route('login'), {
        preserveScroll: true,
        onError: () => {
            form.reset('password');
        }
    });
};

const page = usePage();
// Get the globalToast from the layout via inject
const toast = inject('globalToast', null);

// Watch for flash messages
watch(() => page.props.flash.toast, (flashToast) => {
    if (flashToast && toast) {
        toast({
            variant: flashToast.variant,
            title: flashToast.title,
            description: flashToast.description,
        });
    }
}, { immediate: true });
</script>

<template>
    <!-- Position the Toaster above everything else with proper z-index -->
    <div class="relative">

        <!-- Rest of your content with lower z-index -->
        <div class="background w-full h-full absolute overflow-hidden z-0 dark:bg-black dark:bg-opacity-80"></div>

        <!-- Center the form container -->
        <div class="w-full min-h-screen px-4 sm:px-8 md:px-16 pt-8 sm:pt-16 pb-16 sm:pb-32 flex flex-col justify-center items-center relative z-10">
            <!-- Form Container -->
            <div class="flex flex-col justify-center items-center z-10 w-full max-w-[30rem]">
                <!-- Add smaller logo here -->
                <div class="flex justify-center mb-6">
                    <img class="w-24 h-24 sm:w-32 sm:h-32" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
                </div>

                <div class="mb-4 text-center">
                    <p class="font-FontSpring-bold text-lg sm:text-xl text-white">
                        Welcome Back to Campus Connect
                    </p>
                </div>

                <!-- Changed bg-slate-100 to bg-background to match registration forms -->
                <div class="w-full h-auto gap-10 bg-background dark:bg-gray-900 p-6 sm:p-10 rounded-sm">
                    <form @submit.prevent="submit">
                        <div class="w-full">
                            <!-- Username Field -->
                            <div class="relative">
                                <label for="username" class="block mb-2 text-sm font-medium text-black dark:text-white">Username:</label>
                                <div class="relative mb-6">
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M9.5,2.5c0-1.381,1.119-2.5,2.5-2.5s2.5,1.119,2.5,2.5-1.119,2.5-2.5,2.5-2.5-1.119-2.5-2.5Zm7.5,7.5v3c0,1.474-.81,2.75-2,3.444v6.556c0,.552-.447,1-1,1s-1-.448-1-1v-6h-2v6c0,.552-.447,1-1,1s-1-.448-1-1v-6.556c-1.19-.694-2-1.97-2-3.444v-3c0-2.206,1.794-4,4-4h2c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2h-2c-1.103,0-2,.897-2,2v3c0,1.103,.897,2,2,2h2c1.103,0,2-.897,2-2v-3Z" />
                                            </svg>
                                        </div>
                                        <input v-model="form.username" type="text" id="username"
                                            :disabled="form.processing"
                                            @focus="clearFieldError('username')"
                                            @input="clearFieldError('username')"
                                            class="bg-gray-50 dark:bg-gray-800 border border-border dark:border-gray-700 text-black dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 text-sm rounded-lg focus:ring-secondary focus:border-secondary focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed"
                                            :class="{ 'ring-2 ring-destructive ring-offset-1': form.errors.username }"
                                            placeholder="juancruz@gmail.com">
                                    </div>
                                    <div v-if="form.errors.username" class="w-full flex justify-end items-center">
                                        <p class="text-destructive text-xs sm:text-sm">{{ form.errors.username }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- WMSU Email Field -->
                            <div class="relative">
                                <label for="wmsu_email" class="block mb-2 text-sm font-medium text-black dark:text-white">
                                    WMSU Email: (If available)
                                </label>
                                <div class="relative mb-6">
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 text-primary-color" aria-hidden="true" fill="currentColor" viewBox="0 0 20 16">
                                                <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z" />
                                                <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z" />
                                            </svg>
                                        </div>
                                        <input v-model="form.wmsu_email" id="wmsu_email"
                                            :disabled="form.processing"
                                            @focus="clearFieldError('wmsu_email')"
                                            @input="clearFieldError('wmsu_email')"
                                            class="bg-gray-50 dark:bg-gray-800 border border-border dark:border-gray-700 text-black dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 text-sm rounded-lg focus:ring-secondary focus:border-secondary focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed"
                                            :class="{ 'ring-2 ring-destructive ring-offset-1': form.errors.wmsu_email }"
                                            placeholder="eh1234000@wmsu.edu.ph">
                                    </div>
                                    <div v-if="form.errors.wmsu_email" class="w-full flex justify-end items-center">
                                        <p class="text-destructive text-xs sm:text-sm">{{ form.errors.wmsu_email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="relative">
                                <label for="password" class="block mb-2 text-sm font-medium text-black dark:text-white">Password:</label>
                                <div class="relative mb-6">
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M7.505,24A7.5,7.5,0,0,1,5.469,9.283,7.368,7.368,0,0,1,9.35,9.235l7.908-7.906A4.5,4.5,0,0,1,20.464,0h0A3.539,3.539,0,0,1,24,3.536a4.508,4.508,0,0,1-1.328,3.207L22,7.415A2.014,2.014,0,0,1,20.586,8H19V9a2,2,0,0,1-2,2H16v1.586A1.986,1.986,0,0,1,15.414,14l-.65.65a7.334,7.334,0,0,1-.047,3.88,7.529,7.529,0,0,1-6.428,5.429A7.654,7.654,0,0,1,7.505,24Zm0-13a5.5,5.5,0,1,0,5.289,6.99,5.4,5.4,0,0,0-.1-3.3,1,1,0,0,1,.238-1.035L14,12.586V11a2,2,0,0,1,2-2h1V8a2,2,0,0,1,2-2h1.586l.672-.672A2.519,2.519,0,0,0,22,3.536,1.537,1.537,0,0,0,20.465,2a2.52,2.52,0,0,0-1.793.743l-8.331,8.33a1,1,0,0,1-1.036.237A5.462,5.462,0,0,0,7.5,11ZM5,18a1,1,0,1,0,1-1A1,1,0,0,0,5,18Z" />
                                            </svg>
                                        </div>
                                        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" id="password"
                                            :disabled="form.processing"
                                            @focus="clearFieldError('password')"
                                            @input="clearFieldError('password')"
                                            class="bg-gray-50 dark:bg-gray-800 border border-border dark:border-gray-700 text-black dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 text-sm rounded-lg focus:ring-secondary focus:border-secondary focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 pr-10 disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed"
                                            :class="{ 'ring-2 ring-destructive ring-offset-1': form.errors.password }"
                                            placeholder="password123">
                                        <button type="button" @click="togglePassword"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                            <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'w-5 h-5']"></i>
                                        </button>
                                    </div>
                                    <div class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-center">
                                        <a href="#" class="text-sm text-primary-color">Forgot password?</a>
                                        <p v-if="form.errors.password" class="text-destructive text-xs sm:text-sm mt-1 sm:mt-0">{{ form.errors.password }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-4">
                                <div class="flex justify-start items-center gap-2">
                                    <input v-model="form.remember" type="checkbox" id="remember"
                                        :disabled="form.processing"
                                        class="w-4 h-4 appearance-none border border-border dark:border-gray-700 rounded checked:bg-primary-color checked:accent-primary-color focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2 cursor-pointer transition-all duration-200 disabled:cursor-not-allowed">
                                    <label for="remember" class="text-gray-700 dark:text-gray-300">Remember Me</label>
                                </div>
                            </div>

                            <!-- Login Button -->
                            <div class="mb-4">
                                <button type="submit" :disabled="form.processing"
                                    class="w-full bg-primary-color text-white rounded-lg py-2.5 mt-4 disabled:opacity-75 disabled:cursor-not-allowed">
                                    {{ form.processing ? 'Logging in...' : 'Login' }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Register Link -->
                    <div class="w-full flex justify-center sm:justify-end items-center mt-4"> <!-- Added mt-4 for spacing -->
                        <p class="text-sm text-primary-color">
                            Don't have an account?
                            <Link href="/register/personal-info" class="hover:underline">Register here</Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
