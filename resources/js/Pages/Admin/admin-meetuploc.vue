<template>
  <AdminLayout :user="auth.user">
    <div class="container mx-auto px-4">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold">Meetup Locations</h1>
        <p class="text-gray-600">Manage system-wide meetup locations</p>
      </div>

      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6">
          <!-- Search and Actions -->
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
              <Input 
                type="search"
                placeholder="Search locations..."
                v-model="search"
                class="w-64"
              />
              <Select v-model="filter">
                <SelectTrigger>
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Locations</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <Button @click="openCreateDialog">Add New Location</Button>
          </div>

          <!-- Locations Table -->
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Address</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Created At</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="location in locations.data" :key="location.id">
                  <TableCell>{{ location.id }}</TableCell>
                  <TableCell>{{ location.name }}</TableCell>
                  <TableCell>{{ location.address }}</TableCell>
                  <TableCell>
                    <Badge :variant="location.is_active ? 'success' : 'secondary'">
                      {{ location.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </TableCell>
                  <TableCell>{{ formatDate(location.created_at) }}</TableCell>
                  <TableCell>
                    <div class="flex items-center gap-2">
                      <Button variant="outline" size="sm" @click="editLocation(location)">
                        Edit
                      </Button>
                      <Button 
                        variant="destructive" 
                        size="sm" 
                        @click="confirmDelete(location)"
                      >
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog :open="showDialog" @update:open="showDialog = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ editingLocation ? 'Edit' : 'Create' }} Location</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <Label>Name</Label>
            <Input v-model="form.name" required />
          </div>
          <div>
            <Label>Address</Label>
            <Textarea v-model="form.address" required />
          </div>
          <div>
            <Label>Description</Label>
            <Textarea v-model="form.description" />
          </div>
          <div class="flex items-center gap-2">
            <Checkbox v-model="form.is_active" id="is_active" />
            <Label for="is_active">Active</Label>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="showDialog = false">
              Cancel
            </Button>
            <Button type="submit">Save</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Location</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this location? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction @click="deleteLocation">Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { Label } from '@/Components/ui/label'
import { Checkbox } from '@/Components/ui/checkbox'
import { Badge } from '@/Components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/Components/ui/dialog'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/Components/ui/alert-dialog'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'

const props = defineProps({
  auth: Object,
  locations: Object,
})

const search = ref('')
const filter = ref('all')
const showDialog = ref(false)
const showDeleteDialog = ref(false)
const editingLocation = ref(null)
const form = ref({
  name: '',
  address: '',
  description: '',
  is_active: true,
})

const handleSubmit = () => {
  if (editingLocation.value) {
    router.put(route('admin.locations.update', editingLocation.value.id), form.value)
  } else {
    router.post(route('admin.locations.store'), form.value)
  }
  showDialog.value = false
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const openCreateDialog = () => {
  editingLocation.value = null
  form.value = {
    name: '',
    address: '',
    description: '',
    is_active: true,
  }
  showDialog.value = true
}

const editLocation = (location) => {
  editingLocation.value = location
  form.value = {
    name: location.name,
    address: location.address,
    description: location.description,
    is_active: location.is_active,
  }
  showDialog.value = true
}

const confirmDelete = (location) => {
  editingLocation.value = location
  showDeleteDialog.value = true
}

const deleteLocation = () => {
  router.delete(route('admin.locations.destroy', editingLocation.value.id))
  showDeleteDialog.value = false
}
</script>