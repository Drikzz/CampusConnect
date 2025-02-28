<script setup lang="ts">
import { ref, shallowRef } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '../DashboardLayout.vue';
import StatsCard from '../../../Components/StatsCard.vue'
import OrderStatusBadge from '../../../Components/OrderStatusBadge.vue'
import { Button } from '@/Components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  FlexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useVueTable,
  type ColumnDef,
} from '@tanstack/vue-table'
import { h } from 'vue'

const props = defineProps({
  user: Object,
  stats: Object,
  orders: Array
})

const data = shallowRef(props.orders)

const columns: ColumnDef<any>[] = [
  {
    accessorKey: 'id',
    header: 'Order ID',
    cell: ({ row }) => h('div', {}, `#${row.getValue('id')}`),
  },
  {
    accessorKey: 'buyer',
    header: 'Customer',
    cell: ({ row }) => h('div', {}, [
      h('div', { class: 'font-medium' }, row.getValue('buyer').name),
      h('div', { class: 'text-sm text-gray-500' }, row.getValue('buyer').email),
    ]),
  },
  {
    accessorKey: 'total',
    header: 'Total',
    cell: ({ row }) => h('div', {}, `₱${formatNumber(row.getValue('total'))}`),
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) => h(OrderStatusBadge, { status: row.getValue('status') }),
  },
  {
    accessorKey: 'created_at',
    header: 'Date',
    cell: ({ row }) => h('div', {}, formatDate(row.getValue('created_at'))),
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h(Button, {
      variant: 'link',
      onClick: () => viewOrder(row.getValue('id')),
    }, 'View Details'),
  },
]

const table = useVueTable({
  get data() {
    return data.value
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
})

const formatNumber = (num: number) => new Intl.NumberFormat().format(num)
const formatDate = (date: string) => new Date(date).toLocaleDateString()

const viewOrder = (orderId: string) => {
  router.visit(route('seller.orders.show', orderId))
}
</script>

<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-6">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard title="Pending Orders" :value="stats.pendingOrders" />
        <StatsCard title="Active Orders" :value="stats.activeOrders" />
        <StatsCard title="Total Orders" :value="stats.totalOrders" />
        <StatsCard title="Total Sales" :value="`₱${formatNumber(stats.totalSales)}`" />
      </div>

      <!-- Orders Table -->
      <div class="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
              <TableHead v-for="header in headerGroup.headers" :key="header.id">
                <FlexRender
                  v-if="!header.isPlaceholder"
                  :render="header.column.columnDef.header"
                  :props="header.getContext()"
                />
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="table.getRowModel().rows?.length">
              <TableRow
                v-for="row in table.getRowModel().rows"
                :key="row.id"
                class="hover:bg-muted/50"
              >
                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                  <FlexRender
                    :render="cell.column.columnDef.cell"
                    :props="cell.getContext()"
                  />
                </TableCell>
              </TableRow>
            </template>
            <TableRow v-else>
              <TableCell :colspan="columns.length" class="h-24 text-center">
                No orders found.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-end space-x-2 py-4">
        <Button
          variant="outline"
          size="sm"
          :disabled="!table.getCanPreviousPage()"
          @click="table.previousPage()"
        >
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="!table.getCanNextPage()"
          @click="table.nextPage()"
        >
          Next
        </Button>
      </div>
    </div>
  </DashboardLayout>
</template>
