<template>
  <div v-if="open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6 space-y-4">
      <h2 class="text-xl font-semibold">Activate Your Seller Wallet</h2>
      <p class="text-gray-600">
        To start selling products and managing orders, you need to activate your wallet first. Please agree to our terms and conditions.
      </p>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="flex items-start">
          <input 
            type="checkbox"
            id="terms"
            v-model="acceptTerms"
            class="mt-1 rounded border-gray-300"
            required
          />
          <label for="terms" class="ml-2 text-sm text-gray-600">
            I agree to the wallet terms and conditions
          </label>
        </div>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="$emit('update:open', false)"
            class="px-4 py-2 border rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="!acceptTerms || isSubmitting"
            class="px-4 py-2 bg-primary-color text-white rounded-md hover:bg-primary-color/90 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Activating...' : 'Activate Wallet' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  open: Boolean
})

const emit = defineEmits(['update:open', 'submit'])

const acceptTerms = ref(false)
const isSubmitting = ref(false)

const handleSubmit = () => {
  if (!acceptTerms.value) return
  
  isSubmitting.value = true
  emit('submit')
  isSubmitting.value = false
}
</script>
