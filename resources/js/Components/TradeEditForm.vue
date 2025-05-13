<script setup>
import { ref, reactive, computed, onMounted, watch, onBeforeUnmount, nextTick, inject } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
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
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import TradeSummaryDialog from './TradeSummaryDialog.vue';
import { ImageUploader } from "@/Components/ui/image-uploader";
import { ImagePreview } from "@/Components/ui/image-preview";

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
    tradeId: {
        type: [Number, String],
        required: true
    },
    product: {
        type: Object,
        required: true
    },
    open: {
        type: Boolean,
        required: true
    },
    getOptimizedImageUrl: {
        type: Function,
        default: (url) => {
            if (!url) return '/images/placeholder-product.jpg';
            if (url.startsWith('http://') || url.startsWith('https://')) {
                return url;
            }
            if (url.includes('storage/storage/')) {
                url = url.replace('storage/storage/', 'storage/');
            }
            if (url.startsWith('/storage/')) {
                return url;
            } else if (url.startsWith('storage/')) {
                return '/' + url;
            } else {
                return '/storage/' + url;
            }
        }
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
const showSummaryDialog = ref(false);

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
            current_images: [],
            condition: 'new'
        }
    ]
});

const dialogTitle = computed(() => {
    return `Edit Trade Offer for ${props.product?.name || 'Product'}`;
});

const handleDialogClose = (isOpen) => {
    if (!isOpen) {
        closeDialog();
    }
};

const croppedImages = ref([]);

watch(() => form.offered_items.length, (newLength, oldLength) => {
  if (newLength > croppedImages.value.length) {
    for (let i = croppedImages.value.length; i < newLength; i++) {
      croppedImages.value.push([]);
    }
  }
});

const handleCroppedImage = (croppedImageData, itemIndex) => {
  if (!croppedImageData) return;
  
  const byteString = atob(croppedImageData.split(',')[1]);
  const ab = new ArrayBuffer(byteString.length);
  const ia = new Uint8Array(ab);
  for (let i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }
  
  const blob = new Blob([ab], { type: 'image/jpeg' });
  const file = new File([blob], `item-${itemIndex}-image-${Date.now()}.jpg`, { type: 'image/jpeg' });
  
  if (!form.offered_items[itemIndex].images) {
    form.offered_items[itemIndex].images = [];
  }
  form.offered_items[itemIndex].images.push(file);
  
  clearItemFieldError(itemIndex, 'images');
};

const handleImageUploaderError = (itemIndex, errorMessage) => {
  const errorString = Array.isArray(errorMessage) ? errorMessage[0] : errorMessage;
  errors.value[`item_${itemIndex}_images`] = errorString;
};

const selectedScheduleId = ref('');

const conditionOptions = [
    { value: 'new', label: 'New' },
    { value: 'used_like_new', label: 'Used - Like New' },
    { value: 'used_good', label: 'Used - Good' },
    { value: 'used_fair', label: 'Used - Fair' }
];

const productImageUrl = computed(() => {
  if (!props.product) return '/images/placeholder-product.jpg';
  if (props.product.images && props.product.images.length > 0) {
    const image = props.product.images[0];
    if (typeof image === 'string') {
      return ensureValidImagePath(image);
    } else if (image && typeof image === 'object' && image.url) {
      return ensureValidImagePath(image.url);
    }
  }
  return '/images/placeholder-product.jpg';
});

const ensureValidImagePath = (path) => {
  if (!path) return '/images/placeholder-product.jpg';
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  if (path.includes('storage/storage/')) {
    path = path.replace('storage/storage/', 'storage/');
  }
  if (path.startsWith('/storage/')) {
    return path;
  } else if (path.startsWith('storage/')) {
    return '/' + path;
  } else {
    return '/storage/' + path;
  }
};

const sellerName = computed(() => {
  if (!props.product || !props.product.seller) return 'Unknown Seller';
  return props.product.seller.first_name && props.product.seller.last_name ? 
    `${props.product.seller.first_name} ${props.product.seller.last_name}` : 
    props.product.seller.name || 'Unknown Seller';
});

const sellerProfilePicture = computed(() => {
  if (!props.product || !props.product.seller) return '/images/placeholder-avatar.jpg';
  if (props.product.seller.profile_picture) {
    return ensureValidImagePath(props.product.seller.profile_picture);
  }
  return '/images/placeholder-avatar.jpg';
});

