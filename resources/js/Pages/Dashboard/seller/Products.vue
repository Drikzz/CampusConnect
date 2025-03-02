<template>
  <DashboardLayout :user="user" :stats="stats">
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">My Products</h2>
        <Button @click="showAddForm">Add Product</Button>
      </div>

      <!-- Products Table and Empty State -->
      <div v-if="formattedProducts.length > 0">
        <DataTable
          ref="dataTable"
          :columns="[
            { key: 'product', label: 'Product' },
            { key: 'category', label: 'Category' },
            { key: 'price', label: 'Price', sortable: true },
            { key: 'stock', label: 'Stock', sortable: true },
            { key: 'status', label: 'Status', sortable: true },
            { key: 'availability', label: 'Availability' },
            { key: 'actions', label: 'Actions' }
          ]"
          :data="formattedProducts"
          :get-actions="getActions"
          @bulk-delete="handleBulkDelete"
          @bulk-restore="handleBulkRestore"
          @bulk-force-delete="handleBulkForceDelete"
        />
      </div>
      <div v-else class="bg-gray-50 p-10 text-center rounded-lg">
        <p class="text-gray-500">No products found. Add your first product to get started.</p>
      </div>

      <!-- Product Form Dialog -->
      <Dialog :open="showDialog" @update:open="closeDialog">
        <DialogContent class="flex flex-col max-h-[90vh] w-full max-w-3xl p-0">
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <DialogTitle class="text-lg font-semibold">
              {{ isEditing ? 'Edit' : 'Add' }} Product
            </DialogTitle>
            <DialogClose class="text-gray-400 hover:text-gray-500 focus:outline-none">
              <!-- <X class="h-5 w-5" />
              <span class="sr-only">Close</span> -->
            </DialogClose>
          </div>

          <div class="flex-1 overflow-y-auto p-4">
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div class="grid grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                  <!-- Name -->
                  <div>
                    <Label for="name">Product Name</Label>
                    <Input 
                      id="name" 
                      v-model="form.name" 
                      :disabled="isSubmitting"
                      :class="{'border-red-500': formErrors.name}"
                    />
                    <p v-if="formErrors.name" class="text-red-500 text-xs mt-1">
                      {{ formErrors.name }}
                    </p>
                  </div>

                  <!-- Category Selection -->
                  <div>
                    <Label>Category</Label>
                    <Popover v-model:open="categoryOpen">
                      <PopoverTrigger as-child>
                        <Button
                          variant="outline"
                          role="combobox"
                          :disabled="isSubmitting"
                          :aria-expanded="categoryOpen"
                          class="w-full justify-between"
                        >
                          {{ props.categories.find(c => c.id.toString() === form.category)?.name || 'Select category...' }}
                          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent class="w-full p-0">
                        <Command>
                          <CommandInput placeholder="Search category..." v-model="categorySearch" />
                          <CommandEmpty>No category found.</CommandEmpty>
                          <CommandGroup>
                            <ScrollArea class="h-fit">
                              <CommandItem
                                v-for="category in filteredCategories"
                                :key="category.value"
                                :value="category.value"
                                @select="selectCategory(category.value)"
                              >
                                <Check
                                  :class="[
                                    'mr-2 h-4 w-4',
                                    form.category === category.value ? 'opacity-100' : 'opacity-0'
                                  ]"
                                />
                                {{ category.label }}
                              </CommandItem>
                            </ScrollArea>
                          </CommandGroup>
                        </Command>
                      </PopoverContent>
                    </Popover>
                    <p v-if="formErrors.category" class="text-red-500 text-xs mt-1">
                      {{ formErrors.category }}
                    </p>
                  </div>

                  <!-- Price, Discount, and Stock in one row -->
                  <div class="grid grid-cols-3 gap-4">
                    <div>
                      <Label for="price">Price (₱)</Label>
                      <Input 
                        id="price" 
                        type="number" 
                        v-model="form.price"
                        min="0"
                        step="0.01"
                        :disabled="isSubmitting"
                        :class="{'border-red-500': formErrors.price}"
                      />
                      <p v-if="formErrors.price" class="text-red-500 text-xs mt-1">
                        {{ formErrors.price }}
                      </p>
                    </div>

                    <div>
                      <Label for="discount">Discount (%)</Label>
                      <Input 
                        id="discount" 
                        type="number" 
                        v-model="form.discount"
                        min="0"
                        max="100"
                        :disabled="isSubmitting"
                        :class="{'border-red-500': formErrors.discount}"
                      />
                      <p v-if="formErrors.discount" class="text-red-500 text-xs mt-1">
                        {{ formErrors.discount }}
                      </p>
                      <p class="text-xs text-gray-500 mt-1" v-if="form.price && form.discount">
                        Final price: ₱{{ calculateDiscountedPrice }}
                      </p>
                    </div>

                    <div>
                      <Label for="stock">Stock</Label>
                      <Input 
                        id="stock" 
                        type="number" 
                        v-model="form.stock"
                        min="0"
                        :disabled="isSubmitting"
                        :class="{'border-red-500': formErrors.stock}"
                      />
                      <p v-if="formErrors.stock" class="text-red-500 text-xs mt-1">
                        {{ formErrors.stock }}
                      </p>
                    </div>
                  </div>

                  <!-- Add Status field only in edit mode -->
                  <div v-if="isEditing" class="space-y-2">
                    <Label>Status</Label>
                    <div class="flex space-x-4">
                      <label v-for="status in statusOptions" :key="status" class="flex items-center space-x-2">
                        <input
                          type="radio"
                          :value="status"
                          v-model="form.status"
                          name="status"
                          class="text-primary-600 focus:ring-primary-500"
                        />
                        <span>{{ status }}</span>
                      </label>
                    </div>
                  </div>

                  <!-- Description with static height -->
                  <div>
                    <Label for="description">Description</Label>
                    <Textarea 
                      id="description" 
                      v-model="form.description"
                      rows="4"
                      :disabled="isSubmitting"
                    />
                  </div>

                  <!-- Trade Availability -->
                  <div>
                    <Label class="mb-2">Availability</Label>
                    <RadioGroup v-model="form.trade_availability" class="grid grid-cols-3 gap-2">
                      <div class="flex items-center space-x-2">
                        <RadioGroupItem value="buy" id="buy" />
                        <Label for="buy" class="text-sm">For Sale</Label>
                      </div>
                      <div class="flex items-center space-x-2">
                        <RadioGroupItem value="trade" id="trade" />
                        <Label for="trade" class="text-sm">For Trade</Label>
                      </div>
                      <div class="flex items-center space-x-2">
                        <RadioGroupItem value="both" id="both" />
                        <Label for="both" class="text-sm">Both</Label>
                      </div>
                    </RadioGroup>
                  </div>

                  <!-- Add TagSelect component -->
                  <TagSelect
                    v-model="form.tags"
                    :available-tags="availableTags"
                    :error="formErrors.tags"
                  />
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                  <Label class="block mb-2">Product Images</Label>
                  <!-- Main Image -->
                  <div class="mb-4">
                    <div class="aspect-video relative border rounded-lg overflow-hidden group">
                      <img 
                        v-if="form.imagesPreviews[0]" 
                        :src="form.imagesPreviews[0]"
                        class="w-full h-full object-cover"
                      />
                      <div 
                        v-else
                        class="w-full h-full flex items-center justify-center bg-gray-50"
                      >
                        <label 
                          class="cursor-pointer text-center p-4"
                          :for="'main-image'"
                        >
                          <span class="block text-sm text-gray-500">Main Image</span>
                          <span class="text-xs text-gray-400">(Click to upload)</span>
                        </label>
                      </div>
                      <!-- Add remove button -->
                      <button
                        v-if="form.imagesPreviews[0]"
                        type="button"
                        @click="removeImage(0, true)"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 
                               opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <X class="h-4 w-4" />
                      </button>
                      <input
                        type="file"
                        id="main-image"
                        class="hidden"
                        accept="image/*"
                        @change="handleMainImageUpload"
                      />
                    </div>
                  </div>

                  <!-- Additional Images -->
                  <div class="grid grid-cols-3 gap-2">
                    <div 
                      v-for="(img, index) in form.additionalImages"
                      :key="`additional-${index}`"
                      class="aspect-square relative border rounded-lg overflow-hidden group"
                    >
                      <img 
                        v-if="img?.preview" 
                        :src="img.preview"
                        class="w-full h-full object-cover"
                      />
                      <div 
                        v-else
                        class="w-full h-full flex items-center justify-center bg-gray-50"
                      >
                        <label 
                          class="cursor-pointer text-center p-2"
                          :for="`additional-image-${index}`"
                        >
                          <span class="block text-xs text-gray-400">Image {{ index + 1 }}</span>
                          <span class="block text-xs text-gray-400">(Click to add)</span>
                        </label>
                      </div>
                      <!-- Add remove button -->
                      <button
                        v-if="img?.preview"
                        type="button"
                        @click="removeImage(index)"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 
                               opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <X class="h-4 w-4" />
                      </button>
                      <input
                        type="file"
                        :id="`additional-image-${index}`"
                        class="hidden"
                        accept="image/*"
                        @change="(e) => handleAdditionalImagesUpload(e, index)"
                      />
                    </div>
                  </div>
                  <p class="text-xs text-gray-500">Main image required. Up to 5 additional images allowed.</p>
                </div>
              </div>

              <DialogFooter class="border-t p-4 mt-auto">
                <Button 
                  type="button" 
                  variant="outline" 
                  :disabled="isSubmitting"
                  @click="closeDialog"
                >
                  Cancel
                </Button>
                <Button 
                  type="submit"
                  :disabled="isSubmitting"
                  class="relative ml-2"
                >
                  <span :class="{ 'opacity-0': isSubmitting }">
                    {{ isEditing ? 'Update' : 'Save' }} Product
                  </span>
                  <span 
                    v-if="isSubmitting" 
                    class="absolute inset-0 flex items-center justify-center"
                  >
                    Saving...
                  </span>
                </Button>
              </DialogFooter>
            </form>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Update Confirmation Dialog -->
      <AlertDialog :open="showUpdateAlert" @update:open="showUpdateAlert = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Update Product</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to update this product? This will overwrite the existing information.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="handleUpdateCancel">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="submitForm">Update</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <!-- Delete Confirmation Dialog -->
      <AlertDialog :open="showDeleteAlert" @update:open="showDeleteAlert = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Product</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete this product? This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showDeleteAlert = false">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="handleDelete">Delete</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <!-- Force Delete Confirmation Dialog -->
      <AlertDialog :open="showForceDeleteAlert" @update:open="showForceDeleteAlert = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Permanently Delete Product</AlertDialogTitle>
            <AlertDialogDescription>
              This action cannot be undone. This will permanently delete the product and all associated data.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showForceDeleteAlert = false">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmForceDelete">Delete Permanently</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '../DashboardLayout.vue'
