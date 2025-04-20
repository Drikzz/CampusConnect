<template>
  <AdminLayout :user="auth.user">
    <div class="container mx-auto">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold">Campus Locations</h1>
        <p class="text-gray-600">Manage campus-wide meetup locations for students and sellers</p>
      </div>

      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6">
          <!-- Search and Actions -->
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <div class="w-full sm:w-auto">
              <Input 
                type="search"
                placeholder="Search locations..."
                v-model="search"
                @input="debounceSearch"
                class="w-full sm:w-64"
              />
            </div>
            <Button @click="openCreateDialog" class="w-full sm:w-auto">
              <PlusIcon class="w-4 h-4 mr-2" />
              Add New Location
            </Button>
          </div>

          <!-- Locations Table -->
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Coordinates</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="location in locations.data" :key="location.id" class="hover:bg-gray-50">
                  <TableCell class="font-medium">{{ location.name }}</TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="text-xs text-gray-500">Lat: {{ location.latitude }}</span>
                      <span class="text-xs text-gray-500">Long: {{ location.longitude }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <Button 
                        variant="ghost" 
                        size="icon"
                        @click="viewOnMap(location)"
                        title="View on map"
                      >
                        <MapPinIcon class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="icon"
                        @click="editLocation(location)"
                        title="Edit location"
                      >
                        <EditIcon class="h-4 w-4" />
                      </Button>
                      <div class="relative group">
                        <Button 
                          variant="ghost" 
                          size="icon"
                          @click="confirmDelete(location)"
                          :disabled="isLocationInUse(location)"
                          title="Delete location"
                          :class="{ 'opacity-50 cursor-not-allowed bg-gray-100': isLocationInUse(location) }"
                        >
                          <TrashIcon class="h-4 w-4" :class="isLocationInUse(location) ? 'text-gray-400' : 'text-red-500'" />
                          <span v-if="isLocationInUse(location)" class="sr-only">Cannot delete - location in use</span>
                        </Button>
                        <div 
                          v-if="isLocationInUse(location)" 
                          class="absolute -top-10 right-0 bg-black text-white text-xs p-2 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10 w-48 text-center"
                        >
                          Cannot delete - this location is in use
                        </div>
                      </div>
                    </div>
                  </TableCell>
                </TableRow>
                
                <TableRow v-if="locations.data.length === 0">
                  <TableCell colspan="3" class="h-24 text-center">
                    No locations found. 
                    <Button variant="link" @click="openCreateDialog">Add your first location</Button>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <!-- Pagination -->
            <div v-if="locations.data.length > 0" class="mt-4 flex justify-between items-center">
              <div class="text-sm text-gray-500">
                Showing {{ locations.from }} to {{ locations.to }} of {{ locations.total }} locations
              </div>
              <div class="flex gap-2">
                <Button 
                  variant="outline" 
                  size="sm" 
                  :disabled="!locations.prev_page_url"
                  @click="changePage(locations.current_page - 1)"
                >
                  Previous
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  :disabled="!locations.next_page_url"
                  @click="changePage(locations.current_page + 1)"
                >
                  Next
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Info (only visible during development) -->
    <div v-if="isDev" class="mt-8 p-4 bg-gray-100 rounded-lg text-sm">
      <h3 class="font-semibold mb-2">Debug Information:</h3>
      <div v-for="location in locations.data" :key="location.id" class="mb-2">
        <div>Location: {{ location.name }}</div>
        <div>In Use: {{ isLocationInUse(location) ? 'Yes' : 'No' }} (meetup_locations_count: {{ location.meetup_locations_count }})</div>
      </div>
    </div>
    
    <!-- Create/Edit Dialog -->
    <Dialog :open="showDialog" @update:open="showDialog = $event">
      <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <DialogHeader>
          <DialogTitle>{{ editingLocation ? 'Edit Location' : 'Add New Location' }}</DialogTitle>
          <DialogDescription>
            {{ editingLocation 
              ? 'Update the details for this campus location.' 
              : 'Add a new meetup location for the campus.' }}
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="handleSubmit" class="space-y-4 flex-1 overflow-y-auto p-1">
          <!-- Location Name (Always Manual) -->
          <div>
            <Label for="name">Location Name</Label>
            <Input 
              id="name" 
              v-model="form.name" 
              placeholder="e.g. WMSU Main Library" 
              required 
            />
            <div v-if="errors.name" class="text-sm text-red-500 mt-1">
              {{ errors.name }}
            </div>
          </div>
          
          <!-- Map to assist with coordinate selection -->
          <div class="space-y-4">
            <div>
              <Label>Select Location on Map (or search)</Label>
              <div class="flex space-x-2 mb-2">
                <Input 
                  id="map-search" 
                  v-model="mapSearch" 
                  placeholder="Search for a location..."
                  class="flex-1"
                  @keydown.enter.prevent="searchLocation"
                />
                <Button type="button" variant="outline" @click="searchLocation">
                  <SearchIcon class="h-4 w-4 mr-2" />
                  Search
                </Button>
              </div>
            </div>
            
            <div class="h-[250px] border rounded-md relative" ref="mapContainer">
              <!-- Map will be mounted here -->
            </div>
          </div>
          
          <!-- Manual coordinate inputs -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="latitude">Latitude</Label>
              <Input 
                id="latitude" 
                v-model="form.latitude" 
                type="text" 
                inputmode="decimal"
                pattern="[0-9]*[.]?[0-9]*"
                step="0.0000001" 
                placeholder="e.g. 6.912892" 
                required 
              />
              <div v-if="errors.latitude" class="text-sm text-red-500 mt-1">
                {{ errors.latitude }}
              </div>
            </div>
            <div>
              <Label for="longitude">Longitude</Label>
              <Input 
                id="longitude" 
                v-model="form.longitude" 
                type="text"
                inputmode="decimal"
                pattern="[0-9]*[.]?[0-9]*"
                step="0.0000001" 
                placeholder="e.g. 122.061776" 
                required 
              />
              <div v-if="errors.longitude" class="text-sm text-red-500 mt-1">
                {{ errors.longitude }}
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 text-sm text-gray-500 bg-blue-50 p-3 rounded-md">
            <InfoIcon class="h-4 w-4 text-blue-500" />
            <span>Click on the map to set coordinates, or enter them manually. The location name must always be entered manually.</span>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="showDialog = false">
              Cancel
            </Button>
            <Button type="submit">
              {{ editingLocation ? 'Update Location' : 'Add Location' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Location</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete "{{ locationToDelete?.name }}"? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction variant="destructive" @click="deleteLocation">Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onBeforeUnmount, onMounted, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import { 
  PlusIcon,
  InfoIcon,
  MapPinIcon,
  EditIcon,
  TrashIcon,
  SearchIcon
} from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/Components/ui/dialog'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/Components/ui/alert-dialog'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

// Import Leaflet libraries
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.css'
import 'leaflet-defaulticon-compatibility'
import { OpenStreetMapProvider } from 'leaflet-geosearch'

// Add this line to define the isDev ref
const isDev = ref(import.meta.env.DEV)

const props = defineProps({
  auth: Object,
  locations: Object,
  filters: Object,
})

const page = usePage()
const search = ref(props.filters?.search || '')
const showDialog = ref(false)
const showDeleteDialog = ref(false)
const editingLocation = ref(null)
const locationToDelete = ref(null)
const errors = ref({})
const form = ref({
  name: '',
  latitude: '',
  longitude: '',
})

// Map related refs
const mapContainer = ref(null)
const map = ref(null)
const marker = ref(null)
const mapSearch = ref('')
const searchProvider = new OpenStreetMapProvider()

// Function to initialize the map
const initializeMap = async () => {
  if (!mapContainer.value || map.value) return;

  await nextTick()
  
  // Default to WMSU (center of your campus locations)
  const defaultLat = 6.9130;
  const defaultLng = 122.0624;
  
  map.value = L.map(mapContainer.value).setView([defaultLat, defaultLng], 16);
  
  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map.value);
  
  // Add click event to map
  map.value.on('click', handleMapClick);
  
  // If we're editing an existing location, place a marker there
  if (editingLocation.value && form.value.latitude && form.value.longitude) {
    setMarker(form.value.latitude, form.value.longitude);
  }
}

// Function to set a marker at the given coordinates
const setMarker = (lat, lng) => {
  // Remove existing marker if any
  if (marker.value) {
    map.value.removeLayer(marker.value);
  }
  
  // Add new marker
  marker.value = L.marker([lat, lng]).addTo(map.value);
  
  // Center map on marker
  map.value.setView([lat, lng], 16);
}

// Handle map click - set marker and update form
const handleMapClick = (e) => {
  const { lat, lng } = e.latlng;
  
  // Format with 8 decimal places but store as numbers
  form.value.latitude = parseFloat(lat.toFixed(8));
  form.value.longitude = parseFloat(lng.toFixed(8));
  
  // Set marker
  setMarker(lat, lng);
}

// Search for a location
const searchLocation = async () => {
  if (!mapSearch.value) return;
  
  try {
    const results = await searchProvider.search({ query: mapSearch.value + ' WMSU Zamboanga' });
    
    if (results && results.length > 0) {
      const { x: lng, y: lat } = results[0];
      
      // Format with 8 decimal places but store as numbers
      form.value.latitude = parseFloat(lat.toFixed(8));
      form.value.longitude = parseFloat(lng.toFixed(8));
      
      // Update map marker
      setMarker(lat, lng);
      
      // Reset search input
      mapSearch.value = '';
    }
  } catch (error) {
    console.error('Location search error:', error);
  }
}

// Watch for dialog open/close to initialize map
watch(showDialog, async (isOpen) => {
  if (isOpen) {
    await nextTick();
    initializeMap();
  } else {
    // Clean up map when dialog closes
    if (map.value) {
      map.value.remove();
      map.value = null;
      marker.value = null;
    }
  }
});

// Handle form submission for create/edit
const handleSubmit = () => {
  // Ensure latitude and longitude are numbers before submission
  form.value.latitude = parseFloat(form.value.latitude);
  form.value.longitude = parseFloat(form.value.longitude);
  
  if (editingLocation.value) {
    router.put(route('admin.locations.update', editingLocation.value.id), form.value, {
      onSuccess: () => {
        showDialog.value = false;
        errors.value = {};
      },
      onError: (err) => {
        errors.value = err;
      }
    });
  } else {
    router.post(route('admin.locations.store'), form.value, {
      onSuccess: () => {
        showDialog.value = false;
        errors.value = {};
      },
      onError: (err) => {
        errors.value = err;
      }
    });
  }
}

// Check if location is being used by any meetup locations
const isLocationInUse = (location) => {
  // Log for debugging
  console.log(`Checking location ${location.name}: meetup_locations_count = ${location.meetup_locations_count}`);
  
  // Ensure we have a valid number and check if it's greater than 0
  return location.meetup_locations_count > 0;
}

// Open map with coordinates
const viewOnMap = (location) => {
  const url = `https://www.google.com/maps/search/?api=1&query=${location.latitude},${location.longitude}`;
  window.open(url, '_blank');
}

// Initialize new location form
const openCreateDialog = () => {
  editingLocation.value = null;
  form.value = {
    name: '',
    latitude: '',
    longitude: '',
  };
  errors.value = {};
  showDialog.value = true;
}

// Prepare edit form with existing data
const editLocation = (location) => {
  editingLocation.value = location;
  form.value = {
    name: location.name,
    latitude: location.latitude,
    longitude: location.longitude,
  };
  errors.value = {};
  showDialog.value = true;
}

// Prepare delete confirmation
const confirmDelete = (location) => {
  if (isLocationInUse(location)) return; // Prevent deletion if in use
  locationToDelete.value = location;
  showDeleteDialog.value = true;
}

// Perform deletion
const deleteLocation = () => {
  router.delete(route('admin.locations.destroy', locationToDelete.value.id), {
    onSuccess: () => {
      showDeleteDialog.value = false;
    }
  });
}

// Debounce search input
let timeout;
const debounceSearch = () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    router.get(route('admin.locations'), {
      search: search.value
    }, {
      preserveState: true,
      replace: true
    });
  }, 300);
}

// Handle pagination
const changePage = (page) => {
  router.get(route('admin.locations'), {
    page: page,
    search: search.value
  }, {
    preserveState: true,
    replace: true
  });
}

// Clean up on component unmount
onBeforeUnmount(() => {
  clearTimeout(timeout);
  if (map.value) {
    map.value.remove();
    map.value = null;
  }
});
</script>

<style>
/* Add required Leaflet CSS styles */
.leaflet-container {
  height: 100%;
  width: 100%;
}
</style>
