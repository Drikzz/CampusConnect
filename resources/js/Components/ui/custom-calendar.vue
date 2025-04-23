<script setup lang="ts">
import { cn } from '@/lib/utils'
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading } from '@/Components/ui/calendar'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { type DateValue, getLocalTimeZone, today } from '@internationalized/date'
import { useVModel } from '@vueuse/core'
import { CalendarRoot, type CalendarRootEmits, type CalendarRootProps, useDateFormatter, useForwardPropsEmits } from 'reka-ui'
import { computed, type HTMLAttributes, type Ref, ref, watch } from 'vue'

const props = defineProps<CalendarRootProps & { 
  class?: HTMLAttributes['class'],
  fromDate?: Date,
  toDate?: Date
}>()

const emits = defineEmits<CalendarRootEmits>()

// Initialize placeholder with today's date if no model value
const placeholder = ref(props.modelValue || props.placeholder())

// Watch for model value changes
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    placeholder.value = newVal
  }
}, { immediate: true })

// Fix the months computation to always use placeholder value
const months = computed(() => {
  const currentPlaceholder = placeholder.value || today(getLocalTimeZone())
  return Array.from({ length: 12 }, (_, i) => ({
    value: (i + 1).toString(),
    label: new Date(2000, i).toLocaleString('default', { month: 'long' })
  }))
})

// Fix years computation to handle selection properly
const years = computed(() => {
  const currentYear = new Date().getFullYear()
  const startYear = currentYear - 100
  const endYear = currentYear + 5
  return Array.from({ length: endYear - startYear + 1 }, (_, i) => ({
    value: (startYear + i).toString(),
    label: startYear + i
  }))
})

// Update month and year selection handlers to properly update the date
const handleMonthSelect = (monthStr: string) => {
  const month = parseInt(monthStr)
  if (!isNaN(month) && placeholder.value) {
    const newDate = placeholder.value.set({ month })
    placeholder.value = newDate
    emits('update:modelValue', newDate)
  }
}

const handleYearSelect = (yearStr: string) => {
  const year = parseInt(yearStr)
  if (!isNaN(year) && placeholder.value) {
    const newDate = placeholder.value.set({ year })
    placeholder.value = newDate
    emits('update:modelValue', newDate)
  }
}

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
const formatter = useDateFormatter('en')
</script>

<template>
  <CalendarRoot
    v-slot="{ date, grid, weekDays }"
    v-model:placeholder="placeholder"
    v-bind="forwarded"
    :class="cn('rounded-md border p-3', props.class)"
  >
    <CalendarHeader>
      <CalendarHeading class="flex w-full items-center justify-between gap-2">
        <Select
          :model-value="placeholder?.month?.toString()"
          @update:model-value="handleMonthSelect"
        >
          <SelectTrigger aria-label="Select month" class="w-[60%] h-10">
            <SelectValue placeholder="Select month" />
          </SelectTrigger>
          <SelectContent class="max-h-[200px]">
            <SelectItem
              v-for="month in months"
              :key="month.value"
              :value="month.value"
            >
              {{ month.label }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select
          :model-value="placeholder?.year?.toString()"
          @update:model-value="handleYearSelect"
        >
          <SelectTrigger aria-label="Select year" class="w-[40%] h-10">
            <SelectValue placeholder="Select year" />
          </SelectTrigger>
          <SelectContent class="max-h-[200px] overflow-y-auto">
            <SelectItem
              v-for="year in years"
              :key="year.value"
              :value="year.value"
            >
              {{ year.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </CalendarHeading>
    </CalendarHeader>

    <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
      <CalendarGrid v-for="month in grid" :key="month.value.toString()">
        <CalendarGridHead>
          <CalendarGridRow>
            <CalendarHeadCell v-for="day in weekDays" :key="day">
              {{ day }}
            </CalendarHeadCell>
          </CalendarGridRow>
        </CalendarGridHead>
        <CalendarGridBody>
          <CalendarGridRow 
            v-for="(weekDates, index) in month.rows" 
            :key="`weekDate-${index}`" 
            class="mt-2 w-full"
          >
            <CalendarCell
              v-for="weekDate in weekDates"
              :key="weekDate.toString()"
              :date="weekDate"
            >
              <CalendarCellTrigger
                :day="weekDate"
                :month="month.value"
              />
            </CalendarCell>
          </CalendarGridRow>
        </CalendarGridBody>
      </CalendarGrid>
    </div>
  </CalendarRoot>
</template>