const totalOfferedValue = computed(() => {
  const itemsValue = form.offered_items.reduce((sum, item) => {
    const value = parseFloat(item.estimated_value) || 0;
    const quantity = parseInt(item.quantity) || 1;
    return sum + (value * quantity);
  }, 0);
  const cash = parseFloat(form.additional_cash) || 0;
  return itemsValue + cash;
});

const productEffectivePrice = computed(() => {
  if (!props.product) return 0;
  const price = parseFloat(props.product.price) || 0;
  const discountedPrice = parseFloat(props.product.discounted_price) || 0;
  return (discountedPrice && discountedPrice < price) 
    ? discountedPrice 
    : price;
});

const showValueValidation = ref(false);

const isValueSufficient = computed(() => {
  if (!showValueValidation.value) return true;
  return totalOfferedValue.value >= productEffectivePrice.value;
});

const totalValueErrorMessage = computed(() => {
  if (isValueSufficient.value) return '';
  return `The total estimated value of your offer (₱${formatPrice(totalOfferedValue.value)}) must be at least the product's value (₱${formatPrice(productEffectivePrice.value)})`;
});

watch(() => form.offered_items, () => {
  const hasValues = form.offered_items.some(item => 
    item.estimated_value > 0 || item.quantity > 1 || item.name || item.description
  ) || form.additional_cash > 0;
  
  if (hasValues) {
    showValueValidation.value = true;
  }
}, { deep: true });

watch(() => form.additional_cash, (newValue) => {
  if (newValue > 0) {
    showValueValidation.value = true;
  }
});

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
          location: location.name || location.custom_location || 'Location Not Available',
          location_id: location.location_id || null,
          day: day,
          timeFrom: formatTime(location.available_from || '09:00'),
          timeUntil: formatTime(location.available_until || '17:00'),
          description: location.description || '',
          maxMeetups: location.max_daily_meetups || 5,
          latitude: location.latitude || DEFAULT_LATITUDE,
          longitude: location.longitude || DEFAULT_LONGITUDE,
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
  const fromTime = selectedSchedule.value.timeFrom || '09:00';
  const untilTime = selectedSchedule.value.timeUntil || '17:00';
  
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
  if (endPeriod === 'am' && endHour === 12) endHour = 0;
  
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

const loadTradeDetails = async () => {
  try {
    console.log('Loading trade details for trade ID:', props.tradeId);
    loading.value = true;
    
    const response = await axios.get(`/trade/${props.tradeId}/edit-details`);
    console.log('Raw API response for trade details:', response.data);
    
    if (response.data && response.data.success) {
      const tradeData = response.data.trade;
      console.log('Trade data loaded successfully:', tradeData);
      
      console.log('Product data in response:', tradeData.product);
      
      meetupLocations.value = tradeData.meetup_locations || [];
      console.log('Meetup locations:', meetupLocations.value);
      
      form.seller_product_id = tradeData.seller_product_id;
      form.meetup_location_id = tradeData.meetup_location_id;
      form.meetup_date = tradeData.meetup_date || '';
      form.preferred_time = tradeData.preferred_time || '';
      form.additional_cash = tradeData.additional_cash || 0;
      form.notes = tradeData.notes || '';
      
      if (tradeData.offered_items && tradeData.offered_items.length > 0) {
        form.offered_items = tradeData.offered_items.map(item => {
          const processedImages = processItemImages(item.images || []);
          
          return {
            id: item.id,
            name: item.name || '',
            quantity: item.quantity || 1,
            estimated_value: item.estimated_value || 0,
            description: '',
            condition: item.condition || 'used_good',
            current_images: processedImages,
            images: []
          };
        });
        
        console.log('Formatted offered items with processed images:', form.offered_items);
      } else {
        form.offered_items = [
          {
            name: '',
            quantity: 1,
            estimated_value: 0,
            description: '', 
            condition: 'new',
            current_images: [],
            images: []
          }
        ];
      }
      
      if (form.meetup_location_id && tradeData.meetup_day) {
        selectedScheduleId.value = `${form.meetup_location_id}_${tradeData.meetup_day}`;
        
        nextTick(() => {
          const schedule = availableSchedules.value.find(s => s.id === selectedScheduleId.value);
          if (schedule) {
            selectedSchedule.value = schedule;
            console.log('Selected schedule set:', selectedSchedule.value);
          }
        });
      }
    } else {
      throw new Error(response.data?.message || 'Failed to load trade details');
    }
  } catch (error) {
    console.error('Error loading trade details:', error);
    toast({
      title: 'Error',
      description: error.message || 'Failed to load trade details',
      variant: 'destructive'
    });
  } finally {
    loading.value = false;
  }
};

