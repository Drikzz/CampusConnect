<template>
  <AdminLayout title="Categories & Tags">
    <meta name="csrf-token" :content="$page.props.csrf_token">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Categories & Tags Management</h1>
      
      <!-- Debug Info -->
      <div v-if="false" class="bg-yellow-100 p-4 border border-yellow-400 rounded mb-4">
        <p>Debug Info:</p>
        <pre>Categories: {{ categories?.length || 0 }}</pre>
        <pre>Filtered Categories: {{ filteredCategories.length }}</pre>
        <pre>Tags: {{ tags?.length || 0 }}</pre>
      </div>

      <!-- Error Boundary -->
      <div v-if="!categories && !tags" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded my-4">
        <strong>Error:</strong> Unable to load categories and tags data. Please refresh the page or contact support.
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-4 sm:mb-6">
        <nav class="-mb-px flex space-x-4 sm:space-x-8">
          <button 
            @click="activeTab = 'categories'" 
            :class="[ 
              activeTab === 'categories' 
                ? 'border-crimson text-crimson' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-2 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm'
            ]"
          >
            Categories
          </button>
          <button 
            @click="activeTab = 'tags'" 
            :class="[ 
              activeTab === 'tags' 
                ? 'border-crimson text-crimson' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-2 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm'
            ]"
          >
            Tags
          </button>
        </nav>
      </div>
      
      <!-- Categories Tab -->
      <div v-if="activeTab === 'categories'" class="space-y-4 sm:space-y-6">
        <!-- Categories Form - Only show for creating new categories -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md" v-if="showCategoryForm && !editingCategory">
          <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Create New Category</h2>
          <form @submit.prevent="createCategory()" class="space-y-3 sm:space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input
                v-model="categoryForm.name"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 sm:py-2 px-2 sm:px-3 focus:outline-none focus:ring-crimson focus:border-crimson text-sm"
                placeholder="Enter category name"
              />
              <div v-if="errors.name" class="text-red-500 text-xs sm:text-sm mt-1">{{ errors.name }}</div>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
              <button
                type="submit"
                class="inline-flex justify-center py-1.5 sm:py-2 px-3 sm:px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
              >
                Create
              </button>
              <button
                type="button"
                @click="cancelCategoryForm"
                class="inline-flex justify-center py-1.5 sm:py-2 px-3 sm:px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
        
        <!-- Categories List -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md overflow-hidden">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-3 sm:space-y-0">
            <h2 class="text-lg sm:text-xl font-semibold">Categories List</h2>
            <div class="flex flex-wrap gap-2">
              <button
                @click="toggleCategoryForm"
                class="inline-flex items-center px-2.5 sm:px-3 py-1.5 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                v-if="!showCategoryForm && !editingCategory"
              >
                Create Category
              </button>
              <button
                v-if="selectedCategories.length > 0"
                @click="bulkDeleteCategories"
                class="inline-flex items-center px-2.5 sm:px-3 py-1.5 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                :disabled="isBulkDeleteLoading"
              >
                Delete Selected
              </button>
            </div>
          </div>
          
          <!-- Search & Filter -->
          <div class="mb-4">
            <input
              v-model="categorySearch"
              type="text"
              class="w-full sm:w-2/3 md:w-1/3 border border-gray-300 rounded-md shadow-sm py-1.5 sm:py-2 px-2 sm:px-3 focus:outline-none focus:ring-crimson focus:border-crimson text-sm"
              placeholder="Search categories..."
            />
          </div>
          
          <!-- Table -->
          <div class="overflow-x-auto -mx-4 sm:-mx-6">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="p-2">
                      <div class="flex items-center">
                        <input
                          type="checkbox"
                          class="h-4 w-4 text-crimson focus:ring-crimson border-gray-300 rounded"
                          :checked="selectAllCategories"
                          @change="toggleAllCategories"
                        />
                      </div>
                    </th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products Count</th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="category in paginatedCategories" :key="category.id">
                    <td class="p-2">
                      <div class="flex items-center">
                        <input
                          type="checkbox"
                          class="h-4 w-4 text-crimson focus:ring-crimson border-gray-300 rounded"
                          :value="category.id"
                          v-model="selectedCategories"
                        />
                      </div>
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-xs sm:text-sm">{{ category.name }}</td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-xs sm:text-sm">{{ category.products_count || 0 }}</td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-right text-xs sm:text-sm font-medium">
                      <div class="flex justify-end space-x-2">
                        <button
                          @click="editCategory(category)"
                          class="text-crimson hover:text-crimson-dark"
                        >
                          Edit
                        </button>
                        <button
                          @click="deleteCategory(category.id)"
                          class="text-white bg-crimson hover:bg-crimson-dark px-2 py-1 rounded-md text-xs font-medium"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="paginatedCategories.length === 0">
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No categories found</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Pagination for Categories -->
          <div class="border-t border-gray-200 bg-white px-2 sm:px-4 py-2 sm:py-3 mt-4">
            <div class="flex flex-col sm:flex-row justify-between items-center">
              <div class="text-xs sm:text-sm text-gray-700 mb-2 sm:mb-0">
                Showing
                <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span>
                to
                <span class="font-medium">
                  {{ Math.min(currentPage * itemsPerPage, filteredCategories.length) }}
                </span>
                of
                <span class="font-medium">{{ filteredCategories.length }}</span>
                results
              </div>

              <!-- Simple pagination -->
              <div class="flex justify-center space-x-1 sm:space-x-2">
                <button
                  @click="changePage(Math.max(1, currentPage - 1))"
                  :disabled="currentPage === 1"
                  class="px-2 sm:px-3 py-1 border rounded-md text-xs sm:text-sm"
                  :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                >
                  Previous
                </button>
                
                <div class="hidden sm:flex space-x-1">
                  <template v-for="page in totalPages" :key="page">
                    <button
                      @click="changePage(page)"
                      class="px-2 sm:px-3 py-1 border rounded-md text-xs sm:text-sm"
                      :class="[currentPage === page ? 'bg-crimson text-white' : 'bg-gray-200 hover:bg-gray-300']"
                    >
                      {{ page }}
                    </button>
                  </template>
                </div>
                
                <div class="flex sm:hidden items-center px-2">
                  <span class="text-xs">{{ currentPage }} / {{ totalPages }}</span>
                </div>
                
                <button
                  @click="changePage(Math.min(totalPages, currentPage + 1))"
                  :disabled="currentPage === totalPages"
                  class="px-2 sm:px-3 py-1 border rounded-md text-xs sm:text-sm"
                  :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tags Tab -->
      <div v-if="activeTab === 'tags'" class="space-y-4 sm:space-y-6">
        <!-- Tags Form - Only show for creating new tags -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md" v-if="showTagForm && !editingTag">
          <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Create New Tag</h2>
          <form @submit.prevent="createTag()" class="space-y-3 sm:space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input
                v-model="tagForm.name"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1.5 sm:py-2 px-2 sm:px-3 focus:outline-none focus:ring-crimson focus:border-crimson text-sm"
                placeholder="Enter tag name"
              />
              <div v-if="errors.name" class="text-red-500 text-xs sm:text-sm mt-1">{{ errors.name }}</div>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
              <button
                type="submit"
                class="inline-flex justify-center py-1.5 sm:py-2 px-3 sm:px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
              >
                Create
              </button>
              <button
                type="button"
                @click="cancelTagForm"
                class="inline-flex justify-center py-1.5 sm:py-2 px-3 sm:px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
        
        <!-- Tags List -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md overflow-hidden">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-3 sm:space-y-0">
            <h2 class="text-lg sm:text-xl font-semibold">Tags List</h2>
            <div class="flex flex-wrap gap-2">
              <button
                @click="toggleTagForm"
                class="inline-flex items-center px-2.5 sm:px-3 py-1.5 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                v-if="!showTagForm && !editingTag"
              >
                Create Tag
              </button>
              <button
                v-if="selectedTags.length > 0"
                @click="bulkDeleteTags"
                class="inline-flex items-center px-2.5 sm:px-3 py-1.5 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                :disabled="isBulkDeleteLoading"
              >
                Delete Selected
              </button>
            </div>
          </div>
          
          <!-- Search & Filter -->
          <div class="mb-4">
            <input
              v-model="tagSearch"
              type="text"
              class="w-full sm:w-2/3 md:w-1/3 border border-gray-300 rounded-md shadow-sm py-1.5 sm:py-2 px-2 sm:px-3 focus:outline-none focus:ring-crimson focus:border-crimson text-sm"
              placeholder="Search tags..."
            />
          </div>
          
          <!-- Table -->
          <div class="overflow-x-auto -mx-4 sm:-mx-6">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="p-2">
                      <div class="flex items-center">
                        <input
                          type="checkbox"
                          class="h-4 w-4 text-crimson focus:ring-crimson border-gray-300 rounded"
                          :checked="selectAllTags"
                          @change="toggleAllTags"
                        />
                      </div>
                    </th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Slug</th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products Count</th>
                    <th scope="col" class="px-2 sm:px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="tag in paginatedTags" :key="tag.id" class="text-xs sm:text-sm">
                    <td class="p-2">
                      <div class="flex items-center">
                        <input
                          type="checkbox"
                          class="h-4 w-4 text-crimson focus:ring-crimson border-gray-300 rounded"
                          :value="tag.id"
                          v-model="selectedTags"
                        />
                      </div>
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap">
                      {{ getTagName(tag) }}
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap hidden sm:table-cell">
                      {{ getTagSlug(tag) }}
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap">
                      {{ getProductsCount(tag) }}
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-right">
                      <div class="flex justify-end space-x-2">
                        <button
                          @click="editTag(tag)"
                          class="text-crimson hover:text-crimson-dark"
                        >
                          Edit
                        </button>
                        <button
                          @click="deleteTag(tag.id)"
                          class="text-white bg-crimson hover:bg-crimson-dark px-2 py-1 rounded-md text-xs font-medium"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="paginatedTags.length === 0">
                    <td colspan="5" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">No tags found</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Pagination -->
          <div class="border-t border-gray-200 bg-white px-2 sm:px-4 py-2 sm:py-3 mt-4">
            <div class="flex flex-col sm:flex-row justify-between items-center">
              <div class="text-xs sm:text-sm text-gray-700 mb-2 sm:mb-0">
                Showing
                <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span>
                to
                <span class="font-medium">
                  {{ Math.min(currentPage * itemsPerPage, filteredTags.length) }}
                </span>
                of
                <span class="font-medium">{{ filteredTags.length }}</span>
                results
              </div>
              
              <!-- Simple pagination -->
              <div class="flex justify-center space-x-1 sm:space-x-2">
                <button
                  @click="changePage(Math.max(1, currentPage - 1))"
                  :disabled="currentPage === 1"
                  class="px-2 sm:px-3 py-1 border rounded-md text-xs sm:text-sm"
                  :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                >
                  Previous
                </button>
                
                <div class="hidden sm:flex space-x-1">
                  <template v-for="page in totalPages" :key="page">
                    <button
                      @click="changePage(page)"
                      class="px-2 sm:px-3 py-1 border rounded-md text-xs sm:text-sm"
                      :class="[currentPage === page ? 'bg-crimson text-white' : 'bg-gray-200 hover:bg-gray-300']"
                    >
                      {{ page }}
                    </button>
                  </template>
                </div>
                
                <div class="flex sm:hidden items-center px-2">
                  <span class="text-xs">{{ currentPage }} / {{ totalPages }}</span>
                </div>
                
                <button
                  @click="changePage(Math.min(totalPages, currentPage + 1))"
                  :disabled="currentPage === totalPages"
                  class="px-2 sm:px-3 py-1 border rounded-md text-xs sm:text-sm"
                  :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Modal Dialogs -->

      <!-- Category Edit Modal -->
      <div v-if="editingCategory" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Edit Category: {{ editingCategory.name }}</h3>
            <form @submit.prevent="updateCategory()" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input
                  v-model="categoryForm.name"
                  type="text"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-crimson focus:border-crimson text-sm"
                  placeholder="Enter category name"
                />
                <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
              </div>
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="cancelCategoryEdit"
                  class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                >
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Tag Edit Modal -->
      <div v-if="editingTag" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Edit Tag: {{ getTagName(editingTag) }}</h3>
            <form @submit.prevent="updateTag()" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input
                  v-model="tagForm.name"
                  type="text"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-crimson focus:border-crimson text-sm"
                  placeholder="Enter tag name"
                />
                <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
              </div>
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="cancelTagEdit"
                  class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-crimson hover:bg-crimson-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-crimson"
                >
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Success/Error Messages -->
      <div v-if="$page.props.flash.success" class="mt-4 bg-green-100 border border-green-400 text-green-700 px-3 sm:px-4 py-2 sm:py-3 rounded text-sm">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="mt-4 bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-2 sm:py-3 rounded text-sm">
        {{ $page.props.flash.error }}
      </div>

      <!-- Toast Notification -->
      <div v-if="toast.show" :class="['toast', toast.type === 'success' ? 'toast-success' : 'toast-error']">
        {{ toast.message }}
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed, ref, watch, onMounted } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

