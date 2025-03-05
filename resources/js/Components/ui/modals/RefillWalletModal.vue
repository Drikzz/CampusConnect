<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Refill Your Wallet</DialogTitle>
        <DialogDescription>
          Add funds to your wallet using your preferred payment method.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <Label>Amount (₱)</Label>
          <Input 
            type="number" 
            v-model="form.amount"
            min="100"
            step="100"
            placeholder="Enter amount"
          />
          <p v-if="form.errors.amount" class="text-sm text-red-500">
            {{ form.errors.amount }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Minimum amount: ₱100</p>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:open', false)">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            Continue to Payment
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

defineProps({
  open: Boolean
})

defineEmits(['update:open', 'refilled'])

const form = useForm({
  amount: 100
})

const handleSubmit = () => {
  form.post(route('seller.wallet.refill'), {
    onSuccess: () => {
      form.reset()
      emit('refilled')
    }
  })
}
</script>
