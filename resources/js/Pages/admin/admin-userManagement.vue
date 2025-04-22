<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    TrashIcon, 
    PencilIcon,
    XMarkIcon,
    EyeIcon,
    ShieldExclamationIcon,
    MagnifyingGlassIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline';

// Define props to receive data from the controller
const props = defineProps({
    users: Object,
    filters: Object
});

// Reactive state
const selectedUsers = ref([]);
const search = ref(props.filters?.search || '');
const searchInput = ref(props.filters?.search || ''); // Add separate input for better UX
const filter = ref(props.filters?.filter || 'all');
const sortField = ref(props.filters?.sort || 'created_at');
const sortDirection = ref(props.filters?.direction || 'desc');
const showUserModal = ref(false);
const currentUser = ref(null);
const showEditModal = ref(false);
const showBanModal = ref(false);
const isSearching = ref(false); 

// Client-side filtered users for real-time feedback
const clientSideFilteredUsers = ref([...props.users?.data || []]);

// For local filtering without server requests
const lastServerSearch = ref('');
const lastServerFilter = ref('all');

const editForm = ref({
    id: null,
    first_name: '',
    middle_name: '',
    last_name: '',
    username: '',
    gender: '',
    date_of_birth: '',
    phone: ''
});
const banForm = ref({
    user_id: null,
    username: '',
    reason: '',
    duration: '7_days', // Default value
    custom_days: 14 // Default custom days
});
const formErrors = ref({});
const banFormErrors = ref({});

// Simple date formatter
const formatDate = (dateString) => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

// Format date for input fields
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0]; // Format as YYYY-MM-DD
};

// Format date of birth
const formatDateOfBirth = (dateString) => {
    if (!dateString) return 'Not provided';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// Available filters
const filterOptions = [
    { value: 'all', label: 'All Users' },
    { value: 'admins', label: 'Admins' },
    { value: 'sellers', label: 'Sellers' },
    { value: 'customers', label: 'Customers' },
    { value: 'verified', label: 'Verified Users' },
    { value: 'unverified', label: 'Unverified Users' },
    { value: 'banned', label: 'Banned Users' }
];

// Available ban durations with new Custom option
const banDurations = [
    { value: '1_day', label: '1 Day' },
    { value: '7_days', label: '7 Days' },
    { value: '30_days', label: '30 Days' },
    { value: '90_days', label: '90 Days' },
    { value: 'custom', label: 'Custom' },
    { value: 'permanent', label: 'Permanent' }
];

// Toggle select all users
const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedUsers.value = props.users.data.map(user => user.id);
    } else {
        selectedUsers.value = [];
    }
};

// Show user details
const showUserDetails = (user) => {
    currentUser.value = user;
    showUserModal.value = true;
};

// Edit user function
const editUser = (user) => {
    // Initialize the form with user data
    editForm.value = {
        id: user.id,
        first_name: user.first_name || '',
        middle_name: user.middle_name || '',
        last_name: user.last_name || '',
        username: user.username || '',
        gender: user.gender || '',
        date_of_birth: formatDateForInput(user.date_of_birth),
        phone: user.phone || ''
    };
    formErrors.value = {};
    showEditModal.value = true;
};

// Save user edits
const saveUserEdit = () => {
    formErrors.value = {};
    
    // Debug the form data
    console.log('Submitting user data:', editForm.value);
    
    // Validate form
    let hasErrors = false;
    if (!editForm.value.first_name) {
        formErrors.value.first_name = 'First name is required';
        hasErrors = true;
    }
    if (!editForm.value.last_name) {
        formErrors.value.last_name = 'Last name is required';
        hasErrors = true;
    }
    if (!editForm.value.username) {
        formErrors.value.username = 'Username is required';
        hasErrors = true;
    }
    if (!editForm.value.gender) {
        formErrors.value.gender = 'Gender is required';
        hasErrors = true;
    }
    if (!editForm.value.date_of_birth) {
        formErrors.value.date_of_birth = 'Date of birth is required';
        hasErrors = true;
    }
    if (!editForm.value.phone) {
        formErrors.value.phone = 'Phone number is required';
        hasErrors = true;
    } else if (!/^\+?[0-9]{11,}$/.test(editForm.value.phone)) {
        formErrors.value.phone = 'Enter a valid phone number';
        hasErrors = true;
    }

    if (hasErrors) {
        return;
    }

    // Submit form via Inertia with direct URL instead of route helper
    router.put(`/admin/users/${editForm.value.id}`, editForm.value, {
        onSuccess: () => {
            showEditModal.value = false;
            // Add a success message
            alert('User updated successfully!');
        },
        onError: (errors) => {
            formErrors.value = errors;
            console.error('Error updating user:', errors);
        },
        preserveScroll: true,
    });
};

