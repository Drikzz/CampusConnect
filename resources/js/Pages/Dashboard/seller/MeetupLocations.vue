<script setup>
// Fix the import by adding onUnmounted
import { ref, onMounted, computed, nextTick, watch, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '../DashboardLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { useToast } from '@/Components/ui/toast/use-toast'
import { TimeSelect } from '@/Components/ui/time-select'
import { Checkbox } from '@/Components/ui/checkbox'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/Components/ui/dialog'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogAction,
  AlertDialogCancel,
} from '@/Components/ui/alert-dialog'
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxList,
  ComboboxSeparator,
  ComboboxTrigger
} from '@/Components/ui/combobox'
import { Check, ChevronsUpDown } from 'lucide-vue-next' // Add this import
import { ScrollArea } from '@/Components/ui/scroll-area' // Add ScrollArea to imports
import { 
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
import { 
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/Components/ui/command'

const props = defineProps({
  user: Object,
  stats: Object,
  meetupLocations: Array,
  locations: Array,
  errors: Object, // Add errors prop to receive validation errors from Inertia
  flash: Object  // Add flash prop to receive flash messages from Inertia
})

const page = usePage()
const { toast } = useToast()
const showForm = ref(false)
const isEditing = ref(false)
const editId = ref(null)
const formErrors = ref({})

// Add loading state
const isSubmitting = ref(false)

// Add validation state
const validationState = ref({
  location_id: { valid: true, message: '' },
  full_name: { valid: true, message: '' },
  phone: { valid: true, message: '' },
  available_days: { valid: true, message: '' },
  available_from: { valid: true, message: '' },
  available_until: { valid: true, message: '' },
  max_daily_meetups: { valid: true, message: '' }
})

// Add form validation functions
const validatePhone = (phone) => {
  const phoneRegex = /^[0-9]{11}$/
  const isValid = phoneRegex.test(phone)
  validationState.value.phone = {
    valid: isValid,
    message: isValid ? '' : 'Phone number must be 11 digits'
  }
  return isValid
}

const validateTime = (from, until) => {
  if (!from || !until) return false
  const fromTime = new Date(`2000-01-01 ${from}`)
  const untilTime = new Date(`2000-01-01 ${until}`)
  const isValid = fromTime < untilTime
  validationState.value.available_until = {
    valid: isValid,
    message: isValid ? '' : 'End time must be after start time'
  }
  return isValid
}

const validateForm = () => {
  let isValid = true

  // Location validation
  if (!form.value.location_id) {
    validationState.value.location_id = {
      valid: false,
      message: 'Please select a location'
    }
    isValid = false
  }

  // Name validation
  if (!form.value.full_name.trim()) {
    validationState.value.full_name = {
      valid: false,
      message: 'Full name is required'
    }
    isValid = false
  }

  // Phone validation
  if (!validatePhone(form.value.phone)) {
    isValid = false
  }

  // Days validation
  if (selectedDays.value.length === 0) {
    validationState.value.available_days = {
      valid: false,
      message: 'Please select at least one day'
    }
    isValid = false
  }

  // Time validation
  if (!validateTime(form.value.available_from, form.value.available_until)) {
    isValid = false
  }

  // Max daily meetups validation
  const maxMeetups = parseInt(form.value.max_daily_meetups)
  if (!maxMeetups || maxMeetups < 1 || maxMeetups > 50) {
    validationState.value.max_daily_meetups = {
      valid: false,
      message: 'Maximum daily meetups must be between 1 and 50'
    }
    isValid = false
  }

  return isValid
}

// Clean up the onMounted function
onMounted(() => {
  const flashMessage = page.props.flash?.message;
  const flashType = page.props.flash?.type;
  
  if (flashMessage) {
    toast({
      title: flashType === 'success' ? 'Success' : 'Error',
      description: flashMessage,
      variant: flashType === 'success' ? 'default' : 'destructive'
    });
  }
  
  if (props.errors && Object.keys(props.errors).length > 0) {
    formErrors.value = props.errors;
    // If there are validation errors, show the dialog
    showDialog.value = true;
  }
});

// Define days with consistent formatting
const daysList = [
  'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
]

// Add time constraints
const timeConstraints = {
  fromTime: { min: 6, max: 18 },  // 6 AM to 6 PM
  toTime: { min: 7, max: 19 }     // 7 AM to 7 PM
}

// Update form structure to use checked property for days
const form = ref({
  location_id: '',
  full_name: props.user?.first_name + ' ' + props.user?.last_name,
  phone: props.user?.phone || '',
  description: '',
  available_from: '09:00',
  available_until: '17:00',
  available_days: daysList.map(day => ({
    label: day,
    value: day,
    checked: false
  })),
  max_daily_meetups: 5,
  is_default: false
})

// Update selectedDays computed property
const selectedDays = computed({
  get: () => form.value.available_days.filter(day => day.checked).map(day => day.value),
  set: (value) => {
    form.value.available_days.forEach(day => {
      day.checked = value.includes(day.value)
    })
  }
})

// Reset the form to default values
const resetForm = () => {
  form.value = {
    location_id: '',
    full_name: props.user?.first_name + ' ' + props.user?.last_name,
    phone: props.user?.phone || '',
    description: '',
    available_from: '09:00',
    available_until: '17:00',
    available_days: daysList.map(day => ({
      label: day,
      value: day,
      checked: false
    })),
    max_daily_meetups: 5,
    is_default: false
  }
  
  isEditing.value = false
  editId.value = null
  formErrors.value = {}
}

// Helper to parse available days regardless of format
const parseAvailableDays = (rawDays) => {
  if (!rawDays) return []
  
  // If it's already an array, return a copy
  if (Array.isArray(rawDays)) {
    return [...rawDays]
  }
  
  // If it's a string (JSON), try to parse it
  if (typeof rawDays === 'string') {
    try {
      const parsedData = JSON.parse(rawDays)
      return Array.isArray(parsedData) ? parsedData : []
    } catch (e) {
      console.error('Error parsing available days:', e, rawDays)
      return []
    }
  }
  
  return []
}

// Add this helper function near your other utility functions
const formatTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const date = new Date(2000, 0, 1, hours, minutes);
  return date.toLocaleTimeString('en-US', { 
    hour: 'numeric',
    minute: '2-digit',
    hour12: true 
  }).toLowerCase();
}

// Add a helper function to ensure time format is HH:mm
const formatTimeForInput = (time) => {
  if (!time) return '09:00';
  // If time already in HH:mm format, return it
  if (/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(time)) return time;
  
  try {
    // Convert any time string to HH:mm format
    const [hours, minutes] = time.split(':');
    const date = new Date(2000, 0, 1, hours, minutes);
    return date.toTimeString().slice(0, 5); // Get HH:mm part
  } catch (e) {
    console.error('Error formatting time:', e);
    return '09:00'; // Default fallback
  }
};

// Add a function to check if form has changed
const hasFormChanged = (originalLocation) => {
  if (!originalLocation) return true;

  const originalDays = parseAvailableDays(originalLocation.available_days);
  const currentDays = selectedDays.value;

  return (
    originalLocation.location_id.toString() !== form.value.location_id ||
    originalLocation.full_name !== form.value.full_name ||
    originalLocation.phone !== form.value.phone ||
    originalLocation.description !== form.value.description ||
    originalLocation.available_from !== form.value.available_from ||
    originalLocation.available_until !== form.value.available_until ||
    originalLocation.max_daily_meetups !== form.value.max_daily_meetups ||
    originalLocation.is_default !== form.value.is_default ||
    !arraysEqual(originalDays, currentDays)
  );
};

// Helper to compare arrays
const arraysEqual = (a, b) => {
  if (a.length !== b.length) return false;
  return a.every(item => b.includes(item)) && b.every(item => a.includes(item));
};

// Store original location data
const originalLocation = ref(null);

// Update the edit location function to properly set available times
const editLocation = (location) => {
  isEditing.value = true;
  editId.value = location.id;
  originalLocation.value = { ...location }; // Store original data
  
  const availableDays = parseAvailableDays(location.available_days);
  
  form.value = {
    ...form.value,
    location_id: location.location_id.toString(),
    full_name: location.full_name,
    phone: location.phone,
    description: location.description || '',
    available_from: formatTimeForInput(location.available_from),
    available_until: formatTimeForInput(location.available_until),
    available_days: daysList.map(day => ({
      label: day,
      value: day,
      checked: availableDays.includes(day)
    })),
    max_daily_meetups: location.max_daily_meetups || 5,
    is_default: location.is_default || false
  };
  
  showDialog.value = true;
};

// Helper function to format available days for display
const formatAvailableDays = (days) => {
  const parsedDays = parseAvailableDays(days)
  return parsedDays.length > 0 ? parsedDays.join(', ') : 'None'
}

// Replace deleteLocation function with confirmDelete
const confirmDelete = (location) => {
  locationToDelete.value = location
  showDeleteAlert.value = true
}

// Add handleDelete function
const handleDelete = () => {
  if (locationToDelete.value) {
    router.delete(route('seller.meetup-locations.destroy', locationToDelete.value.id), {
      onSuccess: () => {
        showDeleteAlert.value = false
        locationToDelete.value = null
      }
    })
  }
}

// Update the showAddForm function
const showAddForm = () => {
  resetForm();
  isEditing.value = false;
  showDialog.value = true;
};

// Add prevent submit handler
const preventSubmit = (e) => {
  e.preventDefault()
  e.stopPropagation()
}

// Update the location related refs and computed
const locationSearch = ref('')

const filteredLocations = computed(() => {
  return props.locations.filter(location => 
    location.name.toLowerCase().includes(locationSearch.value.toLowerCase())
  ).map(location => ({
    value: location.id.toString(),
    label: location.name
  }))
})

// Add function to get selected location name
const selectedLocationName = computed(() => {
  const location = props.locations.find(loc => loc.id.toString() === form.value.location_id)
  return location?.name || 'Select a location...'
})

// Update handleSubmit to include all validations
const handleSubmit = () => {
  if (isEditing.value && !hasFormChanged(originalLocation.value)) {
    closeDialog();
    return;
  }

  // Run full form validation
  if (!validateForm()) {
    // Show specific validation errors for each field
    if (selectedDays.value.length === 0) {
      toast({
        title: 'Validation Error',
        description: 'Please select at least one available day',
        variant: 'destructive'
      })
    } else if (!form.value.location_id) {
      toast({
        title: 'Validation Error',
        description: 'Please select a location',
        variant: 'destructive'
      })
    } else if (!form.value.full_name.trim()) {
      toast({
        title: 'Validation Error',
        description: 'Please enter your full name',
        variant: 'destructive'
      })
    } else if (!validatePhone(form.value.phone)) {
      toast({
        title: 'Validation Error',
        description: 'Please enter a valid 11-digit phone number',
        variant: 'destructive'
      })
    } else if (!validateTime(form.value.available_from, form.value.available_until)) {
      toast({
        title: 'Validation Error',
        description: 'End time must be after start time',
        variant: 'destructive'
      })
    } else {
      // Generic error if we missed something specific
      toast({
        title: 'Validation Error',
        description: 'Please check all required fields',
        variant: 'destructive'
      })
    }
    return
  }

  // For editing, show confirmation dialog
  if (isEditing.value) {
    showDialog.value = false
    showUpdateAlert.value = true
  } else {
    // Direct submission for new locations
    submitForm()
  }
}

// Simplify the submitForm function
const submitForm = () => {
  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;
  
  const formData = {
    ...form.value,
    available_days: selectedDays.value,
    available_from: formatTimeForInput(form.value.available_from),
    available_until: formatTimeForInput(form.value.available_until)
  };

  if (isEditing.value) {
    router.put(route('seller.meetup-locations.update', editId.value), formData);
  } else {
    router.post(route('seller.meetup-locations.store'), formData);
  }
}

// Ensure handleUpdateCancel works correctly
const handleUpdateCancel = () => {
  showUpdateAlert.value = false;
  showDialog.value = true;
};

// Fix closeDialog to force it closed
const closeDialog = () => {
  formErrors.value = {};
  showDialog.value = false;
  isSubmitting.value = false;
  originalLocation.value = null;
  resetForm();
};

// Add watchers for real-time validation
watch(() => form.value.phone, validatePhone)
watch([() => form.value.available_from, () => form.value.available_until], 
  ([from, until]) => validateTime(from, until)
)

const showDialog = ref(false)
const showDeleteAlert = ref(false)
const showUpdateAlert = ref(false)
const locationToDelete = ref(null)

// Update the locations computed property for Combobox
const locationOptions = computed(() => props.locations?.map(location => ({
  label: location.name,
  value: location.id.toString()
})) || [])

// Add these refs for combobox control
const locationOpen = ref(false)

// Helper function to get selected location name
const getSelectedLocation = computed(() => {
  const selected = locationOptions.value.find(loc => loc.value === form.value.location_id)
  return selected?.label || 'Select a location...'
})

// Add function to select location
const selectLocation = (value) => {
  form.value.location_id = value
  locationOpen.value = false
}

// Add page level event handlers
onMounted(() => {
  router.on('success', () => {
    showDialog.value = false;
    showUpdateAlert.value = false;
    isSubmitting.value = false;
    resetForm();
  });

  if (page.props.flash?.message) {
    toast({
      title: page.props.flash.type === 'success' ? 'Success' : 'Error',
      description: page.props.flash.message,
      variant: page.props.flash.type === 'success' ? 'default' : 'destructive'
    });
  }
});

onUnmounted(() => {
  router.off('success');
});

</script>

<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-8 relative">
      <div class="flex justify-between items-center relative z-10">
        <h1 class="text-2xl font-bold">Meetup Locations</h1>
        <Button type="button" @click="showAddForm">Add New Location</Button>
      </div>

      <!-- Locations List -->
      <div v-if="meetupLocations && meetupLocations.length > 0" class="space-y-4">
        <div v-for="location in meetupLocations" :key="location.id" class="bg-white p-6 rounded-lg shadow">
          <div class="flex justify-between">
            <div>
              <h3 class="font-medium text-lg">{{ location.location?.name }}</h3>
              <p>{{ location.full_name }} - {{ location.phone }}</p>
              
              <div class="mt-2">
                <span class="text-sm font-medium">Available Days:</span>
                <span class="ml-2 text-sm">{{ formatAvailableDays(location.available_days) }}</span>
              </div>
              
              <div>
                <span class="text-sm font-medium">Daily meetup:</span>
                <span class="ml-2 text-sm">{{ (location.max_daily_meetups) }}</span>
              </div>
              
              <div>
                <span class="text-sm font-medium">Hours:</span>
                <span class="ml-2 text-sm">{{ formatTime(location.available_from) }} - {{ formatTime(location.available_until) }}</span>
              </div>
              
              <div v-if="location.is_default" class="mt-2">
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Default Location</span>
              </div>
            </div>
            
            <div class="space-x-2">
              <Button size="sm" variant="outline" @click="editLocation(location)">Edit</Button>
              <Button 
                size="sm" 
                variant="outline" 
                class="text-red-600" 
                @click="confirmDelete(location)"
              >
                Delete
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-gray-50 p-10 text-center rounded-lg">
        <p class="text-gray-500">No meetup locations found. Add a location to get started.</p>
      </div>
    </div>

    <!-- Form Dialog -->
    <Dialog 
      :open="showDialog" 
      @update:open="(value) => value ? openDialog() : closeDialog()"
    >
      <DialogContent class="flex flex-col max-h-[90vh] w-full max-w-md md:max-w-lg">
        <DialogHeader class="border-b py-4">
          <DialogTitle>{{ isEditing ? 'Edit' : 'Add' }} Meetup Location</DialogTitle>
        </DialogHeader>

        <div class="flex-1 overflow-y-auto p-4">
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="full_name">Full Name</Label>
                <Input 
                  id="full_name" 
                  v-model="form.full_name" 
                  required 
                  :disabled="isSubmitting"
                />
                <p v-if="formErrors.full_name" class="text-red-500 text-sm mt-1">
                  {{ formErrors.full_name }}
                </p>
              </div>
              
              <div>
                <Label for="phone">Phone Number</Label>
                <Input 
                  id="phone"
                  v-model="form.phone"
                  :disabled="isSubmitting"
                  :class="{'border-red-500': !validationState.phone.valid}"
                />
                <p v-if="!validationState.phone.valid" class="text-red-500 text-sm mt-1">
                  {{ validationState.phone.message }}
                </p>
              </div>
            </div>
            
            <!-- Location Selection -->
            <div class="w-full">
              <Label>Location</Label>
              <Popover v-model:open="locationOpen">
                <PopoverTrigger as-child>
                  <Button
                    type="button" 
                    variant="outline"
                    role="combobox"
                    :disabled="isSubmitting"
                    :aria-expanded="locationOpen"
                    class="w-full justify-between bg-white border-primary-color"
                    @click="preventSubmit"
                  >
                    {{ getSelectedLocation }}
                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                  </Button>
                </PopoverTrigger>
                <PopoverContent class="w-full p-0">
                  <Command @submit.prevent> <!-- Prevent form submission -->
                    <CommandInput 
                      placeholder="Search location..."
                      @click="preventSubmit"
                    />
                    <CommandEmpty>No location found.</CommandEmpty>
                    <CommandGroup>
                      <ScrollArea class="h-[200px]">
                        <CommandItem
                          v-for="location in filteredLocations"
                          :key="location.value"
                          :value="location.value"
                          @click="selectLocation(location.value)"
                        >
                          <Check
                            :class="[
                              'mr-2 h-4 w-4',
                              form.location_id === location.value ? 'opacity-100' : 'opacity-0'
                            ]"
                          />
                          {{ location.label }}
                        </CommandItem>
                      </ScrollArea>
                    </CommandGroup>
                  </Command>
                </PopoverContent>
              </Popover>
              <p v-if="formErrors.location_id" class="text-red-500 text-sm mt-1">
                {{ formErrors.location_id }}
              </p>
            </div>

            <!-- Update Available Days section -->
            <div>
              <Label>Available Days</Label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                <div v-for="day in form.available_days" :key="day.value" class="flex items-center space-x-2">
                  <Checkbox
                    :id="'day-' + day.value"
                    v-model="day.checked"
                    :disabled="isSubmitting"
                    class="data-[state=checked]:bg-primary"
                  />
                  <Label :for="'day-' + day.value" class="text-sm">{{ day.label }}</Label>
                </div>
              </div>
              <p v-if="formErrors.available_days" class="text-red-500 text-sm mt-1">
                {{ Array.isArray(formErrors.available_days) ? formErrors.available_days[0] : formErrors.available_days }}
              </p>
            </div>
            
            <!-- Available Hours -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label>Available From</Label>
                <TimeSelect
                  v-model="form.available_from"
                  :minHour="timeConstraints.fromTime.min"
                  :maxHour="timeConstraints.fromTime.max"
                  placeholder="Select start time"
                  :disabled="isSubmitting"
                />
                <p v-if="formErrors.available_from" class="text-red-500 text-sm mt-1">
                  {{ formErrors.available_from }}
                </p>
              </div>
              
              <div>
                <Label>Available Until</Label>
                <TimeSelect
                  v-model="form.available_until"
                  :minHour="timeConstraints.toTime.min"
                  :maxHour="timeConstraints.toTime.max"
                  placeholder="Select end time"
                  :disabled="isSubmitting"
                />
                <p v-if="formErrors.available_until" class="text-red-500 text-sm mt-1">
                  {{ formErrors.available_until }}
                </p>
              </div>
            </div>
            
            <!-- Max Daily Meetups -->
            <div>
              <Label for="max_daily_meetups">Maximum Daily Meetups</Label>
              <Input 
                id="max_daily_meetups" 
                v-model="form.max_daily_meetups" 
                type="number" 
                min="1" 
                max="50" 
                required 
                :disabled="isSubmitting"
              />
              <p v-if="formErrors.max_daily_meetups" class="text-red-500 text-sm mt-1">{{ formErrors.max_daily_meetups }}</p>
            </div>
            
            <!-- Default Location Option -->
            <div class="flex items-center space-x-2">
              <input 
                type="checkbox" 
                id="is_default" 
                v-model="form.is_default"
                class="h-4 w-4 rounded border-gray-300 text-primary-color focus:ring-primary-color"
              />
              <Label for="is_default">Set as default meetup location</Label>
            </div>
            
            <!-- Description -->
            <div>
              <Label for="description">Description (Optional)</Label>
              <textarea 
                id="description" 
                v-model="form.description" 
                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-color"
                rows="3"
                :disabled="isSubmitting"
              ></textarea>
            </div>
            
            <!-- Debug output -->
            <!-- <div class="text-sm text-gray-500">
              <p>Selected days: {{ selectedDays }}</p>
            </div> -->
            
            <!-- Form Actions -->
            <DialogFooter class="border-t p-4 mt-auto">
              <Button 
                type="button" 
                variant="outline" 
                :disabled="isSubmitting"
                @click="closeDialog"
              >
                Cancel
              </Button>
              <Button 
                type="button" 
                @click="handleSubmit"
                :disabled="isSubmitting"
                class="relative ml-2"
              >
                <span :class="{ 'opacity-0': isSubmitting }">
                  {{ isEditing ? 'Update' : 'Save' }} Location
                </span>
                <span 
                  v-if="isSubmitting" 
                  class="absolute inset-0 flex items-center justify-center"
                >
                  Saving...
                </span>
              </Button>
            </DialogFooter>
          </form>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Alert -->
    <AlertDialog :open="showDeleteAlert" @update:open="showDeleteAlert = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Meetup Location</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this meetup location? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showDeleteAlert = false">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="handleDelete">Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Update Confirmation Alert -->
    <AlertDialog :open="showUpdateAlert" @update:open="showUpdateAlert = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Update Meetup Location</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to update this meetup location? Previous settings will be overwritten.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="handleUpdateCancel">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="submitForm">Update</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </DashboardLayout>
</template>

<style>
/* Only keep necessary ScrollArea styles using Tailwind classes */
.PopoverContent .ScrollAreaViewport {
  @apply h-[200px];
}

.PopoverContent .ScrollAreaScrollbar {
  @apply w-1.5 mr-0.5;
}

/* Fix dialog layout */
.DialogOverlay {
  @apply z-50;
}

.DialogContent {
  @apply flex flex-col w-full max-w-md md:max-w-lg lg:max-w-xl max-h-[90vh];
}

.DialogHeader {
  @apply border-b p-4;
}

.DialogFooter {
  @apply border-t p-4 mt-auto flex justify-end items-center space-x-2;
}
</style>