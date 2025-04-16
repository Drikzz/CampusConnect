<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { Button } from "@/Components/ui/button";
import { Calendar } from "@/Components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { CalendarIcon } from 'lucide-vue-next';
import { format, isValid, parseISO, getDay, addDays, isBefore, startOfDay } from 'date-fns';
import { cn } from '@/lib/utils';

const props = defineProps({
  modelValue: {
    type: [Date, String],
    default: null
  },
  selectedDay: {
    type: String,
    default: ''
  },
  isDateDisabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

// Component state
const isOpen = ref(false);
const displayDate = ref(null);
const calendarKey = ref(0);
const showCalendar = ref(false);
const defaultMonth = ref(new Date());

// Days of the week mapped to numbers
const days = {
  'sunday': 0,
  'monday': 1,
  'tuesday': 2,
  'wednesday': 3,
  'thursday': 4,
  'friday': 5,
  'saturday': 6
};

// Get the day number from the selected day prop
const selectedDayNumber = computed(() => {
  if (!props.selectedDay) return null;
  
  const dayLower = props.selectedDay.toLowerCase();
  return days[dayLower] !== undefined ? days[dayLower] : null;
});

// Format date for display
const formattedDate = computed(() => {
  if (!displayDate.value) return "Select a date";
  
  try {
    return format(displayDate.value, 'MMMM d, yyyy');
  } catch (e) {
    console.error('Date formatting error:', e);
    return "Select a date";
  }
});

// Initialize from props.modelValue
watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    displayDate.value = null;
    return;
  }
  
  try {
    // Handle both Date objects and ISO strings
    let dateValue;
    if (typeof newValue === 'string') {
      dateValue = new Date(newValue);
    } else if (newValue instanceof Date) {
      dateValue = newValue;
    } else {
      console.error('Unsupported date format:', newValue);
      return;
    }
      
    if (!isNaN(dateValue.getTime())) {
      displayDate.value = dateValue;
      // Also update default month to match the selected date
      defaultMonth.value = new Date(dateValue);
      // console.log("Set displayDate to:", displayDate.value);
    } else {
      console.error('Invalid date value:', newValue);
      displayDate.value = null;
    }
  } catch (e) {
    console.error('Date parsing error:', e);
    displayDate.value = null;
  }
}, { immediate: true });

// Get current date at the start of day for comparison
const today = computed(() => startOfDay(new Date()));

// Function to disable dates that don't match the selected day or are in the past
const disableDate = (date) => {
  if (!date) return true;
  if (props.isDateDisabled) return true;
  
  try {
    // Check if date is in the past
    if (isBefore(date, today.value)) {
      return true;
    }
    
    // Check if the day of week matches the selected day
    if (selectedDayNumber.value !== null) {
      return getDay(date) !== selectedDayNumber.value;
    }
    
    return false;
  } catch (e) {
    console.error('Error in disableDate:', e);
    return true;
  }
};

// Update the default month calculation when popover opens
const updateDefaultMonth = () => {
  try {
    // Start with current date
    const now = new Date();
    
    // If we have a valid display date, use it
    if (displayDate.value && isValid(displayDate.value)) {
      defaultMonth.value = new Date(displayDate.value);
      return;
    }
    
    // If we have a selected day, find next occurrence of that day
    if (selectedDayNumber.value !== null) {
      const currentDay = getDay(now);
      const daysToAdd = (selectedDayNumber.value + 7 - currentDay) % 7;
      defaultMonth.value = addDays(now, daysToAdd);
      return;
    }
    
    defaultMonth.value = now;
  } catch (e) {
    console.error('Error creating default month:', e);
    defaultMonth.value = new Date();
  }
};

// When popover opens, prepare and show calendar
watch(() => isOpen.value, (newIsOpen) => {
  if (newIsOpen) {
    // First update the default month value
    updateDefaultMonth();
    
    // Increment key to force full component re-render
    calendarKey.value++;
    
    // Show calendar component after a brief delay
    showCalendar.value = false;
    nextTick(() => {
      showCalendar.value = true;
    });
  } else {
    // Hide calendar when popover closes
    showCalendar.value = false;
  }
});

// Function to handle date selection
const handleDateSelect = (newDate) => {
  try {
    if (!newDate || props.isDateDisabled) {
      emit('update:modelValue', null);
      isOpen.value = false;
      return;
    }
    
    // Create a string in YYYY-MM-DD format for consistent output
    let dateObj;
    
    try {
      dateObj = typeof newDate === 'string' ? parseISO(newDate) : new Date(newDate);
      
      if (!isValid(dateObj)) {
        throw new Error('Invalid date');
      }
    } catch (e) {
      console.error('Error parsing selected date:', e);
      emit('update:modelValue', null);
      isOpen.value = false;
      return;
    }
    
    // Check if the day matches required day
    if (selectedDayNumber.value !== null && getDay(dateObj) !== selectedDayNumber.value) {
      console.warn('Selected date does not match required day:', props.selectedDay);
      isOpen.value = false;
      return;
    }
    
    // Format as ISO date string (YYYY-MM-DD)
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const isoString = `${year}-${month}-${day}`;
    
    // Update internal state
    displayDate.value = dateObj;
    
    // Close popover first
    isOpen.value = false;
    
    // Emit the formatted date string after UI updates
    nextTick(() => {
      emit('update:modelValue', isoString);
    });
  } catch (e) {
    console.error('Error in handleDateSelect:', e);
    isOpen.value = false;
  }
};
</script>

<template>
  <Popover v-model:open="isOpen">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'w-full justify-start text-left font-normal',
          !displayDate && 'text-muted-foreground',
          props.isDateDisabled && 'opacity-50 cursor-not-allowed'
        )"
        :disabled="props.isDateDisabled"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        <span v-if="props.isDateDisabled">
          Select a meetup schedule first
        </span>
        <span v-else-if="props.selectedDay && !displayDate">
          Select a {{ props.selectedDay }} date
        </span>
        <span v-else>
          {{ formattedDate }}
        </span>
      </Button>
    </PopoverTrigger>
    <PopoverContent v-if="isOpen && showCalendar" class="w-auto p-0" align="start">
      <Calendar
        :key="calendarKey"
        :default-month="defaultMonth"
        :initial-focus="true"
        :disabled-dates="disableDate"
        :selected-date="displayDate"
        mode="single"
        @update:model-value="handleDateSelect"
      />
    </PopoverContent>
    <PopoverContent v-else-if="isOpen" class="w-auto p-0" align="start">
      <div class="p-4 flex items-center justify-center">
        <span class="text-sm text-gray-500">Loading calendar...</span>
      </div>
    </PopoverContent>
  </Popover>
</template>