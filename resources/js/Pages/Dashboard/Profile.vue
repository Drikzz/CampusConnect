<template>
  <DashboardLayout :user="user" :stats="stats">
    <!-- Add Toaster component -->
    <div class="fixed inset-0 pointer-events-none z-[100] flex justify-end p-4">
      <Toaster />
    </div>

    <div class="space-y-8">
      <h2 class="text-2xl font-bold">Profile Settings</h2>

      <!-- Flash Message -->
      <div v-if="flash.success" class="p-4 text-sm text-green-700 bg-green-100 rounded-lg">
        {{ flash.success }}
      </div>

      <form @submit.prevent="updateProfile" class="space-y-8">
        <!-- Profile Picture Section -->
        <div class="flex items-center space-x-6">
          <div class="shrink-0">
            <img :src="profileImageUrl" :alt="user.username" 
                 class="h-24 w-24 object-cover rounded-full outline outline-primary-color/30">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
            <input type="file" ref="profilePicture" @change="handleProfilePicture"
                   :disabled="form.processing"
                   accept="image/*"
                   class="mt-1 block w-full text-sm text-gray-500
                   file:mr-4 file:py-2 file:px-4
                   file:rounded-full file:border-0
                   file:text-sm file:font-semibold
                   file:bg-primary-color/10 file:text-primary-color
                   hover:file:bg-primary-color/20">
            <div v-if="form.errors.profile_picture" class="text-red-500 text-sm mt-1">
              {{ form.errors.profile_picture }}
            </div>
          </div>
        </div>

        <!-- Personal Information -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
              <Input
                v-model="form.first_name"
                :disabled="form.processing"
                type="text"
                placeholder="Enter your first name"
                class="bg-white border-primary-color"
                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.first_name}"
              />
              <div v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">
                {{ form.errors.first_name }}
              </div>
            </div>
            <div>
              <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
              <Input
                v-model="form.middle_name"
                :disabled="form.processing"
                type="text"
                placeholder="Enter your middle name"
                class="bg-white border-primary-color"
                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.middle_name}"
              />
              <div v-if="form.errors.middle_name" class="text-red-500 text-sm mt-1">
                {{ form.errors.middle_name }}
              </div>
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
              <Input
                v-model="form.last_name"
                :disabled="form.processing"
                type="text"
                placeholder="Enter your last name"
                class="bg-white border-primary-color"
                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.last_name}"
              />
              <div v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">
                {{ form.errors.last_name }}
              </div>
            </div>
            <div>
              <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
              <Select v-model="form.gender" :disabled="form.processing">
                <SelectTrigger :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.gender}" class="bg-white border-primary-color">
                  <SelectValue :placeholder="'Select Gender'" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="male">Male</SelectItem>
                  <SelectItem value="female">Female</SelectItem>
                  <SelectItem value="non-binary">Non-binary</SelectItem>
                  <SelectItem value="prefer-not-to-say">Prefer not to say</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.gender" class="text-red-500 text-sm mt-1">
                {{ form.errors.gender }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              <Popover>
                <PopoverTrigger as-child>
                  <Button 
                    variant="outline" 
                    :disabled="form.processing"
                    :class="cn(
                      'w-full p-2.5 justify-start text-left font-normal bg-white border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color',
                      !date && 'text-muted-foreground'
                    )"
                  >
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    {{ date ? format(date, 'PPP') : 'Pick a date' }}
                  </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0" align="start">
                  <CustomCalendar
                    v-model="date"
                    mode="single"
                    class="rounded-md border"
                    @update:model-value="(newDate) => {
                      form.date_of_birth = format(new Date(newDate.toString()), 'yyyy-MM-dd');
                    }"
                  />
                </PopoverContent>
              </Popover>
              <div v-if="form.errors.date_of_birth" class="text-red-500 text-sm mt-1">
                {{ form.errors.date_of_birth }}
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <Input
                v-model="form.phone"
                :disabled="form.processing"
                type="tel"
                placeholder="Enter your phone number"
                class="bg-white border-primary-color"
                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.phone}"
              />
              <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                {{ form.errors.phone }}
              </div>
            </div>

            <!-- Single WMSU Email field with conditional display -->
            <div v-if="requiresWmsuEmail || isAlumni">
              <label for="wmsu_email" class="block text-sm font-medium text-gray-700 mb-1">
                WMSU Email {{ isAlumni ? '(Optional)' : '' }}
              </label>
              <Input
                v-model="form.wmsu_email"
                :disabled="form.processing"
                type="email"
                placeholder="Enter your WMSU email"
                class="bg-white border-primary-color"
                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.wmsu_email}"
              />
              <div v-if="form.errors.wmsu_email" class="text-red-500 text-sm mt-1">
                {{ form.errors.wmsu_email }}
              </div>
            </div>

            <div>
              <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
              <Input
                v-model="form.username"
                :disabled="form.processing"
                type="text"
                placeholder="Enter your username"
                class="bg-white border-primary-color"
                :class="{'ring-2 ring-red-500 ring-offset-1': form.errors.username}"
              />
              <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">
                {{ form.errors.username }}
              </div>
            </div>
          </div>
        </div>

        <!-- Academic Information - Conditional based on user type -->
        <div v-if="showAcademicInfo" class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900">Academic Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Department Selection - For College and Postgrad -->
            <div v-if="showDepartment">
              <label for="wmsu_dept_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <Select v-if="showDepartment" v-model="form.wmsu_dept_id" :disabled="form.processing">
                <SelectTrigger>
                  <SelectValue :placeholder="'Select Department'" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="dept in departments" 
                    :key="dept.id" 
                    :value="dept.id"
                  >
                    {{ dept.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.wmsu_dept_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.wmsu_dept_id }}
              </div>
            </div>
            <!-- Grade Level Selection - For Highschool -->
            <div v-if="showGradeLevel">
              <label for="grade_level_id" class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
              <Select v-if="showGradeLevel" v-model="form.grade_level_id" :disabled="form.processing">
                <SelectTrigger>
                  <SelectValue :placeholder="'Select Grade Level'" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="level in gradeLevels" 
                    :key="level.id" 
                    :value="level.id"
                  >
                    {{ level.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.grade_level_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.grade_level_id }}
              </div>
            </div>
          </div>

          <!-- ID Verification Images - Conditional -->
          <div v-if="requiresWmsuId" class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900">ID Verification*</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Front ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">WMSU ID Front</label>
                <div class="relative">
                  <img class="w-full h-48 object-cover rounded-lg"
                    :src="idFrontImageUrl"
                    alt="ID Front">
                  <input type="file" 
                         ref="idFrontInput" 
                         @change="handleIdFrontChange"
                         :disabled="form.processing"
                         accept="image/*"
                         class="mt-2 block w-full text-sm text-gray-500
                         file:mr-4 file:py-2 file:px-4
                         file:rounded-full file:border-0
                         file:text-sm file:font-semibold
                         file:bg-primary-color/10 file:text-primary-color
                         hover:file:bg-primary-color/20">
                  <div v-if="form.errors.wmsu_id_front" class="text-red-500 text-sm mt-1">
                    {{ form.errors.wmsu_id_front }}
                  </div>
                </div>
              </div>

              <!-- Back ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">WMSU ID Back</label>
                <div class="relative">
                  <img class="w-full h-48 object-cover rounded-lg"
                    :src="idBackImageUrl"
                    alt="ID Back">
                  <input type="file" 
                         ref="idBackInput" 
                         @change="handleIdBackChange"
                         :disabled="form.processing"
                         accept="image/*"
                         class="mt-2 block w-full text-sm text-gray-500
                         file:mr-4 file:py-2 file:px-4
                         file:rounded-full file:border-0
                         file:text-sm file:font-semibold
                         file:bg-primary-color/10 file:text-primary-color
                         hover:file:bg-primary-color/20">
                  <div v-if="form.errors.wmsu_id_back" class="text-red-500 text-sm mt-1">
                    {{ form.errors.wmsu_id_back }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-4 pt-4">
          <button type="submit" 
                  :disabled="form.processing"
                  class="bg-primary-color text-white px-6 py-2 rounded-md hover:bg-primary-color/90">
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>

      <!-- Become Seller Section -->
      <div v-if="!user.is_seller" class="border-t pt-8 mt-8">
        <h3 class="text-lg font-medium mb-4">Become a Seller</h3>
        <p class="text-gray-600 mb-6">Start selling your products on our platform</p>
        <Link :href="route('dashboard.become-seller')"
              class="inline-block bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
          Apply Now
        </Link>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, ref, watch, onUnmounted } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import DashboardLayout from './DashboardLayout.vue'
