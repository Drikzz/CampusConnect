<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted, inject } from 'vue';
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

const props = defineProps({
    userTypes: Array,
    departments: Array,
    gradeLevels: Array
});

// For client-side validation only, get the global toast function
const toast = inject('globalToast', null);

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

const handleDateChange = (newDate) => {
    if (!newDate) {
        date.value = null;
        form.date_of_birth = '';
        return;
    }

    try {
        const dateObj = new Date(newDate.toString());
        if (!isNaN(dateObj.getTime())) {
            date.value = newDate;
            form.date_of_birth = format(dateObj, 'yyyy-MM-dd');
            
            // Clear date of birth error when a valid date is selected
            if (form.errors.date_of_birth) {
                delete form.errors.date_of_birth;
                
                // Check age requirement immediately for better UX
                if (!isOldEnough(form.date_of_birth)) {
                    form.errors.date_of_birth = `You must be at least ${minAge} years old to register`;
                }
            }
        }
    } catch (error) {
        console.error('Error handling date change:', error);
    }
};

// Add this watch to properly handle date changes
watch(() => date.value, handleDateChange);

// Add watchers for form fields to clear errors when values change
watch(() => form.user_type_id, () => {
    if (form.errors.user_type_id) delete form.errors.user_type_id;
});

watch(() => form.grade_level_id, () => {
    if (form.errors.grade_level_id) delete form.errors.grade_level_id;
});

watch(() => form.wmsu_dept_id, () => {
    if (form.errors.wmsu_dept_id) delete form.errors.wmsu_dept_id;
});

watch(() => form.first_name, () => {
    if (form.errors.first_name) delete form.errors.first_name;
});

watch(() => form.middle_name, () => {
    if (form.errors.middle_name) delete form.errors.middle_name;
});

watch(() => form.last_name, () => {
    if (form.errors.last_name) delete form.errors.last_name;
});

watch(() => form.gender, () => {
    if (form.errors.gender) delete form.errors.gender;
});

watch(() => form.date_of_birth, () => {
    if (form.errors.date_of_birth) delete form.errors.date_of_birth;
});

watch(() => form.phone, () => {
    if (form.errors.phone) delete form.errors.phone;
    
    // Optional: Add custom validation for phone format (11 digits)
    if (form.phone && !/^[0-9]{11}$/.test(form.phone)) {
        form.errors.phone = 'Please enter a valid 11-digit phone number';
    }
});

