<template>
  <Card>
    <CardHeader>
      <CardTitle>Refund Requests</CardTitle>
      <CardDescription>Manage pending refund requests</CardDescription>
    </CardHeader>
    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Request ID</TableHead>
            <TableHead>Buyer</TableHead>
            <TableHead>Amount</TableHead>
            <TableHead>Reason</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="request in requests" :key="request.id">
            <TableCell>#{{ request.id }}</TableCell>
            <TableCell>{{ request.buyer_name }}</TableCell>
            <TableCell>₱{{ formatNumber(request.amount) }}</TableCell>
            <TableCell>{{ request.reason }}</TableCell>
            <TableCell>
              <span :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                {
                  'bg-yellow-100 text-yellow-800': request.status === 'pending',
                  'bg-green-100 text-green-800': request.status === 'approved',  
                  'bg-red-100 text-red-800': request.status === 'rejected'
                }
              ]">
                {{ request.status }}
              </span>
            </TableCell>
            <TableCell>
              <div class="flex space-x-2">
                <Button 
                  v-if="request.status === 'pending'"
                  variant="success" 
                  size="sm" 
                  @click="$emit('approve', request)"
                >
                  Approve
                </Button>
                <Button 
                  v-if="request.status === 'pending'"
                  variant="destructive" 
                  size="sm" 
                  @click="$emit('reject', request)"
                >
                  Reject
                </Button>
                <Button 
                  variant="outline" 
                  size="sm"
                  @click="showProofImage(request)"
                  v-if="request.proof_image"
                >
                  View Proof
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>

    <!-- Image Preview Dialog -->
    <Dialog :open="!!selectedImage" @update:open="selectedImage = null">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Proof Image</DialogTitle>
        </DialogHeader>
        <img v-if="selectedImage" :src="selectedImage" class="w-full rounded-lg" />
      </DialogContent>
    </Dialog>
  </Card>
</template>

<script setup>
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/Components/ui/dialog"
import { Button } from "@/Components/ui/button"

defineProps({
  requests: {
    type: Array,
    required: true
  }
})

const selectedImage = ref(null)

const formatNumber = (num) => new Intl.NumberFormat().format(num)

const showProofImage = (request) => {
  selectedImage.value = request.proof_image
}
</script>