import { useToast } from '@/Components/ui/toast/use-toast'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select } from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogClose
} from '@/Components/ui/dialog'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogAction,
  AlertDialogCancel,
} from '@/Components/ui/alert-dialog'
import { Check, ChevronsUpDown, X } from 'lucide-vue-next'
import { ScrollArea } from '@/Components/ui/scroll-area'
import { 
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/Components/ui/command'
import { 
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
import {
  RadioGroup,
  RadioGroupItem,
} from '@/Components/ui/radio-group'

// Add these imports
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

// Add to script setup imports
import TagSelect from '@/Components/Forms/TagSelect.vue';
import DataTable from '@/Components/ui/data-table.vue'

const props = defineProps({
  user: Object,
  stats: Object,
  products: {
    type: Object,
    required: true,
    default: () => ({
      data: [],
      meta: {
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1
      }
    })
  },
  categories: Array,
  availableTags: {
    type: Array,
    required: true
  }
})

// Add a computed property for formatted products
const formattedProducts = computed(() => {
  return props.products?.data?.map(product => ({
    id: product.id,
    name: product.name,
    category: product.category,
    price: product.price,
    discounted_price: product.discounted_price,
    stock: product.stock,
    status: product.status,
    is_buyable: product.is_buyable,
    is_tradable: product.is_tradable,
    images: product.images,
    tags: product.tags,
    deleted_at: product.deleted_at,
    category_id: product.category_id,
    description: product.description
  })) || []
})

const { toast } = useToast()
const page = usePage()

// Form and UI state
const showDialog = ref(false)
const showDeleteAlert = ref(false)
const showUpdateAlert = ref(false)
const showForceDeleteAlert = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const editingProduct = ref(null)
const productToDelete = ref(null)
const productToForceDelete = ref(null)
const formErrors = ref({})
const imagePreview = ref(null)

// Add these new refs for popover control
const categoryOpen = ref(false)

// Add computed for filtered categories
const filteredCategories = computed(() => {
  const searchTerm = categorySearch.value?.toLowerCase() || ''
  return props.categories.filter(category => 
    category.name.toLowerCase().includes(searchTerm)
  ).map(category => ({
    value: category.id.toString(),
    label: category.name
  }))
})

const categorySearch = ref('')

// Add function to select category
const selectCategory = (value) => {
  form.value.category = value
  categoryOpen.value = false
}

// Form data
const form = ref({
  name: '',
  description: '',
  category: '',
  price: '',
  discount: '',
  stock: '',
  trade_availability: 'buy',
  main_image: null,
  additionalImages: Array(5).fill(null).map(() => ({
    file: null,
    preview: null
  })),
  status: 'Active',
  imagesPreviews: Array(6).fill(null), // 1 main + 5 additional
  tags: [],
})

// Add status options
const statusOptions = ['Active', 'Inactive']

// Add computed for discounted price
const calculateDiscountedPrice = computed(() => {
  const price = parseFloat(form.value.price) || 0
  const discount = parseFloat(form.value.discount) || 0
  const discountedPrice = price * (1 - (discount / 100))
  return discountedPrice.toFixed(2)
})

// Reset form to initial state
const resetForm = () => {
  cleanupPreviews()
  form.value = {
    name: '',
    description: '',
    category: '',
    price: '',
    discount: '',
    stock: '',
    trade_availability: 'buy',
    main_image: null,
    additionalImages: Array(5).fill(null).map(() => ({
      file: null,
      preview: null
    })),
    status: 'Active',
    imagesPreviews: Array(6).fill(null),
    tags: [],
  }
  imagePreview.value = null
  formErrors.value = {}
  isEditing.value = false
  editingProduct.value = null
}

// Show add form
const showAddForm = () => {
  resetForm()
  showDialog.value = true
}

// Update the editProduct function to properly set tags
const editProduct = async (product) => {
    isEditing.value = true
    editingProduct.value = product
    
    cleanupPreviews()
    
    form.value = {
        name: product.name,
        description: product.description,
        category: product.category_id.toString(), // Make sure it's a string
        price: product.price,
        discount: product.discount ? product.discount * 100 : 0,
        stock: product.stock,
        trade_availability: product.is_buyable && product.is_tradable ? 'both' : 
                       product.is_buyable ? 'buy' : 'trade',
        status: product.status,
        main_image: null,
        additionalImages: Array(5).fill().map(() => ({ file: null, preview: null })),
        imagesPreviews: Array(6).fill(null),
        tags: product.tags?.map(tag => ({
            id: tag.id,
            name: tag.name,
            slug: tag.slug || ''
        })) || []
    }

    // Log the tags for debugging
    console.log('Loaded product tags:', product.tags)
    
    // Load existing images if available
    if (product.images?.length > 0) {
        // Set main image preview
        form.value.imagesPreviews[0] = `/storage/${product.images[0]}`
        
        // Set additional images previews
        product.images.slice(1).forEach((image, index) => {
            if (index < 5) { // Limit to 5 additional images
                form.value.additionalImages[index] = {
                    file: null,
                    preview: `/storage/${image}`,
                    existingPath: image // Keep track of existing image path
                }
            }
        })
    }
    
    showDialog.value = true
}

// Confirm delete
const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteAlert.value = true
}

// Handle delete
const handleDelete = () => {
  if (productToDelete.value) {
    router.delete(route('seller.products.destroy', productToDelete.value.id), {
      onSuccess: () => {
        showDeleteAlert.value = false
        productToDelete.value = null
        toast({
          title: 'Success',
          description: 'Product archived successfully',
          variant: 'default'
        })
      },
      onError: (error) => {
        toast({
          title: 'Error',
          description: error.message || 'Failed to delete product',
          variant: 'destructive'
        })
      }
    })
  }
}

// Handle form submission
const handleSubmit = () => {
  if (!validateForm()) return

  if (isEditing.value) {
    showUpdateAlert.value = true
    showDialog.value = false
  } else {
    submitForm()
  }
}

// Validate form
const validateForm = () => {
  formErrors.value = {}
  let isValid = true

  if (!form.value.name) {
    formErrors.value.name = 'Product name is required'
    isValid = false
  }

  if (!form.value.category) {
    formErrors.value.category = 'Category is required'
    isValid = false
  }

  if (!form.value.price || form.value.price <= 0) {
    formErrors.value.price = 'Price must be greater than 0'
    isValid = false
  }

  if (!form.value.stock || form.value.stock < 0) {
    formErrors.value.stock = 'Stock must be 0 or greater'
    isValid = false
  }

  if (!isEditing.value && !form.value.main_image) {
    formErrors.value.main_image = 'Main image is required'
    isValid = false
  }

  if (form.value.tags.length > 5) {
    formErrors.value.tags = 'Maximum 5 tags allowed'
    isValid = false
  }

  if (!isValid) {
    toast({
      title: 'Validation Error',
      description: 'Please check all required fields',
      variant: 'destructive'
    })
  }

  return isValid
}

// Update the submitForm function's tag handling
const submitForm = () => {
    if (!validateForm()) return;

    isSubmitting.value = true;
    const formData = new FormData();
    
    // Add basic fields
    formData.append('name', form.value.name);
    formData.append('description', form.value.description);
    formData.append('category', form.value.category);
    formData.append('price', form.value.price);
    formData.append('discount', form.value.discount);
    formData.append('stock', form.value.stock);
    formData.append('trade_availability', form.value.trade_availability);
    formData.append('status', form.value.status);

    // Update tag handling
    if (form.value.tags && form.value.tags.length > 0) {
        const tagIds = form.value.tags.map(tag => tag.id);
        formData.append('tags', JSON.stringify(tagIds));
    } else {
        formData.append('tags', JSON.stringify([]));  // Send empty array if no tags
    }

    // Handle main image - Fix for new images
    if (form.value.main_image instanceof File) {
        formData.append('main_image', form.value.main_image);
    }

    // Handle additional images - Fix for new images
    form.value.additionalImages
        .filter(img => img?.file instanceof File)
        .forEach((img, index) => {
            formData.append(`additional_images[${index}]`, img.file);
        });

    // Handle removed images
    if (isEditing.value && editingProduct.value) {
        const removedImages = [];
        const currentImages = [];

        // Track current main image
        if (form.value.imagesPreviews[0]) {
            const mainImagePath = form.value.imagesPreviews[0].replace('/storage/', '');
            if (!mainImagePath.startsWith('blob:')) {
                currentImages.push(mainImagePath);
            }
        }

        // Track current additional images
        form.value.additionalImages.forEach(img => {
            if (img?.preview) {
                const preview = img.preview.replace('/storage/', '');
                if (!preview.startsWith('blob:')) {
                    currentImages.push(preview);
                }
            }
        });

        // Find removed images
        editingProduct.value.images?.forEach(img => {
            if (!currentImages.includes(img)) {
                removedImages.push(img);
            }
        });

        if (removedImages.length > 0) {
            formData.append('removed_images', JSON.stringify(removedImages));
        }
    }

    if (isEditing.value) {
        // Use route helper for update
        router.post(route('seller.products.update', editingProduct.value.id), formData, {
            onSuccess: () => {
                handleSuccess('Product updated successfully');
                showUpdateAlert.value = false;
                showDialog.value = false;
            },
            onError: (errors) => {
                handleError(errors);
                showDialog.value = true;
            },
            preserveScroll: true
        });
    } else {
        router.post(route('seller.products.store'), formData, {
            onSuccess: () => {
                handleSuccess('Product added successfully');
                showDialog.value = false;
            },
            onError: handleError,
            preserveScroll: true
        });
    }
};

// Handle image uploads
const handleMainImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Clean up existing preview
        if (form.value.imagesPreviews[0]) {
            URL.revokeObjectURL(form.value.imagesPreviews[0]);
        }
        form.value.main_image = file;
        form.value.imagesPreviews[0] = URL.createObjectURL(file);
    }
};

