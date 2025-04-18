<script setup>
import { ref, reactive, computed, onMounted, watch, onBeforeUnmount, nextTick, inject } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { useToast } from "@/Components/ui/toast/use-toast";
import { format } from "date-fns";
import MeetupDate from '@/Components/ui/trade-calendar/meetup-date.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/Components/ui/tooltip";
import { Checkbox } from "@/Components/ui/checkbox";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";

const globalToast = inject('globalToast', null);
const { toast: localToast } = useToast();
const toast = globalToast || localToast;

const mapInitAttempts = ref(0);
const MAX_INIT_ATTEMPTS = 5;

const L = ref(null);
const loadLeaflet = async () => {
  if (typeof window !== 'undefined') {
    L.value = await import('leaflet');
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
  }
};

const DEFAULT_LATITUDE = 6.9214;
const DEFAULT_LONGITUDE = 122.0790;

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    open: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['close', 'update:open']);
const loading = ref(false);
const errors = ref({});
const meetupLocations = ref([]);
const isLoadingMeetupLocations = ref(false);
const selectedSchedule = ref(null);
const showMap = ref(false);
const isMapReady = ref(false);

const form = useForm({
    seller_product_id: props.product.id,
    meetup_location_id: '',
    meetup_date: '',
    preferred_time: '',
    additional_cash: 0,
    notes: '',
    offered_items: [
        {
            name: '',
            quantity: 1,
            estimated_value: 0,
            description: '',
            images: [],
            condition: 'new'
        }
    ]
});

const selectedScheduleId = ref('');

const conditionOptions = [
    { value: 'new', label: 'New' },
    { value: 'used_like_new', label: 'Used - Like New' },
    { value: 'used_good', label: 'Used - Good' },
    { value: 'used_fair', label: 'Used - Fair' }
];

const productImageUrl = computed(() => {
  return props.product?.images?.[0] 
    ? `/storage/${props.product.images[0]}` 
    : '/images/placeholder-product.jpg';
});

const sellerName = computed(() => {
  return props.product?.seller 
    ? `${props.product.seller.first_name} ${props.product.seller.last_name}`
    : 'Unknown Seller';
});

const sellerProfilePicture = computed(() => {
  return props.product?.seller?.profile_picture
    ? `/storage/${props.product.seller.profile_picture}`
    : '/images/placeholder-avatar.jpg';
});

const fetchMeetupLocations = async () => {
    if (!props.product?.id || isLoadingMeetupLocations.value) return;
    
    try {
        isLoadingMeetupLocations.value = true;
        const response = await fetch(`/trade/${props.product.id}/meetup-locations`);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || `HTTP error! Status: ${response.status}`);
        }
        
        meetupLocations.value = data.meetupLocations.map(loc => ({
            id: loc.id,
            name: loc.name || loc.custom_location || 'Unknown Location',
            description: loc.description || '',
            available_days: loc.available_days || [],
            available_from: loc.available_from || '09:00',
            available_until: loc.available_until || '17:00',
            is_default: !!loc.is_default,
            latitude: loc.latitude ? parseFloat(loc.latitude) : DEFAULT_LATITUDE,
            longitude: loc.longitude ? parseFloat(loc.longitude) : DEFAULT_LONGITUDE,
            max_daily_meetups: loc.max_daily_meetups || 5,
            location_id: loc.location_id,
            use_default_coordinates: !loc.latitude || !loc.longitude
        }));
        
        if (meetupLocations.value.length === 0) {
            errors.value = { 
                ...errors.value,
                meetup_locations: ['No meetup locations available for this seller.']
            };
        }
    } catch (error) {
        errors.value = { 
            ...errors.value,
            meetup_locations: [error.message || "Failed to load meetup locations"]
        };
    } finally {
        isLoadingMeetupLocations.value = false;
    }
};

const availableSchedules = computed(() => {
    const schedules = [];
    if (meetupLocations.value && meetupLocations.value.length > 0) {
        meetupLocations.value.forEach(location => {
            let availableDays = location.available_days || [];
            if (typeof availableDays === 'string') {
                try {
                    availableDays = JSON.parse(availableDays);
                } catch (e) {
                    availableDays = [];
                }
            }
            if (!Array.isArray(availableDays)) {
                availableDays = [];
            }
            availableDays.forEach(day => {
                schedules.push({
                    id: `${location.id}_${day}`,
                    location: location.name || 'Location Not Available',
                    location_id: location.location_id || null,
                    day: day,
                    timeFrom: formatTime(location.available_from),
                    timeUntil: formatTime(location.available_until),
                    description: location.description || '',
                    maxMeetups: location.max_daily_meetups,
                    latitude: location.latitude,
                    longitude: location.longitude,
                    is_default: location.is_default || false,
                    meetup_location_id: location.id
                });
            });
        });
    }
    return schedules;
});

const selectedScheduleDay = computed(() => {
    if (!selectedScheduleId.value) return '';
    const schedule = availableSchedules.value.find(s => s.id === selectedScheduleId.value);
    return schedule?.day || '';
});