// Close the edit modal
const closeEditModal = () => {
    showEditModal.value = false;
};

// Close the modal
const closeModal = () => {
    showUserModal.value = false;
    currentUser.value = null;
};

// Handle user deletion
const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.delete', id));
    }
};

// Show ban user modal
const showBanUserModal = (user) => {
    banForm.value = {
        user_id: user.id,
        username: user.username,
        reason: '',
        duration: '7_days',
        custom_days: 14 // Default to 14 days for custom duration
    };
    banFormErrors.value = {};
    showBanModal.value = true;
};

// Submit the ban
const submitBan = () => {
    banFormErrors.value = {};
    
    // Validate form
    let hasErrors = false;
    if (!banForm.value.reason) {
        banFormErrors.value.reason = 'Please provide a reason for the ban';
        hasErrors = true;
    }
    
    if (banForm.value.duration === 'custom' && (!banForm.value.custom_days || banForm.value.custom_days < 1)) {
        banFormErrors.value.custom_days = 'Please specify a valid number of days (minimum 1)';
        hasErrors = true;
    }
    
    if (hasErrors) {
        return;
    }
    
    // Prepare data for submission
    const formData = {
        reason: banForm.value.reason,
        duration: banForm.value.duration,
    };
    
    // Add custom_days if using custom duration
    if (banForm.value.duration === 'custom') {
        formData.custom_days = banForm.value.custom_days;
    }

    // Submit form via Inertia
    router.post(route('admin.users.ban', banForm.value.user_id), formData, {
        onSuccess: () => {
            showBanModal.value = false;
        },
        onError: (errors) => {
            banFormErrors.value = errors;
        },
        preserveScroll: true,
    });
};

// Unban a user
const unbanUser = (userId, username) => {
    if (confirm(`Are you sure you want to unban ${username}?`)) {
        router.post(route('admin.users.unban', userId), {}, {
            preserveScroll: true,
        });
    }
};

// Close ban modal
const closeBanModal = () => {
    showBanModal.value = false;
};

// Clean up search timeout on component unmount
let searchTimeout = null;

// Improved search implementation with better loading state management
const performSearch = () => {
    router.get(route('admin.users'), {
        search: search.value,
        filter: filter.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
        onSuccess: (page) => {
            isSearching.value = false;
            // Update client-side filtered users with new server data
            clientSideFilteredUsers.value = page.props.users.data;
        },
        onError: (errors) => {
            console.error('Search error:', errors);
            isSearching.value = false;
            
            // Show a simple error message to the user
            alert('An error occurred while searching. Please try again with different terms.');
        }
    });
};

// Improved debounced search handler that doesn't show loading on every keystroke
const handleSearchInput = () => {
    clearTimeout(searchTimeout);
    
    // Update the search value immediately
    search.value = searchInput.value.trim();
    
    // Always apply client-side filtering first for immediate feedback
    applyClientSideFiltering();
    
    // Then send the request to the server for complete results
    // Use a very short timeout just to prevent hammering the server on every keystroke
    searchTimeout = setTimeout(() => {
        if (search.value !== lastServerSearch.value || filter.value !== lastServerFilter.value) {
            isSearching.value = true;
            performSearch();
            lastServerSearch.value = search.value;
            lastServerFilter.value = filter.value;
        }
    }, 300);
};

// Clear search function with improved UX
const clearSearch = () => {
    searchInput.value = '';
    search.value = '';
    clearTimeout(searchTimeout); // Clear any pending search
    
    // Apply client-side filtering immediately
    applyClientSideFiltering();
    
    // Then perform server search
    performSearch();
};