const processItemImages = (images) => {
  const processedImages = [];
  
  if (!images || (Array.isArray(images) && images.length === 0)) {
    return processedImages;
  }
  
  try {
    if (typeof images === 'string' && (images.startsWith('[') || images.startsWith('{'))) {
      try {
        const parsed = JSON.parse(images);
        if (Array.isArray(parsed)) {
          return parsed.map(img => ensureValidImagePath(img)).filter(Boolean);
        } else if (parsed) {
          return [ensureValidImagePath(parsed)];
        }
      } catch (e) {
        console.error('Failed to parse JSON images:', e);
        if (images) {
          return [ensureValidImagePath(images)];
        }
      }
    } else if (typeof images === 'string' && images) {
      return [ensureValidImagePath(images)];
    } else if (Array.isArray(images)) {
      return images.map(img => ensureValidImagePath(img)).filter(Boolean);
    }
  } catch (error) {
    console.error('Error processing item images:', error);
  }
  
  return processedImages;
};

watch(
    [() => props.open, () => props.tradeId], 
    ([isOpen, tradeId]) => {
      if (isOpen && tradeId) {
        loadTradeDetails();
      }
    },
    { immediate: true }
);

watch(() => selectedScheduleId.value, (newSchedule) => {
  if (newSchedule) {
    const schedule = availableSchedules.value.find(s => String(s.id) === String(newSchedule));
    selectedSchedule.value = schedule || null;
    
    if (schedule) {
      const [locationId] = String(schedule.id).split('_');
      
      if (String(form.meetup_location_id) !== String(locationId)) {
        form.meetup_location_id = locationId;
      }
    }
  } else {
    selectedSchedule.value = null;
  }
}, { immediate: true });

const schedulesByLocation = computed(() => {
  const grouped = {};
  
  if (!meetupLocations.value || !Array.isArray(meetupLocations.value)) {
    return {};
  }
  
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
        timeFrom: formatTime(location.available_from || '09:00'),
        timeUntil: formatTime(location.available_until || '17:00'),
      });
    });
    
    if (schedules.length > 0) {
      const locationName = location.name || location.custom_location || 'Unknown Location';
      
      grouped[location.id] = {
        id: location.id,
        name: locationName,
        latitude: location.latitude || DEFAULT_LATITUDE,
        longitude: location.longitude || DEFAULT_LONGITUDE,
        description: location.description || '',
        is_default: location.is_default || false,
        schedules: schedules,
        use_default_coordinates: !location.latitude || !location.longitude
      };
    }
  });
  
  return grouped;
});

const formatTime = (time) => {
  if (!time) return '';
  
  try {
    let hours, minutes;
    if (time.includes(':')) {
      [hours, minutes] = time.split(':');
    } else {
      return time;
    }
    
    const hourNum = parseInt(hours);
    if (isNaN(hourNum)) return time;
    
    const suffix = hourNum >= 12 ? 'PM' : 'AM';
    const hour12 = hourNum % 12 || 12;
    
    return `${hour12}:${minutes} ${suffix}`;
  } catch (e) {
    console.error('Error formatting time:', e);
    return time;
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price);
};

const capitalizeFirst = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
};

const clearItemFieldError = (index, fieldName) => {
  const errorKey = `offered_items.${index}.${fieldName}`;
  if (errors.value && errors.value[errorKey]) {
    delete errors.value[errorKey];
  }
  const oldErrorKey = `item_${index}_${fieldName}`;
  if (errors.value && errors.value[oldErrorKey]) {
    delete errors.value[oldErrorKey];
  }
};

const closeDialog = () => {
  form.reset();
  errors.value = {};
  selectedSchedule.value = null;
  showValueValidation.value = false;
  emit('close');
  emit('update:open', false);
};

const submitTradeOffer = () => {
  showValueValidation.value = true;
  
  errors.value = {};
  
  showSummaryDialog.value = true;
};