const availableTimeSlots = computed(() => {
    if (!selectedSchedule.value) return [];
    const slots = [];
    const fromTime = selectedSchedule.value.timeFrom;
    const untilTime = selectedSchedule.value.timeUntil;
    const fromParts = fromTime.match(/(\d+):(\d+)\s*([ap]m)?/i);
    const untilParts = untilTime.match(/(\d+):(\d+)\s*([ap]m)?/i);
    if (!fromParts || !untilParts) return [];
    let startHour = parseInt(fromParts[1]);
    const startMinute = parseInt(fromParts[2]);
    const startPeriod = fromParts[3]?.toLowerCase();
    if (startPeriod === 'pm' && startHour < 12) startHour += 12;
    if (startPeriod === 'am' && startHour === 12) startHour = 0;
    let endHour = parseInt(untilParts[1]);
    const endMinute = parseInt(untilParts[2]);
    const endPeriod = untilParts[3]?.toLowerCase();
    if (endPeriod === 'pm' && endHour < 12) endHour += 12;
    if (endPeriod === 'am' && endHour === 12) startHour = 0;
    const startDate = new Date(2000, 0, 1, startHour, startMinute);
    const endDate = new Date(2000, 0, 1, endHour, endMinute);
    let currentTime = new Date(startDate);
    while (currentTime < endDate) {
        const hour = currentTime.getHours();
        const minute = currentTime.getMinutes();
        const timeValue = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
        const timeDisplay = currentTime.toLocaleTimeString('en-US', { 
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).toLowerCase();
        slots.push({
            value: timeValue,
            display: timeDisplay
        });
        currentTime.setMinutes(currentTime.getMinutes() + 30);
    }
    return slots;
});

watch(
    [() => props.open, () => props.product?.id], 
    ([isOpen, productId]) => {
        if (isOpen && productId) {
            form.seller_product_id = productId;
            fetchMeetupLocations();
        }
    },
    { immediate: true }
);

const handleDialogClose = (isOpen) => {
    if (!isOpen) {
        closeDialog();
    }
};

watch(() => selectedScheduleId.value, (newSchedule) => {
  if (newSchedule) {
    const schedule = availableSchedules.value.find(s => String(s.id) === String(newSchedule));
    selectedSchedule.value = schedule || null;
    
    if (schedule) {
      console.log('Selected schedule:', schedule);
      const [locationId] = String(schedule.id).split('_');
      console.log('Extracted location ID:', locationId);
      
      if (String(form.meetup_location_id) !== String(locationId)) {
        console.log('Updating form.meetup_location_id from', form.meetup_location_id, 'to', locationId);
        form.meetup_location_id = locationId;
      }
    }
  } else {
    selectedSchedule.value = null;
  }
}, { immediate: true });

watch(() => selectedScheduleId.value, (newValue, oldValue) => {
  if (newValue) {
    const newValueStr = String(newValue);
    const oldValueStr = oldValue !== undefined ? String(oldValue) : undefined;
    
    if (newValueStr !== oldValueStr) {
      console.log('Meetup schedule changed:', {
        scheduleId: newValue,
        scheduleIdType: typeof newValue,
        schedule: availableSchedules.value.find(s => s.id === newValue),
        previousScheduleId: oldValue,
        previousScheduleIdType: typeof oldValue
      });
    }
  }
}, { immediate: true });

// Add watchers to clear errors when corresponding fields change
watch(() => selectedScheduleId.value, () => {
  clearFieldError('meetup_schedule');
});

watch(() => form.meetup_date, () => {
  clearFieldError('meetup_date');
});

watch(() => form.preferred_time, () => {
  clearFieldError('preferred_time');
});

const handleDateSelection = (date) => {
  try {
    if (!date) {
      form.meetup_date = '';
      return;
    }
    
    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
      form.meetup_date = date;
      clearFieldError('meetup_date');
      return;
    }
    
    let selectedDate;
    if (date instanceof Date) {
      selectedDate = date;
    } else {
      selectedDate = new Date(date);
    }
    
    if (isNaN(selectedDate.getTime())) {
      console.error("Invalid date object:", date);
      form.meetup_date = '';
      return;
    }
    
    const year = selectedDate.getFullYear();
    const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const day = String(selectedDate.getDate()).padStart(2, '0');
    form.meetup_date = `${year}-${month}-${day}`;
    clearFieldError('meetup_date');
    
    console.log("Date set successfully:", form.meetup_date);
  } catch (error) {
    console.error("Date selection error:", error);
    form.meetup_date = '';
    
    if (date) {
      errors.value.meetup_date = ['Invalid date selection. Please try again.'];
    }
  }
};

const addOfferedItem = () => {
    form.offered_items.push({
        name: '',
        quantity: 1,
        estimated_value: 0,
        description: '',
        images: [],
        condition: 'new'
    });
};

const removeOfferedItem = (index) => {
    if (form.offered_items.length > 1) {
        form.offered_items.splice(index, 1);
    }
};

const removeImage = (itemIndex, imageIndex) => {
  try {
    if (!form.offered_items[itemIndex] || !form.offered_items[itemIndex].images) {
      return;
    }
    const updatedImages = [...form.offered_items[itemIndex].images];
    updatedImages.splice(imageIndex, 1);
    form.offered_items[itemIndex].images = updatedImages;
  } catch (error) {}
};

const getImagePreviewUrl = (image) => {
  try {
    if (typeof image === 'string') {
      return image;
    }
    if (window.URL && image instanceof File) {
      return window.URL.createObjectURL(image);
    }
    return '/images/placeholder-product.jpg';
  } catch (error) {
    return '/images/placeholder-product.jpg';
  }
};

const handleImageError = (e) => {
  e.target.src = '/images/placeholder-product.jpg';
};

const handleFileUpload = (files, itemIndex) => {
  try {
    if (!files || files.length === 0) {
      return;
    }
    const validFiles = Array.from(files).filter(file => {
      if (!file.type.startsWith('image/')) {
        errors.value[`item_${itemIndex}_images`] = ['Please upload only image files'];
        return false;
      }
      const maxSizeMB = 5;
      const maxSizeBytes = maxSizeMB * 1024 * 1024;
      if (file.size > maxSizeBytes) {
        errors.value[`item_${itemIndex}_images`] = [`Maximum file size is ${maxSizeMB}MB`];
        return false;
      }
      return true;
    });
    if (validFiles.length === 0) {
      return;
    }
    if (!form.offered_items[itemIndex]) {
      return;
    }
    if (!form.offered_items[itemIndex].images) {
      form.offered_items[itemIndex].images = [];
    }
    form.offered_items[itemIndex].images = [
      ...form.offered_items[itemIndex].images,
      ...validFiles
    ];
    document.getElementById(`file-upload-${itemIndex}`).value = '';
  } catch (error) {
    errors.value[`item_${itemIndex}_images`] = ['Failed to process uploaded files'];
  }
};

