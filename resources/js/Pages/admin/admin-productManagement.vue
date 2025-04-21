<script setup>
import { ref, onMounted, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    TrashIcon, 
    PencilIcon,
    XMarkIcon,
    EyeIcon,
    PhotoIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    products: Object, // Change this from Array to Object to support pagination metadata
    categories: Array,
});

// State management refs
const selectedProducts = ref([]);
const search = ref('');
const showProductModal = ref(false);
const currentProduct = ref(null);
const sortField = ref('created_at');
const sortDirection = ref('desc');
const selectedImageIndex = ref(0);
const selectedImage = ref(null);
const imageList = ref([]);
const imageLoadAttempts = ref({}); // Track loading attempts for each image

// Add these new state variables for image handling optimization
const successfullyLoadedImages = ref(new Set());
const failedImages = ref(new Set());
const maxLoadAttempts = ref(2); // Limit retries to prevent infinite loops

// Add these new state variables for edit functionality
const showEditModal = ref(false);
const editForm = ref({
    id: null,
    name: '',
    price: null,
    description: '',
    status: '',
    category_id: null,
    is_buyable: false,
    is_tradable: false
});
const formErrors = ref({});

const uploadedImages = ref([]);
const imagesToRemove = ref([]);
const imagePreviewUrls = ref([]);
const maxImages = ref(5); // Maximum number of images allowed
const isUploading = ref(false);

const productItems = computed(() => {
    // Handle both array and paginated object formats
    return props.products.data || props.products || [];
});

// Update the filteredProducts computed property to use the new productItems
const filteredProducts = computed(() => {
    if (!search.value) return productItems.value;
    const searchLower = search.value.toLowerCase();
    return productItems.value.filter(product => 
        product.name.toLowerCase().includes(searchLower) || 
        getSellerFullName(product.seller)?.toLowerCase().includes(searchLower)
    );
});

// Helper function to get seller's full name
const getSellerFullName = (seller) => {
    if (!seller) return '';
    return `${seller.first_name} ${seller.last_name}`;
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedProducts.value = filteredProducts.value.map(product => product.id);
    } else {
        selectedProducts.value = [];
    }
};

// Enhanced editProduct function to initialize image data
const editProduct = (product) => {
    // Initialize the form with product data
    editForm.value = {
        id: product.id,
        name: product.name || '',
        price: product.price || 0,
        description: product.description || '',
        status: product.status || 'active',
        category_id: product.category_id || null,
        is_buyable: product.is_buyable || false,
        is_tradable: product.is_tradable || false
    };

    // Reset image state
    uploadedImages.value = [];
    imagesToRemove.value = [];
    imagePreviewUrls.value = [];

    // Get all valid images properly filtered
    const allImages = getAllProductImages(product);
    
    // Initialize with existing product images - now with better filtering
    if (allImages.length > 0) {
        // Convert all images to the expected format with proper URLs
        imagePreviewUrls.value = allImages.map(img => ({
            url: img,
            isExisting: true,
            originalUrl: img
        }));
    }

    formErrors.value = {};
    showEditModal.value = true;
};

const deleteProduct = (productId) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('admin.products.delete', productId));
    }
};

const bulkDeleteProducts = () => {
    if (selectedProducts.value.length === 0) {
        alert('Please select at least one product to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedProducts.value.length} selected products?`)) {
        router.delete(route('admin.products.bulk-delete'), {
            data: { ids: selectedProducts.value }
        });
    }
};

// Format price as currency
const formatPrice = (price) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(price);
};

// Enhanced version of showProductDetails with better image handling
const showProductDetails = (product) => {
    currentProduct.value = product;
    showProductModal.value = true;
    
    // Get all product images and deduplicate them
    let images = getAllProductImages(product);
    
    // Filter out any invalid URLs or empty strings before deduplicating
    images = images.filter(img => img && img.trim() !== '');
    
    // Ensure we have unique images only
    const uniqueImages = [...new Set(images)];
    imageList.value = uniqueImages;
    
    // Reset image selection to first image
    selectedImageIndex.value = 0;
    
    if (uniqueImages && uniqueImages.length > 0) {
        // Check if first image is already in failed set
        const firstImage = uniqueImages[0];
        if (failedImages.value.has(firstImage)) {
            // If first image previously failed, try next one or use placeholder
            if (uniqueImages.length > 1) {
                selectedImage.value = uniqueImages[1];
            } else {
                selectedImage.value = '/images/placeholder-product.jpg';
            }
        } else {
            selectedImage.value = firstImage;
        }
        
        // Preload only the first few images for better performance
        uniqueImages.slice(0, 2).forEach((src, index) => {
            // Skip if already successfully loaded or failed
            if (successfullyLoadedImages.value.has(src) || failedImages.value.has(src)) {
                return;
            }
            
            const img = new Image();
            img.onload = () => {
                successfullyLoadedImages.value.add(src);
            };
            img.onerror = () => {
                // Only try one alternative to avoid multiple requests
                const altSrc = `/storage/${src.replace(/^\/storage\/|^\//, '')}`;
                const altImg = new Image();
                altImg.onload = () => {
                    successfullyLoadedImages.value.add(altSrc);
                    // Update the image list with the working URL
                    if (index < imageList.value.length) {
                        imageList.value[index] = altSrc;
                        if (index === selectedImageIndex.value) {
                            selectedImage.value = altSrc;
                        }
                    }
                };
                altImg.onerror = () => {
                    failedImages.value.add(src);
                };
                altImg.src = altSrc;
            };
            img.src = src;
        });
    } else {
        selectedImage.value = null;
    }
};

