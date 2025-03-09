<template>
  <div v-if="open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6 space-y-4">
      <h2 class="text-xl font-semibold">Add Funds to Wallet</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Amount (₱)</label>
          <input 
            v-model="amount"
            type="number"
            min="100"
            step="100"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Reference Number</label>
          <input 
            v-model="referenceNumber"
            type="text"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Payment Screenshot</label>
          <input 
            type="file"
            accept="image/*"
            required
            @change="handleFileChange"
            class="mt-1 block w-full"
          />
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
            :disabled="isSubmitting"
            class="px-4 py-2 bg-primary-color text-white rounded-md hover:bg-primary-color/90"
          >
            {{ isSubmitting ? 'Processing...' : 'Submit Deposit' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  open: Boolean,
  wallet: Object
})

const emit = defineEmits(['update:open', 'submit'])

const amount = ref(100)
const referenceNumber = ref('')
const screenshot = ref(null)
const isSubmitting = ref(false)

const handleFileChange = (e) => {
  screenshot.value = e.target.files[0]
}

const handleSubmit = () => {
  isSubmitting.value = true
  
  const formData = new FormData()
  formData.append('amount', amount.value)
  formData.append('reference_number', referenceNumber.value)
  formData.append('screenshot', screenshot.value)
  
  emit('submit', formData)
  isSubmitting.value = false
}
</script>
