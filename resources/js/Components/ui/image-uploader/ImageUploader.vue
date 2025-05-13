<script setup>
import { ref, computed } from 'vue';
import { useToast } from '@/Components/ui/toast/use-toast';
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";

// Import vue-advanced-cropper directly
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  existingImages: {
    type: Array,
    default: () => []
  },
  maxFiles: {
    type: Number,
    default: 5
  },
  maxFileSize: {
    type: Number,
    default: 5 * 1024 * 1024 // 5MB default
  },
  accept: {
    type: String,
    default: 'image/*'
  },
  multiple: {
    type: Boolean,
    default: true
  },
  hasError: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: [String, Array], // Accept both String and Array types
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'error', 'remove-existing']);

const { toast } = useToast();
const fileInputRef = ref(null);
const isFileDraggedOver = ref(false);

const showCropDialog = ref(false);
const currentFile = ref(null);
const cropperState = ref(null);

const canAddMoreFiles = computed(() => {
  const totalImages = props.modelValue.length + props.existingImages.length;
  return props.multiple && (!props.maxFiles || totalImages < props.maxFiles);
});

const displayError = computed(() => {
  if (!props.errorMessage) return '';
  return Array.isArray(props.errorMessage) ? props.errorMessage[0] : props.errorMessage;
});

const handleDrop = (event) => {
  event.preventDefault();
  event.stopPropagation();
  isFileDraggedOver.value = false;
  
  const files = event.dataTransfer?.files;
  if (files && files.length > 0) {
    processFiles(files);
  }
};

