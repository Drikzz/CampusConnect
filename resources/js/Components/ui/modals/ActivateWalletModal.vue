<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Activate Your Seller Wallet</DialogTitle>
        <DialogDescription>
          Before you can start selling products and managing transactions, you need to activate your wallet.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-2">
          <div class="flex items-start">
            <Checkbox 
              id="terms" 
              v-model="form.terms_accepted"
              class="mt-1"
            />
            <label for="terms" class="ml-2 text-sm text-gray-600">
              I agree to the wallet terms and conditions, including fee structures and transaction limits.
            </label>
          </div>
          <p v-if="form.errors.terms_accepted" class="text-sm text-red-500">
            {{ form.errors.terms_accepted }}
          </p>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:open', false)">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            Activate Wallet
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
import { Checkbox } from '@/Components/ui/checkbox'

defineProps({
  open: Boolean
})

defineEmits(['update:open', 'activated'])

const form = useForm({
  terms_accepted: false
})

const handleSubmit = () => {
  form.post(route('seller.wallet.activate'), {
    onSuccess: () => {
      form.reset()
      emit('activated')
    }
  })
}
</script>
