<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import { Button } from "@/Components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { CalendarIcon, Check, ChevronsUpDown } from "lucide-vue-next";
import { format, subYears } from "date-fns"; // Add subYears import
import { cn } from "@/lib/utils";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from "@/Components/ui/command";
import { ScrollArea } from "@/Components/ui/scroll-area";
import CustomCalendar from '@/Components/ui/custom-calendar.vue';
import { today, getLocalTimeZone, parseDate } from '@internationalized/date';
import { Input } from "@/Components/ui/input"; // Add this import
import { Toaster } from '@/Components/ui/toast';
import { useToast } from '@/Components/ui/toast/use-toast';

const props = defineProps({
    userTypes: Array,
    departments: Array,
    gradeLevels: Array
});

const form = useForm({
    user_type_id: '',
    grade_level_id: '',
    wmsu_dept_id: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    date_of_birth: '',
    phone: ''
});

onMounted(() => {
    // Restore form data from sessionStorage
    const formFields = [
        'user_type_id', 
        'grade_level_id', 
        'wmsu_dept_id', 
        'first_name', 
        'middle_name', 
        'last_name', 
        'gender', 
        'date_of_birth', 
        'phone'
    ];

    formFields.forEach(field => {
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
            console.log("RegisterPersonalInfo: Navigating away from registration - clearing data");
            clearFormData();
        }
    });

    // Also listen for page unload events
    window.addEventListener('beforeunload', () => {
        clearFormData();
    });
});

// Add watcher for form fields
watch(form, (newValues) => {
    // Save form data to sessionStorage
    Object.entries(newValues).forEach(([key, value]) => {
        if (value) {
            sessionStorage.setItem(key, value);
        }
    });
}, { deep: true });

const showDepartment = ref(false);
const showGradeLevel = ref(false);

watch(() => form.user_type_id, (newValue) => {
    showDepartment.value = ['COL', 'PG'].includes(newValue);
    showGradeLevel.value = newValue === 'HS';
});

const date = ref(today(getLocalTimeZone()));

watch(() => form.date_of_birth, (newValue) => {
    if (newValue) {
        try {
            const [year, month, day] = newValue.split('-');
            date.value = parseDate(`${year}-${month}-${day}`);
        } catch (e) {
            console.error('Invalid date format:', e);
        }
    }
}, { immediate: true });

// Calculate minimum date for 12 years old
const minAge = 12;
const maxDate = computed(() => {
    return subYears(new Date(), minAge);
});

// Add this for better year navigation - calculate minimum year (e.g., 80 years ago)
const minDate = computed(() => {
    return subYears(new Date(), 80); // Allow up to 80 years back
});

// Function to check if user is at least minAge years old
const isOldEnough = (birthDate) => {
    if (!birthDate) return false;
    
    const birthDateObj = new Date(birthDate);
    const today = new Date();
    
    let age = today.getFullYear() - birthDateObj.getFullYear();
    const monthDiff = today.getMonth() - birthDateObj.getMonth();
    
    // Adjust age if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
        age--;
    }
    
    return age >= minAge;
};

const submit = () => {
    // Basic validation before submission
    let hasErrors = false;
    const requiredFields = {
        user_type_id: 'User type',
        first_name: 'First name',
        last_name: 'Last name',
        gender: 'Gender',
        date_of_birth: 'Date of birth',
        phone: 'Phone number'
    };

    // Check required fields
    Object.entries(requiredFields).forEach(([field, label]) => {
        if (!form[field]) {
            form.errors[field] = `${label} is required`;
            hasErrors = true;
        }
    });

    // Check age requirement
    if (form.date_of_birth && !isOldEnough(form.date_of_birth)) {
        form.errors.date_of_birth = `You must be at least ${minAge} years old to register`;
        hasErrors = true;
    }

    // Additional validation for conditional fields
    if (showDepartment.value && !form.wmsu_dept_id) {
        form.errors.wmsu_dept_id = 'Department is required';
        hasErrors = true;
    }

    if (showGradeLevel.value && !form.grade_level_id) {
        form.errors.grade_level_id = 'Grade level is required';
        hasErrors = true;
    }

    // Phone number validation
    if (form.phone && !/^[0-9]{11}$/.test(form.phone)) {
        form.errors.phone = 'Please enter a valid 11-digit phone number';
        hasErrors = true;
    }

    if (hasErrors) {
        toast({
            variant: 'destructive',
            title: 'Validation Error',
            description: 'Please check all required fields.'
        });
        return;
    }

    // Use standard form submission with preserveState: false to ensure a fresh page load
    form.post(route('register.step1'), {
        // Don't use manual redirection - let the controller handle the redirect
        onError: (errors) => {
            console.error("Form submission errors:", errors);
        }
    });
};