// Helper function to clear a specific field error
const clearFieldError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
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

    // Clear all errors before re-validation
    form.clearErrors();
    
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

    if (hasErrors && toast) {
        // Only use the toast directly for client-side validation
        toast({
            variant: 'destructive',
            title: 'Validation Error',
            description: 'Please check all required fields.'
        });
        return;
    }

    // Use standard form submission with preserveState: false to ensure a fresh page load
    form.post(route('register.step1'), {
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

        <!-- Existing template content -->
        <div class="background w-full h-full absolute z-0 dark:bg-black dark:bg-opacity-80"></div>

        <!-- Center the form container -->
        <div class="w-full min-h-screen px-4 sm:px-8 md:px-16 pt-8 sm:pt-16 pb-16 sm:pb-32 flex flex-col justify-center items-center relative z-10">
            <!-- Form Container -->
            <div class="flex flex-col justify-center w-full max-w-[40rem] px-4">
                <!-- Add smaller logo here -->
                <div class="flex justify-center mb-6">
                    <img class="w-24 h-24 sm:w-32 sm:h-32" src="/storage/app/public/imgs/CampusConnect.png" alt="CampusConnect Logo">
                </div>

                <!-- Progress Indicator Component would go here -->

                <form @submit.prevent="submit">
                    <div class="w-full mx-auto h-auto bg-background dark:bg-gray-900 p-6 sm:p-10 rounded-sm">
                        <div class="mb-6">
                            <p class="font-FontSpring-bold text-2xl sm:text-3xl text-primary-color">Personal Information</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- User Type Combobox -->
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">User Type*</label>
                                <Popover v-model:open="userTypeOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="userTypeOpen"
                                            :disabled="form.processing"
                                            class="w-full h-10 justify-between bg-white dark:bg-gray-800 border-border dark:border-gray-700 disabled:opacity-50 dark:text-white text-left"
                                            :class="{'ring-2 ring-destructive ring-offset-1': form.errors.user_type_id}"
                                            @click="clearFieldError('user_type_id')"
                                        >
                                            {{ getSelectedUserType || "Select user type..." }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[calc(100vw-2rem)] sm:w-[400px] p-0 dark:bg-gray-800 dark:border-gray-700">
                                        <Command>
                                            <CommandInput placeholder="Search user type..." class="dark:bg-gray-800 dark:text-white" />
                                            <CommandEmpty class="dark:text-gray-400">No user type found.</CommandEmpty>
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
                                                        class="dark:text-white dark:hover:bg-gray-700"
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
                                <div v-if="form.errors.user_type_id" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.user_type_id }}
                                </div>
                            </div>

                            <!-- Conditional Fields -->
                            <div v-if="showGradeLevel" class="col-span-1 sm:col-span-2">
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Select Highschool level</label>
                                <Popover v-model:open="gradeLevelOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="gradeLevelOpen"
                                            :disabled="form.processing"
                                            class="w-full h-10 justify-between bg-white dark:bg-gray-800 border-border dark:border-gray-700 disabled:opacity-50 dark:text-white text-left"
                                            :class="{'ring-2 ring-destructive ring-offset-1': form.errors.grade_level_id}"
                                            @click="clearFieldError('grade_level_id')"
                                        >
                                            {{ getSelectedGradeLevel || "--Select highshool level--" }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[calc(100vw-2rem)] sm:w-[400px] p-0 dark:bg-gray-800 dark:border-gray-700">
                                        <Command>
                                            <CommandInput placeholder="Search grade level..." class="dark:bg-gray-800 dark:text-white" />
                                            <CommandEmpty class="dark:text-gray-400">No grade level found.</CommandEmpty>
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
                                                        class="dark:text-white dark:hover:bg-gray-700"
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
                                <div v-if="form.errors.grade_level_id" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.grade_level_id }}
                                </div>
                            </div>

                            <div v-if="showDepartment" class="col-span-1 sm:col-span-2">
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Select Department</label>
                                <Popover v-model:open="departmentOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="departmentOpen"
                                            :disabled="form.processing"
                                            class="w-full h-10 justify-between bg-white dark:bg-gray-800 border-border dark:border-gray-700 disabled:opacity-50 dark:text-white text-left"
                                            :class="{'ring-2 ring-destructive ring-offset-1': form.errors.wmsu_dept_id}"
                                            @click="clearFieldError('wmsu_dept_id')"
                                        >
                                            {{ getSelectedDepartment || "--Select Department--" }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[calc(100vw-2rem)] sm:w-[400px] p-0 dark:bg-gray-800 dark:border-gray-700">
                                        <Command>
                                            <CommandInput placeholder="Search department..." class="dark:bg-gray-800 dark:text-white" />
                                            <CommandEmpty class="dark:text-gray-400">No department found.</CommandEmpty>
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
                                                        class="dark:text-white dark:hover:bg-gray-700"
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
                                <div v-if="form.errors.wmsu_dept_id" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.wmsu_dept_id }}
                                </div>
                            </div>

                            <!-- Personal Information Fields -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">First Name*</label>
                                <Input
                                    v-model="form.first_name"
                                    type="text"
                                    autocomplete="given-name"
                                    :disabled="form.processing"
                                    placeholder="Enter your first name"
                                    class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 dark:text-white"
                                    :class="{'ring-2 ring-destructive ring-offset-1': form.errors.first_name}"
                                    @focus="clearFieldError('first_name')"
                                    @input="clearFieldError('first_name')"
                                />
                                <div v-if="form.errors.first_name" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.first_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Middle Name (Optional)</label>
                                <Input
                                    v-model="form.middle_name"
                                    type="text"
                                    autocomplete="additional-name"
                                    :disabled="form.processing"
                                    placeholder="Enter your middle name (optional)"
                                    class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 dark:text-white"
                                    :class="{'ring-2 ring-destructive ring-offset-1': form.errors.middle_name}"
                                    @focus="clearFieldError('middle_name')"
                                    @input="clearFieldError('middle_name')"
                                />
                                <div v-if="form.errors.middle_name" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.middle_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Last Name*</label>
                                <Input
                                    v-model="form.last_name"
                                    type="text"
                                    autocomplete="family-name"
                                    :disabled="form.processing"
                                    placeholder="Enter your last name"
                                    class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 dark:text-white"
                                    :class="{'ring-2 ring-destructive ring-offset-1': form.errors.last_name}"
                                    @focus="clearFieldError('last_name')"
                                    @input="clearFieldError('last_name')"
                                />
                                <div v-if="form.errors.last_name" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.last_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Gender*</label>
                                <Popover v-model:open="genderOpen">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            role="combobox"
                                            :aria-expanded="genderOpen"
                                            :disabled="form.processing"
                                            class="w-full p-2.5 justify-between bg-white dark:bg-gray-800 border-border dark:border-gray-700 disabled:opacity-50 dark:text-white text-left"
                                            :class="{'ring-2 ring-destructive ring-offset-1': form.errors.gender}"
                                            @click="clearFieldError('gender')"
                                        >
                                            {{ getGenderDisplay }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-[calc(100vw-2rem)] sm:w-[400px] p-0 dark:bg-gray-800 dark:border-gray-700">
                                        <Command>
                                            <CommandInput placeholder="Search gender..." class="dark:bg-gray-800 dark:text-white" />
                                            <CommandEmpty class="dark:text-gray-400">No gender options found.</CommandEmpty>
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
                                                        class="dark:text-white dark:hover:bg-gray-700"
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
                                <div v-if="form.errors.gender" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.gender }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Date of Birth*</label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button 
                                            variant="outline" 
                                            :disabled="form.processing" 
                                            :class="cn(
                                                'w-full p-2.5 justify-start text-left font-normal bg-white dark:bg-gray-800 border border-border dark:border-gray-700 text-black dark:text-white rounded-lg focus:ring-secondary focus:border-secondary',
                                                !date && 'text-muted-foreground',
                                                form.errors.date_of_birth && 'ring-2 ring-destructive ring-offset-1'
                                            )"
                                            @click="clearFieldError('date_of_birth')"
                                        >
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ date ? format(new Date(date.toString()), 'PPP') : 'Pick a date' }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0 dark:bg-gray-800 dark:border-gray-700" align="start">
                                        <CustomCalendar
                                            v-model="date"
                                            mode="single"
                                            class="rounded-md border dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                            :fromDate="minDate"
                                            :toDate="maxDate"
                                            @update:model-value="(newDate) => {
                                                if (newDate) {
                                                    form.date_of_birth = format(new Date(newDate.toString()), 'yyyy-MM-dd');
                                                    clearFieldError('date_of_birth');
                                                    // Check age requirement immediately
                                                    if (!isOldEnough(form.date_of_birth)) {
                                                        form.errors.date_of_birth = `You must be at least ${minAge} years old to register`;
                                                    }
                                                } else {
                                                    form.date_of_birth = '';
                                                }
                                            }"
                                        />
                                        <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">
                                            You must be at least {{ minAge }} years old to register.
                                        </div>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="form.errors.date_of_birth" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.date_of_birth }}
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-black dark:text-white">Phone Number*</label>
                                <Input
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    :disabled="form.processing"
                                    placeholder="09123456789"
                                    class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 dark:text-white"
                                    :class="{'ring-2 ring-destructive ring-offset-1': form.errors.phone}"
                                    @focus="clearFieldError('phone')"
                                    @input="clearFieldError('phone')"
                                    maxlength="11"
                                />
                                <div v-if="form.errors.phone" class="text-destructive text-xs sm:text-sm mt-1">
                                    {{ form.errors.phone }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-between mt-6 gap-4">
                            <Link :href="route('login')" class="text-primary-color hover:underline text-center sm:text-left">
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