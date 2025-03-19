<template>
  <div class="w-full">
    <!-- Table Controls -->
    <div class="flex items-center justify-between py-4">
      <div class="flex gap-2 items-center">
        <Input class="max-w-sm" :placeholder="searchPlaceholder || 'Search...'" :model-value="filterValue"
          @update:model-value="onFilterChange" />
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline">
              Columns
              <ChevronDown class="ml-2 h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuCheckboxItem v-for="column in columns" :key="column.key" class="capitalize"
              :model-value="!hiddenColumns.includes(column.key)"
              @update:model-value="(value) => toggleColumn(column.key, value)">
              {{ column.label }}
            </DropdownMenuCheckboxItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Add Reset Filters Button -->
        <Button v-if="showResetButton || filterValue || hiddenColumns.length > 0 || sortConfig.key" variant="ghost"
          @click="resetFilters" class="text-xs">
          <RefreshCwIcon class="h-4 w-4 mr-1" />
          {{ resetButtonLabel || 'Reset Filters' }}
        </Button>
      </div>

      <div class="flex gap-2 items-center">
        <!-- Custom buttons area (like Add Funds, Withdraw) -->
        <slot name="custom-buttons" v-if="showCustomButtons"></slot>

        <!-- Bulk Action Buttons -->
        <div v-if="selectable && selectedRows.length > 0" class="flex gap-2">
          <Button v-if="hasDeletedItems" variant="outline" @click="handleBulkRestore">
            Restore Selected ({{ selectedRows.length }})
          </Button>
          <Button v-if="hasDeletedItems" variant="destructive" @click="handleBulkForceDelete">
            Delete Permanently ({{ selectedRows.length }})
          </Button>
          <Button v-else variant="destructive" @click="handleBulkDelete">
            Archive Selected ({{ selectedRows.length }})
          </Button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="rounded-md border data-table">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[40px] px-4" v-if="selectable">
              <Checkbox :model-value="isAllSelected" @update:model-value="toggleSelectAll" aria-label="Select all" />
            </TableHead>
            <TableHead v-for="column in visibleColumns" :key="column.key" :class="{
              'cursor-pointer': column.sortable,
              'w-[50px] text-center': column.key === 'rowNumber',
              'w-[80px]': column.key === 'type'
            }" @click="column.sortable && sortBy(column.key)">
              <div class="flex items-center gap-3" :class="{ 'justify-center': column.key === 'rowNumber' }">
                <!-- Changed from space-x-2 to gap-3 for better spacing -->
                <span>{{ column.label }}</span>
                <ArrowUpDown v-if="column.sortable" class="h-4 w-4" />
              </div>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="row in paginatedData" :key="row.id">
            <!-- Add row checkbox -->
            <TableCell class="w-[40px] px-4" v-if="selectable">
              <Checkbox :model-value="selectedRows.includes(row.id)"
                @update:model-value="(checked) => toggleRow(row.id, checked)" aria-label="Select row" />
            </TableCell>
            <TableCell v-for="column in visibleColumns" :key="column.key"
              :class="{ 'text-center': column.key === 'rowNumber' }">
              <!-- Row Number Cell -->
              <span v-if="column.key === 'rowNumber'" class="font-medium text-gray-500">
                {{ row.rowNumber || ((currentPage - 1) * itemsPerPageRef) + paginatedData.indexOf(row) + 1 }}
              </span>

              <!-- Type Cell with Icon -->
              <div v-else-if="column.key === 'type'" class="flex items-center space-x-3">
                <div v-if="row.typeIcon" class="p-1.5 rounded-full" :class="row.typeIconBg">
                  <component :is="row.typeIcon" class="h-4 w-4" :class="row.typeIconClass" />
                </div>
                <span>{{ row.type }}</span>
              </div>

              <!-- Product Cell -->
              <div v-else-if="column.key === 'product'" class="flex items-center space-x-3">
                <img :src="row.images?.[0] ? `/storage/${row.images[0]}` : '/placeholder.png'"
                  class="h-10 w-10 rounded-lg object-cover" :alt="row.name" />
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

              <!-- Amount Cell with color based on credit/debit -->
              <span v-else-if="column.key === 'amount'" :class="{
                'text-green-600 font-medium': row.amount?.includes('+'),
                'text-red-600 font-medium': row.amount?.includes('-'),
                'text-red font-medium': row.isWithdrawal
              }">
                {{ row[column.key] }}
              </span>

              <!-- Type Cell - to show Credit/Debit with proper color -->
              <span v-else-if="column.key === 'type'" :class="{
                'text-green-600 font-medium': row.isCredit === true,
                'text-red-600 font-medium': !row.isCredit,
                'text-red font-medium': row.isWithdrawal
              }">
                {{ row[column.key] }}
              </span>

              <!-- Status Cell - Fixed to properly style all statuses including rejected -->
              <span v-else-if="column.key === 'status'" :class="{
                'px-2 py-1 rounded-full text-xs border': true,
                'bg-yellow-100 text-yellow-800 border-yellow-300': row.status?.toLowerCase() === 'pending',
                'bg-blue-100 text-blue-800 border-blue-300': row.status?.toLowerCase() === 'in_process',
                'bg-green-100 text-green-800 border-green-300': ['active', 'completed', 'approved'].includes(row.status?.toLowerCase()),
                'bg-red/10 text-red border-red/30': ['inactive', 'rejected', 'failed', 'denied'].includes(row.status?.toLowerCase())
              }">
                {{ formatStatus(row.status) }}
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
              <div v-else-if="column.key === 'actions'" class="flex justify-end gap-2">
                <template v-for="(action, idx) in getActions(row)" :key="idx">
                  <Button size="sm" :variant="action.variant" :class="action.class" @click="action.action">
                    {{ action.label }}
                  </Button>
                </template>
              </div>

              <!-- Default Cell -->
              <template v-else>
                {{ row[column.key] }}
              </template>
            </TableCell>
          </TableRow>

          <!-- No results row -->
          <TableRow v-if="filteredData.length === 0">
            <TableCell :colspan="visibleColumns.length + (selectable ? 1 : 0)" class="h-24 text-center">
              No results found.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between py-4">
      <span class="text-sm text-gray-700">
        Showing {{ paginatedData.length ? ((currentPage - 1) * itemsPerPageRef) + 1 : 0 }}
        to {{ Math.min(currentPage * itemsPerPageRef, filteredData.length) }}
        of {{ filteredData.length }} items
      </span>
      <div class="flex items-center space-x-6">
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-700">Rows per page:</span>
          <select :value="itemsPerPageRef" @change="updateItemsPerPage($event.target.value)"
            class="border-gray-300 rounded-md text-sm focus:ring-primary-500 focus:border-primary-500">
            <option v-for="option in [5, 10, 15, 20, 50]" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>
        <div class="space-x-2 flex items-center">
          <Button variant="outline" size="sm" :disabled="currentPage <= 1" @click="changePage(currentPage - 1)">
            Previous
          </Button>
          <span class="mx-2 text-sm">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <Button variant="outline" size="sm" :disabled="currentPage >= totalPages"
            @click="changePage(currentPage + 1)">
            Next
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ArrowUpDown, RefreshCwIcon } from 'lucide-vue-next'
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
  },
  selectable: {
    type: Boolean,
    default: false
  },
  itemsPerPage: {
    type: Number,
    default: 10
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  // Add new props for reset functionality
  showResetButton: {
    type: Boolean,
    default: false
  },
  resetButtonLabel: {
    type: String,
    default: 'Reset Filters'
  },
  // Add prop for showing custom buttons
  showCustomButtons: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['bulk-delete', 'bulk-restore', 'bulk-force-delete', 'update:itemsPerPage'])

