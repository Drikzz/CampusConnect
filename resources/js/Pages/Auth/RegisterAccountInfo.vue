<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Progress } from "@/Components/ui/progress";
import { Card, CardContent } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { Link } from '@inertiajs/vue3';
import { Toaster } from '@/Components/ui/toast';
import { useToast } from '@/Components/ui/toast/use-toast';

const props = defineProps({
    registrationData: Object
});

const form = useForm({
    username: '',
    wmsu_email: '',
    password: '',
    password_confirmation: '',
    wmsu_id_front: null,
    wmsu_id_back: null,
    progress: null
});

onMounted(() => {
    // Restore non-sensitive form data from sessionStorage
    ['username', 'wmsu_email'].forEach(field => {
        const value = sessionStorage.getItem(field);
        if (value) {
            form[field] = value;
        }
    });

    // Add a direct event listener to clear data when navigating away
    const registrationRoutes = ['/register', '/register/details', '/register/personal-info'];
    
    router.on('navigate', (event) => {
        const targetPath = new URL(event.detail.page.url, window.location.origin).pathname;
        const isRegistrationRoute = registrationRoutes.some(route => targetPath.startsWith(route));
        
        if (!isRegistrationRoute) {
            console.log("RegisterAccountInfo: Navigating away from registration - clearing data");
            clearFormData();
        }
    });

    // Also listen for page unload events
    window.addEventListener('beforeunload', () => {
        clearFormData();
    });
});

// Function to clear registration form data with explicit force parameter
function clearFormData(force = false) {
    console.log("Clearing form data from RegisterAccountInfo");
    const formFields = [
        'user_type_id', 
        'grade_level_id', 
        'wmsu_dept_id', 
        'first_name', 
        'middle_name', 
        'last_name', 
        'gender', 
        'date_of_birth', 
        'phone',
        'username',
        'wmsu_email',
        'from_account_info',
        'registration_in_progress'
    ];
    
    formFields.forEach(field => {
        sessionStorage.removeItem(field);
    });
    
    // If forced, also reset the form
    if (force) {
        form.reset();
    }
}

watch(() => ({
    username: form.username,
    wmsu_email: form.wmsu_email
}), (newValues) => {
    Object.entries(newValues).forEach(([key, value]) => {
        if (value) {
            sessionStorage.setItem(key, value);
        }
    });
}, { deep: true });

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const passwordStrength = ref('');
const passwordFeedback = ref([]);

const previewUrls = ref({
    profile_picture: null,
    wmsu_id_front: null,
    wmsu_id_back: null
});

const handleFileUpload = async (event, field) => {
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
        previewUrls.value[field] = URL.createObjectURL(file);
        form[field] = file;
    }
};

const removeImage = (field) => {
    form[field] = null;
    previewUrls.value[field] = null;
};

const checkPasswordStrength = (password) => {
    let strength = 0;
    const feedback = [];

    if (password.length >= 8) strength++;
    else feedback.push('At least 8 characters');

    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('At least one uppercase letter');

    if (/[a-z]/.test(password)) strength++;
    else feedback.push('At least one lowercase letter');

    if (/[0-9]/.test(password)) strength++;
    else feedback.push('At least one number');

    if (/[^A-Za-z0-9]/.test(password)) strength++;
    else feedback.push('At least one special character');

    const strengthMap = {
        0: 'Very Weak',
        1: 'Weak',
        2: 'Fair',
        3: 'Good',
        4: 'Strong',
        5: 'Very Strong'
    };

    passwordStrength.value = strengthMap[strength];
    passwordFeedback.value = feedback;
};

// Add password match check function
const passwordMatch = computed(() => {
    if (!form.password || !form.password_confirmation) return null;
    return form.password === form.password_confirmation;
});