const getFormattedMeetupSchedule = () => {
  if (!form.meetup_date || !form.preferred_time || !selectedScheduleDay.value) {
    return null;
  }
  
  // Format for Laravel Carbon parsing: YYYY-MM-DD HH:MM
  // The controller expects this to be split by comma
  return `${form.meetup_date}, ${form.preferred_time}`;
}

const validateLocationAndSchedule = () => {
  // Clear only location-related errors
  if (errors.value.meetup_schedule) delete errors.value.meetup_schedule;
  
  let isValid = true;
  
  if (!form.meetup_location_id) {
    errors.value.meetup_schedule = ['Please select a meetup location first'];
    isValid = false;
    return isValid;
  }
  
  // Check if there are available schedules for this location
  const locationData = schedulesByLocation.value[form.meetup_location_id];
  if (!locationData || !locationData.schedules || locationData.schedules.length === 0) {
    errors.value.meetup_schedule = ['This location has no available schedules'];
    isValid = false;
    return isValid;
  }
  
  if (!selectedScheduleId.value) {
    errors.value.meetup_schedule = ['Please select a schedule (day) first'];
    isValid = false;
  }
  
  return isValid;
};

const submitTradeOffer = () => {
  // Clear ALL errors before submission
  errors.value = {};
  
  // DEBUG: Log all form values with their data types
  console.log('==================== FORM DATA TYPES ====================');
  console.log('meetup_schedule format:', getFormattedMeetupSchedule(), '| Type:', typeof getFormattedMeetupSchedule());
  console.log('selected_day:', selectedScheduleDay.value, '| Type:', typeof selectedScheduleDay.value);
  console.log('seller_product_id:', form.seller_product_id, '| Type:', typeof form.seller_product_id);
  console.log('meetup_location_id:', form.meetup_location_id, '| Type:', typeof form.meetup_location_id);
  console.log('preferred_time:', form.preferred_time, '| Type:', typeof form.preferred_time);
  console.log('additional_cash:', form.additional_cash, '| Type:', typeof form.additional_cash);
  console.log('notes:', form.notes, '| Type:', typeof form.notes);

  // Validate in sequence and stop if any validation fails
  if (!validateLocationAndSchedule()) {
    return;
  }

  // Only check date if location/schedule is valid
  if (!form.meetup_date) {
    errors.value.meetup_date = ['Please select a valid meetup date'];
    return;
  }

  // Only check time if date is valid
  if (!form.preferred_time) {
    errors.value.preferred_time = ['Please select a preferred time'];
    return;
  }
  
  // Validate offered items
  let isItemsValid = true;
  
  if (!form.offered_items || form.offered_items.length === 0) {
    errors.value.offered_items = ['At least one item must be offered'];
    isItemsValid = false;
  } else {
    form.offered_items.forEach((item, index) => {
      if (!item.name) {
        errors.value[`offered_items.${index}.name`] = ['Item name is required'];
        isItemsValid = false;
      }
      if (!item.quantity || item.quantity < 1) {
        errors.value[`offered_items.${index}.quantity`] = ['Quantity must be at least 1'];
        isItemsValid = false;
      }
      if (item.estimated_value === undefined || item.estimated_value < 0) {
        errors.value[`offered_items.${index}.estimated_value`] = ['Estimated value is required'];
        isItemsValid = false;
      }
      if (!item.condition) {
        errors.value[`offered_items.${index}.condition`] = ['Condition is required'];
        isItemsValid = false;
      }
    });
  }
  
  if (!isItemsValid) {
    return;
  }

  loading.value = true;
  const formData = new FormData();
  
  const formattedSchedule = getFormattedMeetupSchedule();
  if (!formattedSchedule) {
    errors.value = {
      ...errors.value,
      meetup_schedule: ['Invalid meetup schedule']
    };
    loading.value = false;
    return;
  }
  
  formData.append('meetup_schedule', formattedSchedule);
  formData.append('selected_day', selectedScheduleDay.value);
  formData.append('seller_product_id', form.seller_product_id);
  formData.append('meetup_location_id', Number(form.meetup_location_id));
  formData.append('preferred_time', form.preferred_time);
  formData.append('additional_cash', form.additional_cash || 0);
  formData.append('notes', form.notes || '');

  // Process each offered item properly
  form.offered_items.forEach((item, index) => {
    console.log(`Item ${index+1} images:`, item.images, 
    'Length:', item.images?.length, 
    'File objects:', item.images?.filter(i => i instanceof File).length);

    formData.append(`offered_items[${index}][name]`, item.name);
    formData.append(`offered_items[${index}][quantity]`, item.quantity);
    formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
    formData.append(`offered_items[${index}][description]`, item.description || '');
    formData.append(`offered_items[${index}][condition]`, item.condition);
    
    // Create an empty array to track image info that will be stringified
    const imageInfoArray = [];

    // Process images properly
    if (item.images && item.images.length > 0) {
      item.images.forEach((image, imageIndex) => {
        if (image instanceof File) {
          // Use a consistent naming format for the files
          const uniqueFilename = `offered_item_${index}_image_${imageIndex}_${Date.now()}_${image.name}`;
          
          // Add to form data with unique key
          formData.append(`offered_items[${index}][image_files][]`, image);
          
          // Store the metadata in our tracking array
          imageInfoArray.push({
            original_name: image.name,
            size: image.size,
            type: image.type,
            unique_key: uniqueFilename
          });
        }
      });
      
      // Add the stringified image metadata
      formData.append(`offered_items[${index}][images_json]`, JSON.stringify(imageInfoArray));
    }
  });

  router.post(route('trade.submit'), formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Trade offer submitted successfully!",
        variant: "success"
      });
      closeDialog();
    },
    onError: (validationErrors) => {
      errors.value = validationErrors;
      toast({
        title: "Error",
        description: "Please check the form for errors",
        variant: "destructive"
      });
    },
    onFinish: () => {
      loading.value = false;
    }
  });
};