// Table state
const filterValue = ref('')
const hiddenColumns = ref([])
const currentPage = ref(1)
const selectedRows = ref([])
const itemsPerPageRef = ref(props.itemsPerPage)

// Watch for changes in the itemsPerPage prop
watch(() => props.itemsPerPage, (newValue) => {
  itemsPerPageRef.value = newValue;
}, { immediate: true });

// Function to update items per page
const updateItemsPerPage = (value) => {
  const numValue = parseInt(value, 10);
  itemsPerPageRef.value = numValue;
  currentPage.value = 1; // Reset to first page when changing items per page
  emit('update:itemsPerPage', numValue);
};

// Add sorting state
const sortConfig = ref({ key: null, direction: 'asc' })

// Watch for data changes to reset pagination
watch(() => props.data.length, () => {
  currentPage.value = 1
})

// Watch for filter changes to reset pagination
watch(filterValue, () => {
  currentPage.value = 1
})

// Computed properties
const visibleColumns = computed(() => {
  return props.columns.filter(col => !hiddenColumns.value.includes(col.key))
})

// Update filteredData to include comprehensive search
const filteredData = computed(() => {
  let filtered = [...props.data]

  // Apply filter
  if (filterValue.value) {
    const search = filterValue.value.toLowerCase()
    filtered = filtered.filter(item => {
      // Check all visible fields for matches
      return visibleColumns.value.some(column => {
        const value = item[column.key]
        if (value === null || value === undefined) return false
        return String(value).toLowerCase().includes(search)
      })
    })
  }

  // Apply sorting
  if (sortConfig.value.key) {
    filtered.sort((a, b) => {
      let aVal = a[sortConfig.value.key]
      let bVal = b[sortConfig.value.key]

      // Handle numeric values (extract numbers from formatted strings)
      if (['price', 'stock', 'amount'].includes(sortConfig.value.key)) {
        // Extract numeric part if the value contains currency symbols or formatting
        if (typeof aVal === 'string') {
          aVal = parseFloat(aVal.replace(/[^-0-9.]/g, ''))
        }
        if (typeof bVal === 'string') {
          bVal = parseFloat(bVal.replace(/[^-0-9.]/g, ''))
        }
      }

      // Handle date strings
      if (sortConfig.value.key === 'created_at' || sortConfig.value.key === 'date') {
        aVal = new Date(aVal).getTime()
        bVal = new Date(bVal).getTime()
      }

      if (sortConfig.value.direction === 'asc') {
        return aVal > bVal ? 1 : -1
      }
      return aVal < bVal ? 1 : -1
    })
  }

  return filtered
})

