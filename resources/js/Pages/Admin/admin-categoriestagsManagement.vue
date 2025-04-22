<template>
  <AdminLayout title="Categories & Tags">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6">Categories & Tags Management</h1>
      
      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button 
            @click="activeTab = 'categories'" 
            :class="[
              activeTab === 'categories' 
                ? 'border-indigo-500 text-indigo-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Categories
          </button>
          <button 
            @click="activeTab = 'tags'" 
            :class="[
              activeTab === 'tags' 
                ? 'border-indigo-500 text-indigo-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Tags
          </button>
        </nav>
      </div>
      
      <!-- Categories Tab -->
      <div v-if="activeTab === 'categories'" class="space-y-6">
        <!-- Categories Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-4">{{ editingCategory ? 'Edit Category' : 'Create New Category' }}</h2>
          <form @submit.prevent="editingCategory ? updateCategory() : createCategory()" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input
                v-model="categoryForm.name"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter category name"
              />
              <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
            </div>
            <div class="flex space-x-2">
              <button
                type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                {{ editingCategory ? 'Update' : 'Create' }}
              </button>
              <button
                v-if="editingCategory"
                type="button"
                @click="cancelEditCategory"
                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
        
        <!-- Categories List -->
        <div class="bg-white p-6 rounded-lg shadow-md overflow-hidden">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Categories List</h2>
            <div class="flex space-x-2">
              <button
                v-if="selectedCategories.length > 0"
                @click="bulkDeleteCategories"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
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
              class="w-full md:w-1/3 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Search categories..."
            />
          </div>
          
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="p-4">
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        :checked="selectAllCategories"
                        @change="toggleAllCategories"
                      />
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products Count</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="category in filteredCategories" :key="category.id">
                  <td class="p-4">
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        :value="category.id"
                        v-model="selectedCategories"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ category.products ? category.products.length : 0 }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="editCategory(category)"
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteCategory(category.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredCategories.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500">No categories found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <!-- Tags Tab -->
      <div v-if="activeTab === 'tags'" class="space-y-6">
        <!-- Tags Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-4">{{ editingTag ? 'Edit Tag' : 'Create New Tag' }}</h2>
          <form @submit.prevent="editingTag ? updateTag() : createTag()" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input
                v-model="tagForm.name"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter tag name"
              />
              <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                v-model="tagForm.description"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter tag description"
                rows="3"
              ></textarea>
              <div v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</div>
            </div>
            <div class="flex space-x-2">
              <button
                type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                {{ editingTag ? 'Update' : 'Create' }}
              </button>
              <button
                v-if="editingTag"
                type="button"
                @click="cancelEditTag"
                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
        
        <!-- Tags List -->
        <div class="bg-white p-6 rounded-lg shadow-md overflow-hidden">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Tags List</h2>
            <div class="flex space-x-2">
              <button
                v-if="selectedTags.length > 0"
                @click="bulkDeleteTags"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
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
              class="w-full md:w-1/3 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Search tags..."
            />
          </div>
          
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="p-4">
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        :checked="selectAllTags"
                        @change="toggleAllTags"
                      />
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products Count</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="tag in filteredTags" :key="tag.id">
                  <td class="p-4">
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        :value="tag.id"
                        v-model="selectedTags"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ tag.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ tag.slug }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="truncate max-w-xs block">{{ tag.description || 'No description' }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ tag.products ? tag.products.length : 0 }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="editTag(tag)"
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteTag(tag.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredTags.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">No tags found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <!-- Success/Error Messages -->
      <div v-if="$page.props.flash.success" class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ $page.props.flash.error }}
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

