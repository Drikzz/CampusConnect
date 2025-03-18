<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    TrashIcon, 
    PencilIcon,
    XMarkIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';

// Define props to receive data from the controller
const props = defineProps({
    users: Object,
    filters: Object
});

// Reactive state
const selectedUsers = ref([]);
const search = ref(props.filters?.search || '');
const filter = ref(props.filters?.filter || 'all');
const sortField = ref(props.filters?.sort || 'created_at');
const sortDirection = ref(props.filters?.direction || 'desc');
const showUserModal = ref(false);
const currentUser = ref(null);
const showEditModal = ref(false);
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
const formErrors = ref({});

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
    { value: 'unverified', label: 'Unverified Users' }
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


// Handle search and filters
const performSearch = () => {
    router.get(route('admin.users'), {
        search: search.value,
        filter: filter.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Watch for changes to trigger search
watch([search, filter], () => {
    performSearch();
});

// Handle sorting
const sort = (field) => {
    sortDirection.value = sortField.value === field && sortDirection.value === 'asc' ? 'desc' : 'asc';
    sortField.value = field;
    performSearch();
};

// Computed properties
const isAllSelected = computed(() => {
    return props.users.data.length > 0 && selectedUsers.value.length === props.users.data.length;
});

</script>

<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold mb-4">User Management</h1>
        
        <div class="bg-white rounded-lg p-4 mb-6">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input 
                        type="text" 
                        v-model="search" 
                        class="w-full p-2 border rounded"
                        placeholder="Search users..."
                    />
                </div>
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
                    <select 
                        v-model="filter" 
                        class="w-full p-2 border rounded"
                    >
                        <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
            
            <table class="min-w-full bg-white border">
                <thead>
                    <tr>
                        <th class="py-2 px-3 border-b">
                            <input 
                                type="checkbox"
                                :checked="isAllSelected" 
                                @change="toggleSelectAll"
                            />
                        </th>
                        <th 
                            @click="sort('username')"
                            class="py-2 px-3 border-b text-left cursor-pointer"
                        >
                            Username {{ sortField === 'username' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </th>
                        <th 
                            @click="sort('email')"
                            class="py-2 px-3 border-b text-left cursor-pointer"
                        >
                            WMSU Email {{ sortField === 'email' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </th>
                        <th class="py-2 px-3 border-b text-left">Role</th>
                        <th 
                            @click="sort('created_at')"
                            class="py-2 px-3 border-b text-left cursor-pointer"
                        >
                            Joined {{ sortField === 'created_at' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                        </th>
                        <th class="py-2 px-3 border-b text-left">Status</th>
                        <th class="py-2 px-3 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in props.users.data" :key="user.id" class="hover:bg-gray-50">
                        <td class="py-2 px-3 border-b">
                            <input 
                                type="checkbox" 
                                v-model="selectedUsers" 
                                :value="user.id"
                            />
                        </td>
                        <td class="py-2 px-3 border-b">{{ user.username || user.name || 'Unknown' }}</td>
                        <td class="py-2 px-3 border-b">{{ user.wmsu_email || 'Not provided' }}</td>
                        <td class="py-2 px-3 border-b">
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
                        <td class="py-2 px-3 border-b">{{ formatDate(user.created_at) }}</td>
                        <td class="py-2 px-3 border-b">
                            <span 
                                :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]"
                            >
                                {{ user.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3 border-b">
                            <div class="flex items-center space-x-3">
                                <!-- View details button with eye icon -->
                                <div class="relative group">
                                    <button 
                                        @click="showUserDetails(user)"
                                        class="p-1.5 rounded hover:bg-blue-100 transition-colors duration-200"
                                    >
                                        <EyeIcon class="w-5 h-5 text-blue-600 group-hover:text-blue-800 transition-colors duration-200" />
                                    </button>
                                    <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        View User Details
                                        <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                    </div>
                                </div>

                                <!-- Edit user button with pen icon -->
                                <div class="relative group">
                                    <button 
                                        @click="editUser(user)"
                                        class="p-1.5 rounded hover:bg-green-100 transition-colors duration-200"
                                    >
                                        <PencilIcon class="w-5 h-5 text-green-600 group-hover:text-green-800 transition-colors duration-200" />
                                    </button>
                                    <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Edit User
                                        <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                    </div>
                                </div>

                                <!-- Delete button with tooltip -->
                                <div class="relative group">
                                    <button 
                                        @click="deleteUser(user.id)"
                                        class="p-1.5 rounded hover:bg-red-100 transition-colors duration-200"
                                    >
                                        <TrashIcon class="w-5 h-5 text-red-600 transition-colors duration-200" />
                                    </button>
                                    <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                        Delete User
                                        <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.users.data || props.users.data.length === 0">
                        <td colspan="7" class="text-center py-4">No users found</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Simple Pagination -->
            <div v-if="props.users.links && props.users.links.length > 0" class="mt-4 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing {{ props.users.from || 0 }} to {{ props.users.to || 0 }} 
                        of {{ props.users.total || 0 }} results
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link 
                        v-if="props.users.prev_page_url" 
                        :href="props.users.prev_page_url"
                        class="px-4 py-2 bg-white border rounded hover:bg-gray-50"
                    >
                        Previous
                    </Link>
                    <Link 
                        v-if="props.users.next_page_url"
                        :href="props.users.next_page_url"
                        class="px-4 py-2 bg-white border rounded hover:bg-gray-50"
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
</style>