// Try alternative URLs for an image
const tryAlternativeUrls = (originalSrc, index) => {
    const attempts = imageLoadAttempts.value[index] || 0;
    if (attempts >= 4) return; // Limit attempts
    
    // Create alternative URLs
    const alternatives = [
        `/storage/${originalSrc.replace(/^\/storage\/|^\//, '')}`,
        `/images/${originalSrc.split('/').pop()}`,
        `/images/products/${originalSrc.split('/').pop()}`,
        `/assets/products/${originalSrc.split('/').pop()}`
    ];
    
    // Try next alternative
    const nextUrl = alternatives[attempts];
    imageLoadAttempts.value[index] = attempts + 1;
    
    if (nextUrl) {
        // Update the image list with the alternative URL
        if (index < imageList.value.length) {
            const updatedList = [...imageList.value];
            updatedList[index] = nextUrl;
            imageList.value = updatedList;
            
            // If this was the selected image, update it
            if (index === selectedImageIndex.value) {
                selectedImage.value = nextUrl;
            }
            
            // Try to load it
            const img = new Image();
            img.onload = () => {
                console.log(`Alternative URL ${attempts} successful for image ${index}: ${nextUrl}`);
            };
            img.onerror = () => {
                console.log(`Alternative URL ${attempts} failed for image ${index}: ${nextUrl}`);
                tryAlternativeUrls(originalSrc, index); // Try next alternative
            };
            img.src = nextUrl;
        }
    }
};

// Optimized selectImage function to prevent unnecessary reloads
const selectImage = (image, index) => {
    if (selectedImageIndex.value === index) return; // Skip if already selected
    
    console.log(`Selecting image at index ${index}:`, image);
    selectedImageIndex.value = index;
    selectedImage.value = image;
    
    // If this image URL has already successfully loaded, don't try to preload again
    if (successfullyLoadedImages.value.has(image)) {
        console.log(`Image ${index} already successfully loaded, skipping preload`);
        return;
    }
    
    // If this image URL has already failed, use placeholder immediately
    if (failedImages.value.has(image)) {
        console.log(`Image ${index} previously failed, using placeholder`);
        selectedImage.value = '/images/placeholder-product.jpg';
        return;
    }
    
    // Only attempt to preload if we haven't already tried
    if (!imageLoadAttempts.value[index]) {
        const img = new Image();
        img.onload = () => {
            console.log(`Successfully loaded image ${index} on selection`);
            successfullyLoadedImages.value.add(image);
        };
        img.onerror = () => {
            console.log(`Failed to load image ${index} on selection`);
            // Only try one alternative to avoid multiple requests
            const altSrc = `/storage/${image.replace(/^\/storage\/|^\//, '')}`;
            selectedImage.value = altSrc;
            
            // Try the alternative URL once
            const altImg = new Image();
            altImg.onload = () => {
                successfullyLoadedImages.value.add(altSrc);
            };
            altImg.onerror = () => {
                failedImages.value.add(image);
                selectedImage.value = '/images/placeholder-product.jpg';
            };
            altImg.src = altSrc;
        };
        imageLoadAttempts.value[index] = true;
        img.src = image;
    }
};