// Handle additional images upload
const handleAdditionalImagesUpload = (event, index) => {
  const file = event.target.files[0]
  if (!file) return

  // Clean up existing preview
  if (form.value.additionalImages[index]?.preview) {
    URL.revokeObjectURL(form.value.additionalImages[index].preview)
  }

  const previewUrl = URL.createObjectURL(file)
  form.value.additionalImages[index] = {
    file,
    preview: previewUrl,
    existingPath: null // Clear existing path when new file is uploaded
  }
}

const removeImage = (index, isMainImage = false) => {
  if (isMainImage) {
    if (form.value.imagesPreviews[0]) {
      URL.revokeObjectURL(form.value.imagesPreviews[0])
    }
    form.value.main_image = null
    form.value.imagesPreviews[0] = null
  } else {
    if (form.value.additionalImages[index]?.preview) {
      URL.revokeObjectURL(form.value.additionalImages[index].preview)
    }
    form.value.additionalImages[index] = { file: null, preview: null, existingPath: null }
  }
}

// Add cleanup for all image previews
const cleanupPreviews = () => {
  if (form.value.main_image?.preview) {
    URL.revokeObjectURL(form.value.main_image.preview)
  }
  
  form.value.additionalImages.forEach(img => {
    if (img?.preview) {
      URL.revokeObjectURL(img.preview)
    }
  })
}

