<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Adjust Wallet Balance</DialogTitle>
        <DialogDescription>
          Modify the wallet balance for {{ seller?.name }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <Label>Current Balance</Label>
          <p class="text-lg font-medium">₱{{ formatNumber(seller?.balance ?? 0) }}</p>
        </div>

        <div>
          <Label>Adjustment Type</Label>
          <Select v-model="form.type">
            <option value="add">Add Balance</option>
            <option value="deduct">Deduct Balance</option>
          </Select>
        </div>

        <div>
          <Label>Amount</Label>
          <Input 
            type="number" 
            v-model="form.amount" 
            min="0" 
            step="0.01" 
            required
          />
        </div>

        <div>
          <Label>Reason</Label>
          <Textarea 
            v-model="form.reason" 
            placeholder="Enter reason for adjustment"
            required
          />
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('close')">
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Processing...' : 'Confirm' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription } from "@/Components/ui/dialog"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Label } from "@/Components/ui/label"
import { Select } from "@/Components/ui/select"
import { Textarea } from "@/Components/ui/textarea"

const props = defineProps({
  show: Boolean,
  seller: Object
})

const form = ref({
  type: 'add',
  amount: '',
  reason: ''
})

const isSubmitting = ref(false)

const formatNumber = (num) => new Intl.NumberFormat().format(num)

const handleSubmit = () => {
  isSubmitting.value = true
  emit('submit', {
    seller_id: props.seller?.id,
    ...form.value
  })
}

defineEmits(['close', 'submit'])
</script>
