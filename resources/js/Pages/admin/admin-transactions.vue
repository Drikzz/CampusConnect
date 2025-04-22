<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Updated import path
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    userTypes: Array,
    departments: Array
});

const selectedUsers = ref([]);
const search = ref('');
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">User Management</h2>
                <button 
                    class="bg-primary-color text-white px-4 py-2 rounded-lg hover:bg-primary-color/90"
                    @click="openAddUserModal"
                >
                    + Add User
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <input 
                    type="text" 
                    v-model="search" 
                    class="w-full md:w-1/2 p-2 border rounded-lg" 
                    placeholder="Search users..."
                >
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm font-semibold">
                            <th class="p-3">
                                <input type="checkbox" @change="toggleSelectAll">
                            </th>
                            <th class="p-3">Username</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">User Type</th>
                            <th class="p-3">Date Added</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id" class="border-t">
                            <td class="p-3">
                                <input 
                                    type="checkbox" 
                                    v-model="selectedUsers" 
                                    :value="user.id"
                                >
                            </td>
                            <td class="p-3">{{ user.username }}</td>
                            <td class="p-3">{{ user.first_name }} {{ user.last_name }}</td>
                            <td class="p-3">{{ user.email }}</td>
                            <td class="p-3">{{ user.user_type }}</td>
                            <td class="p-3">{{ formatDate(user.created_at) }}</td>
                            <td class="p-3 space-x-2">
                                <button 
                                    class="text-blue-600 hover:text-blue-800"
                                    @click="editUser(user)"
                                >
                                    Edit
                                </button>
                                <button 
                                    class="text-red-600 hover:text-red-800"
                                    @click="deleteUser(user.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>