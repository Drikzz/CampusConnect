<script setup>
import { ref, watch, computed } from 'vue';
import { Button } from "@/Components/ui/button";
import { XCircle } from 'lucide-vue-next';

const props = defineProps({
    files: {
        type: Array,
        default: () => []
    },
    multiple: {
        type: Boolean,
        default: false
    },
    accept: {
        type: String,
        default: 'image/*'
    }
});

const emit = defineEmits(['update:files']);

// Store files locally
const localFiles = ref([]);

// Initialize localFiles from props
watch(() => props.files, (newFiles) => {
    if (newFiles && Array.isArray(newFiles)) {
        localFiles.value = [...newFiles];
    }
}, { immediate: true });

// File input ref to trigger programmatically
const fileInput = ref(null);

// Open file dialog
const openFileDialog = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

// Handle file selection
const handleFileSelection = (event) => {
    const newFiles = Array.from(event.target.files || []);
    if (newFiles.length === 0) return;
    
    // If not multiple, replace files
    if (!props.multiple) {
        localFiles.value = newFiles;
    } else {
        // Add to existing files
        localFiles.value = [...localFiles.value, ...newFiles];
    }
    
    // Clear file input to allow selecting the same file again
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    
    // Emit update
    emit('update:files', localFiles.value);
};

// Remove file
const removeFile = (index) => {
    localFiles.value = localFiles.value.filter((_, i) => i !== index);
    emit('update:files', localFiles.value);
};

// Generate preview URLs for images
const previewUrls = computed(() => {
    return localFiles.value.map(file => {
        // If file is a string (URL), return it directly
        if (typeof file === 'string') {
            return file;
        }
        
        // If file is a File object, create URL
        if (file instanceof File) {
            return URL.createObjectURL(file);
        }
        
        return null;
    });
});

// File type check
const isImage = (file) => {
    if (typeof file === 'string') {
        return file.match(/\.(jpeg|jpg|gif|png)$/i) !== null;
    }
    return file.type.startsWith('image/');
};

// Get file name
const getFileName = (file, index) => {
    if (typeof file === 'string') {
        // Extract filename from URL
        return file.split('/').pop();
    }
    return file.name || `File ${index + 1}`;
};
</script>

<template>
    <div class="w-full">
        <!-- Hidden file input -->
        <input
            ref="fileInput"
            type="file"
            class="hidden"
            :multiple="multiple"
            :accept="accept"
            @change="handleFileSelection"
        />
        
        <!-- Upload button -->
        <Button 
            type="button" 
            variant="outline" 
            @click="openFileDialog"
            class="w-full h-32 flex flex-col items-center justify-center border-dashed"
        >
            <span class="text-lg mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </span>
            <span>Click to upload images</span>
            <span class="text-xs text-gray-500 mt-1">You can upload {{ multiple ? 'multiple files' : 'one file' }}</span>
        </Button>
        
        <!-- File previews -->
        <div v-if="localFiles.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
            <div 
                v-for="(file, index) in localFiles" 
                :key="index"
                class="relative group border rounded-md overflow-hidden"
            >
                <!-- Image preview -->
                <img 
                    v-if="isImage(file)" 
                    :src="previewUrls[index]" 
                    :alt="getFileName(file, index)"
                    class="w-full h-24 object-cover" 
                />
                
                <!-- Non-image file -->
                <div v-else class="w-full h-24 flex items-center justify-center bg-gray-100">
                    <span class="text-gray-500">{{ getFileName(file, index) }}</span>
                </div>
                
                <!-- Remove button -->
                <button
                    type="button"
                    @click="removeFile(index)"
                    class="absolute top-1 right-1 bg-white rounded-full p-1 opacity-70 hover:opacity-100"
                >
                    <XCircle class="h-5 w-5 text-red-500" />
                </button>
            </div>
        </div>
    </div>
</template>