// Success and error handlers
const handleSuccess = (message) => {
  isSubmitting.value = false
  showDialog.value = false
  showUpdateAlert.value = false
  resetForm()
  toast({
    title: 'Success',
    description: message,
    variant: 'default'
  })
}

const handleError = (error) => {
  isSubmitting.value = false
  formErrors.value = error.errors
  toast({
    title: 'Error',
    description: error.message || 'An error occurred',
    variant: 'destructive'
  })
}

// Modal handlers
const handleUpdateCancel = () => {
  showUpdateAlert.value = false
  showDialog.value = true
}

const closeDialog = () => {
  showDialog.value = false
  resetForm()
}

// Setup lifecycle hooks
onMounted(() => {
  const flashMessage = page.props.flash?.message
  const flashType = page.props.flash?.type
  
  if (flashMessage) {
    toast({
      title: flashType === 'success' ? 'Success' : 'Error',
      description: flashMessage,
      variant: flashType === 'success' ? 'default' : 'destructive'
    })
  }
})

onUnmounted(() => {
  // Just cleanup image previews and form state
  cleanupPreviews()
  showDialog.value = false
  showDeleteAlert.value = false
  showUpdateAlert.value = false
  showForceDeleteAlert.value = false
  productToForceDelete.value = null
  resetForm()
})

