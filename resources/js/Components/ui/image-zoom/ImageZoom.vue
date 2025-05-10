<script setup>
import { ref, onMounted, watch, watchEffect } from 'vue';
import VueImageZoom from 'vue-image-zoomer';

const props = defineProps({
  // Image URL to zoom
  imageUrl: {
    type: String,
    required: true
  },
  // Zoom amount for the zoomer
  zoomAmount: {
    type: Number,
    default: 2
  },
  // Alt text for the image
  alt: {
    type: String,
    default: 'Image'
  },
  // Additional classes for the zoomer
  containerClass: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['error']);

// Track if the zoomer loaded correctly
const zoomLoaded = ref(false);
const zoomError = ref(false);

const handleZoomError = () => {
  zoomError.value = true;
  emit('error');
};

// Check if the zoomer loaded properly
onMounted(() => {
  setTimeout(() => {
    const zoomElement = document.querySelector('.vue-image-zoomer');
    if (!zoomElement) {
      handleZoomError();
    } else {
      zoomLoaded.value = true;
    }
  }, 300);
});

// Watch for changes to the imageUrl
watchEffect(() => {
  console.log("ImageZoom component rendering with URL:", props.imageUrl);
});
</script>

<template>
  <div class="image-zoom-wrapper" :class="containerClass">
    <!-- Only the zoomer functionality -->
    <VueImageZoom
      :regular="imageUrl"
      :zoom="imageUrl"
      :zoom-amount="zoomAmount"
      :alt="alt"
      class="w-full h-full"
      img-class="object-contain w-full h-full"
      @error="handleZoomError"
    >
      <!-- Fallback slot content -->
      <img 
        :src="imageUrl" 
        :alt="alt"
        class="object-contain max-h-full max-w-full"
      />
    </VueImageZoom>
    
    <!-- Pure fallback in case component fails -->
    <div v-if="zoomError" class="fallback-image-container">
      <img 
        :src="imageUrl" 
        :alt="alt"
        class="object-contain max-h-full max-w-full"
      />
    </div>
  </div>
</template>

<style scoped>
.image-zoom-wrapper {
  width: 100%;
  height: 100%;
  position: relative;
}

/* Ensure vue-image-zoom has proper cursors */
:deep(.vue-image-zoomer) {
  cursor: crosshair !important;
  width: 100% !important;
  height: 100% !important;
  position: relative !important;
  overflow: hidden !important;
}

:deep(.vue-image-zoomer img) {
  width: 100% !important;
  height: 100% !important;
  object-fit: contain !important;
  margin: 0 auto !important;
}

/* Make sure the magnified view doesn't stick to edge */
:deep(.vue-image-zoomer .zoomed-image) {
  position: absolute !important;
  max-width: 100% !important;
  max-height: 100% !important;
  pointer-events: none;
  z-index: 100;
  border: 2px solid white;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  background-color: white;
}

/* Fallback image styling */
.fallback-image-container {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
}
</style>