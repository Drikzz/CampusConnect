<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Card, CardContent } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { Link } from '@inertiajs/vue3';
import { Toaster } from '@/Components/ui/toast';
import { useToast } from '@/Components/ui/toast/use-toast';

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
const { toast } = useToast();

// Watch for flash messages
watch(() => page.props.flash.toast, (flashToast) => {
    if (flashToast) {
        toast({
            variant: flashToast.variant,
            title: flashToast.title,
            description: flashToast.description,
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
        <div class="fixed inset-0 pointer-events-none z-[100] flex justify-end p-4">
            <Toaster />
        </div>

        <div class="background w-full h-full absolute z-0"></div>

        <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">
            <!-- Logo Container -->
            <div class="w-1/2">
                <img class="w-[30rem] h-[30rem]" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
            </div>

            <!-- Form Container -->
            <div class="flex flex-col justify-center items-end">
                <form @submit.prevent="submitWithPhoto" enctype="multipart/form-data">
                    <Card class="w-[40rem]">
                        <CardContent class="p-10">
                            <div class="mb-8 text-center">
                                <p class="font-FontSpring-bold text-3xl text-primary-color">Add a Profile Picture</p>
                                <p class="text-gray-500 mt-2">This step is optional. You can skip it if you prefer.</p>
                            </div>

                            <!-- Profile Picture Upload -->
                            <div class="mb-8 flex flex-col items-center">
                                <div class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center overflow-hidden bg-gray-50 mb-4">
                                    <img v-if="previewUrl" :src="previewUrl" 
                                        class="w-full h-full object-cover" />
                                    <div v-else class="text-gray-400 text-center">
                                        <i class="fas fa-user text-4xl mb-2"></i>
                                        <p class="text-sm">No image</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2 items-center">
                                    <Input 
                                        type="file"
                                        accept="image/*"
                                        @change="handleFileUpload"
                                        :disabled="form.processing"
                                        class="bg-white border-primary-color file:bg-primary-color file:text-white file:border-0"
                                    />
                                    <p class="text-sm text-gray-500">Max size: 2MB (JPG, JPEG, PNG)</p>
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
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-between mt-8">
                                <!-- Add Back Button -->
                                <Link 
                                    :href="route('register.details')"
                                    class="text-primary-color hover:underline px-6 py-2"
                                    :disabled="form.processing"
                                >
                                    Back
                                </Link>
                                
                                <Button 
                                    type="button" 
                                    variant="outline"
                                    @click="skipStep"
                                    :disabled="form.processing"
                                    class="px-8"
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
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </div>
</template>