// Add restore and force delete functions
const handleRestore = (product) => {
  router.post(route('seller.products.restore', product.id), {}, {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: 'Product restored successfully',
        variant: 'default'
      })
    },
    onError: (error) => handleError(error)
  })
}

const handleForceDelete = (product) => {
  productToForceDelete.value = product
  showForceDeleteAlert.value = true
}

const confirmForceDelete = () => {
  if (productToForceDelete.value) {
    router.delete(route('seller.products.force-delete', productToForceDelete.value.id), {
      onSuccess: () => {
        showForceDeleteAlert.value = false
        productToForceDelete.value = null
        toast({
          title: 'Success',
          description: 'Product permanently deleted',
          variant: 'default'
        })
      },
      onError: (error) => handleError(error)
    })
  }
}

// Update table actions cell
const getActions = (product) => {
  if (product.deleted_at) {
    return [
      {
        label: 'Restore',
        action: () => handleRestore(product),
        variant: 'outline',
        class: 'text-green-600'
      },
      {
        label: 'Delete Permanently',
        action: () => handleForceDelete(product),
        variant: 'outline',
        class: 'text-red-600'
      }
    ]
  }
  
  return [
    {
      label: 'Edit',
      action: () => editProduct(product),
      variant: 'outline'
    },
    {
      label: 'Archive',
      action: () => confirmDelete(product),
      variant: 'outline',
      class: 'text-red-600'
    }
  ]
}

