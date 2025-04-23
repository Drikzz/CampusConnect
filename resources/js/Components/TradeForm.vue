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
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import TradeSummaryDialog from './TradeSummaryDialog.vue';
import { ImageUploader } from "@/Components/ui/image-uploader";
import { ImagePreview } from "@/Components/ui/image-preview";
import axios from 'axios';

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
    },
    existingTrade: {
        type: Object,
        default: null
    },
    editMode: {
        type: Boolean,
        default: false
    }
});

const isProductValid = computed(() => {
    return props.product && typeof props.product === 'object' && props.product.id;
});

const emit = defineEmits(['close', 'update:open', 'updateSuccess']);
const loading = ref(false);
const errors = ref({});
const meetupLocations = ref([]);
const isLoadingMeetupLocations = ref(false);
const selectedSchedule = ref(null);
const showMap = ref(false);
const isMapReady = ref(false);
const showSummaryDialog = ref(false);

const form = useForm({
    seller_product_id: isProductValid.value ? props.product.id : null,
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
  const timestamp = Date.now();
  const fileName = `trade_items/${timestamp}-item-${itemIndex}.jpg`;
  const file = new File([blob], fileName, { type: 'image/jpeg' });
  
  file.isCropped = true;
  file.storagePath = `storage/${fileName}`;
  
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
  if (productDetails.value && productDetails.value.images && productDetails.value.images.length > 0) {
    return productDetails.value.images[0];
  }
  
  if (props.product?.images && props.product.images.length > 0) {
    const image = props.product.images[0];
    if (typeof image === 'string') {
      return image;
    }
  }
  
  return '/images/placeholder-product.jpg';
});

const sellerName = computed(() => {
  if (!isProductValid.value || !props.product.seller) return 'Unknown Seller';
  
  return `${props.product.seller.first_name || ''} ${props.product.seller.last_name || ''}`.trim() || 'Unknown Seller';
});

const sellerProfilePicture = computed(() => {
  return props.product?.seller?.profile_picture
    ? `/storage/${props.product.seller.profile_picture}`
    : '/images/placeholder-avatar.jpg';
});

const totalOfferedValue = computed(() => {
  const itemsValue = form.offered_items.reduce((sum, item) => {
    const value = parseFloat(item.estimated_value) || 0;
    const quantity = parseInt(item.quantity) || 0;
    return sum + (value * quantity);
  }, 0);
  const cash = parseFloat(form.additional_cash) || 0;
  return itemsValue + cash;
});