// Improved image error handling with better caching and limiting retries
const handleImageError = (event) => {
    const img = event.target;
    const originalSrc = img.getAttribute('data-original-src') || img.src;
    const attemptCount = parseInt(img.getAttribute('data-attempt-count') || '0');
    
    // Check if this image URL has already failed completely
    if (failedImages.value.has(originalSrc)) {
        img.src = '/images/placeholder-product.jpg';
        img.classList.add('error-image');
        return;
    }
    
    // Prevent infinite loops - max attempts
    if (attemptCount >= maxLoadAttempts.value) {
        console.log(`Reached max attempts for ${originalSrc}, showing placeholder`);
        img.src = '/images/placeholder-product.jpg';
        img.classList.add('error-image');
        failedImages.value.add(originalSrc);
        return;
    }
    
    // Set up alternative paths to try - only one per attempt to reduce load
    const alternatives = [
        `/storage/${originalSrc.replace(/^\/storage\/|^\//, '')}`,
        `/images/placeholder-product.jpg` // Last resort is always placeholder
    ];
    
    // Try only the next alternative URL based on attempt count
    const nextUrl = alternatives[attemptCount];
    
    if (nextUrl) {
        console.log(`Trying alternative path ${attemptCount}: ${nextUrl}`);
        img.setAttribute('data-original-src', originalSrc);
        img.setAttribute('data-attempt-count', (attemptCount + 1).toString());
        img.src = nextUrl;
    } else {
        // Fall back to placeholder
        img.src = '/images/placeholder-product.jpg';
        img.classList.add('error-image');
        failedImages.value.add(originalSrc);
    }
};

// Get all images from a product with improved robustness and filtering
const getAllProductImages = (product) => {
    try {
        // First check the formatted images from the backend
        if (product.formatted_images && Array.isArray(product.formatted_images) && product.formatted_images.length > 0) {
            // Filter out any null/undefined/empty entries and ensure URLs are valid
            return product.formatted_images
                .filter(img => img && img.trim() !== '') // Remove nulls/undefined/empty strings
                .map(img => ensureValidImageUrl(img));
        }
        
        // Then check if images is an array directly
        if (product.images) {
            if (Array.isArray(product.images) && product.images.length > 0) {
                return product.images
                    .filter(img => img && img.trim() !== '')
                    .map(img => ensureValidImageUrl(img));
            }
            
            // If images is a string, try to parse as JSON
            if (typeof product.images === 'string') {
                try {
                    const parsedImages = JSON.parse(product.images);
                    if (Array.isArray(parsedImages) && parsedImages.length > 0) {
                        return parsedImages
                            .filter(img => img && img.trim() !== '')
                            .map(img => ensureValidImageUrl(img));
                    }
                    
                    // Single image as string (if not empty)
                    if (product.images.trim() !== '') {
                        return [ensureValidImageUrl(product.images)];
                    }
                } catch (e) {
                    // If parsing failed, treat as a single image path (if not empty)
                    if (product.images.trim() !== '') {
                        return [ensureValidImageUrl(product.images)];
                    }
                }
            }
        }
    } catch (error) {
        console.error("Error processing product images:", error);
    }
    
    // Default fallback - empty array
    return [];
};

// Ensure image URL is valid
const ensureValidImageUrl = (imagePath) => {
    if (!imagePath) return null;
    
    // If already a valid URL, return as is
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // Clean up path from double slashes or other issues
    const cleanPath = imagePath.replace(/\/+/g, '/').replace(/^\//, '');
    
    // If path already includes storage, just ensure it has proper URL format
    if (imagePath.includes('storage/')) {
        return imagePath.startsWith('/') ? imagePath : `/${imagePath}`;
    }
    
    // Add storage path if needed
    return `/storage/${cleanPath}`;
};

// Close product details modal
const closeModal = () => {
    showProductModal.value = false;
    currentProduct.value = null;
};

// Close the edit modal
const closeEditModal = () => {
    showEditModal.value = false;
    formErrors.value = {};
};

// Handle image selection
const handleImageUpload = (event) => {
    const files = event.target.files;
    if (!files || files.length === 0) return;
    
    // Check if we'd exceed the maximum allowed images
    if (imagePreviewUrls.value.length + files.length > maxImages.value) {
        alert(`You can only upload a maximum of ${maxImages.value} images.`);
        return;
    }
    
    Array.from(files).forEach(file => {
        // Only allow image files
        if (!file.type.startsWith('image/')) {
            alert('Please upload only image files');
            return;
        }

        // Size limit (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size should not exceed 5MB');
            return;
        }

        // Add to uploaded images
        uploadedImages.value.push(file);
        
        // Generate preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviewUrls.value.push({
                url: e.target.result,
                isExisting: false,
                file: file
            });
        };
        reader.readAsDataURL(file);
    });

    // Clear the input so the same file can be selected again if needed
    event.target.value = null;
};

// Remove image (works for both new and existing images)
const removeImage = (index) => {
    const image = imagePreviewUrls.value[index];
    
    // If it's an existing image, mark it for removal from the server
    if (image.isExisting && image.originalUrl) {
        imagesToRemove.value.push(image.originalUrl);
    }
    
    // If it's a newly uploaded image, remove it from uploadedImages
    if (!image.isExisting && uploadedImages.value.length > 0) {
        const fileIndex = uploadedImages.value.findIndex(f => f === image.file);
        if (fileIndex !== -1) {
            uploadedImages.value.splice(fileIndex, 1);
        }
    }
    
    // Remove from preview URLs
    imagePreviewUrls.value.splice(index, 1);
};

