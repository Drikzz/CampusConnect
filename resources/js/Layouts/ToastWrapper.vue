<template>
  <div class="fixed top-4 right-4 z-50">
    <Toaster />
  </div>
</template>

<script setup>
import { Toaster } from '@/Components/ui/toast'
import { useToast } from '@/Components/ui/toast/use-toast'
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const { toast } = useToast()

// Watch for flash messages
watch(() => page.props.flash.toast, (message) => {
  if (message) {
    toast({
      title: message.title,
      description: message.description,
      variant: message.variant || 'default',
    })
  }
}, { immediate: true })
</script>