import { Input } from '@/Components/ui/input'
import { Button } from "@/Components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover"
import { CalendarIcon, Check, ChevronsUpDown } from "lucide-vue-next"
import { format } from "date-fns"
import { cn } from "@/lib/utils"
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/Components/ui/select'
import CustomCalendar from '@/Components/ui/custom-calendar.vue'
import { today, getLocalTimeZone, parseDate } from '@internationalized/date'
import { Toaster } from '@/Components/ui/toast'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps({
  user: Object,
  stats: Object,
  departments: Array,
  gradeLevels: Array,
  errors: Object // Add this prop
})

const flash = computed(() => usePage().props.flash || {})

const form = useForm({
  first_name: props.user.first_name,
  middle_name: props.user.middle_name,
  last_name: props.user.last_name,
  gender: props.user.gender || '',
  phone: props.user.phone,
  date_of_birth: props.user.date_of_birth,
  username: props.user.username,
  wmsu_email: props.user.wmsu_email,
  wmsu_dept_id: props.user.wmsu_dept_id,
  grade_level_id: props.user.grade_level_id,
  profile_picture: null,
  wmsu_id_front: null,
  wmsu_id_back: null
})

// Computed properties for conditional rendering
const showAcademicInfo = computed(() => {
  return ['HS', 'COL', 'PG'].includes(props.user.user_type_code)
})