export default {
  components: {
    AdminLayout,
  },
  props: {
    categories: Array,
    tags: Array,
  },
  setup(props) {
    // Debug props
    console.log('Categories & Tags Props:', { 
      categories: props.categories, 
      tags: props.tags,
    });

    const activeTab = ref('categories');
    const errors = usePage().props.errors;
    
    const showCategoryForm = ref(false);
    const showTagForm = ref(false);
    
    const categoryForm = useForm({
      name: '',
    });
    const editingCategory = ref(null);
    const selectedCategories = ref([]);
    const categorySearch = ref('');
    
    const tagForm = useForm({
      name: '',
      description: '',
    });
    const editingTag = ref(null);
    const selectedTags = ref([]);
    const tagSearch = ref('');
    
    const isBulkDeleteLoading = ref(false);

    const toast = ref({
      show: false,
      message: '',
      type: 'success'
    });
    
    // Fix: Define itemsPerPage before using it
    const itemsPerPage = ref(10);
    const currentPage = ref(1);
    
    // Categories computed properties
    const filteredCategories = computed(() => {
      if (!props.categories) return [];
      if (!categorySearch.value) return props.categories;
      const search = categorySearch.value.toLowerCase();
      return props.categories.filter(category => 
        (category.name || '').toLowerCase().includes(search)
      );
    });
    
    const selectAllCategories = computed(() => {
      return filteredCategories.value.length > 0 && 
             selectedCategories.value.length === filteredCategories.value.length;
    });
    
    const paginatedCategories = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      return filteredCategories.value.slice(start, start + itemsPerPage.value);
    });

    const filteredTags = computed(() => {
      if (!props.tags) return [];
      if (!tagSearch.value) return props.tags;
      const search = tagSearch.value.toLowerCase();
      return props.tags.filter(tag => 
        (tag.name || '').toLowerCase().includes(search) || 
        (tag.description && tag.description.toLowerCase().includes(search))
      );
    });
    
    const selectAllTags = computed(() => {
      return filteredTags.value.length > 0 && 
             selectedTags.value.length === filteredTags.value.length;
    });
    
    const paginatedTags = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      return filteredTags.value.slice(start, start + itemsPerPage.value);
    });
    
    const totalPages = computed(() => {
      const total = activeTab.value === 'categories' 
        ? Math.ceil(filteredCategories.value.length / itemsPerPage.value)
        : Math.ceil(filteredTags.value.length / itemsPerPage.value);
      return Math.max(1, total);
    });
    
    // Helper functions
    const changePage = (page) => {
      currentPage.value = page;
    };
    
    const getTagName = (tag) => {
      if (!tag) return 'Unknown Tag';
      return tag.name || `Tag #${tag.id || 'Unknown'}`;
    };
    
    const getTagSlug = (tag) => {
      if (!tag) return '';
      return tag.slug || `tag-${tag.id || 'unknown'}`;
    };
    
    const getProductsCount = (tag) => {
      if (!tag) return 0;
      if (tag.products_count !== undefined) return tag.products_count;
      return tag.products ? tag.products.length : 0;
    };
    
    // Form visibility methods
    const toggleCategoryForm = () => {
      showCategoryForm.value = !showCategoryForm.value;
      if (showCategoryForm.value) {
        categoryForm.reset();
      }
    };
    
    const cancelCategoryForm = () => {
      showCategoryForm.value = false;
      editingCategory.value = null;
      categoryForm.reset();
    };
    
    const toggleTagForm = () => {
      showTagForm.value = !showTagForm.value;
      if (showTagForm.value) {
        tagForm.reset();
      }
    };
    
    const cancelTagForm = () => {
      showTagForm.value = false;
      editingTag.value = null;
      tagForm.reset();
    };
    
    const createCategory = () => {
      // Submit new category via AJAX
      if (!categoryForm.name) {
        toast.value = {
          show: true,
          message: 'Category name is required',
          type: 'error'
        };
        setTimeout(() => {
          toast.value.show = false;
        }, 3000);
        return;
      }

      isBulkDeleteLoading.value = true; // Reuse loading indicator
      
      axios.post('/admin/categories', {
        name: categoryForm.name
      })
        .then(response => {
          toast.value = {
            show: true,
            message: 'Category created successfully',
            type: 'success'
          };
          
          // Reset form and hide it
          categoryForm.reset();
          showCategoryForm.value = false;
          
          // Hide toast after delay
          setTimeout(() => {
            toast.value.show = false;
          }, 3000);
          
          // Refresh the page to show new data
          router.reload();
        })
        .catch(error => {
          console.error('Error creating category:', error);
          
          // Extract error message from response if available
          const errorMessage = error.response?.data?.message || 'Failed to create category';
          
          toast.value = {
            show: true,
            message: errorMessage,
            type: 'error'
          };
          
          setTimeout(() => {
            toast.value.show = false;
          }, 5000);
        })
        .finally(() => {
          isBulkDeleteLoading.value = false;
        });
    };
    
    const editCategory = (category) => {
      // Set the editing category and populate form
      editingCategory.value = category;
      categoryForm.name = category.name;
      // The modal will show automatically because editingCategory is not null
    };
    
    const cancelCategoryEdit = () => {
      // Make sure the modal is fully closed
      categoryForm.reset();
      editingCategory.value = null;
    };
    
    const updateCategory = () => {
      if (!editingCategory.value) return;
      
      if (!categoryForm.name) {
        toast.value = {
          show: true,
          message: 'Category name is required',
          type: 'error'
        };
        setTimeout(() => {
          toast.value.show = false;
        }, 3000);
        return;
      }

      isBulkDeleteLoading.value = true;
      
      // CRITICAL FIX: Force use of the correct endpoint URL - don't rely on relative paths
      // Strip any trailing slashes from the base URL to avoid double slashes
      const baseURL = window.location.origin.replace(/\/$/, '');
      const url = `${baseURL}/admin/categories/${editingCategory.value.id}`;
      
      // Debug log for diagnosing URL issues
      console.log('Current page URL:', window.location.href);
      console.log('Category update URL:', url);
      
      const data = { name: categoryForm.name };
      
      // Directly use form-based POST with _method=PUT to avoid browser PUT method issues
      const formData = new FormData();
      formData.append('name', categoryForm.name);
      formData.append('_method', 'PUT'); // Laravel method spoofing
      
      // Get token from meta tag or page props
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                   usePage().props.csrf_token;
      
      // Use more compatible POST request with method override
      axios({
        method: 'post', // Always use POST for form submissions
        url: url,
        data: formData,
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(response => {
          // First show success message
          toast.value = {
            show: true,
            message: 'Category updated successfully',
            type: 'success'
          };
          
          // Reset form and clear editing state BEFORE reloading
          categoryForm.reset();
          
          // Close the modal
          const currentCategory = editingCategory.value;
          editingCategory.value = null;
          
          // Use a small delay to ensure the modal is fully closed before reload
          setTimeout(() => {
            router.reload();
          }, 300);
        })
        .catch(error => {
          console.error('Error updating category:', error);
          console.error('Response data:', error.response?.data);
          console.error('Response status:', error.response?.status);
          console.error('Response headers:', error.response?.headers);
          
          // Extract error message from response if available
          const errorMessage = error.response?.data?.message || 'Failed to update category';
          
          toast.value = {
            show: true,
            message: errorMessage,
            type: 'error'
          };
          
          setTimeout(() => {
            toast.value.show = false;
          }, 5000);
        })
        .finally(() => {
          isBulkDeleteLoading.value = false;
        });
    };

    const deleteCategory = (id) => {
      if (confirm('Are you sure you want to delete this category? Products in this category will be moved to the default Uncategorized category.')) {
        isBulkDeleteLoading.value = true;
        
        axios.delete(`/admin/categories/${id}`)
          .then(response => {
            if (response.data.success) {
              toast.value = {
                show: true,
                message: response.data.message,
                type: 'success'
              };
              
              setTimeout(() => {
                toast.value.show = false;
              }, 3000);
              
              router.reload();
            }
          })
          .catch(error => {
            console.error('Error deleting category:', error);
            
            const errorMessage = error.response?.data?.message || 
                                'Failed to delete category. Please try again.';
            
            toast.value = {
              show: true,
              message: errorMessage,
              type: 'error'
            };
            
            setTimeout(() => {
              toast.value.show = false;
            }, 5000);
          })
          .finally(() => {
            isBulkDeleteLoading.value = false;
          });
      }
    };

    const toggleAllCategories = () => {
      if (selectedCategories.value.length === filteredCategories.value.length) {
        selectedCategories.value = [];
      } else {
        selectedCategories.value = filteredCategories.value.map(c => c.id);
      }
    };
    
    const bulkDeleteCategories = () => {
      if (!selectedCategories.value.length) {
        toast.value = {
          show: true,
          message: 'Please select at least one category to delete',
          type: 'error'
        };
        setTimeout(() => {
          toast.value.show = false;
        }, 3000);
        return;
      }
      
      if (confirm(`Are you sure you want to delete ${selectedCategories.value.length} categories? Products in these categories will be moved to the default Uncategorized category.`)) {
        isBulkDeleteLoading.value = true;
        
        axios.delete('/admin/categories/bulk-delete', {
          data: { ids: selectedCategories.value }
        })
          .then(response => {
            if (response.data.success) {
              toast.value = {
                show: true,
                message: response.data.message,
                type: 'success'
              };
              
              selectedCategories.value = [];
              
              setTimeout(() => {
                toast.value.show = false;
              }, 3000);
              
              router.reload();
            }
          })
          .catch(error => {
            console.error('Error deleting categories:', error);
            
            const errorMessage = error.response?.data?.message || 
                                'Failed to delete categories. Please try again.';
            
            toast.value = {
              show: true,
              message: errorMessage,
              type: 'error'
            };
            
            setTimeout(() => {
              toast.value.show = false;
            }, 5000);
          })
          .finally(() => {
            isBulkDeleteLoading.value = false;
          });
      }
    };
    
    const createTag = () => {
      // Submit new tag via AJAX
      if (!tagForm.name) {
        toast.value = {
          show: true,
          message: 'Tag name is required',
          type: 'error'
        };
        setTimeout(() => {
          toast.value.show = false;
        }, 3000);
        return;
      }

      isBulkDeleteLoading.value = true; // Reuse loading indicator
      
      axios.post('/admin/tags', {
        name: tagForm.name,
        description: tagForm.description
      })
        .then(response => {
          toast.value = {
            show: true,
            message: 'Tag created successfully',
            type: 'success'
          };
          
          // Reset form and hide it
          tagForm.reset();
          showTagForm.value = false;
          
          // Hide toast after delay
          setTimeout(() => {
            toast.value.show = false;
          }, 3000);
          
          // Refresh the page to show new data
          router.reload();
        })
        .catch(error => {
          console.error('Error creating tag:', error);
          
          // Extract error message from response if available
          const errorMessage = error.response?.data?.message || 'Failed to create tag';
          
          toast.value = {
            show: true,
            message: errorMessage,
            type: 'error'
          };
          
          setTimeout(() => {
            toast.value.show = false;
          }, 5000);
        })
        .finally(() => {
          isBulkDeleteLoading.value = false;
        });
    };
    
    const editTag = (tag) => {
      // Set the editing tag and populate form
      editingTag.value = tag;
      tagForm.name = tag.name || tag.display_name;
      tagForm.description = tag.description || '';
      // The modal will show automatically because editingTag is not null
    };
    
    const cancelTagEdit = () => {
      // Make sure the modal is fully closed
      tagForm.reset();
      editingTag.value = null;
    };
    
    const updateTag = () => {
      if (!editingTag.value) return;
      
      if (!tagForm.name) {
        toast.value = {
          show: true,
          message: 'Tag name is required',
          type: 'error'
        };
        setTimeout(() => {
          toast.value.show = false;
        }, 3000);
        return;
      }

      isBulkDeleteLoading.value = true;
      
      // CRITICAL FIX: Force use of the correct endpoint URL - don't rely on relative paths
      // Strip any trailing slashes from the base URL to avoid double slashes
      const baseURL = window.location.origin.replace(/\/$/, '');
      const url = `${baseURL}/admin/tags/${editingTag.value.id}`;
      
      // Debug log for diagnosing URL issues
      console.log('Current page URL:', window.location.href);
      console.log('Tag update URL:', url);
      
      // Create payload with only needed fields
      const payload = {
        name: tagForm.name
      };
      
      // Only add description if it's being used
      if ('description' in tagForm && tagForm.description !== undefined) {
        payload.description = tagForm.description;
      }
      
      // Use FormData with method spoofing for better compatibility
      const formData = new FormData();
      formData.append('name', tagForm.name);
      if (payload.description) {
        formData.append('description', payload.description);
      }
      formData.append('_method', 'PUT'); // Laravel method spoofing
      
      // Get token from meta tag or page props
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                   usePage().props.csrf_token;
      
      // Use more compatible POST request with method override
      axios({
        method: 'post', // Always use POST for form submissions
        url: url,
        data: formData,
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(response => {
          // Show success message
          toast.value = {
            show: true,
            message: 'Tag updated successfully',
            type: 'success'
          };
          
          // Reset form and clear editing state BEFORE reloading
          tagForm.reset();
          
          // Close the modal
          const currentTag = editingTag.value;  
          editingTag.value = null;
          
          // Use a small delay to ensure the modal is fully closed before reload
          setTimeout(() => {
            router.reload();
          }, 300);
        })
        .catch(error => {
          console.error('Error updating tag:', error);
          console.error('Response data:', error.response?.data);
          console.error('Response status:', error.response?.status);
          console.error('Response headers:', error.response?.headers);
          
          // Extract error message from response if available
          const errorMessage = error.response?.data?.message || 'Failed to update tag';
          
          toast.value = {
            show: true,
            message: errorMessage,
            type: 'error'
          };
          
          setTimeout(() => {
            toast.value.show = false;
          }, 5000);
        })
        .finally(() => {
          isBulkDeleteLoading.value = false;
        });
    };

    const deleteTag = (id) => {
      if (confirm('Are you sure you want to delete this tag? Products associated with this tag will be assigned to the default General tag.')) {
        isBulkDeleteLoading.value = true;
        
        axios.delete(`/admin/tags/${id}`)
          .then(response => {
            if (response.data.success) {
              toast.value = {
                show: true,
                message: response.data.message,
                type: 'success'
              };
              
              setTimeout(() => {
                toast.value.show = false;
              }, 3000);
              
              router.reload();
            }
          })
          .catch(error => {
            console.error('Error deleting tag:', error);
            
            const errorMessage = error.response?.data?.message || 
                                'Failed to delete tag. Please try again.';
            
            toast.value = {
              show: true,
              message: errorMessage,
              type: 'error'
            };
            
            setTimeout(() => {
              toast.value.show = false;
            }, 5000);
          })
          .finally(() => {
            isBulkDeleteLoading.value = false;
          });
      }
    };

    const toggleAllTags = () => {
      if (selectedTags.value.length === filteredTags.value.length) {
        selectedTags.value = [];
      } else {
        selectedTags.value = filteredTags.value.map(t => t.id);
      }
    };

    const bulkDeleteTags = () => {
      if (!selectedTags.value.length) {
        toast.value = {
          show: true,
          message: 'Please select at least one tag to delete',
          type: 'error'
        };
        setTimeout(() => {
          toast.value.show = false;
        }, 3000);
        return;
      }
      
      if (confirm(`Are you sure you want to delete ${selectedTags.value.length} tags? Products associated with these tags will be assigned to the default General tag.`)) {
        isBulkDeleteLoading.value = true;
        
        axios.delete('/admin/tags/bulk-delete', {
          data: { ids: selectedTags.value }
        })
          .then(response => {
            if (response.data.success) {
              toast.value = {
                show: true,
                message: response.data.message,
                type: 'success'
              };
              
              selectedTags.value = [];
              
              setTimeout(() => {
                toast.value.show = false;
              }, 3000);
              
              router.reload();
            }
          })
          .catch(error => {
            console.error('Error deleting tags:', error);
            
            const errorMessage = error.response?.data?.message || 
                              'Failed to delete tags. Please try again.';
            
            toast.value = {
              show: true,
              message: errorMessage,
              type: 'error'
            };
            
            setTimeout(() => {
              toast.value.show = false;
            }, 5000);
          })
          .finally(() => {
            isBulkDeleteLoading.value = false;
          });
      }
    };

    // Add click outside handlers for both modals
    const closeOnEscape = (e) => {
      if (e.key === 'Escape') {
        if (editingCategory.value) {
          cancelCategoryEdit();
        }
        if (editingTag.value) {
          cancelTagEdit();
        }
      }
    };

    // Fix CSRF token issues
    onMounted(() => {
      console.log('Categories & Tags component mounted');
      
      // Try multiple approaches to get CSRF token
      const metaToken = document.head.querySelector('meta[name="csrf-token"]');
      const pageToken = usePage().props.csrf_token;
      
      // Set both X-CSRF-TOKEN and X-XSRF-TOKEN headers for Laravel
      if (metaToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = metaToken.content;
        console.log('Set CSRF token from meta tag');
      } else if (pageToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = pageToken;
        console.log('Set CSRF token from page props');
      }
      
      // Also mark requests as AJAX
      axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
      
      // Add withCredentials for cookies
      axios.defaults.withCredentials = true;

      // Check if we need to load data
      if (!props.categories || props.categories.length === 0 || !props.tags || props.tags.length === 0) {
        console.log('Data appears to be missing, fetching from server...');
        
        // Attempt to load data from server
        axios.get('/admin/categories-tags')
          .then(response => {
            if (response.data.categories) {
              props.categories = response.data.categories;
            }
            if (response.data.tags) {
              props.tags = response.data.tags;
            }
            console.log('Data fetched successfully:', {
              categories: props.categories?.length || 0,
              tags: props.tags?.length || 0
            });
          })
          .catch(error => {
            console.error('Failed to fetch data:', error);
            // Set toast to show error
            toast.value = {
              show: true,
              message: 'Failed to load data. Please refresh the page.',
              type: 'error'
            };
            setTimeout(() => {
              toast.value.show = false;
            }, 5000);
          });
      }

      // Add event listener for Escape key
      document.addEventListener('keydown', closeOnEscape);
    });

    // Fix for title display in edit forms
    const formTitle = computed(() => {
      if (activeTab.value === 'categories') {
        return editingCategory.value ? `Edit Category: ${editingCategory.value.name}` : 'Create New Category';
      } else {
        return editingTag.value ? `Edit Tag: ${getTagName(editingTag.value)}` : 'Create New Tag';
      }
    });

    return {
      activeTab,
      errors,
      showCategoryForm,
      showTagForm,
      categoryForm,
      editingCategory,
      selectedCategories,
      categorySearch,
      tagForm,
      editingTag,
      selectedTags,
      tagSearch,
      isBulkDeleteLoading,
      toast,
      itemsPerPage: itemsPerPage.value,
      currentPage,
      filteredCategories,
      selectAllCategories,
      paginatedCategories,
      filteredTags,
      selectAllTags,
      paginatedTags,
      totalPages,
      changePage,
      getTagName,
      getTagSlug,
      getProductsCount,
      toggleCategoryForm,
      cancelCategoryForm,
      toggleTagForm,
      cancelTagForm,
      createCategory,
      editCategory,
      updateCategory,
      deleteCategory,
      bulkDeleteCategories,
      toggleAllCategories,
      createTag,
      editTag,
      updateTag,
      deleteTag,
      bulkDeleteTags,
      toggleAllTags,
      formTitle,
      closeOnEscape,
      cancelCategoryEdit,
      cancelTagEdit,
    };
  },
};
</script>

<style>
/* Add crimson color classes since they aren't standard in Tailwind */
.text-crimson {
  color: #dc143c;
}
.border-crimson {
  border-color: #dc143c;
}
.bg-crimson {
  background-color: #dc143c;
}
.bg-crimson-dark {
  background-color: #b01030;
}
.hover\:bg-crimson-dark:hover {
  background-color: #b01030;
}
.hover\:text-crimson-dark:hover {
  color: #b01030;
}
.focus\:ring-crimson:focus {
  --tw-ring-color: #dc143c;
}
.focus\:border-crimson:focus {
  border-color: #dc143c;
}

/* Add toast styling */
.toast {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  z-index: 50;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  transition: all 0.3s ease;
}
.toast-success {
  background-color: #10b981;
  color: white;
}
.toast-error {
  background-color: #dc143c;
  color: white;
}
</style>