// Handle filter changes without immediate loading indicator
const handleFilterChange = () => {
    // Apply client-side filtering immediately for responsive UI
    applyClientSideFiltering();
    
    // Then send the request to the server
    if (filter.value !== lastServerFilter.value) {
        isSearching.value = true;
        performSearch();
        lastServerFilter.value = filter.value;
    }
};

// Client-side filtering function for immediate feedback
const applyClientSideFiltering = () => {
    if (!props.users?.data) return;
    
    // Start with all users
    let filtered = [...props.users.data];
    
    // Apply search filter
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(user => 
            (user.username && user.username.toLowerCase().includes(searchLower)) || 
            (user.name && user.name.toLowerCase().includes(searchLower)) || 
            (user.wmsu_email && user.wmsu_email.toLowerCase().includes(searchLower)) || 
            (user.first_name && user.first_name.toLowerCase().includes(searchLower)) || 
            (user.last_name && user.last_name.toLowerCase().includes(searchLower)) ||
            ((user.first_name + ' ' + user.last_name).toLowerCase().includes(searchLower))
        );
    }
    
    // Apply role/status filter
    if (filter.value !== 'all') {
        if (filter.value === 'admins') {
            filtered = filtered.filter(user => user.is_admin);
        } else if (filter.value === 'sellers') {
            filtered = filtered.filter(user => user.is_seller);
        } else if (filter.value === 'customers') {
            filtered = filtered.filter(user => !user.is_seller && !user.is_admin);
        } else if (filter.value === 'verified') {
            filtered = filtered.filter(user => user.email_verified_at);
        } else if (filter.value === 'unverified') {
            filtered = filtered.filter(user => !user.email_verified_at);
        } else if (filter.value === 'banned') {
            filtered = filtered.filter(user => user.is_banned);
        }
    }
    
    clientSideFilteredUsers.value = filtered;
};

// Updated sort function with better error handling and immediate client-side sorting
const sort = (field) => {
    sortDirection.value = sortField.value === field && sortDirection.value === 'asc' ? 'desc' : 'asc';
    sortField.value = field;
    
    // Apply client-side sorting immediately
    applyClientSideSorting(field);
    
    // Then perform server request
    isSearching.value = true;
    performSearch();
};

// New function for client-side sorting
const applyClientSideSorting = (field) => {
    const direction = sortDirection.value;
    
    clientSideFilteredUsers.value = [...clientSideFilteredUsers.value].sort((a, b) => {
        const aVal = a[field] || '';
        const bVal = b[field] || '';
        
        // Handle dates
        if (field === 'created_at' || field === 'email_verified_at') {
            const aDate = aVal ? new Date(aVal).getTime() : 0;
            const bDate = bVal ? new Date(bVal).getTime() : 0;
            return direction === 'asc' ? aDate - bDate : bDate - aDate;
        }
        
        // Handle strings
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            return direction === 'asc' 
                ? aVal.localeCompare(bVal) 
                : bVal.localeCompare(aVal);
        }
        
        // Handle numbers
        return direction === 'asc' ? aVal - bVal : bVal - aVal;
    });
};

// Clean up timeout on component unmount
onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

// Watch for props updates to refresh client-side data
watch(() => props.users?.data, (newUsers) => {
    if (newUsers) {
        clientSideFilteredUsers.value = [...newUsers];
        applyClientSideFiltering();
    }
}, { immediate: true });

// Computed properties
const isAllSelected = computed(() => {
    return clientSideFilteredUsers.value.length > 0 && 
           selectedUsers.value.length === clientSideFilteredUsers.value.length;
});

</script>

