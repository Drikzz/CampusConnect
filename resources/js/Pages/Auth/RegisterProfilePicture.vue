<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, inject } from 'vue';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Card, CardContent } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    registrationData: Object,
    accountData: Object
});

const form = useForm({
    profile_picture: null,
});

const previewUrl = ref(null);

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File is too large! Please select an image under 2MB.');
            event.target.value = '';
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            alert('Please upload only JPG, JPEG, or PNG files.');
            event.target.value = '';
            return;
        }

        // Create preview
        previewUrl.value = URL.createObjectURL(file);
        form.profile_picture = file;
    }
};

const removeImage = () => {
    form.profile_picture = null;
    previewUrl.value = null;
};

const submitWithPhoto = () => {
    if (!form.profile_picture) {
        toast({
            variant: 'destructive',
            title: 'Error',
            description: 'Please upload a profile picture first.'
        });
        return;
    }
    
    submitRegistration();
};

const skipStep = () => {
    // Submit without a profile picture
    submitRegistration();
};

const submitRegistration = () => {
    // Send all data to complete registration
    form.post(route('register.complete'), {
        onProgress: (progress) => {
            form.progress = progress;
        },
        onSuccess: () => {
            form.reset();
            previewUrl.value = null;
        },
        onError: (errors) => {
            console.error('Registration errors:', errors);
        }
    });
};

const page = usePage();
// For client-side validation only, get the global toast function
const toast = inject('globalToast', null);

// Add watcher for flash messages
watch(() => page.props.flash.message, (message) => {
    if (message && toast) {
        toast({
            variant: 'default',
            title: 'Notification',
            description: message,
        });
    }
}, { immediate: true });

// Also watch for flash errors if needed
watch(() => page.props.flash.error, (error) => {
    if (error && toast) {
        toast({
            variant: 'destructive',
            title: 'Error',
            description: error,
        });
    }
}, { immediate: true });

onMounted(() => {
    // Reset scroll position when component mounts
    window.scrollTo(0, 0);
});
</script>

<template>
    <div class="relative">

        <div class="background w-full h-full absolute z-0 dark:bg-black dark:bg-opacity-80"></div>

        <!-- Center the form container -->
        <div class="w-full min-h-screen px-4 sm:px-8 md:px-16 pt-8 sm:pt-16 pb-16 sm:pb-32 flex flex-col justify-center items-center relative z-10">
            <!-- Form Container -->
            <div class="flex flex-col justify-center w-full max-w-[40rem] px-4">
                <!-- Add smaller logo here -->
                <div class="flex justify-center mb-6">
                    <img class="w-24 h-24 sm:w-32 sm:h-32" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
                </div>

                <form @submit.prevent="submitWithPhoto" enctype="multipart/form-data">
                    <Card class="w-full mx-auto dark:bg-gray-900 dark:border-gray-700">
                        <CardContent class="p-6 sm:p-10">
                            <div class="mb-6 sm:mb-8 text-center">
                                <p class="font-FontSpring-bold text-2xl sm:text-3xl text-primary-color">Add a Profile Picture</p>
                                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm sm:text-base">This step is optional. You can skip it if you prefer.</p>
                            </div>

                            <!-- Profile Picture Upload -->
                            <div class="mb-6 sm:mb-8 flex flex-col items-center">
                                <div class="w-32 h-32 sm:w-40 sm:h-40 border-2 border-dashed border-border dark:border-gray-700 rounded-full flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-800 mb-4">
                                    <img v-if="previewUrl" :src="previewUrl" 
                                        class="w-full h-full object-cover" />
                                    <div v-else class="text-gray-400 text-center">
                                        <i class="fas fa-user text-3xl sm:text-4xl mb-2"></i>
                                        <p class="text-xs sm:text-sm">No image</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2 items-center w-full max-w-xs">
                                    <Input 
                                        type="file"
                                        accept="image/*"
                                        @change="handleFileUpload"
                                        :disabled="form.processing"
                                        class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 file:bg-primary-color file:text-white file:border-0 dark:text-white text-xs sm:text-sm"
                                        :class="{'ring-2 ring-destructive ring-offset-1': form.errors.profile_picture}"
                                    />
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Max size: 2MB (JPG, JPEG, PNG)</p>
                                    <Button 
                                        v-if="previewUrl"
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="removeImage"
                                    >
                                        Remove Image
                                    </Button>
                                </div>
                                <div v-if="form.errors.profile_picture" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.profile_picture }}
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex flex-col sm:flex-row justify-between mt-6 sm:mt-8 gap-4">
                                <!-- Add Back Button -->
                                <Link 
                                    :href="route('register.details')"
                                    class="text-primary-color hover:underline px-6 py-2 text-center sm:text-left"
                                    :disabled="form.processing"
                                >
                                    Back
                                </Link>
                                
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <Button 
                                        type="button" 
                                        variant="outline"
                                        @click="skipStep"
                                        :disabled="form.processing"
                                        class="px-8 dark:border-gray-700 dark:text-gray-300 order-1 sm:order-none"
                                    >
                                        Skip for now
                                    </Button>
                                    
                                    <Button 
                                        type="submit" 
                                        :disabled="!previewUrl || form.processing"
                                        class="bg-primary-color text-primary-foreground"
                                    >
                                        {{ form.processing ? 'Processing...' : 'Complete Registration' }}
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </div>
</template>
