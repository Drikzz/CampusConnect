<template>
  <div class="w-full">
    <!-- Table Controls -->
    <div class="flex items-center justify-between py-4">
      <div class="flex gap-2 items-center">
        <Input
          class="max-w-sm"
          placeholder="Filter products..."
          :model-value="filterValue"
          @update:model-value="onFilterChange"
        />
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline">
              Columns <ChevronDown class="ml-2 h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuCheckboxItem
              v-for="column in columns"
              :key="column.key"
              class="capitalize"
              :model-value="!hiddenColumns.includes(column.key)"
              @update:model-value="(value) => toggleColumn(column.key, value)"
            >
              {{ column.label }}
            </DropdownMenuCheckboxItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Bulk Action Buttons -->
      <div v-if="selectedRows.length > 0" class="flex gap-2">
        <Button 
          v-if="hasDeletedItems"
          variant="outline" 
          @click="handleBulkRestore"
        >
          Restore Selected ({{ selectedRows.length }})
        </Button>
        <Button 
          v-if="hasDeletedItems"
          variant="destructive" 
          @click="handleBulkForceDelete"
        >
          Delete Permanently ({{ selectedRows.length }})
        </Button>
        <Button 
          v-else
          variant="destructive" 
          @click="handleBulkDelete"
        >
          Archive Selected ({{ selectedRows.length }})
        </Button>
      </div>
    </div>

    <!-- Table -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[40px] px-4">
              <Checkbox
                :model-value="isAllSelected"
                @update:model-value="toggleSelectAll"
                aria-label="Select all"
              />
            </TableHead>
            <TableHead 
              v-for="column in visibleColumns" 
              :key="column.key"
              :class="{ 'cursor-pointer': column.sortable }"
              @click="column.sortable && sortBy(column.key)"
            >
              <div class="flex items-center space-x-2">
                {{ column.label }}
                <ArrowUpDown v-if="column.sortable" class="h-4 w-4" />
              </div>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="row in filteredData" :key="row.id">
            <!-- Add row checkbox -->
            <TableCell class="w-[40px] px-4">
              <Checkbox
                :model-value="selectedRows.includes(row.id)"
                @update:model-value="(checked) => toggleRow(row.id, checked)"
                aria-label="Select row"
              />
            </TableCell>
            <TableCell v-for="column in visibleColumns" :key="column.key">
              <!-- Product Cell -->
              <div v-if="column.key === 'product'" class="flex items-center space-x-3">
                <img 
                  :src="row.images?.[0] ? `/storage/${row.images[0]}` : '/placeholder.png'"
                  class="h-10 w-10 rounded-lg object-cover"
                  :alt="row.name"
                />
                <div>
                  <span class="font-medium">{{ row.name }}</span>
                  <p v-if="row.discounted_price" class="text-xs text-gray-500">
                    <span class="line-through">₱{{ row.price }}</span>
                    <span class="text-primary-color ml-1">₱{{ row.discounted_price }}</span>
                  </p>
                </div>
              </div>

              <!-- Category Cell -->
              <span v-else-if="column.key === 'category'">
                {{ row.category?.name }}
              </span>

              <!-- Price Cell -->
              <template v-else-if="column.key === 'price'">
                <span v-if="!row.discounted_price">₱{{ row.price }}</span>
                <span v-else class="text-primary-color">₱{{ row.discounted_price }}</span>
              </template>

              <!-- Status Cell -->
              <span v-else-if="column.key === 'status'" :class="{
                'px-2 py-1 rounded-full text-xs': true,
                'bg-green-100 text-green-800': row.status === 'Active',
                'bg-red-100 text-red-800': row.status === 'Inactive'
              }">
                {{ row.status }}
              </span>

              <!-- Availability Cell -->
              <div v-else-if="column.key === 'availability'" class="flex space-x-1">
                <span v-if="row.is_buyable" class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                  Sale
                </span>
                <span v-if="row.is_tradable" class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">
                  Trade
                </span>
              </div>

              <!-- Actions Cell -->
              <div v-else-if="column.key === 'actions'" class="flex justify-end space-x-2">
                <slot name="actions" :row="row">
                  <template v-for="action in getActions(row)" :key="action.label">
                    <Button 
                      size="sm" 
                      :variant="action.variant"
                      :class="action.class"
                      @click="action.action"
                    >
                      {{ action.label }}
                    </Button>
                  </template>
                </slot>
              </div>

              <!-- Default Cell -->
              <template v-else>
                {{ row[column.key] }}
              </template>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between py-4">
      <span class="text-sm text-gray-700">
        Showing {{ data.length }} items
      </span>
      <div class="space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="currentPage <= 1"
          @click="currentPage--"
        >
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="currentPage >= totalPages"
          @click="currentPage++"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ArrowUpDown } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { ChevronDown } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { Checkbox } from '@/Components/ui/checkbox'

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  getActions: {
    type: Function,
    default: () => []
  }
})