const showDepartment = computed(() => {
  return ['COL', 'PG'].includes(props.user.user_type_code)
})

const showGradeLevel = computed(() => {
  return props.user.user_type_code === 'HS'
})

const profileImageUrl = computed(() => {
  return props.user.profile_picture 
    ? props.user.profile_picture // Now using the full URL from the backend
    : '/images/default-avatar.png'
})

// Add new computed properties for ID images
const idFrontImageUrl = computed(() => {
  return props.user.wmsu_id_front 
    ? props.user.wmsu_id_front.startsWith('http') 
      ? props.user.wmsu_id_front 
      : `/storage/${props.user.wmsu_id_front}`
    : '/images/id-placeholder.png'
})

const idBackImageUrl = computed(() => {
  return props.user.wmsu_id_back
    ? props.user.wmsu_id_back.startsWith('http')
      ? props.user.wmsu_id_back
      : `/storage/${props.user.wmsu_id_back}`
    : '/images/id-placeholder.png'
})

const date = ref(today(getLocalTimeZone()))

// Add watch for date_of_birth changes
watch(() => form.date_of_birth, (newValue) => {
    if (newValue) {
        try {
            const [year, month, day] = newValue.split('-');
            date.value = parseDate(`${year}-${month}-${day}`);
        } catch (e) {
            console.error('Invalid date format:', e);
        }
    }
}, { immediate: true })

// Add toast setup
const { toast } = useToast()
const page = usePage()

// Watch for flash messages
watch(() => page.props.flash.toast, (flashToast) => {
  if (flashToast) {
    toast({
      variant: flashToast.variant,
      title: flashToast.title,
      description: flashToast.description,
    })
  }
}, { immediate: true })

// Add this computed property to properly handle errors
const formErrors = computed(() => usePage().props.errors)

// Add new computed properties for conditional requirements
const isAlumni = computed(() => props.user.user_type_code === 'ALM')
const requiresWmsuEmail = computed(() => ['HS', 'COL', 'EMP', 'PG'].includes(props.user.user_type_code))
const requiresWmsuId = computed(() => ['HS', 'COL', 'EMP', 'PG', 'ALM'].includes(props.user.user_type_code))

function updateProfile() {
  // Basic validation before submission
  let hasErrors = false;
  const requiredFields = {
    first_name: 'First name',
    last_name: 'Last name',
    gender: 'Gender',
    date_of_birth: 'Date of birth',
    phone: 'Phone number'
  };

  // Add conditional required fields
  if (requiresWmsuEmail.value) {
    requiredFields.wmsu_email = 'WMSU Email';
  }

  if (requiresWmsuId.value) {
    if (!form.wmsu_id_front && !props.user.wmsu_id_front) {
      form.errors.wmsu_id_front = 'WMSU ID Front is required';
      hasErrors = true;
    }
    if (!form.wmsu_id_back && !props.user.wmsu_id_back) {
      form.errors.wmsu_id_back = 'WMSU ID Back is required';
      hasErrors = true;
    }
  }

  form.post(route('profile.update'), {
    preserveScroll: true,
    forceFormData: true, // Force FormData for file uploads
    onSuccess: () => {
      // Reset file inputs after successful submission
      if (form.profile_picture) {
        form.profile_picture = null
        if (profilePictureInput.value) profilePictureInput.value.value = ''
      }
      if (form.wmsu_id_front) {
        form.wmsu_id_front = null
        if (idFrontInput.value) idFrontInput.value.value = ''
      }
      if (form.wmsu_id_back) {
        form.wmsu_id_back = null
        if (idBackInput.value) idBackInput.value.value = ''
      }

      toast({
        variant: 'default',
        title: 'Success!',
        description: 'Profile updated successfully.'
      })
    },
    onError: (errors) => {
      console.error('Form errors:', errors)
      toast({
        variant: 'destructive',
        title: 'Error!',
        description: 'There was a problem updating your profile.'
      })
    }
  })
}

// Add refs for file inputs
const profilePictureInput = ref(null)
const idFrontInput = ref(null)
const idBackInput = ref(null)

// Update the file handling functions
function handleProfilePicture(e) {
  if (e.target.files[0]) {
    form.profile_picture = e.target.files[0]
  }
}

function handleIdFrontChange(e) {
  if (e.target.files[0]) {
    form.wmsu_id_front = e.target.files[0]
  }
}

function handleIdBackChange(e) {
  if (e.target.files[0]) {
    form.wmsu_id_back = e.target.files[0]
  }
}
</script>