const submit = () => {
    // Instead of posting directly to register.complete, store the data and go to profile picture page
    form.post(route('register.account-info'), {
        onError: (errors) => {
            console.error("Form submission errors:", errors);
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
</script>

<template>
    <!-- Add toast container at the top level -->
    <div class="relative">
        <div class="fixed inset-0 pointer-events-none z-[100] flex justify-end p-4">
            <Toaster />
        </div>

        <!-- Existing template content -->
        <div class="background w-full h-full absolute z-0"></div>

        <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">
            <!-- Logo Container -->
            <div class="w-1/2">
                <img class="w-[30rem] h-[30rem]" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
            </div>

            <!-- Form Container -->
            <div class="flex flex-col justify-center items-end">
                <!-- Progress Indicator Component would go here -->

                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <Card class="w-[40rem]">
                        <CardContent class="p-10">
                            <div class="mb-8">
                                <p class="font-FontSpring-bold text-3xl text-primary-color">Account Details</p>
                            </div>

                            <!-- Remove Profile Picture Upload section -->

                            <!-- Account Fields -->
                            <div class="space-y-6">
                                <!-- Username -->
                                <div>
                                    <Label for="username" class="text-base">Username*</Label>
                                    <Input 
                                        id="username"
                                        v-model="form.username" 
                                        type="text"
                                        autocomplete="username"
                                        placeholder="Choose a username"
                                        :disabled="form.processing"
                                        class="mt-2 bg-white border-primary-color"
                                        :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.username}"
                                    />
                                    <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.username }}
                                    </div>
                                </div>

                                <!-- WMSU Email (conditional) -->
                                <div v-if="['HS', 'COL', 'PG', 'EMP'].includes(registrationData.user_type_id) || registrationData.user_type_id === 'ALM'">
                                    <Label for="wmsu_email" class="text-base">
                                        WMSU Email{{ ['HS', 'COL', 'PG', 'EMP'].includes(registrationData.user_type_id) ? '*' : ' (Optional)' }}
                                    </Label>
                                    <Input 
                                        id="wmsu_email"
                                        v-model="form.wmsu_email" 
                                        type="text"
                                        autocomplete="email"
                                        placeholder="youremail@wmsu.edu.ph"
                                        :disabled="form.processing"
                                        class="mt-2 bg-white border-primary-color"
                                        :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.wmsu_email}"
                                    />
                                    <div v-if="form.errors.wmsu_email" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.wmsu_email }}
                                    </div>
                                </div>

                                <!-- Password Section -->
                                <div class="space-y-4">
                                    <div>
                                        <Label for="password" class="text-base">Password*</Label>
                                        <div class="relative mt-2">
                                            <Input 
                                                id="password"
                                                v-model="form.password" 
                                                :type="showPassword ? 'text' : 'password'"
                                                autocomplete="new-password"
                                                placeholder="Enter your password"
                                                @input="checkPasswordStrength(form.password)"
                                                :disabled="form.processing"
                                                class="bg-white border-primary-color pr-10"
                                                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.password}"
                                            />
                                            <button 
                                                type="button" 
                                                @click="showPassword = !showPassword"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        <div v-if="passwordStrength" 
                                            :class="{'text-red': passwordStrength === 'Very Weak',
                                                'text-orange-600': passwordStrength === 'Weak',
                                                'text-yellow-600': passwordStrength === 'Fair',
                                                'text-blue-600': passwordStrength === 'Good',
                                                'text-green-600': passwordStrength === 'Strong' || passwordStrength === 'Very Strong'}"
                                            class="text-sm mt-1 flex items-center gap-2"
                                        >
                                            <span>Strength:</span> {{ passwordStrength }}
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="password_confirmation" class="text-base">Confirm Password*</Label>
                                        <div class="relative mt-2">
                                            <Input 
                                                id="password_confirmation"
                                                v-model="form.password_confirmation" 
                                                :type="showConfirmPassword ? 'text' : 'password'"
                                                autocomplete="off"
                                                placeholder="Confirm your password"
                                                :disabled="form.processing"
                                                class="bg-white border-primary-color pr-10"
                                                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.password_confirmation}"
                                            />
                                            <button 
                                                type="button" 
                                                @click="showConfirmPassword = !showConfirmPassword"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        <!-- Live password confirmation feedback -->
                                        <div v-if="form.password && form.password_confirmation" 
                                            :class="{
                                                'text-green-600': passwordMatch,
                                                'text-red-500': !passwordMatch
                                            }"
                                            class="text-sm mt-1 flex items-center gap-2"
                                        >
                                            <span v-if="passwordMatch">✓ Passwords match</span>
                                            <span v-else>✗ Passwords don't match</span>
                                        </div>
                                        <!-- Static error message from server validation -->
                                        <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.password_confirmation }}
                                        </div>
                                    </div>
                                </div>

                                <!-- ID Verification (conditional) -->
                                <template v-if="['HS', 'COL', 'EMP', 'PG', 'ALM'].includes(registrationData.user_type_id)">
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-semibold text-gray-800">ID Verification</h3>
                                        <div class="grid grid-cols-2 gap-6">
                                            <!-- Front ID -->
                                            <div>
                                                <Label class="text-base">Front of ID*</Label>
                                                <div class="mt-2 space-y-2">
                                                    <div class="w-full h-40 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center overflow-hidden bg-gray-50">
                                                        <img v-if="previewUrls.wmsu_id_front" :src="previewUrls.wmsu_id_front" 
                                                            class="w-full h-full object-contain" />
                                                        <div v-else class="text-gray-400 text-center p-4">
                                                            <i class="fas fa-id-card text-3xl mb-2"></i>
                                                            <p class="text-sm">Front of ID</p>
                                                        </div>
                                                    </div>
                                                    <Input 
                                                        type="file"
                                                        accept="image/*"
                                                        @change="(e) => handleFileUpload(e, 'wmsu_id_front')"
                                                        :disabled="form.processing"
                                                        class="bg-white border-primary-color file:bg-primary-color file:text-white file:border-0"
                                                    />
                                                    <Button 
                                                        v-if="previewUrls.wmsu_id_front"
                                                        type="button"
                                                        variant="destructive"
                                                        size="sm"
                                                        @click="removeImage('wmsu_id_front')"
                                                    >
                                                        Remove
                                                    </Button>
                                                </div>
                                            </div>

                                            <!-- Back ID -->
                                            <div>
                                                <Label class="text-base">Back of ID*</Label>
                                                <div class="mt-2 space-y-2">
                                                    <div class="w-full h-40 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center overflow-hidden bg-gray-50">
                                                        <img v-if="previewUrls.wmsu_id_back" :src="previewUrls.wmsu_id_back" 
                                                            class="w-full h-full object-contain" />
                                                        <div v-else class="text-gray-400 text-center p-4">
                                                            <i class="fas fa-id-card text-3xl mb-2"></i>
                                                            <p class="text-sm">Back of ID</p>
                                                        </div>
                                                    </div>
                                                    <Input 
                                                        type="file"
                                                        accept="image/*"
                                                        @change="(e) => handleFileUpload(e, 'wmsu_id_back')"
                                                        :disabled="form.processing"
                                                        class="bg-white border-primary-color file:bg-primary-color file:text-white file:border-0"
                                                    />
                                                    <Button 
                                                        v-if="previewUrls.wmsu_id_back"
                                                        type="button"
                                                        variant="destructive"
                                                        size="sm"
                                                        @click="removeImage('wmsu_id_back')"
                                                    >
                                                        Remove
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-between mt-8">
                                <Link 
                                    :href="route('register.personal-info')"
                                    class="text-primary-color hover:underline px-6 py-2"
                                    :disabled="form.processing"
                                >
                                    Back
                                </Link>
                                <Button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-primary-color text-primary-foreground"
                                >
                                    {{ form.processing ? 'Processing...' : 'Continue' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </div>
</template>