// Enhanced save product edit function
const saveProductEdit = () => {
    formErrors.value = {};
    isUploading.value = true;
    
    // Basic client-side validation
    let hasErrors = false;
    if (!editForm.value.name) {
        formErrors.value.name = 'Product name is required';
        hasErrors = true;
    }
    if (!editForm.value.price && editForm.value.price !== 0) {
        formErrors.value.price = 'Price is required';
        hasErrors = true;
    } else if (isNaN(parseFloat(editForm.value.price)) || parseFloat(editForm.value.price) < 0) {
        formErrors.value.price = 'Price must be a positive number';
        hasErrors = true;
    }
    if (!editForm.value.description) {
        formErrors.value.description = 'Description is required';
        hasErrors = true;
    }
    if (!editForm.value.status) {
        formErrors.value.status = 'Status is required';
        hasErrors = true;
    }

    if (hasErrors) {
        isUploading.value = false;
        return;
    }

    // Create FormData object to handle file uploads
    const formData = new FormData();
    
    // Add all the form fields
    Object.keys(editForm.value).forEach(key => {
        if (key !== 'images') {
            // Handle boolean values specially
            if (typeof editForm.value[key] === 'boolean') {
                formData.append(key, editForm.value[key] ? '1' : '0');
            } else if (editForm.value[key] !== null) {
                formData.append(key, editForm.value[key]);
            }
        }
    });
    
    // Add newly uploaded images
    uploadedImages.value.forEach((file, index) => {
        formData.append(`new_images[${index}]`, file);
    });
    
    // Add list of images to remove
    imagesToRemove.value.forEach((image, index) => {
        formData.append(`images_to_remove[${index}]`, image);
    });
    
    // Add images that should be kept (for reordering)
    const imagesToKeep = imagePreviewUrls.value
        .filter(img => img.isExisting && !imagesToRemove.value.includes(img.originalUrl))
        .map(img => img.originalUrl);
    
    imagesToKeep.forEach((image, index) => {
        formData.append(`images_to_keep[${index}]`, image);
    });
    
    // Log the URL we're submitting to for debugging
    const url = route('admin.products.update', editForm.value.id);
    console.log('Submitting to URL:', url);
    
    // Submit form via Inertia with FormData for file uploads
    router.post(url, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-HTTP-Method-Override': 'PUT'
        },
        onSuccess: () => {
            showEditModal.value = false;
            isUploading.value = false;
            alert('Product updated successfully!');
        },
        onError: (errors) => {
            formErrors.value = errors;
            console.error('Error updating product:', errors);
            isUploading.value = false;
        },
        preserveState: true,
        forceFormData: true
    });
};

// Handle sorting
const sort = (field) => {
    sortDirection.value = sortField.value === field && sortDirection.value === 'asc' ? 'desc' : 'asc';
    sortField.value = field;
    // Sorting functionality would go here if you have server-side sorting
};

// Computed properties
const isAllSelected = computed(() => {
    return filteredProducts.value.length > 0 && selectedProducts.value.length === filteredProducts.value.length;
});

// Get first image or return null
const getFirstImage = (product) => {
    // Check if product has formatted_images
    if (product.formatted_images && product.formatted_images.length > 0) {
        return product.formatted_images[0];
    }
    
    // Check if product has images array
    if (product.images) {
        if (Array.isArray(product.images) && product.images.length > 0) {
            return product.images[0];
        }
        // If images is a string (JSON), try to parse it
        if (typeof product.images === 'string') {
            try {
                const parsedImages = JSON.parse(product.images);
                if (Array.isArray(parsedImages) && parsedImages.length > 0) {
                    return parsedImages[0];
                }
                return product.images; // Just return the string itself
            } catch {
                return product.images; // If not valid JSON, assume it's a single image path
            }
        }
    }
    return null;
};

// Check if product has a valid image
const hasValidImage = (product) => {
    return getFirstImage(product) !== null;
};