const selectLocationFromMap = (location) => {
  if (!location || !location.schedules || !location.schedules.length) {
    errors.value.meetup_schedule = ['This location has no available days'];
    return;
  }
  
  console.log('Selecting location from map:', location);
  
  // Clear ALL schedule-related errors when selecting a new location
  if (errors.value.meetup_schedule) delete errors.value.meetup_schedule;
  if (errors.value.meetup_date) delete errors.value.meetup_date;
  if (errors.value.preferred_time) delete errors.value.preferred_time;
  
  const locationId = String(location.id);
  const isSameLocation = form.meetup_location_id === locationId;
  
  if (isSameLocation) {
    console.log('Same location selected');
    
    markers.value.forEach(marker => {
      if (marker.isPopupOpen()) {
        marker.closePopup();
      }
    });
    
    showMap.value = false;
    return;
  }
  
  // Reset form values related to scheduling
  form.meetup_location_id = locationId;
  console.log('Set meetup_location_id to:', locationId);
  
  // Clear these values when changing location
  selectedScheduleId.value = '';
  form.meetup_date = '';
  form.preferred_time = '';
  selectedSchedule.value = null;
  
  // Close popups
  markers.value.forEach(marker => {
    if (marker.isPopupOpen()) {
      marker.closePopup();
    }
  });
  
  // Only hide the map after location is selected
  showMap.value = false;
  
  // Set a default schedule if available (first one for this location)
  nextTick(() => {
    const locationData = schedulesByLocation.value[locationId];
    if (locationData && locationData.schedules && locationData.schedules.length > 0) {
      // Auto-select the first schedule for better UX
      selectedScheduleId.value = locationData.schedules[0].id;
      console.log('Auto-selected schedule:', selectedScheduleId.value);
    }
  });
};

// Add this helper to clear errors when users interact with specific fields
const clearFieldError = (fieldName) => {
  if (errors.value && errors.value[fieldName]) {
    delete errors.value[fieldName];
  }
  // Clear any other error that might be displayed prematurely
  if (fieldName === 'meetup_schedule' && errors.value.meetup_date) {
    delete errors.value.meetup_date;
  }
};

const resetLocationSelection = () => {
  form.meetup_location_id = '';
  selectedScheduleId.value = '';
  form.meetup_date = '';
  form.preferred_time = '';
  selectedSchedule.value = null;
  
  showMap.value = true;
  
  if (errors.value && errors.value.meetup_schedule) {
    delete errors.value.meetup_schedule;
  }
  
  setTimeout(() => {
    if (mapElement.value) {
      if (map.value) {
        map.value.invalidateSize();
        updateMapMarkers();
      } else {
        initMap();
      }
    }
  }, 300);
};

const toggleMapView = () => {
  showMap.value = !showMap.value;
  
  if (showMap.value) {
    setTimeout(() => {
      if (mapElement.value) {
        if (map.value) {
          map.value.invalidateSize();
          updateMapMarkers();
          
          if (form.meetup_location_id) {
            highlightSelectedLocation(form.meetup_location_id);
          }
        } else {
          initMap();
        }
      }
    }, 300);
  }
};

const highlightSelectedLocation = (locationId) => {
  if (!map.value || markers.value.length === 0) return;
  
  const location = schedulesByLocation.value[locationId];
  if (!location) return;
  
  markers.value.forEach(marker => {
    const markerLatLng = marker.getLatLng();
    if (markerLatLng.lat === parseFloat(location.latitude) && 
        markerLatLng.lng === parseFloat(location.longitude)) {
      const markerElement = marker.getElement();
      if (markerElement) {
        markerElement.classList.add('selected-location-marker');
      }
    }
  });
};

const closeDialog = () => {
    form.reset();
    errors.value = {};
    selectedSchedule.value = null;
    emit('close');
    emit('update:open', false);
};

const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const date = new Date(2000, 0, 1, hours, minutes);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).toLowerCase();
};

const formatPrice = (price) => {
    return new Intl.NumberFormat().format(price);
};

const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const setPreferredTime = (timeValue) => {
  form.preferred_time = timeValue;
  clearFieldError('preferred_time');
};

const mapElement = ref(null);
const map = ref(null);
const markers = ref([]);

const defaultMeetupLocation = computed(() => {
  return meetupLocations.value.find(loc => loc.is_default);
});

const schedulesByLocation = computed(() => {
  const grouped = {};
  meetupLocations.value.forEach(location => {
    const schedules = [];
    let availableDays = location.available_days || [];
    if (typeof availableDays === 'string') {
      try {
        availableDays = JSON.parse(availableDays);
      } catch (e) {
        availableDays = [];
      }
    }
    if (!Array.isArray(availableDays)) {
      availableDays = [];
    }
    availableDays.forEach(day => {
      schedules.push({
        id: `${location.id}_${day}`,
        day: day,
        timeFrom: formatTime(location.available_from),
        timeUntil: formatTime(location.available_until),
      });
    });
    if (schedules.length > 0) {
      grouped[location.id] = {
        id: location.id,
        name: location.name,
        latitude: location.latitude || DEFAULT_LATITUDE,
        longitude: location.longitude || DEFAULT_LONGITUDE,
        description: location.description,
        is_default: location.is_default || false,
        schedules: schedules,
        use_default_coordinates: location.use_default_coordinates || false
      };
    }
  });
  return grouped;
});

onMounted(async () => {
  await loadLeaflet();
  
  nextTick(() => {
    setTimeout(() => {
      isMapReady.value = true;
      
      if (!mapElement.value) {
        console.log('Map element not immediately available, setting up with delay');
        setTimeout(() => {
          initMap();
        }, 500);
      } else {
        initMap();
      }
      
      showMap.value = true;
    }, 300);
  });
});

onBeforeUnmount(() => {
  if (map.value) {
    map.value.remove();
  }
});