const emit = defineEmits(['bulk-delete', 'bulk-restore', 'bulk-force-delete'])

// Table state
const filterValue = ref('')
const hiddenColumns = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(10)
const selectedRows = ref([])

// Add sorting state
const sortConfig = ref({ key: null, direction: 'asc' })

// Computed properties
const visibleColumns = computed(() => {
  return props.columns.filter(col => !hiddenColumns.value.includes(col.key))
})

// Update filteredData to include sorting
const filteredData = computed(() => {
  let filtered = [...props.data]
  
  // Apply filter
  if (filterValue.value) {
    const search = filterValue.value.toLowerCase()
    filtered = filtered.filter(item => 
      item.name.toLowerCase().includes(search) ||
      item.category?.name.toLowerCase().includes(search)
    )
  }
  
  // Apply sorting
  if (sortConfig.value.key) {
    filtered.sort((a, b) => {
      let aVal = a[sortConfig.value.key]
      let bVal = b[sortConfig.value.key]
      
      // Handle numeric values
      if (['price', 'stock'].includes(sortConfig.value.key)) {
        aVal = parseFloat(aVal)
        bVal = parseFloat(bVal)
      }
      
      if (sortConfig.value.direction === 'asc') {
        return aVal > bVal ? 1 : -1
      }
      return aVal < bVal ? 1 : -1
    })
  }
  
  return filtered
})

const totalPages = computed(() => {
  return Math.ceil(filteredData.value.length / itemsPerPage.value)
})

const isAllSelected = computed(() => {
  return filteredData.value.length > 0 && 
         filteredData.value.every(row => selectedRows.value.includes(row.id))
})

// Add computed for checking deleted items
const hasDeletedItems = computed(() => {
  return selectedRows.value.some(id => 
    props.data.find(item => item.id === id)?.deleted_at
  )
})

// Methods
const onFilterChange = (value) => {
  filterValue.value = value
  currentPage.value = 1
}

const toggleColumn = (key, visible) => {
  if (visible) {
    hiddenColumns.value = hiddenColumns.value.filter(k => k !== key)
  } else {
    hiddenColumns.value.push(key)
  }
}

const toggleSelectAll = (checked) => {
  if (checked) {
    selectedRows.value = filteredData.value.map(row => row.id)
  } else {
    selectedRows.value = []
  }
}

const toggleRow = (rowId, checked) => {
  if (checked) {
    selectedRows.value.push(rowId)
  } else {
    selectedRows.value = selectedRows.value.filter(id => id !== rowId)
  }
}

// Add sorting method
const sortBy = (key) => {
  if (sortConfig.value.key === key) {
    sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc'
  } else {
    sortConfig.value = { key, direction: 'asc' }
  }
}

// Add new methods for bulk actions
const handleBulkDelete = () => {
  if (selectedRows.value.length === 0) return;
  emit('bulk-delete', selectedRows.value);
}

const handleBulkRestore = () => {
  if (selectedRows.value.length === 0) return;
  emit('bulk-restore', selectedRows.value);
}

const handleBulkForceDelete = () => {
  if (selectedRows.value.length === 0) return;
  emit('bulk-force-delete', selectedRows.value);
}

// Expose selected rows to parent component
defineExpose({
  selectedRows,
  clearSelection: () => selectedRows.value = []
})
</script>

<style scoped>
.text-primary-color {
  color: var(--primary-color, #007bff);
}

/* Add sticky styles for pinned columns */
.sticky {
  position: sticky;
  z-index: 1;
}

.left-0 {
  left: 0;
}

.right-0 {
  right: 0;
}
</style>