// Load image with fallback handling
const loadImageWithFallback = (image, index) => {
    return {
        src: image,
        error: false,
        loading: true,
        index
    };
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl md:text-2xl font-bold text-foreground mb-4">Product Management</h1>
        
        <div class="bg-card rounded-lg p-3 md:p-4 mb-6 shadow">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 space-y-3 md:space-y-0">
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Search</label>
                    <input 
                        type="text" 
                        v-model="search" 
                        class="w-full p-2 border rounded bg-background text-foreground"
                        placeholder="Search products..."
                    />
                </div>
            </div>
            
            <div class="overflow-x-auto -mx-3 md:mx-0">
                <table class="min-w-full bg-card border border-border">
                    <thead>
                        <tr class="bg-muted text-sm font-semibold">
                            <th class="py-2 px-3 border-b border-border">
                                <input 
                                    type="checkbox"
                                    :checked="isAllSelected" 
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="py-2 px-3 border-b border-border text-left">Image</th>
                            <th 
                                @click="sort('name')"
                                class="py-2 px-3 border-b border-border text-left cursor-pointer"
                            >
                                Name {{ sortField === 'name' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th 
                                @click="sort('price')"
                                class="py-2 px-3 border-b border-border text-left cursor-pointer"
                            >
                                Price {{ sortField === 'price' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="py-2 px-3 border-b border-border text-left hidden md:table-cell">Seller</th>
                            <th class="py-2 px-3 border-b border-border text-left">Status</th>
                            <th 
                                @click="sort('created_at')"
                                class="py-2 px-3 border-b border-border text-left cursor-pointer hidden sm:table-cell"
                            >
                                Date {{ sortField === 'created_at' ? (sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </th>
                            <th class="py-2 px-3 border-b border-border text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-muted/50">
                            <td class="py-2 px-3 border-b border-border">
                                <input 
                                    type="checkbox" 
                                    v-model="selectedProducts" 
                                    :value="product.id"
                                />
                            </td>
                            <td class="py-2 px-3 border-b border-border">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 bg-muted/50 rounded overflow-hidden flex items-center justify-center">
                                    <img 
                                        v-if="hasValidImage(product)" 
                                        :src="getFirstImage(product)" 
                                        :data-original-src="getFirstImage(product)"
                                        alt="Product image"
                                        @error="handleImageError"
                                        class="w-full h-full object-cover"
                                    >
                                    <PhotoIcon v-else class="w-6 h-6 sm:w-8 sm:h-8 text-muted-foreground" />
                                </div>
                            </td>
                            <td class="py-2 px-3 border-b border-border text-sm">{{ product.name }}</td>
                            <td class="py-2 px-3 border-b border-border text-sm">{{ formatPrice(product.price) }}</td>
                            <td class="py-2 px-3 border-b border-border text-sm hidden md:table-cell">
                                {{ product.seller ? `${product.seller.first_name} ${product.seller.last_name}` : 'Unknown' }}
                            </td>
                            <td class="py-2 px-3 border-b border-border">
                                <span :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    product.status?.toLowerCase() === 'active' ? 'bg-green-100 text-green-800' : 
                                    product.status?.toLowerCase() === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800'
                                ]">
                                    {{ product.status }}
                                </span>
                            </td>
                            <td class="py-2 px-3 border-b border-border text-sm hidden sm:table-cell">{{ formatDate(product.created_at) }}</td>
                            <td class="py-2 px-3 border-b border-border">
                                <div class="flex items-center space-x-1 sm:space-x-3">
                                    <!-- View details button with eye icon -->
                                    <div class="relative group">
                                        <button 
                                            @click="showProductDetails(product)"
                                            class="p-1 sm:p-1.5 rounded hover:bg-blue-100 transition-colors duration-200"
                                        >
                                            <EyeIcon class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 group-hover:text-blue-800 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            View
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>

                                    <!-- Edit product button with pen icon -->
                                    <div class="relative group">
                                        <button 
                                            @click="editProduct(product)"
                                            class="p-1 sm:p-1.5 rounded hover:bg-green-100 transition-colors duration-200"
                                        >
                                            <PencilIcon class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 group-hover:text-green-800 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Edit
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>

                                    <!-- Delete button with tooltip -->
                                    <div class="relative group">
                                        <button 
                                            @click="deleteProduct(product.id)"
                                            class="p-1 sm:p-1.5 rounded hover:bg-red-100 transition-colors duration-200"
                                        >
                                            <TrashIcon class="w-4 h-4 sm:w-5 sm:h-5 text-red-600 transition-colors duration-200" />
                                        </button>
                                        <div class="absolute z-10 w-max bg-black text-white text-xs rounded py-1 px-2 left-1/2 -translate-x-1/2 bottom-full mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Delete
                                            <svg class="absolute text-black h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"><polygon points="0,0 127.5,127.5 255,0" fill="currentColor"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filteredProducts.length">
                            <td colspan="8" class="text-center py-4 text-muted-foreground">No products found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                <div class="w-full sm:w-auto">
                    <button 
                        v-if="selectedProducts.length > 0"
                        @click="bulkDeleteProducts"
                        class="px-3 py-1 bg-destructive text-destructive-foreground rounded hover:bg-destructive/90 text-sm"
                    >
                        Delete Selected ({{ selectedProducts.length }})
                    </button>
                </div>
            </div>

            
            <!-- Enhanced Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0" v-if="props.products && props.products.data">
                <div class="text-xs sm:text-sm text-muted-foreground">
                    Showing {{ props.products.from || 1 }} 
                    to {{ props.products.to || props.products.data.length }} 
                    of {{ props.products.total || props.products.data.length }} results
                </div>
                
                <div class="flex space-x-2">
                    <!-- Previous page button -->
                    <Link 
                        v-if="props.products.prev_page_url"
                        :href="props.products.prev_page_url"
                        class="px-3 sm:px-4 py-1 sm:py-2 bg-background border border-border rounded hover:bg-muted text-foreground text-sm"
                    >
                        Previous
                    </Link>
                    
                    <!-- Current page indicator -->
                    <span class="px-3 sm:px-4 py-1 sm:py-2 bg-muted border border-border rounded text-sm">
                        Page {{ props.products.current_page || 1 }}
                    </span>
                    
                    <!-- Next page button -->
                    <Link 
                        v-if="props.products.next_page_url"
                        :href="props.products.next_page_url"
                        class="px-3 sm:px-4 py-1 sm:py-2 bg-background border border-border rounded hover:bg-muted text-foreground text-sm"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
        
        <!-- Product Details Modal -->
        <div v-if="showProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Product Details
                    </h3>
                    <button 
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-4 space-y-4" v-if="currentProduct">
                    <!-- Product Image Gallery with improved display -->
                    <div class="flex justify-center mb-4">
                        <div class="w-full h-60 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                            <template v-if="selectedImage">
                                <img 
                                    :key="'main-' + selectedImageIndex"
                                    :src="selectedImage"
                                    :data-original-src="selectedImage"
                                    alt="Product image"
                                    @error="handleImageError"
                                    @load="() => successfullyLoadedImages.add(selectedImage)"
                                    class="max-w-full max-h-full object-contain"
                                    loading="eager"
                                    data-attempt-count="0"
                                />
                            </template>
                            <div v-else class="flex items-center justify-center">
                                <PhotoIcon class="h-16 w-16 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image thumbnails with improved image handling -->
                    <div v-if="imageList.length > 1" 
                        class="flex space-x-2 overflow-x-auto pb-2 thumbnail-container">
                        <div 
                            v-for="(image, index) in imageList" 
                            :key="'thumb-' + index"
                            class="w-16 h-16 flex-shrink-0 rounded overflow-hidden cursor-pointer border-2 transition-all duration-200"
                            :class="{'border-primary-color': index === selectedImageIndex, 'border-transparent hover:border-gray-300': index !== selectedImageIndex}"
                            @click="selectImage(image, index)"
                        >
                            <img 
                                :src="failedImages.has(image) ? '/images/placeholder-product.jpg' : image"
                                :data-original-src="image"
                                alt="Product thumbnail" 
                                @error="handleImageError"
                                @load="() => successfullyLoadedImages.add(image)"
                                class="w-full h-full object-cover"
                                loading="lazy"
                                data-attempt-count="0"
                            />
                        </div>
                    </div>
                    
                    <div class="border-b pb-3">
                        <h4 class="text-xl font-medium">{{ currentProduct.name }}</h4>
                        <p class="text-lg text-green-600 font-medium mt-1">
                            {{ formatPrice(currentProduct.price) }}
                        </p>
                    </div>
                    
                    <!-- Product information -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Status</label>
                            <p>
                                <span :class="[
                                    'px-1.5 py-0.5 text-xs rounded-full',
                                    currentProduct.status?.toLowerCase() === 'active' ? 'bg-green-100 text-green-800' : 
                                    currentProduct.status?.toLowerCase() === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800'
                                ]">
                                    {{ currentProduct.status }}
                                </span>
                            </p>
                        </div>
                        
                        <!-- Seller -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Seller</label>
                            <p class="text-gray-900">
                                {{ currentProduct.seller ? `${currentProduct.seller.first_name} ${currentProduct.seller.last_name}` : 'Unknown Seller' }}
                            </p>
                        </div>
                        
                        <!-- Stock -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Stock</label>
                            <p class="text-gray-900">{{ currentProduct.stock || 'Not available' }}</p>
                        </div>
                        
                        <!-- Date Added -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Date Added</label>
                            <p class="text-gray-900">{{ formatDate(currentProduct.created_at) }}</p>
                        </div>
                        
                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Category</label>
                            <p class="text-gray-900">
                                {{ currentProduct.category?.name || 'Uncategorized' }}
                            </p>
                        </div>
                        
                        <!-- Product Type -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Product Type</label>
                            <p class="text-gray-900">
                                <span v-if="currentProduct.is_buyable && currentProduct.is_tradable">Buyable & Tradable</span>
                                <span v-else-if="currentProduct.is_buyable">Buyable</span>
                                <span v-else-if="currentProduct.is_tradable">Tradable</span>
                                <span v-else>Not specified</span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                        <p class="text-sm text-gray-700">
                            {{ currentProduct.description || 'No description provided' }}
                        </p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-3 py-2 border-t flex justify-end">
                    <button 
                        @click="closeModal"
                        class="px-3 py-1 text-sm bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-1 focus:ring-gray-400"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Product Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-auto overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Edit Product
                    </h3>
                    <button 
                        @click="closeEditModal"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Modal Body / Edit Form -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <!-- Product Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                <input 
                                    type="text" 
                                    v-model="editForm.name"
                                    class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                                    :class="{ 'border-red-500': formErrors.name }"
                                />
                                <p v-if="formErrors.name" class="text-red-500 text-xs mt-1">{{ formErrors.name }}</p>
                            </div>
                            
                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                <input 
                                    type="number" 
                                    v-model="editForm.price"
                                    step="0.01"
                                    min="0"
                                    class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                                    :class="{ 'border-red-500': formErrors.price }"
                                />
                                <p v-if="formErrors.price" class="text-red-500 text-xs mt-1">{{ formErrors.price }}</p>
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select 
                                    v-model="editForm.status"
                                    class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                                    :class="{ 'border-red-500': formErrors.status }"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    
                                </select>
                                <p v-if="formErrors.status" class="text-red-500 text-xs mt-1">{{ formErrors.status }}</p>
                            </div>
                            
                            <!-- Product Type -->
                            <div class="flex space-x-4">
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" v-model="editForm.is_buyable" class="rounded border-gray-300 text-primary-color focus:border-primary-color focus:ring focus:ring-primary-color focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Buyable</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" v-model="editForm.is_tradable" class="rounded border-gray-300 text-primary-color focus:border-primary-color focus:ring focus:ring-primary-color focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Tradable</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea 
                                    v-model="editForm.description"
                                    rows="4"
                                    class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-color"
                                    :class="{ 'border-red-500': formErrors.description }"
                                ></textarea>
                                <p v-if="formErrors.description" class="text-red-500 text-xs mt-1">{{ formErrors.description }}</p>
                            </div>
                        </div>
                        
                        <!-- Right Column - Image Management -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Images
                                    <span class="text-gray-500 text-xs ml-2">(Max {{ maxImages }})</span>
                                </label>
                                
                                <!-- Current Images Display with improved validation -->
                                <div class="mb-3">
                                    <div v-if="imagePreviewUrls.length > 0" class="grid grid-cols-3 gap-2">
                                        <div
                                            v-for="(preview, index) in imagePreviewUrls" 
                                            :key="'preview-' + index"
                                            class="relative border rounded-lg overflow-hidden group h-28"
                                        >
                                            <img 
                                                :src="preview.url" 
                                                :data-original-src="preview.url"
                                                :alt="`Product image ${index + 1}`"
                                                @error="handleImageError"
                                                class="w-full h-full object-contain"
                                                data-attempt-count="0"
                                            />
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                                                <button 
                                                    @click="removeImage(index)"
                                                    type="button"
                                                    class="bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                                    title="Remove image"
                                                >
                                                    <XMarkIcon class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p v-else class="text-gray-500 text-sm">No images currently added to this product.</p>
                                </div>
                                
                                <!-- Image Upload Button -->
                                <div v-if="imagePreviewUrls.length < maxImages" class="flex items-center justify-center">
                                    <label
                                        for="image-upload"
                                        class="bg-gray-200 hover:bg-gray-300 transition-colors duration-150 cursor-pointer flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-dashed border-gray-400"
                                    >
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <span>Add Images</span>
                                    </label>
                                    <input 
                                        id="image-upload" 
                                        type="file" 
                                        accept="image/*" 
                                        multiple 
                                        class="hidden" 
                                        @change="handleImageUpload"
                                    />
                                </div>
                                
                                <p v-else class="text-yellow-600 text-sm mt-2">
                                    Maximum number of images reached ({{ maxImages }}).
                                    Remove an existing image to add more.
                                </p>
                                
                                <p v-if="formErrors.images" class="text-red-500 text-xs mt-1">{{ formErrors.images }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form-level errors -->
                    <div v-if="formErrors.error" class="bg-red-50 border-l-4 border-red-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <!-- Error icon -->
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    {{ formErrors.error }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 border-t flex justify-end space-x-3">
                    <button 
                        @click="closeEditModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100"
                        :disabled="isUploading"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="saveProductEdit"
                        class="px-4 py-2 bg-primary-color border border-transparent rounded-md text-sm font-medium text-white hover:bg-primary-color/90 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-1"
                        :disabled="isUploading"
                    >
                        <span v-if="isUploading" class="inline-block animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                        <span>{{ isUploading ? 'Saving...' : 'Save Changes' }}</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- ...existing modals... -->
    </AdminLayout>
</template>

<style scoped>
/* Add some CSS to enhance the hover effect */
button:hover svg {
    color: #ef4444 !important; /* This is the Tailwind red-500 color */
}

.group:hover button {
    background-color: #fee2e2 !important; /* This is the Tailwind red-100 color */
}

/* Override for view button */
button:hover .text-blue-600 {
    color: #1d4ed8 !important; /* Tailwind blue-700 */
}

.group:hover button .text-blue-600 ~ div {
    background-color: #dbeafe !important; /* Tailwind blue-100 */
}

/* Override for edit button */
button:hover .text-green-600 {
    color: #15803d !important; /* Tailwind green-700 */
}

.group:hover button .text-green-600 ~ div {
    background-color: #dcfce7 !important; /* Tailwind green-100 */
}

/* Add styling for the product image display */
.error-image {
    opacity: 0.7;
    filter: grayscale(50%);
}

img {
    transition: opacity 0.3s ease;
}

img[src*="placeholder"] {
    object-fit: scale-down;
    padding: 0.5rem;
}

/* Add styling for the image gallery */
.image-gallery-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.image-gallery-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 10px;
    z-index: 10;
}

.gallery-nav-button {
    background-color: rgba(255,255,255,0.8);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.gallery-nav-button:hover {
    background-color: white;
}

/* Thumbnail styling */
.thumbnail-active {
    border-color: theme('colors.primary-color');
    transform: scale(1.05);
}

.thumbnail-container {
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #CBD5E0 #F7FAFC;
}

.thumbnail-container::-webkit-scrollbar {
    height: 6px;
}

.thumbnail-container::-webkit-scrollbar-track {
    background: #F7FAFC;
}

.thumbnail-container::-webkit-scrollbar-thumb {
    background-color: #CBD5E0;
    border-radius: 3px;
}

/* Image gallery specific styles */
.thumbnail-container {
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #CBD5E0 #F7FAFC;
    padding: 4px;
}

.thumbnail-container > div {
    min-width: 64px; /* Ensure thumbnails have minimum width */
    position: relative;
}

/* Add a hover effect to make it clearer which image is being selected */
.thumbnail-container > div:hover::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255,255,255,0.2);
    pointer-events: none;
}

/* Make the currently selected thumbnail stand out more */
.border-primary-color {
    box-shadow: 0 0 0 2px theme('colors.primary-color');
    transform: scale(1.05);
    z-index: 2;
}

/* Additional image loading improvements */
@keyframes placeholderPulse {
    0% { opacity: 0.6; }
    50% { opacity: 0.8; }
    100% { opacity: 0.6; }
}

.error-image {
    opacity: 0.7;
    filter: grayscale(70%);
}

img {
    transition: opacity 0.3s ease;
}

/* Apply a different style to thumbnails */
.thumbnail-container img {
    transition: all 0.3s ease;
}

.thumbnail-container img:hover {
    transform: scale(1.05);
}

/* Style for active thumbnail */
.border-primary-color {
    box-shadow: 0 0 0 2px theme('colors.primary-color');
    transform: scale(1.05);
    z-index: 5;
}

/* Add new styles for image management */
.image-upload-container {
    border: 2px dashed #ddd;
    padding: 20px;
    text-align: center;
    border-radius: 8px;
    background-color: #f9f9f9;
    cursor: pointer;
    transition: all 0.2s;
}

.image-upload-container:hover {
    border-color: #aaa;
    background-color: #f5f5f5;
}

.image-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
}

.image-preview-item {
    position: relative;
    border-radius: 4px;
    overflow: hidden;
    padding-top: 100%; /* 1:1 Aspect Ratio */
}

.image-preview-item img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
}

.image-preview-item:hover .image-remove-btn {
    opacity: 1;
}

/* Improve placeholder styling */
img[src*="placeholder"] {
    object-fit: contain;
    padding: 0.75rem;
    background-color: #f3f4f6;
}

/* Add styling to indicate loaded images vs placeholders */
.error-image {
    opacity: 0.8;
    filter: grayscale(40%);
    border: 1px solid #f3f4f6;
}
</style>