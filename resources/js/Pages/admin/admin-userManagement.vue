<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    TrashIcon, 
    PencilIcon
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

// Simple date formatter
const formatDate = (dateString) => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString();
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
                            Email {{ sortField === 'email' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
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
                        <td class="py-2 px-3 border-b">{{ user.email }}</td>
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
                                <!-- Edit button with tooltip -->
                                <div class="relative group">
                                    <button 
                                        class="p-1.5 rounded hover:bg-red-100 transition-colors duration-200"
                                    >
                                        <PencilIcon class="w-5 h-5 text-gray-600 group-hover:text-red-600 transition-colors duration-200" />
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
</style>