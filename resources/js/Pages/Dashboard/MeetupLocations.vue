<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Manage Meetup Locations</h2>
        <button @click="showAddLocationModal" class="bg-primary-color text-white px-4 py-2 rounded-lg">
          Add New Location
        </button>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="location in locations" :key="location.id">
              <td class="px-6 py-4">
                <div class="font-medium">{{ location.name }}</div>
                <div v-if="location.landmark" class="text-sm text-gray-500">
                  Landmark: {{ location.landmark }}
                </div>
              </td>
              <td class="px-6 py-4">{{ location.address }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                  location.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ location.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <button @click="editLocation(location)" class="text-blue-600 hover:text-blue-800">
                  Edit
                </button>
                <button @click="deleteLocation(location.id)" class="text-red-600 hover:text-red-800">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'

const props = defineProps({
  user: Object,
  stats: Object,
  locations: Array
})

const locations = ref([])

// Add your methods here for CRUD operations
function showAddLocationModal() {
  // Implementation
}

function editLocation(location) {
  router.get(route('dashboard.address.edit', location.id))
}

function deleteLocation(id) {
  if (confirm('Are you sure you want to delete this location?')) {
    router.delete(route('dashboard.address.destroy', id))
  }
}
</script>