const userTypeOpen = ref(false);
const departmentOpen = ref(false);
const gradeLevelOpen = ref(false);
const genderOpen = ref(false);

// Transform data for combobox
const userTypeOptions = computed(() =>
    props.userTypes.map(type => ({
        value: type.code,
        label: type.name
    }))
);

const departmentOptions = computed(() =>
    props.departments.map(dept => ({
        value: dept.id,
        label: dept.name
    }))
);

const gradeLevelOptions = computed(() =>
    props.gradeLevels.map(level => ({
        value: level.id,
        label: level.name
    }))
);

// Update the gender options to match the backend validation
const genderOptions = [
    { value: 'male', label: 'Male', displayText: 'Male' },
    { value: 'female', label: 'Female', displayText: 'Female' },
    { value: 'non-binary', label: 'Non-binary', displayText: 'Non-binary' },
    { value: 'prefer-not-to-say', label: 'Prefer not to say', displayText: 'Prefer not to say' }
];

// Helper functions to get selected item label
const getSelectedUserType = computed(() =>
    userTypeOptions.value.find(type => type.value === form.user_type_id)?.label || ''
);

const getSelectedDepartment = computed(() =>
    departmentOptions.value.find(dept => dept.value === form.wmsu_dept_id)?.label || ''
);

const getSelectedGradeLevel = computed(() =>
    gradeLevelOptions.value.find(level => level.value === form.grade_level_id)?.label || ''
);

const getGenderDisplay = computed(() => {
    const option = genderOptions.find(opt => opt.value === form.gender);
    return option ? option.displayText : "Select gender...";
});

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

