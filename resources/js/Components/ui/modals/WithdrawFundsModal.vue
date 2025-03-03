<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Withdraw Funds</DialogTitle>
        <DialogDescription>
          Withdraw funds from your wallet to your preferred payment method.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <Label>Available Balance</Label>
          <p class="text-lg font-semibold">₱{{ availableBalance }}</p>
        </div>

        <div>
          <Label>Amount to Withdraw (₱)</Label>
          <Input 
            type="number" 
            v-model="form.amount"
            :max="availableBalance"
            step="0.01"
            placeholder="Enter amount"
          />
          <p v-if="form.errors.amount" class="text-sm text-red-500">
            {{ form.errors.amount }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Maximum: ₱{{ availableBalance }}</p>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:open', false)">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing || form.amount > availableBalance">
            Withdraw
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'

const props = defineProps({
  open: Boolean,
  availableBalance: {
    type: Number,
    required: true
  }
})

defineEmits(['update:open', 'withdrawn'])

const form = useForm({
  amount: ''
})

const handleSubmit = () => {
  form.post(route('seller.wallet.withdraw'), {
    onSuccess: () => {
      form.reset()
      emit('withdrawn')
    }
  })
}
</script>