const handleFileInputChange = (event) => {
  const files = event.target?.files;
  if (files && files.length > 0) {
    processFiles(files);
  }
  
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const processFiles = (files) => {
  try {
    const validFiles = Array.from(files).filter(file => {
      if (!file.type.startsWith('image/')) {
        emit('error', 'Please upload only image files');
        toast({
          title: "Invalid file type",
          description: "Please upload only image files",
          variant: "destructive"
        });
        return false;
      }
      
      if (file.size > props.maxFileSize) {
        const maxSizeMB = Math.floor(props.maxFileSize / (1024 * 1024));
        emit('error', `Maximum file size is ${maxSizeMB}MB`);
        toast({
          title: "File too large",
          description: `Maximum file size is ${maxSizeMB}MB`,
          variant: "destructive"
        });
        return false;
      }
      
      return true;
    });
    
    if (validFiles.length === 0) return;
    
    if (validFiles.length > 0) {
      // Open the first file in the Cropper
      currentFile.value = validFiles[0];
      showCropDialog.value = true;
      
      // If multiple files, add the remaining files directly
      if (props.multiple && validFiles.length > 1) {
        const remainingFiles = validFiles.slice(1);
        const newFiles = [...props.modelValue, ...remainingFiles];
        emit('update:modelValue', newFiles);
      }
    }
  } catch (error) {
    console.error('Error processing files:', error);
    toast({
      title: "Error",
      description: "Failed to process images",
      variant: "destructive"
    });
  }
};

const handleCropChange = (result) => {
  cropperState.value = result;
};

const applyCrop = () => {
  if (!cropperState.value || !cropperState.value.canvas) {
    toast({
      title: "Error",
      description: "Failed to crop image",
      variant: "destructive"
    });
    return;
  }

  try {
    // Get the cropped canvas from the result
    const canvas = cropperState.value.canvas;
    
    // Convert to blob
    canvas.toBlob((blob) => {
      if (!blob) {
        toast({
          title: "Error",
          description: "Failed to process cropped image",
          variant: "destructive"
        });
        return;
      }
      
      // Create a file from the blob
      const fileExtension = currentFile.value.name.split('.').pop();
      const filename = `cropped_${Date.now()}.${fileExtension}`;
      const croppedFile = new File([blob], filename, { 
        type: currentFile.value.type,
        lastModified: Date.now()
      });
      
      // Add to model
      if (props.multiple) {
        const newFiles = [...props.modelValue, croppedFile];
        emit('update:modelValue', newFiles);
      } else {
        emit('update:modelValue', [croppedFile]);
      }
      
      // Close dialog
      closeCropDialog();
      
      toast({
        title: "Success",
        description: "Image cropped successfully",
        variant: "success"
      });
    }, currentFile.value.type);
  } catch (error) {
    console.error('Error applying crop:', error);
    toast({
      title: "Error",
      description: "Failed to crop image",
      variant: "destructive"
    });
  }
};

const closeCropDialog = () => {
  showCropDialog.value = false;
  currentFile.value = null;
  cropperState.value = null;
};

const removeFile = (index) => {
  const newFiles = [...props.modelValue];
  newFiles.splice(index, 1);
  emit('update:modelValue', newFiles);
};

const removeExistingImage = (index) => {
  emit('remove-existing', index);
};

const getFilePreview = (file) => {
  if (typeof file === 'string') {
    return file;
  }
  return URL.createObjectURL(file);
};

const handleDragOver = (event) => {
  event.preventDefault();
  event.stopPropagation();
  isFileDraggedOver.value = true;
};

const handleDragLeave = (event) => {
  event.preventDefault();
  event.stopPropagation();
  isFileDraggedOver.value = false;
};

const openFileBrowser = () => {
  fileInputRef.value?.click();
};

const getFileUrl = (file) => {
  if (!file) return null;
  return URL.createObjectURL(file);
};

// Fix storage paths in existing images
const fixStoragePath = (path) => {
  if (!path) return '';
  
  if (path.includes('storage/storage/')) {
    path = path.replace('storage/storage/', 'storage/');
  }
  
  if (path.startsWith('/storage/storage/')) {
    path = path.replace('/storage/storage/', '/storage/');
  }
  
  return path;
};
</script>

<template>
  <div>
    <div 
      :class="{
        'border-primary-color bg-primary-color/5': isFileDraggedOver,
        'border-destructive': hasError
      }"
      class="border-2 border-dashed border-border dark:border-gray-700 rounded-md p-6 bg-background dark:bg-gray-800 transition-colors duration-200"
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      @drop="handleDrop"
      @click="openFileBrowser"
    >
      <div class="flex flex-col items-center justify-center text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-muted-foreground dark:text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        
        <div class="text-sm text-muted-foreground dark:text-gray-400">
          <span class="font-medium text-primary-color">Click to browse</span> or drag and drop
        </div>
        
        <p class="text-xs text-muted-foreground dark:text-gray-400 mt-1">
          PNG, JPG, JPEG or GIF (max. {{ Math.floor(maxFileSize / (1024 * 1024)) }}MB)
        </p>
        
        <input
          ref="fileInputRef"
          type="file"
          :accept="accept"
          :multiple="multiple"
          @change="handleFileInputChange"
          class="hidden"
        />

        <div v-if="!multiple && (modelValue.length > 0 || existingImages.length > 0)" class="mt-2 text-xs text-amber-600">
          New upload will replace existing image
        </div>

        <div v-if="multiple && maxFiles && (modelValue.length > 0 || existingImages.length > 0)" class="mt-2 text-xs text-muted-foreground dark:text-gray-400">
          {{ modelValue.length + existingImages.length }} / {{ maxFiles }} files uploaded
        </div>
      </div>
    </div>
    
    <p v-if="hasError && errorMessage" class="text-destructive text-xs mt-1">
      {{ displayError }}
    </p>
    
    <!-- Display existing images -->
    <div v-if="existingImages.length > 0 || modelValue.length > 0" class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
      <!-- Existing images from the server -->
      <div 
        v-for="(file, index) in existingImages" 
        :key="`existing-${index}`"
        class="relative group border border-border dark:border-gray-700 rounded-md overflow-hidden aspect-square"
      >
        <img 
          :src="fixStoragePath(file)" 
          class="w-full h-full object-cover"
          @error="$event.target.src = '/images/placeholder-product.jpg'"
          :alt="`Existing preview ${index + 1}`"
        />
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
          <Button 
            type="button" 
            variant="destructive" 
            size="icon"
            class="h-8 w-8 rounded-full"
            @click.stop.prevent="removeExistingImage(index)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </Button>
        </div>
      </div>
      
      <!-- New images to upload -->
      <div 
        v-for="(file, index) in modelValue" 
        :key="`new-${index}`"
        class="relative group border border-border dark:border-gray-700 rounded-md overflow-hidden aspect-square"
      >
        <img 
          :src="getFilePreview(file)" 
          class="w-full h-full object-cover"
          @error="$event.target.src = '/images/placeholder-product.jpg'"
          :alt="`New preview ${index + 1}`"
        />
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
          <Button 
            type="button" 
            variant="destructive" 
            size="icon"
            class="h-8 w-8 rounded-full"
            @click.stop.prevent="removeFile(index)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </Button>
        </div>
      </div>
    </div>
    
    <!-- Simplified Cropper Dialog -->
    <Dialog :open="showCropDialog" @update:open="(val) => !val && closeCropDialog()">
      <DialogContent class="max-w-lg">
        <DialogHeader>
          <DialogTitle>Crop Image</DialogTitle>
          <DialogDescription>
            Adjust the crop area to customize your image
          </DialogDescription>
        </DialogHeader>
        
        <div class="my-6 relative overflow-hidden">
          <!-- Direct use of vue-advanced-cropper -->
          <Cropper
            v-if="currentFile"
            :src="getFileUrl(currentFile)"
            :stencil-props="{
              aspectRatio: 1
            }"
            class="h-[300px] w-full"
            @change="handleCropChange"
          />
        </div>
        
        <DialogFooter>
          <Button 
            type="button" 
            variant="outline" 
            @click="closeCropDialog"
            class="bg-white dark:bg-gray-800 border-border dark:border-gray-700 text-foreground dark:text-white"
          >
            Cancel
          </Button>
          <Button 
            type="button"
            @click="applyCrop"
            class="bg-primary-color text-white"
          >
            Apply Crop
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
/* Existing styles */
.border-dashed {
  cursor: pointer;
}

.border-dashed:hover {
  border-color: hsl(var(--primary));
  background-color: rgba(var(--primary-rgb), 0.05);
}

/* Vue Advanced Cropper Styles */
:deep(.vue-advanced-cropper) {
  background-color: transparent !important;
}

:deep(.vue-advanced-cropper__foreground) {
  background-color: rgba(0, 0, 0, 0.4);
}

/* Dark mode adjustments */
.dark :deep(.vue-advanced-cropper__foreground) {
  background-color: rgba(0, 0, 0, 0.6);
}

:deep(.vue-advanced-cropper__boundary) {
  border-color: hsl(var(--primary));
}

/* Add style for the stencil */
:deep(.vue-circle-stencil),
:deep(.vue-rectangle-stencil) {
  border: 2px solid hsl(var(--primary));
}
</style>