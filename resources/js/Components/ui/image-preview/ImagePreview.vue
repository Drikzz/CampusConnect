<script setup>
import { ref, computed, watch } from 'vue';
import { Dialog, DialogContent } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";

const props = defineProps({
  images: {
    type: Array,
    required: true,
    default: () => []
  },
  alt: {
    type: String,
    default: 'Image'
  }
});

const currentImageIndex = ref(0);
const previewOpen = ref(false);

// Reset current image index when images change
watch(() => props.images, () => {
  currentImageIndex.value = 0;
});

// Return image URL or placeholder if not available
const processedImages = computed(() => {
  if (!props.images || props.images.length === 0) {
    return ['/images/placeholder-product.jpg'];
  }

  return props.images.map(image => {
    if (typeof image === 'string') {
      return image.startsWith('/storage') ? image : `/storage/${image}`;
    }
    return image instanceof File ? URL.createObjectURL(image) : '/images/placeholder-product.jpg';
  });
});

const currentImage = computed(() => {
  return processedImages.value[currentImageIndex.value];
});

function nextImage() {
  currentImageIndex.value = (currentImageIndex.value + 1) % processedImages.value.length;
}

function previousImage() {
  currentImageIndex.value = (currentImageIndex.value - 1 + processedImages.value.length) % processedImages.value.length;
}

function openPreview() {
  previewOpen.value = true;
}

function closePreview() {
  previewOpen.value = false;
}
</script>

<template>
  <div class="image-preview-container">
    <!-- Thumbnail display -->
    <div class="relative rounded-md overflow-hidden cursor-pointer h-full" @click="openPreview">
      <img 
        :src="currentImage" 
        :alt="alt"
        class="w-full h-full object-cover transition-opacity duration-300"
        @error="$event.target.src = '/images/placeholder-product.jpg'"
      />
      
      <!-- Navigation buttons for thumbnails (only show if multiple images) -->
      <div v-if="processedImages.length > 1" class="absolute bottom-2 right-2 flex gap-1">
        <div class="px-2 py-1 bg-black/50 rounded text-xs text-white">
          {{ currentImageIndex + 1 }}/{{ processedImages.length }}
        </div>
      </div>
      
      <div v-if="processedImages.length > 1" class="absolute inset-0 flex justify-between items-center px-1">
        <Button 
          variant="ghost" 
          size="icon" 
          class="h-7 w-7 rounded-full bg-black/30 hover:bg-black/50 text-white transform transition-transform hover:scale-110"
          @click.stop="previousImage"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </Button>
        <Button 
          variant="ghost" 
          size="icon" 
          class="h-7 w-7 rounded-full bg-black/30 hover:bg-black/50 text-white transform transition-transform hover:scale-110"
          @click.stop="nextImage"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </Button>
      </div>
    </div>

    <!-- Fullscreen preview dialog -->
    <Dialog :open="previewOpen" @update:open="closePreview">
      <DialogContent class="sm:max-w-3xl max-h-[90vh] p-0 bg-background dark:bg-gray-900 overflow-hidden">
        <div class="relative h-full">
          <!-- Main image -->
          <div class="w-full h-full flex items-center justify-center p-4">
            <img 
              :key="currentImageIndex" 
              :src="currentImage" 
              :alt="alt"
              class="max-w-full max-h-[70vh] object-contain transition-all duration-300 image-fade"
              @error="$event.target.src = '/images/placeholder-product.jpg'"
            />
          </div>
          
          <!-- Navigation controls -->
          <div v-if="processedImages.length > 1" class="absolute inset-x-0 bottom-4 flex justify-center gap-2">
            <Button 
              variant="secondary" 
              size="sm"
              @click="previousImage"
              class="rounded-full bg-white/90 dark:bg-black/50 hover:bg-white dark:hover:bg-black/70 transform transition-transform hover:scale-105"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
              Previous
            </Button>
            <Button 
              variant="secondary" 
              size="sm"
              @click="nextImage"
              class="rounded-full bg-white/90 dark:bg-black/50 hover:bg-white dark:hover:bg-black/70 transform transition-transform hover:scale-105"
            >
              Next
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </Button>
          </div>
          
          <!-- Close button -->
          <Button 
            variant="ghost" 
            size="icon" 
            @click="closePreview"
            class="absolute top-2 right-2 h-8 w-8 rounded-full bg-black/30 hover:bg-black/50 text-white transform transition-transform hover:scale-110"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </Button>
          
          <!-- Image counter -->
          <div v-if="processedImages.length > 1" class="absolute top-2 left-2 px-2 py-1 bg-black/50 text-white text-xs rounded">
            {{ currentImageIndex + 1 }}/{{ processedImages.length }}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
.image-preview-container {
  position: relative;
  overflow: hidden;
  width: 100%;
  height: 100%;
}

.image-fade {
  opacity: 0;
  animation: fadeIn 0.3s ease-in-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Add a subtle hover effect */
.cursor-pointer:hover {
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.5);
}

/* Improve button hover transitions */
button {
  transition: all 0.2s ease-in-out;
}

/* Prevent dialog content from jumping */
:deep(.dialog-content) {
  will-change: transform;
}
</style>