// Add paginatedData computed property
const paginatedData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPageRef.value
  const endIndex = startIndex + itemsPerPageRef.value
  return filteredData.value.slice(startIndex, endIndex)
})

// Add a check to prevent divide by zero when calculating total pages
const totalPages = computed(() => {
  if (filteredData.value.length === 0) return 1;
  return Math.max(1, Math.ceil(filteredData.value.length / itemsPerPageRef.value));
})

const isAllSelected = computed(() => {
  return paginatedData.value.length > 0 &&
    paginatedData.value.every(row => selectedRows.value.includes(row.id))
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
    selectedRows.value = paginatedData.value.map(row => row.id)
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

// Format status
const formatStatus = (status) => {
  if (!status) return '';

  const statusMap = {
    'pending': 'Pending',
    'in_process': 'In Process',
    'completed': 'Completed',
    'rejected': 'Rejected',
    'active': 'Active',
    'inactive': 'Inactive'
  };

  return statusMap[status.toLowerCase()] || status.charAt(0).toUpperCase() + status.slice(1);
};

// Expose selected rows to parent component
defineExpose({
  selectedRows,
  clearSelection: () => selectedRows.value = []
})

// Add method to handle page changes without preserveScroll
const changePage = (newPage) => {
  currentPage.value = newPage;
  // Scroll to top of the table when changing pages
  setTimeout(() => {
    const tableElement = document.querySelector('.data-table');
    if (tableElement) {
      tableElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }, 100);
}

// Add reset filters method
const resetFilters = () => {
  filterValue.value = '';
  hiddenColumns.value = [];
  sortConfig.value = { key: null, direction: 'asc' };
  currentPage.value = 1;
}
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