const productEffectivePrice = computed(() => {
  if (!props.product) return 0;
  return (props.product.discounted_price && props.product.discounted_price < props.product.price)
    ? parseFloat(props.product.discounted_price)
    : parseFloat(props.product.price);
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
    if (typeof isOpen !== 'boolean') {
        isOpen = false;
    }
    
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

watch(() => selectedScheduleId.value, (newValue) => {
  if (errors.value && errors.value.meetup_schedule) {
    delete errors.value.meetup_schedule;
  }
  if (!newValue) {
      form.meetup_date = '';
      form.preferred_time = '';
      if (errors.value.meetup_date) delete errors.value.meetup_date;
      if (errors.value.preferred_time) delete errors.value.preferred_time;
  }
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
      errors.value.meetup_date = 'Invalid date selection. Please try again.';
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

const getFilePreview = (file) => {
  if (typeof file === 'string') {
    return file;
  }
  return URL.createObjectURL(file);
};

const getFormattedMeetupSchedule = () => {
  if (!form.meetup_date || !form.preferred_time || !selectedScheduleDay.value) {
    return null;
  }
  
  return `${form.meetup_date}, ${form.preferred_time}`;
}

const validateLocationAndSchedule = () => {
  if (errors.value.meetup_schedule) delete errors.value.meetup_schedule;

  let isValid = true;

  if (!form.meetup_location_id) {
    errors.value.meetup_schedule = 'Please select a meetup location first';
    isValid = false;
    return isValid;
  }

  const locationData = schedulesByLocation.value[form.meetup_location_id];
  if (!locationData || !locationData.schedules || locationData.schedules.length === 0) {
    errors.value.meetup_schedule = 'This location has no available schedules';
    isValid = false;
  }

  return isValid;
};

const scrollToFirstError = () => {
  nextTick(() => {
    const errorFields = Object.keys(errors.value);
    if (errorFields.length === 0) return;

    let targetField = null;
    const errorPriority = [
        'meetup_schedule',
        'meetup_date',
        'preferred_time',
        'total_offered_value',
        'offered_items',
    ];

    for (const fieldName of errorPriority) {
        if (errors.value[fieldName]) {
            targetField = document.querySelector(`[data-error="${fieldName}"]`);
            if (targetField) break;
        }
    }

    if (!targetField) {
        for (const key of errorFields) {
            if (key.startsWith('offered_items.') || key.startsWith('item_')) {
                const match = key.match(/offered_items\.(\d+)\.(.+)/) || key.match(/item_(\d+)_(.+)/);
                if (match) {
                    const index = match[1];
                    const field = match[2];
                    targetField = document.querySelector(`#item_${field}_${index}`);
                    if (!targetField) {
                         targetField = document.querySelector(`[data-item-index="${index}"]`);
                    }
                    if (targetField) break;
                }
            }
        }
    }

    if (!targetField) {
        targetField = document.querySelector('.border-destructive') || document.querySelector('[data-error]');
    }

    if (targetField) {
      targetField.scrollIntoView({ behavior: 'smooth', block: 'center' });
      targetField.classList.add('error-highlight');
      setTimeout(() => {
        targetField.classList.remove('error-highlight');
      }, 2000);
    } else {
        console.warn("ScrollToFirstError: Could not find target element for errors:", errors.value);
    }
  });
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

const updateItemImages = (index, images) => {
  form.offered_items[index].images = images;
  if (errors.value[`offered_items.${index}.images`]) {
    delete errors.value[`offered_items.${index}.images`];
  }
};

const submitTradeOffer = () => {
  showValueValidation.value = true;

  console.log('--- submitTradeOffer called ---', new Date().toLocaleTimeString());
  errors.value = {};
  
  let isValid = true;

  if (!validateLocationAndSchedule()) {
    console.log('Validation failed: Location/Schedule Availability');
    isValid = false;
  }

  if (!selectedScheduleId.value) {
    console.log('Validation failed: Day not selected');
    errors.value.meetup_schedule = 'Please select an available day first';
    isValid = false;
  }

  if (!form.meetup_date) {
    console.log('Validation failed: Date not selected');
    errors.value.meetup_date = 'Please select a valid meetup date';
    isValid = false;
  }

  if (!form.preferred_time) {
    console.log('Validation failed: Time not selected');
    errors.value.preferred_time = 'Please select a preferred time';
    isValid = false;
  }

  let isItemsValid = true;

  if (!form.offered_items || form.offered_items.length === 0) {
    errors.value.offered_items = 'At least one item must be offered';
    isItemsValid = false;
  } else {
    form.offered_items.forEach((item, index) => {
      if (!item.name) {
        errors.value[`offered_items.${index}.name`] = 'Item name is required';
        errors.value[`item_${index}_name`] = 'Item name is required';
        isItemsValid = false;
      }
      
      if (!item.quantity || item.quantity < 1) {
        errors.value[`offered_items.${index}.quantity`] = 'Quantity must be at least 1';
        errors.value[`item_${index}_quantity`] = 'Quantity must be at least 1';
        isItemsValid = false;
      }
      
      if (!item.condition) {
        errors.value[`offered_items.${index}.condition`] = 'Condition is required';
        errors.value[`item_${index}_condition`] = 'Condition is required';
        isItemsValid = false;
      }
      
      if (!item.images || item.images.length === 0) {
        errors.value[`offered_items.${index}.images`] = 'At least one image is required';
        errors.value[`item_${index}_images`] = 'At least one image is required';
        isItemsValid = false;
      }
    });
  }
  
  if (!isItemsValid) {
    console.log('Validation failed: Offered items invalid');
    isValid = false;
  }

  console.log(`Comparing Offered: ${totalOfferedValue.value} vs Product: ${productEffectivePrice.value}`);
  if (totalOfferedValue.value < productEffectivePrice.value) {
    console.log('Validation failed: Total offered value too low');
    errors.value.total_offered_value = totalValueErrorMessage.value;
    isValid = false;
  }

  if (!isValid) {
    console.log('Validation failed. Showing all errors.');
    scrollToFirstError();
    return;
  }

  console.log('All validations passed. Showing summary dialog.');
  showSummaryDialog.value = true;
};

const confirmAndSubmit = () => {
  loading.value = true;
  
  const formData = new FormData();
  const formattedSchedule = getFormattedMeetupSchedule();
  
  if (!formattedSchedule) {
    errors.value = { ...errors.value, meetup_schedule: 'Invalid meetup schedule details' };
    loading.value = false;
    showSummaryDialog.value = false;
    scrollToFirstError();
    return;
  }
  
  const isUpdate = props.editMode && props.existingTrade?.id;
  
  if (isUpdate) {
      formData.append('trade_id', props.existingTrade.id);
  }
  
  formData.append('meetup_schedule', formattedSchedule);
  formData.append('selected_day', selectedScheduleDay.value);
  formData.append('seller_product_id', form.seller_product_id);
  formData.append('meetup_location_id', Number(form.meetup_location_id));
  formData.append('preferred_time', form.preferred_time);
  formData.append('additional_cash', form.additional_cash || 0);
  formData.append('notes', form.notes || '');
  
  form.offered_items.forEach((item, index) => {
      if (isUpdate && item.id) {
          formData.append(`offered_items[${index}][id]`, item.id);
      }
      
      formData.append(`offered_items[${index}][name]`, item.name);
      formData.append(`offered_items[${index}][quantity]`, item.quantity);
      formData.append(`offered_items[${index}][estimated_value]`, item.estimated_value);
      formData.append(`offered_items[${index}][description]`, item.description || '');
      formData.append(`offered_items[${index}][condition]`, item.condition);
      
      if (item.current_images && item.current_images.length > 0) {
          formData.append(
              `offered_items[${index}][current_images]`, 
              JSON.stringify(item.current_images)
          );
      }
      
      const imageInfoArray = [];
      if (item.images && item.images.length > 0) {
          item.images.forEach((image, imageIndex) => {
              if (image instanceof File) {
                  const uniqueFilename = image.isCropped 
                      ? image.storagePath.replace(/^\/?(storage\/)?/, '') 
                      : `trade_items/${Date.now()}-${image.name}`;
                  
                  formData.append(`offered_items[${index}][image_files][]`, image);
                  
                  imageInfoArray.push({
                      original_name: image.name, 
                      size: image.size, 
                      type: image.type, 
                      unique_key: uniqueFilename,
                      is_cropped: !!image.isCropped,
                      storage_path: image.storagePath ? image.storagePath.replace(/^\/?(storage\/)?/, '') : null
                  });
              }
          });
          
          if (imageInfoArray.length > 0) {
              formData.append(`offered_items[${index}][images_json]`, JSON.stringify(imageInfoArray));
          }
      }
  });
  
  const routeUrl = isUpdate 
      ? route('trades.update', props.existingTrade.id) 
      : route('trade.submit');
  
  const method = isUpdate ? 'post' : 'post';
  
  if (isUpdate) {
      formData.append('_method', 'PATCH');
  }
  
  router[method](routeUrl, formData, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        toast({ 
            title: "Success", 
            description: isUpdate ? "Trade updated successfully!" : "Trade offer submitted successfully!", 
            variant: "success" 
        });
        showSummaryDialog.value = false;
        
        if (isUpdate) {
            emit('updateSuccess');
        }
        
        nextTick(() => {
          closeDialog();
        });
    },
      onError: (validationErrors) => {
          showSummaryDialog.value = false;
          errors.value = validationErrors;
          toast({ 
              title: "Error", 
              description: "Please check the form for errors", 
              variant: "destructive" 
          });
          
          nextTick(() => {
              scrollToFirstError();
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

const selectLocationFromMap = (location) => {
  if (!location || !location.schedules || !location.schedules.length) {
    errors.value.meetup_schedule = 'This location has no available days';
    return;
  }
  
  console.log('Selecting location from map:', location);
  
  if (errors.value.meetup_schedule) delete errors.value.meetup_schedule;
  if (errors.value.meetup_date) delete errors.value.meetup_date;
  if (errors.value.preferred_time) delete errors.value.preferred_time;
  if (errors.value.total_offered_value) delete errors.value.total_offered_value;
  
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
  
  form.meetup_location_id = locationId;
  console.log('Set meetup_location_id to:', locationId);
  
  selectedScheduleId.value = '';
  form.meetup_date = '';
  form.preferred_time = '';
  selectedSchedule.value = null;
  
  markers.value.forEach(marker => {
    if (marker.isPopupOpen()) {
      marker.closePopup();
    }
  });
  
  showMap.value = false;
  
  nextTick(() => {
    const locationData = schedulesByLocation.value[locationId];
    if (locationData && locationData.schedules && locationData.schedules.length > 0) {
    }
  });
};

const clearFieldError = (fieldName) => {
  if (errors.value && errors.value[fieldName]) {
    delete errors.value[fieldName];
  }
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
  if (errors.value.meetup_date) delete errors.value.meetup_date;
  if (errors.value.preferred_time) delete errors.value.preferred_time;
  if (errors.value.total_offered_value) delete errors.value.total_offered_value;
  
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
    showValueValidation.value = false;
    emit('close');
    emit('update:open', false);
};

const formatTime = (time) => {
  if (!time) return '';
  try {
    const [hours, minutes] = time.split(':');
    const hourNum = parseInt(hours);
    const suffix = hourNum >= 12 ? 'PM' : 'AM';
    const hour12 = hourNum % 12 || 12;
    return `${hour12}:${minutes} ${suffix}`;
  } catch (e) {
    console.error('Error formatting time:', e);
    return time;
  }
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

const productDetails = ref(null);
const isLoadingProduct = ref(false);
const productLoadError = ref(null);

const loadCompleteProductDetails = async () => {
  if (!props.product?.id) return;
  
  isLoadingProduct.value = true;
  
  try {
    const response = await axios.get(`/trade/products/${props.product.id}/details`);
      
    if (response.data) {
      productDetails.value = response.data;
      updateProductWithDetails();
    }
  } catch (error) {
    console.error('Failed to load product details:', error);
    productLoadError.value = 'Failed to load product details';
  } finally {
    isLoadingProduct.value = false;
  }
};

const updateProductWithDetails = () => {
  if (props.product && productDetails.value) {
    props.product.images = ensureArrayFormat(productDetails.value.images);
    
    if (productDetails.value.seller) {
      props.product.seller = productDetails.value.seller;
    }
    
    console.log('Updated product with complete details:', props.product);
  }
};

const ensureArrayFormat = (images) => {
  if (!images) return [];
  if (Array.isArray(images)) return images;
  return typeof images === 'string' ? [images] : [];
};

const prepareOfferForSummary = (formData) => {
  const prepared = { ...formData };
  
  if (prepared.offered_items && Array.isArray(prepared.offered_items)) {
    prepared.offered_items = prepared.offered_items.map(item => {
      const itemCopy = { ...item };
      
      let processedImages = [];
      
      if (itemCopy.images) {
        // Process current_images first - these are existing images from the database
        if (itemCopy.current_images && Array.isArray(itemCopy.current_images) && itemCopy.current_images.length > 0) {
          // Current images are already processed paths, use them directly
          itemCopy.current_images.forEach(imgPath => {
            if (typeof imgPath === 'string' && !processedImages.includes(imgPath)) {
              processedImages.push(imgPath);
            }
          });
        }
        
        // Then process new images (File objects)
        if (Array.isArray(itemCopy.images)) {
          itemCopy.images.forEach(img => {
            if (img instanceof File) {
              // For cropped images that already have a storage path
              if (img.isCropped && img.storagePath) {
                const cleanPath = img.storagePath.replace(/^\/?(storage\/)?/, '');
                processedImages.push(cleanPath);
              } else {
                // For edit mode, skip blob URLs as we should already have current_images
                if (!props.editMode) {
                  // For new files in new trades, create blob URLs for preview
                  const blobUrl = URL.createObjectURL(img);
                  processedImages.push({ 
                    isBlob: true,
                    url: blobUrl,
                    originalName: img.name
                  });
                }
              }
            } else if (typeof img === 'string' && !processedImages.includes(img)) {
              // Clean up any storage prefix and add string paths
              const cleanPath = img.replace(/^\/?(storage\/)?/, '');
              processedImages.push(cleanPath);
            }
          });
        } else if (typeof itemCopy.images === 'string') {
          // Single string path
          const cleanPath = itemCopy.images.replace(/^\/?(storage\/)?/, '');
          processedImages.push(cleanPath);
        } else if (itemCopy.images instanceof File) {
          // Single File object
          if (itemCopy.images.isCropped && itemCopy.images.storagePath) {
            const cleanPath = itemCopy.images.storagePath.replace(/^\/?(storage\/)?/, '');
            processedImages.push(cleanPath);
          } else if (!props.editMode) {
            // For new trades, create blob URL for preview
            const blobUrl = URL.createObjectURL(itemCopy.images);
            processedImages.push({ 
              isBlob: true,
              url: blobUrl,
              originalName: itemCopy.images.name
            });
          }
        }
      }
      
      console.log("Image data before preparing for summary:", itemCopy.images);
      console.log("Image data after preparing for summary:", processedImages);
      
      return {
        ...itemCopy,
        images: processedImages
      };
    });
  }
  
  return prepared;
};

const initializeFormWithExistingTrade = async () => {
  const trade = props.existingTrade;
  console.log("Initializing form with existing trade:", trade);
  
  if (trade.seller_product_id) {
    await loadCompleteProductDetails();
  }
  
  form.seller_product_id = trade.seller_product_id || props.product.id;
  form.meetup_location_id = trade.meetup_location_id?.toString() || '';
  form.additional_cash = parseFloat(trade.additional_cash || 0);
  form.notes = trade.notes || '';
  
  // Handle meetup schedule
  if (trade.meetup_schedule) {
    const meetupDate = new Date(trade.meetup_schedule);
    if (!isNaN(meetupDate.getTime())) {
      const year = meetupDate.getFullYear();
      const month = String(meetupDate.getMonth() + 1).padStart(2, '0');
      const day = String(meetupDate.getDate()).padStart(2, '0');
      form.meetup_date = `${year}-${month}-${day}`;
      
      const hours = String(meetupDate.getHours()).padStart(2, '0');
      const minutes = String(meetupDate.getMinutes()).padStart(2, '0');
      form.preferred_time = `${hours}:${minutes}`;
      
      const dayIndex = meetupDate.getDay();
      const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      const dayName = days[dayIndex];
      
      fetchMeetupLocations().then(() => {
        nextTick(() => {
          if (form.meetup_location_id) {
            const locationSchedules = availableSchedules.value.filter(
              schedule => schedule.meetup_location_id.toString() === form.meetup_location_id.toString()
            );
            
            const matchingSchedule = locationSchedules.find(
              schedule => schedule.day.toLowerCase() === dayName.toLowerCase()
            );
            
            if (matchingSchedule) {
              console.log("Setting selected schedule to:", matchingSchedule.id);
              selectedScheduleId.value = matchingSchedule.id;
              
              showMap.value = false;
            }
          }
        });
      });
    }
  }
  
  // Process offered items - properly decode images from JSON
  if (trade.offered_items && Array.isArray(trade.offered_items) && trade.offered_items.length > 0) {
    form.offered_items = trade.offered_items.map(item => {
      let processedImages = [];
      
      // Handle images from database which are stored as JSON
      if (item.images) {
        try {
          // First, ensure we're working with an array of paths
          let imagePaths;
          
          if (typeof item.images === 'string') {
            // Try to parse if this is a JSON string
            try {
              imagePaths = JSON.parse(item.images);
            } catch (e) {
              // Not a JSON string, treat as a single path
              imagePaths = [item.images];
            }
          } else if (Array.isArray(item.images)) {
            // Already an array
            imagePaths = item.images;
          } else {
            // Fallback for unknown format
            imagePaths = [];
          }
          
          // Ensure array format
          imagePaths = Array.isArray(imagePaths) ? imagePaths : [imagePaths];
          
          // Process each path to ensure consistent format
          processedImages = imagePaths.map(path => {
            // Handle URLs
            if (typeof path === 'string') {
              // Remove storage prefix if present - will be added when rendering
              return path.replace(/^\/?(storage\/)?/, '');
            }
            return path;
          });
        } catch (e) {
          console.error("Error processing image paths:", e);
          processedImages = [];
        }
      }
      
      // If item has current_images (from previous edit), use those too
      if (item.current_images && Array.isArray(item.current_images)) {
        processedImages = [...processedImages, ...item.current_images.map(path => {
          if (typeof path === 'string') {
            return path.replace(/^\/?(storage\/)?/, '');
          }
          return path;
        })];
      }
      
      // Convert to a set and back to array to remove duplicates
      processedImages = [...new Set(processedImages)];
      
      // Log what we're processing for debugging
      console.log("Processing images for item:", item.name, processedImages);
      
      return {
        ...item,
        images: [], // Empty array for new images to add
        current_images: processedImages // Store processed paths here
      };
    });
  }
};

onMounted(async () => {
  await loadLeaflet();
  
  await loadCompleteProductDetails();
  
  if (props.editMode && props.existingTrade) {
    await initializeFormWithExistingTrade();
  }
  
  nextTick(() => {
    setTimeout(() => {
      isMapReady.value = true;
      
      if (props.editMode && props.existingTrade?.meetup_location_id) {
        showMap.value = false;
        return;
      }
      
      showMap.value = true;
      
      setTimeout(() => {
        initMap();
      }, 500);
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
  
  if (!showMap.value) return;
  
  if (props.editMode && form.meetup_location_id) return;
  
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
      closePopupOnClick: true,
      scrollWheelZoom: true,
      zoomAnimation: true
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
    
  } catch (error) {
    console.error('Error initializing map:', error);
  }
};

const updateMapMarkers = () => {
  if (!map.value || !L.value) {
    return;
  }
  try {
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
                <button type="button" class="select-location-btn"
                  style="background: #4F46E5; color: white; padding: 6px 12px; 
                  border-radius: 4px; margin-top: 8px; font-size: 13px; 
                  width: 100%; text-align: center; cursor: pointer;">
                  Select Location
                </button>
              </div>
          </div>`;
      const marker = L.value.marker([lat, lng], { icon })
          .addTo(map.value);
          
      const popup = L.value.popup({
        autoPan: true,
        closeButton: true,
      }).setContent(popupContent);
      
      marker.on('click', () => {
        map.value.closePopup();
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
                map.value.closePopup();
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
      
      marker.on('popupclose', () => {
        marker.unbindPopup();
      });
      
      markers.value.push(marker);
      bounds.extend([lat, lng]);
    });
    if (markers.value.length > 0) {
      map.value.closePopup();
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
      :open="open && !showSummaryDialog" 
      @update:open="handleDialogClose"
      modal
    >
        <DialogContent class="mx-4 w-[75%] sm:max-w-20xl overflow-y-auto max-h-[90vh] p-6 md:p-8 lg:p-10 bg-background dark:bg-gray-900 border-border dark:border-gray-700">
            <div v-if="isLoadingProduct" class="flex flex-col items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                <p class="mt-4 text-muted-foreground">Loading product details...</p>
            </div>
            
            <div v-else-if="productLoadError" class="flex flex-col items-center justify-center py-12 text-destructive">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-4">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p class="text-lg font-medium">Failed to load product details</p>
                <p class="text-sm mt-2">{{ productLoadError }}</p>
                <Button 
                    type="button" 
                    variant="outline" 
                    @click="closeDialog" 
                    class="mt-6"
                >
                    Close
                </Button>
            </div>
            
            <template v-else>
                <DialogHeader>
                    <DialogTitle class="text-foreground dark:text-white font-medium text-xl">Trade for {{ capitalizeFirst(productDetails?.name || product.name) }}</DialogTitle>
                    <DialogDescription class="text-muted-foreground dark:text-gray-400">
                        Offer your items in exchange for this product. The seller will review your offer.
                    </DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitTradeOffer" class="space-y-6">
                    <div class="flex items-center gap-4 p-3 sm:p-4 bg-accent/5 dark:bg-gray-800/50 rounded-lg border border-border dark:border-gray-700">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 overflow-hidden rounded-md shrink-0 border border-border dark:border-gray-700">
                            <img
                                :src="productImageUrl"
                                :alt="productDetails?.name || product.name"
                                class="w-full h-full object-cover"
                                @error="$event.target.src = '/images/placeholder-product.jpg'"
                            />
                        </div>
                        <div>
                            <h3 class="font-medium text-base sm:text-lg text-foreground dark:text-white">
                                {{ capitalizeFirst(productDetails?.name || product.name) }}
                            </h3>
                            <p class="text-muted-foreground dark:text-gray-400 text-sm sm:text-base">
                                <span v-if="(productDetails || product).discounted_price && (productDetails || product).discounted_price < (productDetails || product).price">
                                    <span class="line-through text-muted-foreground mr-1">₱{{ formatPrice((productDetails || product).price) }}</span>
                                    <span class="text-primary-color dark:text-primary-color">₱{{ formatPrice((productDetails || product).discounted_price) }}</span>
                                </span>
                                <span v-else class="text-primary-color dark:text-primary-color">₱{{ formatPrice((productDetails || product).price) }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="border-t border-border dark:border-gray-700 py-3 sm:py-4">
                        <h4 class="font-medium mb-2 text-foreground dark:text-white">Seller Information</h4>
                        <div v-if="productDetails?.seller || product?.seller" class="flex items-start gap-3">
                            <img 
                                :src="(productDetails?.seller?.profile_picture || product?.seller?.profile_picture || '/images/placeholder-avatar.jpg')" 
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover flex-shrink-0 border border-border dark:border-gray-700"
                                @error="$event.target.src = '/images/placeholder-avatar.jpg'"
                            >
                            <div class="space-y-1">
                                <p class="font-medium text-foreground dark:text-white">
                                    {{ (productDetails?.seller?.name) || 
                                       ((productDetails?.seller?.first_name || product?.seller?.first_name) + ' ' + 
                                       (productDetails?.seller?.last_name || product?.seller?.last_name)) }}
                                </p>
                                <div class="text-xs sm:text-sm text-muted-foreground dark:text-gray-400 space-y-0.5">
                                    <p class="flex items-center gap-2">
                                        <span class="font-medium">
                                            @{{ productDetails?.seller?.username || product?.seller?.username }}
                                        </span>
                                    </p>
                                    <p v-if="productDetails?.seller?.location || product?.seller?.location" class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        {{ productDetails?.seller?.location || product?.seller?.location }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground dark:text-gray-400">
                            Seller information not available
                        </div>
                    </div>
                    
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
                                        :class="[
                                            {'opacity-50': !selectedScheduleId},
                                            {'pointer-events-none': !selectedScheduleId},
                                            {'border-destructive': errors.meetup_date && form.meetup_date === ''},
                                            'bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white'
                                        ]"
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
                                                            {{ formatTime(form.preferred_time) }}
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

                    <div class="space-y-2">
                        <Label for="notes" class="text-sm text-foreground dark:text-white">Notes for Seller <span class="text-muted-foreground dark:text-gray-400 text-xs">(Optional)</span></Label>
                        <Textarea 
                            id="notes"
                            v-model="form.notes"
                            placeholder="Add any details about your trade offer here..."
                            class="bg-background dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
                        />
                    </div>

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
                                      v-model="item.images" 
                                      :initial-images="item.current_images || []"
                                      :multiple="true"
                                      :hasError="!!errors[`offered_items.${index}.images`]"
                                      :errorMessage="errors[`offered_items.${index}.images`] || ''"
                                      @update:modelValue="updateItemImages(index, $event)"
                                    />
                                    <p class="text-xs text-muted-foreground dark:text-gray-400">
                                        {{ item.current_images && item.current_images.length ? 'Add more images or remove existing ones.' : 'Upload at least one image of your offered item.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                         <p v-if="errors.offered_items && typeof errors.offered_items === 'string'" class="text-destructive text-xs sm:text-sm mt-2">
                            {{ errors.offered_items }}
                        </p>
                    </div>
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
                            {{ loading ? 'Submitting...' : 'Submit Trade Offer' }}
                        </Button>
                    </DialogFooter>
                </form>
            </template>
        </DialogContent>
    </Dialog>

    <TradeSummaryDialog
      :open="showSummaryDialog"
      :product="product" 
      :product-image-url="productImageUrl || (product.images && product.images.length > 0 ? product.images[0] : '')"
      :offer="prepareOfferForSummary(form)"
      :total-offered-value="totalOfferedValue"
      :selected-location-name="schedulesByLocation[form.meetup_location_id]?.name"
      :selected-day="selectedScheduleDay"
      :selected-time-display="formatTime(form.preferred_time)"
      :loading="loading"
      :edit-mode="editMode"
      @confirm="confirmAndSubmit"
      @cancel="cancelSummary"
      @update:open="showSummaryDialog = $event"
    />
</template>

<style scoped>
/* Add this style to hide the form dialog visually but keep it in DOM */
.visually-hidden {
  opacity: 0 !important;
  pointer-events: none !important;
  visibility: hidden !important;
  position: fixed !important;
  top: -9999px !important;
  left: -9999px !important;
  z-index: -1 !important;
}

/* Ensure TradeSummaryDialog appears on top */
:deep(.trade-summary-dialog) {
  z-index: 100 !important;
}

/* Make sure dialog appears below the summary */
:deep(.dialog-content) {
  z-index: 50;
}

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
