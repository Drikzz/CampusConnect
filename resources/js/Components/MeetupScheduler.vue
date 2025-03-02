<template>
  <div class="space-y-4">
    <form @submit.prevent="scheduleMeetup" class="space-y-4">
      <div>
        <Label>Meetup Location</Label>
        <Select v-model="form.meetup_location_id" required>
          <option value="">Select a location</option>
          <option v-for="location in locations" :key="location.id" :value="location.id">
            {{ location.name }} - {{ location.address }}
          </option>
        </Select>
        <Link href="/dashboard/meetup-locations" class="text-sm text-primary-color hover:text-primary-color/90">
          Manage meetup locations
        </Link>
      </div>

      <div>
        <Label>Meetup Schedule</Label>
        <Input 
          type="datetime-local" 
          v-model="form.meetup_schedule" 
          :min="minDateTime"
          required
        />
      </div>

      <Button type="submit" class="w-full">Schedule Meetup</Button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Select } from '@/Components/ui/select'
import { Label } from '@/Components/ui/label'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  locations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['scheduled'])

const form = ref({
  meetup_location_id: '',
  meetup_schedule: ''
})

const minDateTime = computed(() => {
  const now = new Date()
  now.setMinutes(now.getMinutes() + 30) // Minimum 30 minutes from now
  return now.toISOString().slice(0, 16)
})

const scheduleMeetup = () => {
  router.post(route('seller.orders.schedule-meetup', props.order.id), form.value, {
    onSuccess: () => {
      emit('scheduled')
    }
  })
}
</script>
