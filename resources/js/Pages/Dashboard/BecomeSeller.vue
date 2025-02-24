<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-2xl font-bold mb-6">Become a Seller</h2>

      <div class="bg-white p-6 rounded-lg shadow-md space-y-8">
        <!-- Benefits Section -->
        <section>
          <h3 class="text-xl font-semibold mb-4">Why Sell on Campus Connect?</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start space-x-4">
              <div class="p-2 bg-primary-color/10 rounded-lg">
                <UsersIcon class="w-6 h-6 text-primary-color" />
              </div>
              <div>
                <h4 class="font-medium mb-1">Wide Reach</h4>
                <p class="text-gray-600">Connect with thousands of students and staff members within WMSU</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="p-2 bg-primary-color/10 rounded-lg">
                <ShoppingBagIcon class="w-6 h-6 text-primary-color" />
              </div>
              <div>
                <h4 class="font-medium mb-1">Easy Management</h4>
                <p class="text-gray-600">Simple tools to list and manage your products</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="p-2 bg-primary-color/10 rounded-lg">
                <ShieldCheckIcon class="w-6 h-6 text-primary-color" />
              </div>
              <div>
                <h4 class="font-medium mb-1">Secure Platform</h4>
                <p class="text-gray-600">Safe and secure selling environment</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <div class="p-2 bg-primary-color/10 rounded-lg">
                <TagIcon class="w-6 h-6 text-primary-color" />
              </div>
              <div>
                <h4 class="font-medium mb-1">Flexible Options</h4>
                <p class="text-gray-600">Sell or trade items with other members</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Process Section -->
        <section>
          <h3 class="text-xl font-semibold mb-4">How It Works</h3>
          <div class="space-y-4">
            <div v-for="(step, index) in steps" :key="index" 
              class="flex items-center space-x-4 p-4 rounded-lg border border-gray-200">
              <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-primary-color text-white rounded-full">
                {{ index + 1 }}
              </div>
              <div>
                <h4 class="font-medium">{{ step.title }}</h4>
                <p class="text-gray-600">{{ step.description }}</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Terms and Conditions -->
        <section v-if="showTerms" class="space-y-4">
          <h3 class="text-xl font-semibold">Terms and Conditions</h3>
          <div class="p-4 bg-gray-50 rounded-lg max-h-64 overflow-y-auto">
            <div class="prose prose-sm max-w-none">
              <!-- Terms content -->
              <h4>1. General Terms</h4>
              <p>By becoming a seller on Campus Connect, you agree to...</p>
              <!-- Add more terms sections as needed -->
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <Checkbox v-model="acceptTerms" id="terms" />
            <label for="terms" class="text-sm text-gray-700">
              I have read and agree to the Terms and Conditions
            </label>
          </div>
        </section>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
          <Button v-if="!showTerms" variant="outline" @click="showTerms = true">
            Continue
          </Button>
          <AlertDialog>
            <AlertDialogTrigger asChild>
              <Button 
                v-if="showTerms"
                :disabled="!acceptTerms">
                Apply Now
              </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Become a Seller</AlertDialogTitle>
                <AlertDialogDescription>
                  Are you sure you want to become a seller? This action will enable seller features on your account.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="submitApplication">Continue</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Button } from '@/Components/ui/button'
import { Checkbox } from '@/Components/ui/checkbox'
import { ShieldCheckIcon, ShoppingBagIcon, TagIcon, UsersIcon } from 'lucide-vue-next'
import DashboardLayout from './DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/Components/ui/alert-dialog'

const props = defineProps({
  user: Object,
  stats: Object
})

const showTerms = ref(false)
const acceptTerms = ref(false)

const steps = [
  {
    title: 'Review Terms',
    description: 'Read and accept our seller guidelines and terms'
  },
  {
    title: 'Complete Profile',
    description: 'Ensure your seller profile is complete and verified'
  },
  {
    title: 'List Products',
    description: 'Start adding your products to the marketplace'
  },
  {
    title: 'Start Selling',
    description: 'Begin selling and managing your store'
  }
]

function submitApplication() {
  router.post(route('dashboard.seller.become'), {
    acceptTerms: acceptTerms.value
  })
}
</script>