export default {
  components: {
    AdminLayout
  },
  props: {
    categories: Array,
    tags: Array,
    errors: Object,
  },
  setup(props) {
    // Common state
    const activeTab = ref('categories');
    const errors = usePage().props.errors;
    
    // Categories state
    const categoryForm = useForm({
      name: '',
    });
    const editingCategory = ref(null);
    const selectedCategories = ref([]);
    const categorySearch = ref('');
    
    // Tags state
    const tagForm = useForm({
      name: '',
      description: '',
    });
    const editingTag = ref(null);
    const selectedTags = ref([]);
    const tagSearch = ref('');

    // Categories computed properties
    const filteredCategories = computed(() => {
      if (!categorySearch.value) return props.categories;
      const search = categorySearch.value.toLowerCase();
      return props.categories.filter(category => 
        category.name.toLowerCase().includes(search)
      );
    });
    
    const selectAllCategories = computed(() => {
      return filteredCategories.value.length > 0 && 
             selectedCategories.value.length === filteredCategories.value.length;
    });
    
    // Tags computed properties
    const filteredTags = computed(() => {
      if (!tagSearch.value) return props.tags;
      const search = tagSearch.value.toLowerCase();
      return props.tags.filter(tag => 
        tag.name.toLowerCase().includes(search) || 
        (tag.description && tag.description.toLowerCase().includes(search))
      );
    });
    
    const selectAllTags = computed(() => {
      return filteredTags.value.length > 0 && 
             selectedTags.value.length === filteredTags.value.length;
    });

    // Categories methods
    const createCategory = () => {
      categoryForm.post(route('admin.categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
          categoryForm.reset();
        },
      });
    };

    const editCategory = (category) => {
      editingCategory.value = category;
      categoryForm.name = category.name;
    };

    const updateCategory = () => {
      categoryForm.put(route('admin.categories.update', editingCategory.value.id), {
        preserveScroll: true,
        onSuccess: () => {
          editingCategory.value = null;
          categoryForm.reset();
        },
      });
    };

    const cancelEditCategory = () => {
      editingCategory.value = null;
      categoryForm.reset();
    };

    const deleteCategory = (id) => {
      if (confirm('Are you sure you want to delete this category?')) {
        window.axios.delete(route('admin.categories.delete', id))
          .then(() => {
            // Refresh the page or update the categories list
            window.location.reload();
          })
          .catch(error => {
            console.error('Error deleting category:', error);
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
      if (selectedCategories.value.length === 0) return;
      
      if (confirm(`Are you sure you want to delete ${selectedCategories.value.length} categories?`)) {
        window.axios.delete(route('admin.categories.bulk-delete'), {
          data: { ids: selectedCategories.value }
        })
          .then(() => {
            // Refresh the page or update the categories list
            window.location.reload();
          })
          .catch(error => {
            console.error('Error bulk deleting categories:', error);
          });
      }
    };

    // Tags methods
    const createTag = () => {
      tagForm.post(route('admin.tags.store'), {
        preserveScroll: true,
        onSuccess: () => {
          tagForm.reset();
        },
      });
    };

    const editTag = (tag) => {
      editingTag.value = tag;
      tagForm.name = tag.name;
      tagForm.description = tag.description || '';
    };

    const updateTag = () => {
      tagForm.put(route('admin.tags.update', editingTag.value.id), {
        preserveScroll: true,
        onSuccess: () => {
          editingTag.value = null;
          tagForm.reset();
        },
      });
    };

    const cancelEditTag = () => {
      editingTag.value = null;
      tagForm.reset();
    };

    const deleteTag = (id) => {
      if (confirm('Are you sure you want to delete this tag?')) {
        window.axios.delete(route('admin.tags.delete', id))
          .then(() => {
            // Refresh the page or update the tags list
            window.location.reload();
          })
          .catch(error => {
            console.error('Error deleting tag:', error);
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
      if (selectedTags.value.length === 0) return;
      
      if (confirm(`Are you sure you want to delete ${selectedTags.value.length} tags?`)) {
        window.axios.delete(route('admin.tags.bulk-delete'), {
          data: { ids: selectedTags.value }
        })
          .then(() => {
            // Refresh the page or update the tags list
            window.location.reload();
          })
          .catch(error => {
            console.error('Error bulk deleting tags:', error);
          });
      }
    };

    return {
      activeTab,
      errors,
      
      // Categories
      categoryForm,
      editingCategory,
      selectedCategories,
      categorySearch,
      filteredCategories,
      selectAllCategories,
      createCategory,
      editCategory,
      updateCategory,
      cancelEditCategory,
      deleteCategory,
      toggleAllCategories,
      bulkDeleteCategories,
      
      // Tags
      tagForm,
      editingTag,
      selectedTags,
      tagSearch,
      filteredTags,
      selectAllTags,
      createTag,
      editTag,
      updateTag,
      cancelEditTag,
      deleteTag,
      toggleAllTags,
      bulkDeleteTags
    };
  }
};
</script>