// Function to clear registration form data with explicit force parameter
function clearFormData(force = false) {
    console.log("Clearing form data from RegisterPersonalInfo");
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
                
                <form @submit.prevent="submit">
                    <div class="w-[40rem] h-auto bg-background p-10 rounded-sm">
                        <div class="mb-6">
                            <p class="font-FontSpring-bold text-3xl text-primary-color">Personal Information</p>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- User Type Combobox -->
                            <div class="col-span-2">
                                <label class="block mb-2 text-sm font-medium text-black">User Type*</label>
                                <Popover v-model:open="userTypeOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="userTypeOpen"
                                            :disabled="form.processing"
                                            class="w-full h-10 justify-between bg-white border-primary-color disabled:opacity-50"
                                        >
                                            {{ getSelectedUserType || "Select user type..." }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[400px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search user type..." />
                                            <CommandEmpty>No user type found.</CommandEmpty>
                                            <CommandGroup>
                                                <ScrollArea className="h-[125px]">
                                                    <CommandItem
                                                        v-for="type in userTypeOptions"
                                                        :key="type.value"
                                                        :value="type.value"
                                                        @click="() => {
                                                            form.user_type_id = type.value;
                                                            userTypeOpen = false;
                                                        }"
                                                    >
                                                        <Check
                                                            :class="cn(
                                                                'mr-2 h-4 w-4',
                                                                form.user_type_id === type.value ? 'opacity-100' : 'opacity-0'
                                                            )"
                                                        />
                                                        {{ type.label }}
                                                    </CommandItem>
                                                </ScrollArea>
                                            </CommandGroup>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="form.errors.user_type_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.user_type_id }}
                                </div>
                            </div>

                            <!-- Conditional Fields -->
                            <div v-if="showGradeLevel" class="col-span-2">
                                <label class="block mb-2 text-sm font-medium text-black">Select Highschool level</label>
                                <Popover v-model:open="gradeLevelOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="gradeLevelOpen"
                                            :disabled="form.processing"
                                            class="w-full h-10 justify-between bg-white border-primary-color disabled:opacity-50"
                                        >
                                            {{ getSelectedGradeLevel || "--Select highshool level--" }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[400px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search grade level..." />
                                            <CommandEmpty>No grade level found.</CommandEmpty>
                                            <CommandGroup>
                                                <ScrollArea className="h-[125px]">
                                                    <CommandItem
                                                        v-for="level in gradeLevelOptions"
                                                        :key="level.value"
                                                        :value="level.value"
                                                        @click="() => {
                                                            form.grade_level_id = level.value;
                                                            gradeLevelOpen = false;
                                                        }"
                                                    >
                                                        <Check
                                                            :class="cn(
                                                                'mr-2 h-4 w-4',
                                                                form.grade_level_id === level.value ? 'opacity-100' : 'opacity-0'
                                                            )"
                                                        />
                                                        {{ level.label }}
                                                    </CommandItem>
                                                </ScrollArea>
                                            </CommandGroup>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="form.errors.grade_level_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.grade_level_id }}
                                </div>
                            </div>

                            <div v-if="showDepartment" class="col-span-2">
                                <label class="block mb-2 text-sm font-medium text-black">Select Department</label>
                                <Popover v-model:open="departmentOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="departmentOpen"
                                            :disabled="form.processing"
                                            class="w-full h-10 justify-between bg-white border-primary-color disabled:opacity-50"
                                        >
                                            {{ getSelectedDepartment || "--Select Department--" }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[400px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search department..." />
                                            <CommandEmpty>No department found.</CommandEmpty>
                                            <CommandGroup>
                                                <ScrollArea className="h-[125px]">
                                                    <CommandItem
                                                        v-for="dept in departmentOptions"
                                                        :key="dept.value"
                                                        :value="dept.value"
                                                        @click="() => {
                                                            form.wmsu_dept_id = dept.value;
                                                            departmentOpen = false;
                                                        }"
                                                    >
                                                        <Check
                                                            :class="cn(
                                                                'mr-2 h-4 w-4',
                                                                form.wmsu_dept_id === dept.value ? 'opacity-100' : 'opacity-0'
                                                            )"
                                                        />
                                                        {{ dept.label }}
                                                    </CommandItem>
                                                </ScrollArea>
                                            </CommandGroup>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="form.errors.wmsu_dept_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.wmsu_dept_id }}
                                </div>
                            </div>

                            <!-- Personal Information Fields -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-black">First Name*</label>
                                <Input
                                    v-model="form.first_name"
                                    type="text"
                                    autocomplete="given-name"
                                    :disabled="form.processing"
                                    placeholder="Enter your first name"
                                    class="bg-white border-primary-color"
                                    :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.first_name}"
                                />
                                <div v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.first_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black">Middle Name (Optional)</label>
                                <Input
                                    v-model="form.middle_name"
                                    type="text"
                                    autocomplete="additional-name"
                                    :disabled="form.processing"
                                    placeholder="Enter your middle name (optional)"
                                    class="bg-white border-primary-color"
                                    :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.middle_name}"
                                />
                                <div v-if="form.errors.middle_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.middle_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black">Last Name*</label>
                                <Input
                                    v-model="form.last_name"
                                    type="text"
                                    autocomplete="family-name"
                                    :disabled="form.processing"
                                    placeholder="Enter your last name"
                                    class="bg-white border-primary-color"
                                    :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.last_name}"
                                />
                                <div v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.last_name }}
                                </div>
                            </div>

                            <!-- Replace the corrupted gender dropdown section with this fixed version -->

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black">Gender*</label>
                                <Popover v-model:open="genderOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="genderOpen"
                                            :disabled="form.processing"
                                            class="w-full p-2.5 justify-between bg-white border-primary-color disabled:opacity-50"
                                        >
                                            {{ getGenderDisplay }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[400px] p-0">
                                        <Command>
                                            <CommandInput placeholder="Search gender..." />
                                            <CommandEmpty>No gender options found.</CommandEmpty>
                                            <CommandGroup>
                                                <ScrollArea className="h-[125px]">
                                                    <CommandItem
                                                        v-for="option in genderOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                        @click="() => {
                                                            form.gender = option.value;
                                                            genderOpen = false;
                                                        }"
                                                    >
                                                        <Check
                                                            :class="cn(
                                                                'mr-2 h-4 w-4',
                                                                form.gender === option.value ? 'opacity-100' : 'opacity-0'
                                                            )"
                                                        />
                                                        {{ option.label }}
                                                    </CommandItem>
                                                </ScrollArea>
                                            </CommandGroup>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="form.errors.gender" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.gender }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black">Date of Birth*</label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button 
                                            variant="outline" 
                                            :disabled="form.processing" 
                                            :class="cn(
                                                'w-full p-2.5 justify-start text-left font-normal bg-white border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color',
                                                !date && 'text-muted-foreground'
                                            )"
                                        >
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ date ? format(new Date(date.toString()), 'PPP') : 'Pick a date' }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0" align="start">
                                        <CustomCalendar
                                            v-model="date"
                                            mode="single"
                                            class="rounded-md border"
                                            :fromDate="minDate"
                                            :toDate="maxDate"
                                            @update:model-value="(newDate) => {
                                                form.date_of_birth = format(new Date(newDate.toString()), 'yyyy-MM-dd');
                                            }"
                                        />
                                        <div class="px-4 py-2 text-xs text-gray-500">
                                            You must be at least {{ minAge }} years old to register.
                                        </div>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="form.errors.date_of_birth" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.date_of_birth }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black">Phone Number*</label>
                                <Input
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    :disabled="form.processing"
                                    placeholder="09123456789"
                                    class="bg-white border-primary-color"
                                    :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.phone}"
                                />
                                <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.phone }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-between mt-6">
                            <Link :href="route('login')" class="text-primary-color hover:underline">
                                Already have an account?
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="px-6 py-2 bg-primary-color text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Processing...' : 'Continue' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>