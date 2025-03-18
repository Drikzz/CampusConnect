<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
      <CardTitle>Seller Wallet Overview</CardTitle>
      <Input v-model="search" placeholder="Search sellers..." class="w-[200px]" />
    </CardHeader>
    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Seller Name</TableHead>
            <TableHead>Seller Code</TableHead>
            <TableHead>Balance</TableHead>
            <TableHead>Total Listings</TableHead>
            <TableHead>Fees Collected</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="seller in filteredSellers" :key="seller.id">
            <TableCell>{{ seller.name }}</TableCell>
            <TableCell>{{ seller.seller_code }}</TableCell>
            <TableCell>₱{{ formatNumber(seller.balance) }}</TableCell>
            <TableCell>{{ seller.total_listings }}</TableCell>
            <TableCell>₱{{ formatNumber(seller.fees_collected) }}</TableCell>
            <TableCell>
              <div class="flex space-x-2">
                <Button variant="outline" size="sm" @click="$emit('view-transactions', seller)">
                  View Transactions
                </Button>
                <Button variant="outline" size="sm" @click="$emit('adjust-balance', seller)">
                  Adjust Balance
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table"
import { Button } from "@/components/ui/button"
import { Input } from "@/Components/ui/input"

const props = defineProps({
  sellers: {
    type: Array,
    required: true
  }
})

const search = ref('')

const filteredSellers = computed(() => {
  if (!search.value) return props.sellers
  return props.sellers.filter(seller => 
    seller.name.toLowerCase().includes(search.value.toLowerCase()) ||
    seller.seller_code.toLowerCase().includes(search.value.toLowerCase())
  )
})

const formatNumber = (num) => new Intl.NumberFormat().format(num)
</script>