const confirmAndSubmit = () => {
  loading.value = true;
  
  const formData = new FormData();
  
  const meetupDay = selectedScheduleDay.value;
  
  const meetupSchedule = `${form.meetup_date}, ${form.preferred_time}`;
  
  formData.append('meetup_schedule', meetupSchedule);
  formData.append('selected_day', meetupDay);
  formData.append('seller_product_id', form.seller_product_id);
  formData.append('meetup_location_id', form.meetup_location_id);
  formData.append('preferred_time', form.preferred_time);
  formData.append('additional_cash', form.additional_cash || 0);
  formData.append('notes', form.notes || '');
  
  form.offered_items.forEach((item, index) => {
    formData.append(`offered_items[${index}][name]`, item.name);
    formData.append(`offered_items[${index}][quantity]`, item.quantity);
    formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
    formData.append(`offered_items[${index}][description]`, item.description || '');
    formData.append(`offered_items[${index}][condition]`, item.condition);
    
    if (item.id) {
      formData.append(`offered_items[${index}][id]`, item.id);
    }
    
    if (item.current_images && item.current_images.length > 0) {
      formData.append(`offered_items[${index}][current_images]`, JSON.stringify(item.current_images));
    }
    
    if (item.images && item.images.length > 0) {
      const imageInfoArray = [];
      
      item.images.forEach((image, imageIndex) => {
        if (image instanceof File) {
          const uniqueFilename = `offered_item_${index}_image_${imageIndex}_${Date.now()}_${image.name}`;
          formData.append(`offered_items[${index}][image_files][]`, image);
          
          imageInfoArray.push({
            original_name: image.name,
            size: image.size,
            type: image.type,
            unique_key: uniqueFilename
          });
        }
      });
      
      if (imageInfoArray.length > 0) {
        formData.append(`offered_items[${index}][images_json]`, JSON.stringify(imageInfoArray));
      }
    }
  });
  
  router.post(route('trades.update', props.tradeId), formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Trade offer updated successfully!",
        variant: "success"
      });
      showSummaryDialog.value = false;
      closeDialog();
    },
    onError: (errors) => {
      showSummaryDialog.value = false;
      errors.value = errors;
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

const cancelSummary = () => {
  showSummaryDialog.value = false;
};

const setPreferredTime = (timeValue) => {
  form.preferred_time = timeValue;
  clearFieldError('preferred_time');
};

const clearFieldError = (fieldName) => {
  if (errors.value && errors.value[fieldName]) {
    delete errors.value[fieldName];
  }
};

const addOfferedItem = () => {
  form.offered_items.push({
    name: '',
    quantity: 1,
    estimated_value: 0,
    description: '',
    condition: 'new',
    current_images: [],
    images: []
  });
};

const removeOfferedItem = (index) => {
  if (form.offered_items.length > 1) {
    form.offered_items.splice(index, 1);
  }
};

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
  }
};

const toggleMapView = () => {
  showMap.value = !showMap.value;
};

const resetLocationSelection = () => {
  form.meetup_location_id = '';
  selectedScheduleId.value = '';
  form.meetup_date = '';
  form.preferred_time = '';
  selectedSchedule.value = null;
  
  showMap.value = true;
};

const removeExistingImage = (itemIndex, imageIndex) => {
  if (form.offered_items[itemIndex] && 
      form.offered_items[itemIndex].current_images && 
      form.offered_items[itemIndex].current_images.length > imageIndex) {
    const newImages = [...form.offered_items[itemIndex].current_images];
    newImages.splice(imageIndex, 1);
    form.offered_items[itemIndex].current_images = newImages;
  }
};
</script>

