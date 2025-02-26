<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Button } from '@/Components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
import { ScrollArea } from '@/Components/ui/scroll-area'
import { Clock } from 'lucide-vue-next'

const props = defineProps({
  modelValue: String,
  placeholder: {
    type: String,
    default: 'Select time'
  }
})

const emit = defineEmits(['update:modelValue'])

// Generate hours in 12-hour format
const hours = Array.from({ length: 12 }, (_, i) => (i + 1).toString().padStart(2, '0'))
const minutes = ['00', '15', '30', '45']

const selectedHour = ref(null) // Change from default value to null
const selectedMinute = ref(null) // Change from default value to null
const selectedPeriod = ref('AM')

function convert24to12Hour(hour24) {
  const hour = parseInt(hour24)
  if (hour === 0) return { hour: '12', period: 'AM' }
  if (hour === 12) return { hour: '12', period: 'PM' }
  if (hour > 12) return { hour: (hour - 12).toString().padStart(2, '0'), period: 'PM' }
  return { hour: hour.toString().padStart(2, '0'), period: 'AM' }
}

function convert12to24Hour(hour12, period) {
  const hour = parseInt(hour12)
  if (period === 'AM' && hour === 12) return '00'
  if (period === 'AM') return hour.toString().padStart(2, '0')
  if (period === 'PM' && hour === 12) return '12'
  return (hour + 12).toString().padStart(2, '0')
}

const formattedTime = computed(() => {
  if (!selectedHour.value || !selectedMinute.value) return null
  const hour24 = convert12to24Hour(selectedHour.value, selectedPeriod.value)
  return `${hour24}:${selectedMinute.value}`
})

const displayTime = computed(() => {
  if (!formattedTime.value) return props.placeholder
  return `${selectedHour.value}:${selectedMinute.value} ${selectedPeriod.value}`
})

const updateTime = () => {
  emit('update:modelValue', formattedTime.value)
}

// Add method to initialize from 24h format
const initializeFromValue = () => {
  if (props.modelValue) {
    const [hour24, minute] = props.modelValue.split(':')
    const { hour, period } = convert24to12Hour(hour24)
    selectedHour.value = hour
    selectedMinute.value = minute
    selectedPeriod.value = period
  }
}

// Call on mounted
onMounted(() => {
  initializeFromValue()
})

// Watch for modelValue changes
watch(() => props.modelValue, () => {
  initializeFromValue()
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="w-full justify-start text-left font-normal">
        <Clock class="mr-2 h-4 w-4" />
        {{ displayTime }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[280px] p-0">
      <div class="grid grid-cols-3 gap-2 p-3">
        <!-- Hours -->
        <div class="space-y-2">
          <div class="text-xs font-medium">Hour</div>
          <ScrollArea class="h-[180px] rounded-md border">
            <div class="p-2">
              <Button
                v-for="hour in hours"
                :key="hour"
                variant="ghost"
                :class="[
                  'w-full justify-start font-normal',
                  selectedHour === hour ? 'bg-accent text-accent-foreground' : ''
                ]"
                @click="selectedHour = hour; updateTime()"
              >
                {{ hour }}
              </Button>
            </div>
          </ScrollArea>
        </div>

        <!-- Minutes -->
        <div class="space-y-2">
          <div class="text-xs font-medium">Minute</div>
          <ScrollArea class="h-[180px] rounded-md border">
            <div class="p-2">
              <Button
                v-for="minute in minutes"
                :key="minute"
                variant="ghost"
                :class="[
                  'w-full justify-start font-normal',
                  selectedMinute === minute ? 'bg-accent text-accent-foreground' : ''
                ]"
                @click="selectedMinute = minute; updateTime()"
              >
                {{ minute }}
              </Button>
            </div>
          </ScrollArea>
        </div>

        <!-- AM/PM -->
        <div class="space-y-2">
          <div class="text-xs font-medium">Period</div>
          <div class="rounded-md border p-2">
            <Button
              v-for="period in ['AM', 'PM']"
              :key="period"
              variant="ghost"
              :class="[
                'w-full justify-start font-normal mb-1',
                selectedPeriod === period ? 'bg-accent text-accent-foreground' : ''
              ]"
              @click="selectedPeriod = period; updateTime()"
            >
              {{ period }}
            </Button>
          </div>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>