// Add bulk action methods
const handleBulkDelete = (selectedIds) => {
  router.delete(route('seller.products.bulk-delete'), {
    data: { ids: selectedIds },
    preserveScroll: true,
    onBefore: () => {
      isSubmitting.value = true;
    },
    onSuccess: () => {
      if (dataTable.value) {
        dataTable.value.clearSelection();
      }
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

const handleBulkRestore = (selectedIds) => {
  router.post(route('seller.products.bulk-restore'), {
    ids: selectedIds
  }, {
    preserveScroll: true,
    onBefore: () => {
      isSubmitting.value = true;
    },
    onSuccess: () => {
      if (dataTable.value) {
        dataTable.value.clearSelection();
      }
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

const handleBulkForceDelete = (selectedIds) => {
  router.delete(route('seller.products.bulk-force-delete'), {
    data: { ids: selectedIds },
    preserveScroll: true,
    onBefore: () => {
      isSubmitting.value = true;
    },
    onSuccess: () => {
      if (dataTable.value) {
        dataTable.value.clearSelection();
      }
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};
</script>

<style scoped>
/* Remove default padding from DialogContent */
:deep(.DialogContent) {
  padding: 0 !important;
}

/* Hide any default close button */
:deep(.DialogClose) {
  display: none !important;
}

/* Keep other styles */
.aspect-video {
  aspect-ratio: 3 / 2;
}

.aspect-square {
  aspect-ratio: 1 / 1;
}

.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}
</style>