watch(meetupLocations, () => {
  if (map.value && L.value) {
    updateMapMarkers();
  }
}, { deep: true });

const initMap = () => {
  if (!L.value) {
    console.warn('Leaflet library not loaded');
    return;
  }
  
  if (!mapElement.value) {
    if (mapInitAttempts.value < MAX_INIT_ATTEMPTS) {
      console.warn(`Map element not found in DOM (attempt ${mapInitAttempts.value + 1}/${MAX_INIT_ATTEMPTS})`);
      mapInitAttempts.value++;
      
      setTimeout(() => {
        console.log('Retrying map initialization...');
        initMap();
      }, 500);
      return;
    } else {
      console.error('Map element still not available after maximum retries');
      return;
    }
  }
  
  mapInitAttempts.value = 0;
  
  try {
    if (map.value) {
      map.value.remove();
      map.value = null;
    }
    
    console.log('Creating new map instance');
    const defaultCenter = [DEFAULT_LATITUDE, DEFAULT_LONGITUDE];
    const defaultZoom = 14;
    
    map.value = L.value.map(mapElement.value, {
      attributionControl: false,
      zoomControl: true,
      minZoom: 2,
      closePopupOnClick: true, // Close popups when clicking elsewhere on map
      scrollWheelZoom: true,   // Allow zoom with mouse wheel
      zoomAnimation: true      // Animate zoom transitions
    }).setView(defaultCenter, defaultZoom);
    
    L.value.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map.value);
    
    setTimeout(() => {
      if (map.value) {
        console.log('Invalidating map size');
        map.value.invalidateSize();
        updateMapMarkers();
      }
    }, 300);
    
    console.log('Map initialized successfully');
  } catch (error) {
    console.error('Error initializing map:', error);
  }
};

const updateMapMarkers = () => {
  if (!map.value || !L.value) {
    return;
  }
  try {
    // Close any open popups before removing markers
    if (map.value) {
      map.value.closePopup();
    }
    
    markers.value.forEach(marker => marker.remove());
    markers.value = [];
    const locations = Object.values(schedulesByLocation.value);
    if (locations.length === 0) {
      return;
    }
    const bounds = L.value.latLngBounds();
    locations.forEach(location => {
      const icon = L.value.divIcon({
        className: 'custom-map-marker',
        html: `<div class="${location.is_default ? 'default-marker' : 'normal-marker'}">
                 <span class="marker-label">${location.name.substr(0, 3)}</span>
               </div>`,
        iconSize: [40, 40],
        iconAnchor: [20, 40]
      });
      const lat = parseFloat(location.latitude);
      const lng = parseFloat(location.longitude);
      if (isNaN(lat) || isNaN(lng)) {
        return;
      }
      const popupContent = `
          <div class="map-popup">
              <h4 class="font-bold">${location.name}</h4>
              <p class="text-sm">${location.description || 'No description'}</p>
              <p class="text-xs mt-2">${location.schedules.length} available days</p>
              ${location.use_default_coordinates 
                  ? '<p class="text-xs text-amber-500">Using approximate location</p>' 
                  : ''}
              <div class="select-location-container" data-location-id="${location.id}">
                <button class="select-location-btn"
                  style="background: #4F46E5; color: white; padding: 6px 12px; 
                  border-radius: 4px; margin-top: 8px; font-size: 13px; 
                  width: 100%; text-align: center; cursor: pointer;">
                  Select Location
                </button>
              </div>
          </div>`;
      const marker = L.value.marker([lat, lng], { icon })
          .addTo(map.value);
          
      // Create popup but don't bind it initially
      const popup = L.value.popup({
        autoPan: true,
        closeButton: true,
      }).setContent(popupContent);
      
      // Add a click handler to open popup
      marker.on('click', () => {
        // Close any other open popups first
        map.value.closePopup();
        // Now open this popup
        marker.bindPopup(popup).openPopup();
      });
      
      marker.on('popupopen', () => {
        setTimeout(() => {
          const container = document.querySelector(`.select-location-container[data-location-id="${location.id}"]`);
          if (container) {
            const btn = container.querySelector('.select-location-btn');
            if (btn) {
              const newBtn = btn.cloneNode(true);
              if (btn.parentNode) {
                btn.parentNode.replaceChild(newBtn, btn);
              }
              newBtn.addEventListener('click', () => {
                map.value.closePopup(); // Close popup before handling location
                const locationData = {
                  id: location.id,
                  name: location.name,
                  description: location.description,
                  schedules: location.schedules,
                  latitude: location.latitude, 
                  longitude: location.longitude,
                  is_default: location.is_default
                };
                selectLocationFromMap(locationData);
              });
            }
          }
        }, 50);
      });
      
      // Explicitly handle popup close to clean up
      marker.on('popupclose', () => {
        marker.unbindPopup();
      });
      
      markers.value.push(marker);
      bounds.extend([lat, lng]);
    });
    if (markers.value.length > 0) {
      map.value.fitBounds(bounds, {
        padding: [50, 50],
        maxZoom: 16
      });
      if (form.meetup_location_id) {
        highlightSelectedLocation(form.meetup_location_id);
      }
    }
  } catch (error) {
    console.error('Error updating map markers:', error);
  }
};

watch(() => showMap.value, (isVisible) => {
  if (isVisible && isMapReady.value) {
    nextTick(() => {
      setTimeout(() => {
        if (mapElement.value) {
          if (!map.value) {
            initMap();
          } else {
            map.value.invalidateSize();
            updateMapMarkers();
            
            if (form.meetup_location_id) {
              highlightSelectedLocation(form.meetup_location_id);
            }
          }
        }
      }, 300);
    });
  }
});
</script>