<template>
    <AdminLayout>
        <h1 class="text-xl md:text-2xl font-bold text-foreground mb-4">User Management</h1>
        
        <div class="bg-card rounded-lg p-3 md:p-4 mb-6 shadow">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <!-- Enhanced search input with icons and better error handling -->
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input 
                            type="text" 
                            v-model="searchInput"
                            @input="handleSearchInput"
                            class="w-full pl-10 pr-10 p-2 border rounded bg-background text-foreground focus:ring-2 focus:ring-primary-color focus:border-primary-color"
                            placeholder="Search users by name, username, etc..."
                            :disabled="isSearching"
                        />
                        <!-- Show X button only when there is content and we're not currently searching -->
                        <div v-if="searchInput && !isSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button 
                                @click="clearSearch" 
                                class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                title="Clear search"
                            >
                                <XCircleIcon class="h-5 w-5" />
                            </button>
                        </div>
                        <!-- Loading spinner with improved visibility -->
                        <div v-if="isSearching" class="absolute inset-y-0 right-3 flex items-center">
                            <svg class="animate-spin h-5 w-5 text-primary-color" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <!-- Add helpful search tip -->
                    <p class="text-xs text-gray-500 mt-1">
                        Search by name, username, or WMSU email
                    </p>
                </div>
                <!-- Filter dropdown -->
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Filter</label>
                    <select 
                        v-model="filter" 
                        @change="handleFilterChange"
                        class="w-full p-2 border rounded bg-background text-foreground"
                    >
                        <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
            
            <!-- Results feedback when no results or during search -->
            <div v-if="isSearching" class="mb-4 text-sm text-muted-foreground">
                <span class="flex items-center">
                    <svg class="animate-spin h-4 w-4 mr-2 text-primary-color" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Fetching complete results...
                </span>
            </div>
            
            <div v-else-if="search && clientSideFilteredUsers.length === 0" class="mb-4 py-2 px-3 bg-amber-50 border border-amber-200 rounded text-amber-800">
                No results found for "<span class="font-medium">{{ search }}</span>". Try adjusting your search term.
                <button 
                    @click="clearSearch" 
                    class="ml-2 text-blue-600 hover:text-blue-800 underline focus:outline-none"
                >
                    Clear search
                </button>
            </div>
            
            <!-- Users table -->
            <div class="overflow-x-auto -mx-3 md:mx-0">
                <table class="min-w-full bg-card border border-border">
                    <thead>
                        <tr class="bg-muted text-left">
                            <th class="py-2 px-3 border-b border-border">
                                <input 
                                    type="checkbox"
                                    :checked="isAllSelected" 
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th 
                                @click="sort('username')"
                                class="py-2 px-3 border-b border-border text-left cursor-pointer"
                            >
                                Username {{ sortField === 'username' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th 
                                @click="sort('email')"
                                class="py-2 px-3 border-b border-border text-left cursor-pointer hidden md:table-cell"
                            >
                                WMSU Email {{ sortField === 'email' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="py-2 px-3 border-b border-border text-left">Role</th>
                            <th 
                                @click="sort('created_at')"
                                class="py-2 px-3 border-b border-border text-left cursor-pointer hidden sm:table-cell"
                            >
                                Joined {{ sortField === 'created_at' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="py-2 px-3 border-b border-border text-left">Status</th>
                            <!-- Add column for ban status -->
                            <th class="py-2 px-3 border-b border-border text-left">Ban Status</th>
                            <th class="py-2 px-3 border-b border-border text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in clientSideFilteredUsers" :key="user.id" class="hover:bg-muted/50">
                            <td class="py-2 px-3 border-b border-border">
                                <input 
                                    type="checkbox" 
                                    v-model="selectedUsers" 
                                    :value="user.id"
                                />
                            </td>
                            <td class="py-2 px-3 border-b border-border">{{ user.username || user.name || 'Unknown' }}</td>
                            <td class="py-2 px-3 border-b border-border hidden md:table-cell">{{ user.wmsu_email || 'Not provided' }}</td>
                            <td class="py-2 px-3 border-b border-border">
                                <span 
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        user.role === 'Admin' ? 'bg-purple-100 text-purple-800' : 
                                        user.role === 'Seller' ? 'bg-green-100 text-green-800' : 
                                        'bg-blue-100 text-blue-800'
                                    ]"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="py-2 px-3 border-b border-border hidden sm:table-cell">{{ formatDate(user.created_at) }}</td>
                            <td class="py-2 px-3 border-b border-border">
                                <span 
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ user.status }}
                                </span>
                            </td>
                            <!-- New Ban Status column -->
                            <td class="py-2 px-3 border-b border-border">
                                <div v-if="user.is_banned" class="flex flex-col">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        <span class="relative flex h-2 w-2 mr-1">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                        </span>
                                        Banned
                                    </span>
                                    <div v-if="user.ban_details" class="mt-1 text-xs text-gray-500">
                                        <p v-if="user.ban_details.is_permanent">Permanent</p>
                                        <p v-else-if="user.ban_details.expires_at">
                                            Until {{ formatDate(user.ban_details.expires_at) }}
                                        </p>
                                    </div>
                                </div>
                                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="py-2 px-3 border-b border-border">
                                <div class="flex items-center space-x-1 sm:space-x-3">
                                    <!-- View details button with eye icon -->
                                    <div class="relative group">
                                        <button 
                                            @click="showUserDetails(user)"
                                            class="p-1.5 rounded hover:bg-blue-100 transition-colors duration-200"
                                        >
                                            <EyeIcon class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 group-hover:text-blue-800 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            View
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>

                                    <!-- Edit user button with pen icon -->
                                    <div class="relative group">
                                        <button 
                                            @click="editUser(user)"
                                            class="p-1.5 rounded hover:bg-green-100 transition-colors duration-200"
                                        >
                                            <PencilIcon class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 group-hover:text-green-800 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Edit
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>

                                    <!-- Unban button - only show if user is banned -->
                                    <div v-if="user.is_banned" class="relative group">
                                        <button 
                                            @click="unbanUser(user.id, user.username)"
                                            class="p-1.5 rounded hover:bg-blue-100 transition-colors duration-200"
                                        >
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Remove Ban
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>

                                    <!-- Ban user button - only show if user is not banned -->
                                    <div v-else class="relative group">
                                        <button 
                                            @click="showBanUserModal(user)"
                                            class="p-1.5 rounded hover:bg-orange-100 transition-colors duration-200"
                                        >
                                            <ShieldExclamationIcon class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600 group-hover:text-orange-800 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Ban User
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>

                                    <!-- Delete button with tooltip -->
                                    <div class="relative group">
                                        <button 
                                            @click="deleteUser(user.id)"
                                            class="p-1.5 rounded hover:bg-red-100 transition-colors duration-200"
                                        >
                                            <TrashIcon class="w-4 h-4 sm:w-5 sm:h-5 text-red-600 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Delete
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!clientSideFilteredUsers || clientSideFilteredUsers.length === 0">
                            <td colspan="8" class="text-center py-4 text-muted-foreground">No users found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Simple Pagination -->
            <div v-if="props.users.links && props.users.links.length > 0" class="mt-4 flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Showing {{ props.users.from || 0 }} to {{ props.users.to || 0 }} 
                        of {{ props.users.total || 0 }} results
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link 
                        v-if="props.users.prev_page_url" 
                        :href="props.users.prev_page_url"
                        class="px-3 sm:px-4 py-1 sm:py-2 bg-background border border-border rounded hover:bg-muted text-foreground text-sm"
                    >
                        Previous
                    </Link>
                    <Link 
                        v-if="props.users.next_page_url"
                        :href="props.users.next_page_url"
                        class="px-3 sm:px-4 py-1 sm:py-2 bg-background border border-border rounded hover:bg-muted text-foreground text-sm"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>

        <!-- User Details Modal -->
        <div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-sm mx-auto overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        User Details
                    </h3>
                    <button 
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-4 space-y-4" v-if="currentUser">
                    <div class="flex items-center mb-2">
                        <!-- User avatar - smaller size -->
                        <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden mr-4">
                            <img 
                                v-if="currentUser.profile_photo" 
                                :src="currentUser.profile_photo" 
                                alt="Profile photo"
                                class="w-full h-full object-cover"
                            >
                            <span v-else class="text-xl font-semibold text-gray-600">
                                {{ (currentUser.first_name?.[0] || currentUser.username?.[0] || 'U').toUpperCase() }}
                            </span>
                        </div>
                        
                        <!-- Name and role -->
                        <div>
                            <h4 class="text-base font-medium">{{ currentUser.username }}</h4>
                            <span 
                                :class="[
                                    'px-1.5 py-0.5 text-xs rounded-full inline-block mt-1',
                                    currentUser.role === 'Admin' ? 'bg-purple-100 text-purple-800' : 
                                    currentUser.role === 'Seller' ? 'bg-green-100 text-green-800' : 
                                    'bg-blue-100 text-blue-800'
                                ]"
                            >
                                {{ currentUser.role }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- User information - 2 columns to save space -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <!-- Full name -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Full Name</label>
                            <p class="text-gray-900">
                                {{ currentUser.first_name || 'N/A' }} 
                                {{ currentUser.middle_name ? currentUser.middle_name : '' }} 
                                {{ currentUser.last_name || '' }}
                            </p>
                        </div>
                        
                        <!-- Gender -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Gender</label>
                            <p class="text-gray-900 capitalize">{{ currentUser.gender || 'Not specified' }}</p>
                        </div>
                        
                        <!-- WMSU Email -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">WMSU Email</label>
                            <p class="text-gray-900 truncate">{{ currentUser.wmsu_email || 'Not provided' }}</p>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Date of Birth</label>
                            <p class="text-gray-900">{{ formatDateOfBirth(currentUser.date_of_birth) }}</p>
                        </div>
                        
                        
                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Phone Number</label>
                            <p class="text-gray-900">{{ currentUser.phone || 'Not provided' }}</p>
                        </div>
                        
                        <!-- Account Status -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Account Status</label>
                            <p>
                                <span 
                                    :class="[
                                        'px-1.5 py-0.5 text-xs rounded-full',
                                        currentUser.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ currentUser.status }}
                                </span>
                            </p>
                        </div>
                        
                        <!-- Registered Date -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Registered At</label>
                            <p class="text-gray-900">{{ formatDate(currentUser.created_at) }}</p>
                        </div>
                    </div>
                    
                    <!-- Add ban status to user details -->
                    <div class="mt-2" v-if="currentUser.is_banned && currentUser.ban_details">
                        <label class="block text-xs font-medium text-gray-500">Ban Information</label>
                        <div class="bg-red-50 p-3 rounded-md mt-1">
                            <p class="text-sm font-medium text-red-800">User is currently banned</p>
                            <p class="text-xs text-gray-700 mt-1">Reason: {{ currentUser.ban_details.reason }}</p>
                            <p class="text-xs text-gray-700 mt-1" v-if="currentUser.ban_details.is_permanent">
                                Duration: Permanent
                            </p>
                            <p class="text-xs text-gray-700 mt-1" v-else-if="currentUser.ban_details.expires_at">
                                Expires: {{ formatDate(currentUser.ban_details.expires_at) }}
                            </p>
                            <button 
                                @click="unbanUser(currentUser.id, currentUser.username); closeModal();"
                                class="mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                            >
                                Remove Ban
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-3 py-2 border-t flex justify-end">
                    <button 
                        @click="closeModal"
                        class="px-3 py-1 text-sm bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-1 focus:ring-gray-400"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- User Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Edit User
                    </h3>
                    <button 
                        @click="closeEditModal"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Modal Body / Edit Form -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input 
                                type="text" 
                                v-model="editForm.first_name"
                                class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                                :class="{ 'border-red-500': formErrors.first_name }"
                            />
                            <p v-if="formErrors.first_name" class="text-red-500 text-xs mt-1">{{ formErrors.first_name }}</p>
                        </div>
                        
                        <!-- Middle Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                            <input 
                                type="text" 
                                v-model="editForm.middle_name"
                                class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            />
                        </div>
                    </div>
                    
                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input 
                            type="text" 
                            v-model="editForm.last_name"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': formErrors.last_name }"
                        />
                        <p v-if="formErrors.last_name" class="text-red-500 text-xs mt-1">{{ formErrors.last_name }}</p>
                    </div>
                    
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input 
                            type="text" 
                            v-model="editForm.username"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': formErrors.username }"
                        />
                        <p v-if="formErrors.username" class="text-red-500 text-xs mt-1">{{ formErrors.username }}</p>
                    </div>
                    
                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select 
                            v-model="editForm.gender"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': formErrors.gender }"
                        >
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="non-binary">Non-binary</option>
                            <option value="prefer-not-to-say">Prefer not to say</option>
                        </select>
                        <p v-if="formErrors.gender" class="text-red-500 text-xs mt-1">{{ formErrors.gender }}</p>
                    </div>
                    
                    <!-- Date of Birth -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input 
                            type="date" 
                            v-model="editForm.date_of_birth"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': formErrors.date_of_birth }"
                        />
                        <p v-if="formErrors.date_of_birth" class="text-red-500 text-xs mt-1">{{ formErrors.date_of_birth }}</p>
                    </div>
                    
                    <!-- Phone Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input 
                            type="tel" 
                            v-model="editForm.phone"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': formErrors.phone }"
                            placeholder="+639XXXXXXXXX"
                        />
                        <p v-if="formErrors.phone" class="text-red-500 text-xs mt-1">{{ formErrors.phone }}</p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 border-t flex justify-end space-x-3">
                    <button 
                        @click="closeEditModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="saveUserEdit"
                        class="px-4 py-2 bg-primary-color border border-transparent rounded-md text-sm font-medium text-white hover:bg-primary-color/90"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- Ban User Modal -->
        <div v-if="showBanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <ShieldExclamationIcon class="w-5 h-5 text-orange-600 mr-2" />
                        Ban User: {{ banForm.username }}
                    </h3>
                    <button 
                        @click="closeBanModal"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Modal Body / Ban Form -->
                <div class="p-6 space-y-4">
                    <!-- Ban Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ban Duration</label>
                        <select 
                            v-model="banForm.duration"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                        >
                            <option v-for="duration in banDurations" :key="duration.value" :value="duration.value">
                                {{ duration.label }}
                            </option>
                        </select>
                    </div>
                    
                    <!-- Custom Duration Days Input (shown only when "Custom" is selected) -->
                    <div v-if="banForm.duration === 'custom'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Days</label>
                        <input 
                            type="number" 
                            v-model.number="banForm.custom_days"
                            min="1"
                            max="365"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': banFormErrors.custom_days }"
                            placeholder="Enter number of days"
                        />
                        <p v-if="banFormErrors.custom_days" class="text-red-500 text-xs mt-1">
                            {{ banFormErrors.custom_days }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            You can ban users for 1 to 365 days
                        </p>
                    </div>
                    
                    <!-- Ban Reason -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Ban</label>
                        <textarea 
                            v-model="banForm.reason"
                            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                            :class="{ 'border-red-500': banFormErrors.reason }"
                            rows="4"
                            placeholder="Please provide a reason for banning this user"
                        ></textarea>
                        <p v-if="banFormErrors.reason" class="text-red-500 text-xs mt-1">{{ banFormErrors.reason }}</p>
                    </div>
                    
                    <!-- Warning Message -->
                    <div class="bg-orange-50 p-3 rounded border border-orange-200">
                        <p class="text-sm text-orange-800">
                            <strong>Warning:</strong> Banning a user will prevent them from accessing the platform
                            according to the duration you select. This action can be reversed later.
                        </p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 border-t flex justify-end space-x-3">
                    <button 
                        @click="closeBanModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="submitBan"
                        class="px-4 py-2 bg-orange-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-orange-700"
                    >
                        Ban User
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Add some CSS to enhance the hover effect */
button:hover svg {
    color: #ef4444 !important; /* This is the Tailwind red-500 color */
}

.group:hover button {
    background-color: #fee2e2 !important; /* This is the Tailwind red-100 color */
}

/* Override for view button */
button:hover .text-blue-600 {
    color: #1d4ed8 !important; /* Tailwind blue-700 */
}

.group:hover button .text-blue-600 ~ div {
    background-color: #dbeafe !important; /* Tailwind blue-100 */
}

/* Override for edit button */
button:hover .text-green-600 {
    color: #15803d !important; /* Tailwind green-700 */
}

.group:hover button .text-green-600 ~ div {
    background-color: #dcfce7 !important; /* Tailwind green-100 */
}

/* New styles for ban button */
button:hover .text-orange-600 {
    color: #c2410c !important; /* Tailwind orange-700 */
}

.group:hover button .text-orange-600 ~ div {
    background-color: #ffedd5 !important; /* Tailwind orange-100 */
}

/* New styles for unban button */
button:hover .text-blue-600 {
    color: #2563eb !important; /* Tailwind blue-600 */
}

.group:hover button .text-blue-600 ~ div {
    background-color: #dbeafe !important; /* Tailwind blue-100 */
}

/* Add responsive styles for search input */
@media (max-width: 768px) {
    .pl-10 {
        padding-left: 2.5rem;
    }
    
    .pr-10 {
        padding-right: 2.5rem;
    }
}

/* Style for disabled state */
input:disabled, select:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>