<template>
  <Dialog 
    :open="open && !showSummaryDialog" 
    @update:open="handleDialogClose"
    modal
  >
    <DialogContent class="mx-4 w-[75%] sm:max-w-20xl overflow-y-auto max-h-[90vh] p-6 md:p-8 lg:p-10 bg-background dark:bg-gray-900 border-border dark:border-gray-700">
      <DialogHeader>
        <DialogTitle class="text-foreground dark:text-white font-medium text-xl">{{ dialogTitle }}</DialogTitle>
        <DialogDescription class="text-muted-foreground dark:text-gray-400">
          Update your trade offer details below.
        </DialogDescription>
      </DialogHeader>
      
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
      
      <form v-else @submit.prevent="submitTradeOffer" class="space-y-6">
        <!-- Product Info Card -->
        <div v-if="props.product" class="flex items-center gap-4 p-3 sm:p-4 bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700">
          <div class="w-16 h-16 sm:w-20 sm:h-20 overflow-hidden rounded-md shrink-0 border border-border dark:border-gray-700">
            <ImagePreview 
              :images="[productImageUrl]" 
              :alt="props.product.name || 'Product'"
              class="w-full h-full object-cover"
            />
          </div>
          <div>
            <h3 class="font-medium text-base sm:text-lg text-foreground dark:text-white">{{ props.product?.name }}</h3>
            <p class="text-muted-foreground dark:text-gray-400 text-sm sm:text-base">
              <span v-if="props.product.discounted_price && props.product.discounted_price < props.product.price">
                <span class="line-through text-muted-foreground mr-1">{{ formatPrice(props.product.price) }}</span>
                <span class="text-primary-color dark:text-primary-color">{{ formatPrice(props.product.discounted_price) }}</span>
              </span>
              <span v-else class="text-primary-color dark:text-primary-color">{{ formatPrice(props.product.price || 0) }}</span>
            </p>
          </div>
        </div>

        <!-- Seller Info Card -->
        <div v-if="props.product?.seller" class="border-t border-border dark:border-gray-700 py-3 sm:py-4">
          <h4 class="font-medium mb-2 text-foreground dark:text-white">Seller Information</h4>
          <div class="flex items-start gap-3">
            <img :src="sellerProfilePicture" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover flex-shrink-0 border border-border dark:border-gray-700">
            <div class="space-y-1">
              <p class="font-medium text-foreground dark:text-white">{{ sellerName }}</p>
              <div class="text-xs sm:text-sm text-muted-foreground dark:text-gray-400 space-y-0.5">
                <p class="flex items-center gap-2" v-if="props.product.seller.username">
                  <span class="font-medium">@{{ props.product.seller.username }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Meetup Location Selection -->
        <div class="mb-4 sm:mb-6" data-error="meetup_schedule">
          <h3 class="font-medium mb-3 sm:mb-4 text-foreground dark:text-white">Step 1: Select Meetup Location</h3>
          <p class="text-xs sm:text-sm text-muted-foreground dark:text-gray-400 mb-2">Select a location where you'd like to meet the seller.</p>
          
          <div v-if="isLoadingMeetupLocations" class="flex justify-center items-center h-[250px] sm:h-[300px] border border-border dark:border-gray-700 rounded-md bg-background dark:bg-gray-800">
            <div class="text-muted-foreground dark:text-gray-400">Loading available meetup locations...</div>
          </div>
          <div v-else-if="Object.keys(schedulesByLocation).length === 0" class="flex justify-center items-center h-[250px] sm:h-[300px] border border-border dark:border-gray-700 rounded-md bg-background dark:bg-gray-800">
            <div class="text-amber-600">No meetup locations available. Contact the seller for arrangements.</div>
          </div>
          <div v-else>
            <div v-if="form.meetup_location_id" class="mb-4 border border-border dark:border-gray-700 p-3 sm:p-4 rounded-lg bg-accent/5 dark:bg-gray-800/50">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="font-medium text-base sm:text-lg text-foreground dark:text-white">
                    {{ schedulesByLocation[form.meetup_location_id]?.name || 'Selected Location' }}
                  </h4>
                  <p v-if="schedulesByLocation[form.meetup_location_id]?.description" class="text-xs sm:text-sm text-muted-foreground dark:text-gray-400 mt-1">
                    {{ schedulesByLocation[form.meetup_location_id].description }}
                  </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                  <Button 
                    variant="outline" 
                    size="sm" 
                    type="button"
                    @click="toggleMapView"
                    class="text-xs px-2 py-1 h-auto bg-white dark:bg-gray-800 border-border dark:border-gray-700 dark:text-white"
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
              class="w-full rounded-md border border-border dark:border-gray-700 h-[250px] sm:h-[300px] transition-all duration-300"
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
              <h4 class="font-medium text-sm sm:text-base mb-2 text-foreground dark:text-white">
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
                        class="flex p-3 items-center gap-2 border border-border dark:border-gray-700 rounded-md cursor-pointer
                                hover:bg-accent/5 dark:hover:bg-gray-800/80 peer-focus:ring-2 peer-focus:ring-primary 
                                peer-data-[state=checked]:border-primary-color peer-data-[state=checked]:bg-accent/10
                                text-foreground dark:text-white bg-background dark:bg-gray-800"
                      >
                        <div class="flex-1">
                          <div class="text-foreground dark:text-white font-medium">
                            {{ capitalizeFirst(schedule.day) }}
                          </div>
                          <div class="text-muted-foreground dark:text-gray-400 text-xs">
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

        <!-- Date Selection -->
        <div class="mb-4 sm:mb-6" data-error="meetup_date">
          <h3 class="font-medium mb-2 text-foreground dark:text-white">Step 3: Select Meetup Date</h3>
          <p class="text-xs sm:text-sm text-muted-foreground dark:text-gray-400 mb-2">
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
                    :class="{
                        'opacity-50': !selectedScheduleId,
                        'pointer-events-none': !selectedScheduleId,
                        'border-destructive': errors.meetup_date && form.meetup_date === '',
                        'bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white': true
                    }"
                  />
                </TooltipTrigger>
                <TooltipContent v-if="!selectedScheduleId" class="bg-popover dark:bg-gray-800 text-popover-foreground dark:text-white p-2 rounded shadow-lg max-w-xs">
                  You need to select a meetup schedule first to enable date selection
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
          
          <p v-if="errors.meetup_date" class="text-destructive text-xs sm:text-sm mt-2">{{ errors.meetup_date }}</p>
        </div>

        <!-- Step 4: Preferred Time Selection -->
        <div class="mb-4 sm:mb-6" data-error="preferred_time">
          <h3 class="font-medium mb-2 text-foreground dark:text-white">Step 4: Select Preferred Time</h3>
          <p class="text-xs sm:text-sm text-muted-foreground dark:text-gray-400 mb-2">
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
                      class="w-full bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                      :class="{'opacity-50 cursor-not-allowed': !form.meetup_date || !selectedSchedule}"
                    >
                      <SelectValue>
                        <template v-if="form.preferred_time">
                          <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                              <circle cx="12" cy="12" r="10"></circle>
                              <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            {{ availableTimeSlots.find(slot => slot.value === form.preferred_time)?.display || formatTime(form.preferred_time) }}
                          </div>
                        </template>
                        <template v-else>Select a time</template>
                      </SelectValue>
                    </SelectTrigger>
                    <SelectContent position="popper" class="bg-popover dark:bg-gray-800 border-border dark:border-gray-700">
                      <div class="max-h-[200px] overflow-y-auto p-1">
                        <SelectItem value="placeholder" disabled>Select a time</SelectItem>
                        <SelectItem 
                          v-for="slot in availableTimeSlots" 
                          :key="slot.value" 
                          :value="slot.value"
                          class="text-foreground dark:text-white hover:bg-accent/5 dark:hover:bg-gray-700"
                        >
                          {{ slot.display }}
                        </SelectItem>
                      </div>
                    </SelectContent>
                  </Select>
                </div>
              </TooltipTrigger>
              <TooltipContent v-if="!form.meetup_date || !selectedSchedule" class="bg-popover dark:bg-gray-800 text-popover-foreground dark:text-white p-2 rounded shadow-lg max-w-xs z-50">
                {{ !selectedScheduleId 
                  ? 'You need to select a meetup schedule first' 
                  : 'You need to select a meetup date before choosing a time' }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
          
          <div v-if="form.meetup_date && selectedSchedule && !form.preferred_time" class="mt-3 flex flex-wrap gap-2">
            <p class="text-xs text-muted-foreground dark:text-gray-400 w-full mb-1">Quick select:</p>
            <Button 
              v-for="slot in availableTimeSlots.slice(0, 4)" 
              :key="slot.value"
              type="button"
              variant="outline"
              size="sm"
              @click="setPreferredTime(slot.value)"
              :class="[
                'text-xs sm:text-sm h-8 bg-white dark:bg-gray-800 border-border dark:border-gray-700',
                form.preferred_time === slot.value ? 'bg-primary-color text-white dark:bg-primary-color dark:text-white' : ''
              ]"
            >
              {{ slot.display }}
            </Button>
            <span v-if="availableTimeSlots.length > 4" class="text-xs text-muted-foreground dark:text-gray-400 self-center">
              +{{ availableTimeSlots.length - 4 }} more options
            </span>
          </div>

          <p v-if="errors.preferred_time" class="text-destructive text-xs sm:text-sm mt-2">{{ errors.preferred_time }}</p>
        </div>

        <!-- Additional Cash -->
        <div class="space-y-2" data-error="total_offered_value">
          <Label for="additional_cash" class="text-sm text-foreground dark:text-white">Additional Cash to Match Item Value (₱) <span class="text-muted-foreground dark:text-gray-400 text-xs">(Optional)</span></Label>
          <Input 
            id="additional_cash"
            type="number"
            v-model="form.additional_cash"
            min="0"
            step="0.01"
            class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
            :class="{ 'border-destructive': !isValueSufficient }"
          />
          <p class="text-xs text-muted-foreground dark:text-gray-400">
            Add cash to increase your total offer value. Combined with your item estimated value.
          </p>
          <p v-if="errors.additional_cash" class="text-destructive text-xs sm:text-sm">{{ errors.additional_cash }}</p>
          <p v-if="errors.total_offered_value" class="text-destructive text-xs sm:text-sm">{{ errors.total_offered_value }}</p>
          <p v-else-if="!isValueSufficient" class="text-destructive text-xs">{{ totalValueErrorMessage }}</p>
        </div>

        <!-- Notes -->
        <div class="space-y-2">
          <Label for="notes" class="text-sm text-foreground dark:text-white">Notes for Seller <span class="text-muted-foreground dark:text-gray-400 text-xs">(Optional)</span></Label>
          <Textarea 
            id="notes"
            v-model="form.notes"
            placeholder="Add any details about your trade offer here..."
            class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
          />
        </div>

        <!-- Offered Items -->
        <div class="space-y-4" data-error="offered_items">
          <div class="flex justify-between items-center">
            <h3 class="font-medium text-base sm:text-lg text-foreground dark:text-white">Items to Offer</h3>
            <Button 
              type="button" 
              variant="outline" 
              size="sm" 
              @click="addOfferedItem"
              class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
            >
              Add Another Item
            </Button>
          </div>
          
          <div v-for="(item, index) in form.offered_items" :key="index" :data-item-index="index" class="p-3 sm:p-4 border border-border dark:border-gray-700 rounded-lg space-y-3 sm:space-y-4 bg-accent/5 dark:bg-gray-800/50">
            <div class="flex justify-between">
              <h4 class="font-medium text-sm sm:text-base text-foreground dark:text-white">Item {{ index + 1 }}</h4>
              <Button 
                v-if="form.offered_items.length > 1" 
                type="button"
                variant="ghost" 
                size="sm" 
                @click="removeOfferedItem(index)"
                class="h-8 text-xs text-foreground dark:text-white hover:bg-accent/10 dark:hover:bg-gray-700"
              >
                Remove
              </Button>
            </div>
            <!-- Item form fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
              <div class="space-y-1 sm:space-y-2">
                <Label :for="`item_name_${index}`" class="text-sm text-foreground dark:text-white">Item Name*</Label>
                <Input 
                  :id="`item_name_${index}`"
                  v-model="item.name"
                  @input="clearItemFieldError(index, 'name')"
                  class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                  :class="{ 'border-destructive': errors[`offered_items.${index}.name`] || errors[`item_${index}_name`] }"
                />
                <p v-if="errors[`offered_items.${index}.name`]" class="text-destructive text-xs">
                  {{ errors[`offered_items.${index}.name`] }}
                </p>
                <p v-else-if="errors[`item_${index}_name`]" class="text-destructive text-xs"> 
                  {{ errors[`item_${index}_name`] }}
                </p>
              </div>
              
              <div class="space-y-1 sm:space-y-2">
                <Label :for="`item_quantity_${index}`" class="text-sm text-foreground dark:text-white">Quantity*</Label>
                <Input 
                  :id="`item_quantity_${index}`" 
                  type="number" 
                  v-model="item.quantity"
                  @input="clearItemFieldError(index, 'quantity')"
                  min="1"
                  class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                  :class="{ 'border-destructive': errors[`offered_items.${index}.quantity`] || errors[`item_${index}_quantity`] }"
                />
                <p v-if="errors[`offered_items.${index}.quantity`]" class="text-destructive text-xs">
                  {{ errors[`offered_items.${index}.quantity`] }}
                </p>
                <p v-else-if="errors[`item_${index}_quantity`]" class="text-destructive text-xs"> 
                  {{ errors[`item_${index}_quantity`] }}
                </p>
              </div>
              
              <div class="space-y-1 sm:space-y-2">
                <Label :for="`item_value_${index}`" class="text-sm text-foreground dark:text-white">Estimated Value (₱)*</Label>
                <Input 
                  :id="`item_value_${index}`" 
                  type="number" 
                  v-model="item.estimated_value"
                  @input="clearItemFieldError(index, 'estimated_value')"
                  step="0.01"
                  class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                  :class="{ 'border-destructive': !isValueSufficient }"
                />
                <p v-if="!isValueSufficient" class="text-destructive text-xs">
                  {{ totalValueErrorMessage }}
                </p>
              </div>
              
              <div class="space-y-1 sm:space-y-2">
                <Label :for="`item_condition_${index}`" class="text-sm text-foreground dark:text-white">Condition*</Label>
                <Select 
                  v-model="item.condition"
                  @update:model-value="clearItemFieldError(index, 'condition')"
                >
                  <SelectTrigger class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                      :class="{ 'border-destructive': errors[`offered_items.${index}.condition`] || errors[`item_${index}_condition`] }">
                      <SelectValue placeholder="Select condition" />
                  </SelectTrigger>
                  <SelectContent class="bg-popover dark:bg-gray-800 border-border dark:border-gray-700">
                      <SelectItem 
                          v-for="option in conditionOptions" 
                          :key="option.value" 
                          :value="option.value"
                          class="text-foreground dark:text-white"
                      >
                          {{ option.label }}
                      </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors[`offered_items.${index}.condition`]" class="text-destructive text-xs">
                  {{ errors[`offered_items.${index}.condition`] }}
                </p>
                <p v-else-if="errors[`item_${index}_condition`]" class="text-destructive text-xs"> 
                  {{ errors[`item_${index}_condition`] }}
                </p>
              </div>
              
              <div class="space-y-1 sm:space-y-2 sm:col-span-2">
                <Label class="text-sm text-foreground dark:text-white">Upload Images*</Label> 
                <ImageUploader
                  v-model="form.offered_items[index].images"
                  :existing-images="form.offered_items[index].current_images"
                  :hasError="!!errors[`offered_items.${index}.images`] || !!errors[`item_${index}_images`]"
                  :errorMessage="errors[`offered_items.${index}.images`] || errors[`item_${index}_images`] || ''"
                  @error="(msg) => handleImageUploaderError(index, msg)"
                  @update:modelValue="() => clearItemFieldError(index, 'images')"
                  @remove-existing="(imgIndex) => removeExistingImage(index, imgIndex)"
                />
              </div>
            </div>
          </div>
          <p v-if="errors.offered_items && typeof errors.offered_items === 'string'" class="text-destructive text-xs sm:text-sm mt-2">
            {{ errors.offered_items }}
          </p>
        </div>
        
        <!-- Form Footer -->
        <DialogFooter class="flex justify-between pt-2 sm:pt-4 border-t border-border dark:border-gray-700">
          <Button 
            type="button" 
            variant="outline" 
            @click="closeDialog"
            class="text-xs sm:text-sm bg-white dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
          >
            Cancel
          </Button>
          <Button 
            type="submit" 
            :disabled="loading"
            class="text-xs sm:text-sm bg-primary-color hover:bg-opacity-90 text-white"
          >
            {{ loading ? 'Updating...' : 'Update Trade Offer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
  
  <!-- Trade Summary Dialog -->
  <TradeSummaryDialog
    v-if="showSummaryDialog"
    :open="showSummaryDialog"
    :product="props.product"
    :offer="form"
    :total-offered-value="totalOfferedValue"
    :selected-location-name="meetupLocations.find(loc => loc.id === parseInt(form.meetup_location_id))?.name"
    :selected-day="selectedScheduleDay"
    :selected-time-display="availableTimeSlots.find(slot => slot.value === form.preferred_time)?.display || formatTime(form.preferred_time)"
    @confirm="confirmAndSubmit"
    @cancel="cancelSummary"
  />
</template>

<style scoped>
/* Same styles as TradeForm.vue */
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

/* Enhance dark mode styling for leaflet */
.dark :deep(.leaflet-container) {
  filter: brightness(0.85) contrast(1.1);
}

.dark :deep(.leaflet-popup-content-wrapper) {
  background-color: #1f2937;
  color: white;
}

.dark :deep(.leaflet-popup-tip) {
  background-color: #1f2937;
}

.dark :deep(.map-popup) {
  color: white;
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

@keyframes highlight-pulse {
  0% { box-shadow: 0 0 0 rgba(var(--destructive-rgb), 0.4); }
  50% { box-shadow: 0 0 15px rgba(var(--destructive-rgb), 0.7); }
  100% { box-shadow: 0 0 0 rgba(var(--destructive-rgb), 0.4); }
}

.error-highlight {
  animation: highlight-pulse 1.5s ease-in-out;
  scroll-margin: 100px;
}

[class*="border-dashed"] {
  cursor: pointer;
}

[class*="border-dashed"]:hover {
  border-color: hsl(var(--primary));
  background-color: rgba(var(--primary-rgb), 0.05);
}
</style>