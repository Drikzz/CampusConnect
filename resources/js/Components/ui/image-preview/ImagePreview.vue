<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';

// Props for the component
const props = defineProps({
  images: {
    type: Array,
    default: () => []
  },
  initialIndex: {
    type: Number,
    default: 0
  },
  showNavigation: {
    type: Boolean,
    default: true
  }
});

// Normalize image URL for reliable display
const normalizeImageUrl = (url) => {
  console.log("ImagePreview normalizing URL:", url);
  
  if (!url) return '/images/placeholder-product.jpg';
  
  try {
    // Handle blob URLs directly
    if (typeof url === 'string' && url.startsWith('blob:')) {
      return url;
    }
    
    // Handle already absolute URLs with domain
    if (typeof url === 'string' && (url.startsWith('http://') || url.startsWith('https://'))) {
      return url;
    }
    
    // Handle storage paths
    if (typeof url === 'string') {
      if (url.startsWith('/storage/')) {
        // Already has correct format
        return url;
      } else if (url.startsWith('storage/')) {
        // Add leading slash
        return '/' + url;
      } else if (url.startsWith('/')) {
        // Some other absolute path
        return url;
      } else {
        // Add storage prefix
        return `/storage/${url}`;
      }
    }
    
    // If it's an object with a URL property
    if (typeof url === 'object' && url !== null && url.url) {
      return normalizeImageUrl(url.url);
    }
    
    // Fallback to placeholder
    return '/images/placeholder-product.jpg';
  } catch (e) {
    console.error("Error normalizing image URL in ImagePreview:", e);
    return '/images/placeholder-product.jpg';
  }
};

// Emit events from this component
const emit = defineEmits(['update:index', 'imageClick']);

// Current index state
const currentIndex = ref(props.initialIndex);

// Image loading state
const isImageLoading = ref(true);
const imageError = ref(false);

// Handle image loading events
const onImageLoad = () => {
  isImageLoading.value = false;
  imageError.value = false;
};

const onImageError = () => {
  isImageLoading.value = false;
  imageError.value = true;
};

// Navigation functions
const next = () => {
  if (props.images.length <= 1) return;
  isImageLoading.value = true;
  currentIndex.value = (currentIndex.value + 1) % props.images.length;
  emit('update:index', currentIndex.value);
};

const prev = () => {
  if (props.images.length <= 1) return;
  isImageLoading.value = true;
  currentIndex.value = (currentIndex.value - 1 + props.images.length) % props.images.length;
  emit('update:index', currentIndex.value);
};

// Keyboard navigation
const handleKeyDown = (event) => {
  if (event.key === 'ArrowLeft') {
    prev();
  } else if (event.key === 'ArrowRight') {
    next();
  }
};

// Expose current image URL
const currentImageUrl = computed(() => {
  if (props.images.length === 0 || currentIndex.value >= props.images.length) {
    return '/images/placeholder-product.jpg';
  }
  return normalizeImageUrl(props.images[currentIndex.value]);
});

// Handle index changes
watch(() => props.initialIndex, (newIndex) => {
  if (newIndex !== currentIndex.value) {
    currentIndex.value = newIndex;
    isImageLoading.value = true;
  }
});

watch(() => props.images, () => {
  // Reset index if images change
  if (currentIndex.value >= props.images.length) {
    currentIndex.value = 0;
    isImageLoading.value = true;
  }
}, { deep: true });

// Setup and teardown keyboard event listeners
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <div class="image-preview relative w-full h-full flex items-center justify-center overflow-hidden">
    <div 
      v-if="isImageLoading"
      class="absolute inset-0 flex items-center justify-center bg-muted"
    >
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    
    <img
      :src="currentImageUrl"
      :alt="'Image ' + (currentIndex + 1)"
      @load="onImageLoad"
      @error="onImageError"
      @click="emit('imageClick', currentIndex)"
      class="max-w-full max-h-full object-contain"
      :class="{ 'cursor-pointer': $listeners && $listeners.imageClick }"
    />
    
    <div 
      v-if="imageError"
      class="absolute inset-0 flex flex-col items-center justify-center bg-muted text-muted-foreground"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <p>Failed to load image</p>
    </div>
    
    <div v-if="showNavigation && props.images.length > 1" class="image-navigation-controls absolute inset-x-0 flex justify-between px-4">
      <Button 
        @click="prev" 
        variant="secondary" 
        class="rounded-full h-8 w-8 p-1 bg-black/30 hover:bg-black/50 text-white border-0"
        :disabled="props.images.length <= 1"
      >
        <ChevronLeft class="h-5 w-5" />
      </Button>
      
      <Button 
        @click="next" 
        variant="secondary" 
        class="rounded-full h-8 w-8 p-1 bg-black/30 hover:bg-black/50 text-white border-0"
        :disabled="props.images.length <= 1"
      >
        <ChevronRight class="h-5 w-5" />
      </Button>
    </div>
    
    <div v-if="props.images.length > 1" class="absolute bottom-2 flex gap-1 items-center justify-center w-full">
      <div 
        v-for="(_, index) in props.images" 
        :key="index"
        class="h-1.5 rounded-full transition-all duration-200"
        :class="[
          currentIndex === index ? 'bg-primary w-4' : 'bg-muted w-2 cursor-pointer'
        ]"
        @click="currentIndex = index; emit('update:index', index)"
      ></div>
    </div>
  </div>
</template>

<style scoped>
.image-navigation-controls {
  top: 50%;
  transform: translateY(-50%);
}

/* Add this to make the navigation controls centered vertically */
.image-navigation-controls > button {
  transform: translateY(-50%);
  margin-top: 50%;
}
</style>
