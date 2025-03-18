<template>
  <AdminLayout :user="auth.user">
    <div class="container mx-auto px-4">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold">Reports & Complaints</h1>
        <p class="text-gray-600">Manage user reports and complaints</p>
      </div>

      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6">
          <!-- Search and Filters -->
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
              <Input 
                type="search"
                placeholder="Search reports..."
                v-model="search"
                class="w-64"
              />
              <Select v-model="filter">
                <SelectTrigger>
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Reports</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="resolved">Resolved</SelectItem>
                  <SelectItem value="investigating">Investigating</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Reports Table -->
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Reporter</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Subject</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Date</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <!-- Add table rows here -->
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Input } from '@/Components/ui/input'
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue 
} from '@/Components/ui/select'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/Components/ui/table'

defineProps({
  auth: {
    type: Object,
    required: true
  },
  reports: {
    type: Array,
    default: () => []
  }
})

const search = ref('')
const filter = ref('all')
</script>