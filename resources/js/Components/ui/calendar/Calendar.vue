<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { CalendarRoot, type CalendarRootEmits, type CalendarRootProps, useForwardPropsEmits } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading, CalendarNextButton, CalendarPrevButton } from '.'

// Update props to include disabledDates
const props = defineProps<CalendarRootProps & { 
  class?: HTMLAttributes['class'],
  disabledDates?: Date[] | ((date: Date) => boolean),
  selectedDate?: Date
}>()

const emits = defineEmits<CalendarRootEmits>()

const delegatedProps = computed(() => {
  const { class: _, disabledDates: __, selectedDate: ___, ...delegated } = props
  return delegated
})

// Modify the isSelected function to handle Proxy objects
const isSelected = (date: any) => {
  if (!date || !props.selectedDate) return false;
  
  try {
    // For Proxy objects, check if they have date properties
    const dateDay = date.day || (date.getDate ? date.getDate() : null);
    const dateMonth = date.month ? date.month - 1 : (date.getMonth ? date.getMonth() : null);
    const dateYear = date.year || (date.getFullYear ? date.getFullYear() : null);
    
    // If we don't have valid date parts, return false
    if (dateDay === null || dateMonth === null || dateYear === null) {
      return false;
    }
    
    // Get selectedDate parts
    let selectedDay, selectedMonth, selectedYear;
    
    if (props.selectedDate instanceof Date) {
      selectedDay = props.selectedDate.getDate();
      selectedMonth = props.selectedDate.getMonth();
      selectedYear = props.selectedDate.getFullYear();
    } else {
      console.warn('Selected date is not a standard Date object:', props.selectedDate);
      return false;
    }
    
    // Compare the date parts
    return dateDay === selectedDay && 
           dateMonth === selectedMonth && 
           dateYear === selectedYear;
  } catch (e) {
    console.error('Error comparing dates:', e);
    return false;
  }
};

// Add helper function to check if a date is disabled
const isDateDisabled = (date: Date) => {
  if (!date || !props.disabledDates) return false;
  
  try {
    if (Array.isArray(props.disabledDates)) {
      return props.disabledDates.some(d => 
        d && 
        d.getDate() === date.getDate() && 
        d.getMonth() === date.getMonth() && 
        d.getFullYear() === date.getFullYear()
      );
    }
    
    if (typeof props.disabledDates === 'function') {
      return props.disabledDates(date);
    }
  } catch (e) {
    console.error('Error in isDateDisabled:', e);
  }
  
  return false;
}

// Add helper function to get date cell classes
const getDateClasses = (date: Date) => {
  if (!date) return '';
  
  try {
    if (isDateDisabled(date)) {
      return 'text-muted-foreground opacity-50 cursor-not-allowed';
    }
  } catch (e) {
    console.error('Error in getDateClasses:', e);
  }
  
  return '';
}

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <CalendarRoot
    v-slot="{ grid, weekDays }"
    :class="cn('p-3', props.class)"
    v-bind="forwarded"
  >
    <CalendarHeader>
      <CalendarPrevButton />
      <CalendarHeading />
      <CalendarNextButton />
    </CalendarHeader>

    <div class="flex flex-col gap-y-4 mt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
      <CalendarGrid v-for="month in grid" :key="month.value?.toString() || 'default'">
        <CalendarGridHead>
          <CalendarGridRow>
            <CalendarHeadCell
              v-for="(day, idx) in weekDays" :key="idx"
            >
              {{ day }}
            </CalendarHeadCell>
          </CalendarGridRow>
        </CalendarGridHead>
        <CalendarGridBody>
          <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`" class="mt-2 w-full">
            <CalendarCell
              v-for="(weekDate, dateIndex) in weekDates"
              :key="`date-${index}-${dateIndex}`"
              :date="weekDate"
            >
            <CalendarCellTrigger
              :day="weekDate"
              :month="month.value"
              :class="[
                weekDate ? getDateClasses(weekDate) : '',
                weekDate && isSelected(weekDate) ? 'bg-primary-color text-white hover:bg-primary-color hover:text-white' : ''
              ]"
              :disabled="weekDate ? isDateDisabled(weekDate) : false"
            />
            </CalendarCell>
          </CalendarGridRow>
        </CalendarGridBody>
      </CalendarGrid>
    </div>
  </CalendarRoot>
</template>

<style>
.text-muted-foreground {
  @apply text-gray-400;
}

.text-white {
  color: white;
}
</style>