<script>
import { ref, onMounted, watch } from 'vue';

export default {
  name: 'ImagePreview',
  props: {
    src: {
      type: String,
      required: false,
      default: null
    },
    images: {
      type: Array,
      default: () => []
    },
    alt: {
      type: String,
      default: 'Image preview'
    },
    current: {
      type: Number,
      default: 0
    },
    showControls: {
      type: Boolean,
      default: true
    }
  },

  setup(props, { emit }) {
    const currentIndex = ref(props.current || 0);
    const imageUrls = ref([]);
    const loading = ref(true);
    const error = ref(false);
    
    const processImages = () => {
      loading.value = true;
      error.value = false;
      
      // Handle either a single src or multiple images
      if (props.src) {
        imageUrls.value = [props.src];
      } else if (props.images && props.images.length) {
        // Convert any complex image objects to their URL strings
        imageUrls.value = props.images.map(img => {
          if (typeof img === 'string') return img;
          if (img && img.isBlob && img.url) return img.url;
          if (img && img.url) return img.url;
          if (img && img.src) return img.src;
          return img;
        });
      } else {
        imageUrls.value = [''];
        error.value = true;
      }
    };

    const handleImageError = () => {
      error.value = true;
      emit('error');
    };

    const handleImageLoad = () => {
      loading.value = false;
    };

    const next = () => {
      if (imageUrls.value.length <= 1) return;
      currentIndex.value = (currentIndex.value + 1) % imageUrls.value.length;
      emit('change', currentIndex.value);
    };

    const prev = () => {
      if (imageUrls.value.length <= 1) return;
      currentIndex.value = (currentIndex.value - 1 + imageUrls.value.length) % imageUrls.value.length;
      emit('change', currentIndex.value);
    };

    // Watch for prop changes
    watch(() => props.src, processImages, { immediate: true });
    watch(() => props.images, processImages, { immediate: true });
    watch(() => props.current, (newVal) => {
      currentIndex.value = newVal;
    });

    onMounted(() => {
      processImages();
    });

    return {
      currentIndex,
      imageUrls,
      loading,
      error,
      next,
      prev,
      handleImageError,
      handleImageLoad
    };
  }
};
</script>

<template>
  <div class="image-preview-container relative overflow-hidden">
    <img
      v-if="imageUrls.length > 0 && currentIndex < imageUrls.length"
      :src="imageUrls[currentIndex]"
      :alt="alt"
      class="w-full h-full object-contain"
      @error="handleImageError"
      @load="handleImageLoad"
    />
    <img
      v-else
      src="/images/placeholder-product.jpg"
      :alt="alt"
      class="w-full h-full object-contain"
    />
    
    <!-- Loading indicator -->
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/60 dark:bg-gray-900/60">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    
    <!-- Navigation controls -->
    <div v-if="showControls && imageUrls.length > 1" class="image-navigation-controls absolute inset-0 flex items-center justify-between px-2">
      <button 
        @click.prevent="prev"
        class="bg-white/70 dark:bg-gray-900/70 hover:bg-white/90 dark:hover:bg-gray-900/90 text-gray-800 dark:text-gray-200 rounded-full p-1 shadow"
        aria-label="Previous image"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>
      <button 
        @click.prevent="next"
        class="bg-white/70 dark:bg-gray-900/70 hover:bg-white/90 dark:hover:bg-gray-900/90 text-gray-800 dark:text-gray-200 rounded-full p-1 shadow"
        aria-label="Next image"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>
    </div>
    
    <!-- Image indicator dots -->
    <div v-if="imageUrls.length > 1" class="absolute bottom-2 left-0 right-0 flex justify-center">
      <div class="flex space-x-1">
        <button
          v-for="(_, index) in imageUrls"
          :key="index"
          @click="currentIndex = index"
          class="w-2 h-2 rounded-full transition-all duration-200"
          :class="index === currentIndex ? 'bg-primary scale-125' : 'bg-gray-400 hover:bg-gray-600'"
          aria-label="View image"
        ></button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.image-preview-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.image-navigation-controls {
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
}

.image-preview-container:hover .image-navigation-controls {
  opacity: 1;
}
</style>