<template>
    <Dialog 
        :open="open" 
        @update:open="handleDialogClose"
        modal
    >
        <DialogContent class="sm:max-w-3xl overflow-y-auto max-h-[90vh] p-4 sm:p-6">
            <DialogHeader>
                <DialogTitle>Trade for {{ capitalizeFirst(product.name) }}</DialogTitle>
                <DialogDescription>
                    Offer your items in exchange for this product. The seller will review your offer.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitTradeOffer" class="space-y-6">
                <div class="flex items-center gap-4 p-3 sm:p-4 bg-muted rounded-lg">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 overflow-hidden rounded-md shrink-0 border border-border">
                        <img 
                            :src="productImageUrl" 
                            :alt="product.name" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div>
                        <h3 class="font-medium text-base sm:text-lg">{{ capitalizeFirst(product.name) }}</h3>
                        <p class="text-muted-foreground text-sm sm:text-base">
                          <span v-if="product.discounted_price && product.discounted_price < product.price">
                            <span class="line-through text-muted-foreground mr-1">₱{{ formatPrice(product.price) }}</span>
                            <span class="text-primary">₱{{ formatPrice(product.discounted_price) }}</span>
                          </span>
                          <span v-else class="text-primary">₱{{ formatPrice(product.price) }}</span>
                        </p>
                    </div>
                </div>

                <div class="border-t border-border py-3 sm:py-4">
                    <h4 class="font-medium mb-2">Seller Information</h4>
                    <div v-if="props.product?.seller" class="flex items-start gap-3">
                        <img :src="sellerProfilePicture" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover flex-shrink-0 border border-border">
                        <div class="space-y-1">
                            <p class="font-medium">{{ sellerName }}</p>
                            <div class="text-xs sm:text-sm text-muted-foreground space-y-0.5">
                                <p class="flex items-center gap-2">
                                    <span class="font-medium">@{{ props.product.seller.username }}</span>
                                </p>
                                <p v-if="props.product.seller.phone" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    {{ props.product.seller.phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-muted-foreground">
                        Seller information not available
                    </div>
                </div>

                <div class="mb-4 sm:mb-6">
                    <h3 class="font-medium mb-3 sm:mb-4">Step 1: Select Meetup Location</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground mb-2">Select a location where you'd like to meet the seller.</p>
                    
                    <div v-if="isLoadingMeetupLocations" class="flex justify-center items-center h-[250px] sm:h-[300px] border border-border rounded-md">
                        <div class="text-muted-foreground">Loading available meetup locations...</div>
                    </div>
                    <div v-else-if="Object.keys(schedulesByLocation).length === 0" class="flex justify-center items-center h-[250px] sm:h-[300px] border border-border rounded-md">
                        <div class="text-amber-600">No meetup locations available. Contact the seller for arrangements.</div>
                    </div>
                    <div v-else>
                        <div v-if="form.meetup_location_id" class="mb-4 border border-border p-3 sm:p-4 rounded-lg bg-accent/5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-base sm:text-lg">
                                        {{ schedulesByLocation[form.meetup_location_id]?.name || 'Selected Location' }}
                                    </h4>
                                    <p v-if="schedulesByLocation[form.meetup_location_id]?.description" class="text-xs sm:text-sm text-muted-foreground mt-1">
                                        {{ schedulesByLocation[form.meetup_location_id].description }}
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        type="button"
                                        @click="toggleMapView"
                                        class="text-xs px-2 py-1 h-auto"
                                    >
                                        {{ showMap ? 'Hide Map' : 'Show Map' }}
                                    </Button>
                                    <Button 
                                        variant="destructive" 
                                        size="sm" 
                                        type="button"
                                        @click="resetLocationSelection"
                                        class="text-xs px-2 py-1 h-auto"
                                    >
                                        Change Location
                                    </Button>
                                </div>
                            </div>
                        </div>
                        
                        <div 
                            v-show="showMap || !form.meetup_location_id" 
                            ref="mapElement" 
                            id="trade-map" 
                            class="w-full rounded-md border border-border h-[250px] sm:h-[300px] transition-all duration-300"
                        ></div>
                        
                        <div v-if="(showMap || !form.meetup_location_id) && 
                              Object.values(schedulesByLocation).some(loc => loc.use_default_coordinates)" 
                            class="text-xs text-amber-600 mt-1 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            Some locations use approximate campus coordinates
                        </div>
                        
                        <div v-if="form.meetup_location_id" class="mt-4">
                            <h4 class="font-medium text-sm sm:text-base mb-2">
                              Step 2: Select an available day:
                            </h4>
                            <div class="space-y-2">
                                <RadioGroup v-model="selectedScheduleId">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                        <div v-for="schedule in schedulesByLocation[form.meetup_location_id]?.schedules" 
                                            :key="schedule.id"
                                            class="relative">
                                            <RadioGroupItem 
                                                :value="schedule.id"
                                                :id="`schedule-${schedule.id}`"
                                                class="peer sr-only" 
                                            />
                                            <Label 
                                                :for="`schedule-${schedule.id}`"
                                                class="flex p-3 items-center gap-2 border border-border rounded-md cursor-pointer
                                                      hover:bg-accent/5 peer-focus:ring-2 peer-focus:ring-primary 
                                                      peer-data-[state=checked]:border-primary peer-data-[state=checked]:bg-accent/10"
                                            >
                                                <div class="flex-1">
                                                    <div class="text-foreground font-medium">
                                                        {{ capitalizeFirst(schedule.day) }}
                                                    </div>
                                                    <div class="text-muted-foreground text-xs">
                                                        {{ schedule.timeFrom }} - {{ schedule.timeUntil }}
                                                    </div>
                                                </div>
                                            </Label>
                                        </div>
                                    </div>
                                </RadioGroup>
                            </div>
                        </div>
                    </div>
                    
                    <p v-if="errors.meetup_schedule" class="text-destructive text-xs sm:text-sm mt-2">{{ errors.meetup_schedule }}</p>
                </div>

                <div class="mb-4 sm:mb-6">
                    <h3 class="font-medium mb-2">Step 3: Select Meetup Date</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground mb-2">
                        {{ selectedScheduleId 
                            ? `Choose a ${selectedScheduleDay} date for your meetup.`
                            : 'Please select a meetup schedule first to enable date selection.' }}
                    </p>
                    <div class="relative">
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger as="div" class="w-full">
                                  <MeetupDate
                                    :model-value="form.meetup_date"
                                    :selected-day="selectedScheduleDay"
                                    :is-date-disabled="!selectedScheduleId"
                                    @update:model-value="handleDateSelection"
                                    :class="[
                                        {'opacity-50': !selectedScheduleId},
                                        {'pointer-events-none': !selectedScheduleId},
                                        {'border-destructive': errors.meetup_date && form.meetup_date === ''}
                                    ]"
                                />
                                </TooltipTrigger>
                                <TooltipContent v-if="!selectedScheduleId" class="bg-popover text-popover-foreground p-2 rounded shadow-lg max-w-xs">
                                    You need to select a meetup schedule first to enable date selection
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                    <p v-if="errors.meetup_date && form.meetup_date !== ''" class="text-destructive text-xs sm:text-sm mt-2">{{ errors.meetup_date }}</p>
                </div>

                <div class="mb-4 sm:mb-6">
                    <h3 class="font-medium mb-2">Step 4: Select Preferred Time</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground mb-2">
                        {{ selectedSchedule 
                            ? `Choose a time between ${selectedSchedule.timeFrom} and ${selectedSchedule.timeUntil}.`
                            : 'Please select a meetup schedule and date first.' }}
                    </p>
                    
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as="div" class="w-full">
                                <div class="relative w-full">
                                    <Select 
                                        :model-value="form.preferred_time" 
                                        @update:model-value="setPreferredTime"
                                        :disabled="!form.meetup_date || !selectedSchedule"
                                    >
                                        <SelectTrigger 
                                            class="w-full"
                                            :class="{'opacity-50 cursor-not-allowed': !form.meetup_date || !selectedSchedule}"
                                        >
                                            <SelectValue>
                                                <template v-if="form.preferred_time">
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <polyline points="12 6 12 12 16 14"></polyline>
                                                        </svg>
                                                        {{ availableTimeSlots.find(slot => slot.value === form.preferred_time)?.display || form.preferred_time }}
                                                    </div>
                                                </template>
                                                <template v-else>Select a time</template>
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent position="popper">
                                            <div class="max-h-[200px] overflow-y-auto p-1">
                                                <SelectItem value="placeholder" disabled>Select a time</SelectItem>
                                                <SelectItem 
                                                    v-for="slot in availableTimeSlots" 
                                                    :key="slot.value" 
                                                    :value="slot.value"
                                                >
                                                    {{ slot.display }}
                                                </SelectItem>
                                            </div>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </TooltipTrigger>
                            <TooltipContent v-if="!form.meetup_date || !selectedSchedule" class="bg-popover text-popover-foreground p-2 rounded shadow-lg max-w-xs z-50">
                                {{ !selectedScheduleId 
                                    ? 'You need to select a meetup schedule first' 
                                    : 'You need to select a meetup date before choosing a time' }}
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                    
                    <div v-if="form.meetup_date && selectedSchedule && !form.preferred_time" class="mt-3 flex flex-wrap gap-2">
                        <p class="text-xs text-muted-foreground w-full mb-1">Quick select:</p>
                        <Button 
                            v-for="slot in availableTimeSlots.slice(0, 4)" 
                            :key="slot.value"
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="setPreferredTime(slot.value)"
                            :class="[
                                'text-xs sm:text-sm h-8',
                                form.preferred_time === slot.value ? 'bg-primary text-primary-foreground' : ''
                            ]"
                        >
                            {{ slot.display }}
                        </Button>
                        <span v-if="availableTimeSlots.length > 4" class="text-xs text-muted-foreground self-center">
                            +{{ availableTimeSlots.length - 4 }} more options
                        </span>
                    </div>

                    <p v-if="errors.preferred_time" class="text-destructive text-xs sm:text-sm mt-2">{{ errors.preferred_time }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="additional_cash" class="text-sm">Additional Cash (₱) <span class="text-muted-foreground text-xs">(Optional)</span></Label>
                    <Input 
                        id="additional_cash"
                        type="number"
                        v-model="form.additional_cash"
                        min="0"
                        step="0.01"
                    />
                    <p v-if="errors.additional_cash" class="text-destructive text-xs sm:text-sm">{{ errors.additional_cash }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="notes" class="text-sm">Notes for Seller <span class="text-muted-foreground text-xs">(Optional)</span></Label>
                    <Textarea 
                        id="notes"
                        v-model="form.notes"
                        placeholder="Add any details about your trade offer here..."
                    />
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-medium text-base sm:text-lg">Items to Offer</h3>
                        <Button type="button" variant="outline" size="sm" @click="addOfferedItem">
                            Add Another Item
                        </Button>
                    </div>
                    <div v-for="(item, index) in form.offered_items" :key="index" class="p-3 sm:p-4 border border-border rounded-lg space-y-3 sm:space-y-4">
                        <div class="flex justify-between">
                            <h4 class="font-medium text-sm sm:text-base">Item {{ index + 1 }}</h4>
                            <Button 
                                v-if="form.offered_items.length > 1" 
                                type="button"
                                variant="ghost" 
                                size="sm" 
                                @click="removeOfferedItem(index)"
                                class="h-8 text-xs"
                            >
                                Remove
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div class="space-y-1 sm:space-y-2">
                                <Label :for="`item_name_${index}`" class="text-sm">Item Name*</Label>
                                <Input 
                                    :id="`item_name_${index}`"
                                    v-model="item.name"
                                />
                                <p v-if="errors[`item_${index}_name`]" class="text-destructive text-xs">
                                    {{ errors[`item_${index}_name`] }}
                                </p>
                            </div>
                            <div class="space-y-1 sm:space-y-2">
                                <Label :for="`item_quantity_${index}`" class="text-sm">Quantity*</Label>
                                <Input 
                                    :id="`item_quantity_${index}`" 
                                    type="number" 
                                    v-model="item.quantity"
                                    min="1" 
                                />
                                <p v-if="errors[`item_${index}_quantity`]" class="text-destructive text-xs">
                                    {{ errors[`item_${index}_quantity`] }}
                                </p>
                            </div>
                            <div class="space-y-1 sm:space-y-2">
                                <Label :for="`item_value_${index}`" class="text-sm">Estimated Value (₱)*</Label>
                                <Input 
                                    :id="`item_value_${index}`" 
                                    type="number" 
                                    v-model="item.estimated_value"
                                    min="0" 
                                    step="0.01" 
                                />
                                <p v-if="errors['item_' + index + '_value']" class="text-destructive text-xs">
                                    {{ errors['item_' + index + '_value'] }}
                                </p>
                            </div>
                            <div class="space-y-1 sm:space-y-2">
                                <Label :for="`item_condition_${index}`" class="text-sm">Condition*</Label>
                                <Select 
                                    v-model="item.condition"
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select condition" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="option in conditionOptions" 
                                            :key="option.value" 
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors[`item_${index}_condition`]" class="text-destructive text-xs">
                                    {{ errors[`item_${index}_condition`] }}
                                </p>
                            </div>
                            <div class="space-y-1 sm:space-y-2">
                                <Label :for="`item_description_${index}`" class="text-sm">Description <span class="text-muted-foreground text-xs">(Optional)</span></Label>
                                <Textarea 
                                    :id="`item_description_${index}`" 
                                    v-model="item.description"
                                    placeholder="Describe the item..."
                                    class="text-sm"
                                />
                            </div>
                            <div class="space-y-1 sm:space-y-2 sm:col-span-2">
                                <Label class="text-sm">Upload Images*</Label>
                                <div class="border border-border rounded-md p-2 bg-background">
                                    <input 
                                        type="file"
                                        :id="`file-upload-${index}`"
                                        @change="(e) => handleFileUpload(e.target.files, index)"
                                        multiple
                                        accept="image/*"
                                        class="block w-full text-xs sm:text-sm text-muted-foreground
                                            file:mr-2 sm:file:mr-4 file:py-1 file:px-2 sm:file:py-2 sm:file:px-4
                                            file:rounded-md file:border-0
                                            file:text-xs sm:file:text-sm file:font-semibold
                                            file:bg-secondary file:text-secondary-foreground
                                            hover:file:bg-secondary/80"
                                    />
                                </div>

                                <p v-if="errors[`item_${index}_images`]" class="text-destructive text-xs">
                                    {{ errors[`item_${index}_images`] }}
                                </p>
                                                            
                                <div v-if="item.images && item.images.length > 0" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                    <div v-for="(image, imgIndex) in item.images" :key="`img-${index}-${imgIndex}`" 
                                        class="relative group border border-border rounded-md overflow-hidden h-16 sm:h-20">
                                        <img 
                                            :src="getImagePreviewUrl(image)" 
                                            class="w-full h-full object-cover"
                                            @error="handleImageError"
                                        />
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                            <button 
                                                type="button" 
                                                class="bg-white/70 p-1 rounded-full hover:bg-white"
                                                @click.prevent="removeImage(index, imgIndex)"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-destructive" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="flex justify-between pt-2 sm:pt-4">
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="closeDialog"
                        class="text-xs sm:text-sm"
                    >
                        Cancel
                    </Button>
                    <Button 
                        type="submit" 
                        :disabled="loading"
                        class="text-xs sm:text-sm"
                    >
                        {{ loading ? 'Submitting...' : 'Submit Trade Offer' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
:deep(.custom-map-marker) {
  background: transparent;
  border: none;
}

:deep(.default-marker) {
  width: 28px;
  height: 28px;
  background-color: hsl(var(--primary));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  border: 2px solid white;
  box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

:deep(.normal-marker) {
  width: 24px;
  height: 24px;
  background-color: hsl(var(--muted-foreground));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  border: 2px solid white;
  box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

:deep(.marker-label) {
  font-size: 10px;
  white-space: nowrap;
  text-transform: uppercase;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 8px;
  padding: 0;
  border: 1px solid hsl(var(--border));
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

:deep(.map-popup) {
  padding: 12px;
  min-width: 150px;
}

:deep(.leaflet-popup-content) {
  margin: 0;
}

:deep(.select-location-btn) {
  background-color: hsl(var(--primary));
  color: hsl(var(--primary-foreground));
  padding: 4px 8px;
  border-radius: 4px;
  margin-top: 8px;
  font-size: 12px;
  transition: all 0.2s ease;
  border: none;
}

:deep(.select-location-btn:hover) {
  opacity: 0.9;
  transform: scale(1.05);
  cursor: pointer;
}

#trade-map {
  position: relative;
  z-index: 1;
  min-height: 250px !important;
  min-width: 100%;
  transition: height 0.3s ease-in-out, opacity 0.3s ease-in-out;
  background-color: hsl(var(--muted));
}

#trade-map[style*="display: block"] {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0.6; }
  to { opacity: 1; }
}

:deep(.default-marker),
:deep(.normal-marker) {
  box-shadow: 0 3px 14px rgba(0,0,0,0.4);
  border: 3px solid white;
  text-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

:deep(.leaflet-container) {
  background-color: hsl(var(--accent));
  z-index: 1;
}

:deep(.checkbox) {
  border-color: hsl(var(--border));
}

:deep(.checkbox:hover) {
  border-color: hsl(var(--primary));
}

:deep(.border-destructive) {
  border-color: hsl(var(--destructive)) !important;
  box-shadow: 0 0 0 1px hsl(var(--destructive)) !important;
}

:deep(.meetup-date:not(.pointer-events-none)) {
  pointer-events: auto !important;
  cursor: pointer !important;
}
</style>
