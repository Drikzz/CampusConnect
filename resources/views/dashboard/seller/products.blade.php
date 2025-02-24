@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">My Products</h2>
            <button type="button" onclick="openModal('add-product-modal')"
                class="bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
                Add Product
            </button>
        </div>

        {{-- Search and Filter Section --}}
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" id="search" name="search"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                        onkeyup="filterProducts()">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category" name="category"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                        onchange="filterProducts()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                        onchange="filterProducts()">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Products Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                    @foreach ($products as $product)
                        <tr data-product-id="{{ $product->id }}" data-name="{{ $product->name }}"
                            data-category="{{ $product->category_id }}" data-status="{{ $product->status }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover"
                                            src="{{ asset('storage/' . ($product->images[0] ?? 'products/default.png')) }}"
                                            alt="{{ $product->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->category->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($product->price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->stock }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $product->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if ($product->trashed())
                                    <button onclick="restoreProduct({{ $product->id }})"
                                        class="text-green-600 hover:text-green-900 mr-3">Restore</button>
                                    <button onclick="permanentlyDeleteProduct({{ $product->id }})"
                                        class="text-red-600 hover:text-red-900">Delete Permanently</button>
                                @else
                                    <button onclick="editProduct({{ $product->id }})"
                                        class="text-primary-color hover:text-primary-color/80">Edit</button>
                                    <button onclick="deleteProduct({{ $product->id }})"
                                        class="ml-3 text-red-600 hover:text-red-900">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- Add Product Modal --}}
    <div id="add-product-modal" class="hidden fixed inset-0 z-50 overflow-y-auto h-full w-full"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity"></div>

        <div class="relative w-full max-w-4xl p-5 mx-auto flex items-center min-h-screen">
            <div class="relative bg-white rounded-lg shadow-xl w-full">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">Add New Product</h3>
                    <button type="button" class="modal-close text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Update form action in add product modal --}}
                <form id="add-product-modal-form" method="POST" action="{{ route('dashboard.seller.products.store') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="p-6 max-h-[calc(100vh-250px)] overflow-y-auto">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                    <input type="text" name="name" required class="form-input w-full rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="category" required class="form-select w-full rounded-md">
                                        <option value="">--SELECT A CATEGORY--</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="3" required class="form-textarea w-full rounded-md"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">₱</span>
                                        <input type="number" name="price" step="0.01" required
                                            class="form-input w-full pl-7 rounded-md">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (%)</label>
                                    <input type="number" name="discount" min="0" max="100"
                                        class="form-input w-full rounded-md" placeholder="Enter discount percentage">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                                    <input type="number" name="stock" min="0" required
                                        class="form-input w-full rounded-md">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Main Image</label>
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img id="add-product-modal-main-preview"
                                                class="h-32 w-32 object-cover rounded-lg border hidden">
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="main_image" accept="image/*" required
                                                onchange="previewImage(this, 'add-product-modal-main-preview')"
                                                class="form-input w-full">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Images</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        @for ($i = 1; $i <= 4; $i++)
                                            <div class="space-y-2">
                                                <img id="add-product-modal-preview-{{ $i }}"
                                                    class="h-24 w-24 object-cover rounded-lg border hidden">
                                                <input type="file" name="additional_images[]" accept="image/*"
                                                    onchange="previewImage(this, 'add-product-modal-preview-{{ $i }}')"
                                                    class="form-input w-full">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trade Options</label>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="trade_availability" value="buy" checked
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">For Purchase Only</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="trade_availability" value="trade"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">For Trade Only</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="trade_availability" value="both"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">Both Purchase and Trade</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('add-product-modal')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-color rounded-md hover:bg-primary-color/90">
                            Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Product Modal --}}
    <div id="edit-product-modal" class="hidden fixed inset-0 z-50 overflow-y-auto h-full w-full"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity"></div>

        <div class="relative w-full max-w-4xl p-5 mx-auto flex items-center min-h-screen">
            <div class="relative bg-white rounded-lg shadow-xl w-full">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">Edit Product</h3>
                    <button type="button" class="modal-close text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Update form action in edit product modal --}}
                <form id="edit-product-modal-form" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="p-6 max-h-[calc(100vh-250px)] overflow-y-auto">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                    <input type="text" name="name" required class="form-input w-full rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="category" required class="form-select w-full rounded-md">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="3" required class="form-textarea w-full rounded-md"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">₱</span>
                                        <input type="number" name="price" step="0.01" required
                                            class="form-input w-full pl-7 rounded-md">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (%)</label>
                                    <input type="number" name="discount" min="0" max="100"
                                        class="form-input w-full rounded-md" placeholder="Enter discount percentage">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                                    <input type="number" name="stock" min="0" required
                                        class="form-input w-full rounded-md">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Main Image</label>
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img id="edit-product-modal-main-preview"
                                                class="h-32 w-32 object-cover rounded-lg border hidden">
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="main_image" accept="image/*"
                                                onchange="previewImage(this, 'edit-product-modal-main-preview')"
                                                class="form-input w-full">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Images</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        @for ($i = 1; $i <= 4; $i++)
                                            <div class="space-y-2">
                                                <img id="edit-product-modal-preview-{{ $i }}"
                                                    class="h-24 w-24 object-cover rounded-lg border hidden">
                                                <input type="file" name="additional_images[]" accept="image/*"
                                                    onchange="previewImage(this, 'edit-product-modal-preview-{{ $i }}')"
                                                    class="form-input w-full">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trade Options</label>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="trade_availability" value="buy"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">For Purchase Only</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="trade_availability" value="trade"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">For Trade Only</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="trade_availability" value="both"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">Both Purchase and Trade</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="flex gap-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="Active"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">Active</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" value="Inactive"
                                            class="form-radio text-primary-color">
                                        <span class="ml-2">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('edit-product-modal')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-color rounded-md hover:bg-primary-color/90">
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');

                // Reset form
                const form = document.getElementById(`${modalId}-form`);
                if (form) {
                    form.reset();
                    // Reset all image previews
                    form.querySelectorAll('img[id$="-preview"]').forEach(img => {
                        img.classList.add('hidden');
                        img.src = '';
                    });
                }
            }
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add CSRF token to all fetch requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Helper function for fetch requests
        async function fetchWithCsrf(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            };

            const response = await fetch(url, {
                ...defaultOptions,
                ...options
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }

        // Delete (soft delete)
        async function deleteProduct(productId) {
            if (!confirm('Are you sure you want to delete this product?')) return;

            try {
                const data = await fetchWithCsrf(`/dashboard/seller/products/${productId}`, {
                    method: 'DELETE'
                });

                if (data.success) {
                    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (row) {
                        row.querySelector('td:last-child').innerHTML = `
                            <button onclick="restoreProduct(${productId})" 
                                class="text-green-600 hover:text-green-900 mr-3">Restore</button>
                            <button onclick="permanentlyDeleteProduct(${productId})" 
                                class="text-red-600 hover:text-red-900">Delete Permanently</button>
                        `;

                        const statusBadge = row.querySelector('td:nth-last-child(2) span');
                        statusBadge.textContent = 'Inactive';
                        statusBadge.classList.remove('bg-green-100', 'text-green-800');
                        statusBadge.classList.add('bg-red-100', 'text-red-800');

                        showNotification('Product deleted successfully', 'success');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error deleting product', 'error');
            }
        }

        // Restore
        async function restoreProduct(productId) {
            if (!confirm('Are you sure you want to restore this product?')) return;

            try {
                const data = await fetchWithCsrf(`/dashboard/seller/products/${productId}/restore`, {
                    method: 'POST'
                });

                if (data.success) {
                    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (row) {
                        row.querySelector('td:last-child').innerHTML = `
                            <button onclick="editProduct(${productId})" 
                                class="text-primary-color hover:text-primary-color/80 mr-3">Edit</button>
                            <button onclick="deleteProduct(${productId})" 
                                class="text-red-600 hover:text-red-900">Delete</button>
                        `;

                        const statusBadge = row.querySelector('td:nth-last-child(2) span');
                        statusBadge.textContent = 'Active';
                        statusBadge.classList.remove('bg-red-100', 'text-red-800');
                        statusBadge.classList.add('bg-green-100', 'text-green-800');

                        showNotification('Product restored successfully', 'success');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error restoring product', 'error');
            }
        }

        // Permanent Delete
        async function permanentlyDeleteProduct(productId) {
            if (!confirm(
                    'WARNING: This action cannot be undone! Are you sure you want to permanently delete this product?'
                )) {
                return;
            }

            try {
                const data = await fetchWithCsrf(`/dashboard/seller/products/${productId}/force-delete`, {
                    method: 'DELETE'
                });

                if (data.success) {
                    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s ease-out';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);

                        // Check if table is empty
                        const tbody = document.getElementById('products-table-body');
                        if (!tbody.hasChildNodes()) {
                            tbody.innerHTML = `
                                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found</td></tr>
                            `;
                        }
                        showNotification('Product permanently deleted', 'success');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error permanently deleting product', 'error');
            }
        }

        // Edit Product
        async function editProduct(productId) {
            try {
                const product = await fetchWithCsrf(`/dashboard/seller/products/${productId}/edit`);
                const form = document.getElementById('edit-product-modal-form');
                form.action = `/dashboard/seller/products/${product.id}`;

                // Fill form fields
                form.querySelector('[name="name"]').value = product.name;
                form.querySelector('[name="description"]').value = product.description;
                form.querySelector('[name="category"]').value = product.category_id;
                form.querySelector('[name="price"]').value = product.price;
                form.querySelector('[name="discount"]').value = product.discount * 100;
                form.querySelector('[name="stock"]').value = product.stock;

                // Set trade options
                const tradeValue = product.is_buyable && product.is_tradable ? 'both' :
                    product.is_buyable ? 'buy' : 'trade';
                form.querySelector(`[name="trade_availability"][value="${tradeValue}"]`).checked = true;

                // Set status
                form.querySelector(`[name="status"][value="${product.status}"]`).checked = true;

                // Show existing images
                if (product.images?.length > 0) {
                    const mainPreview = document.getElementById('edit-product-modal-main-preview');
                    mainPreview.src = `/storage/${product.images[0]}`;
                    mainPreview.classList.remove('hidden');

                    product.images.slice(1).forEach((image, index) => {
                        const preview = document.getElementById(`edit-product-modal-preview-${index + 1}`);
                        if (preview) {
                            preview.src = `/storage/${image}`;
                            preview.classList.remove('hidden');
                        }
                    });
                }

                openModal('edit-product-modal');
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error loading product details', 'error');
            }
        }

        // Initialize form submissions
        document.addEventListener('DOMContentLoaded', function() {
            ['add-product-modal-form', 'edit-product-modal-form'].forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);

                        try {
                            const response = await fetch(this.action, {
                                method: this.method,
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                showNotification(data.message || 'Error processing request',
                                    'error');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showNotification('An error occurred', 'error');
                        }
                    });
                }
            });
        });

        function filterProducts() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const categoryFilter = document.getElementById('category').value;
            const statusFilter = document.getElementById('status').value;
            const rows = document.querySelectorAll('#products-table-body tr');

            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const category = row.getAttribute('data-category');
                const status = row.getAttribute('data-status');

                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = !categoryFilter || category === categoryFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add notification helper function if not already present
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white transform transition-transform duration-300 ease-in-out`;
            notification.style.transform = 'translateY(-100%)';
            notification.textContent = message;
            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateY(0)';
            }, 10);

            // Animate out and remove
            setTimeout(() => {
                notification.style.transform = 'translateY(-100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Remove any existing event listeners first
            const forms = ['add-product-modal-form', 'edit-product-modal-form'];
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    // Clone the form to remove all event listeners
                    const newForm = form.cloneNode(true);
                    form.parentNode.replaceChild(newForm, form);

                    // Add the single event listener
                    newForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);

                        try {
                            const response = await fetch(this.action, {
                                method: this.method,
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin'
                            });

                            const data = await response.json();
                            if (data.success) {
                                closeModal(this.closest('[id$="-modal"]').id);
                                showNotification(data.message || 'Product saved successfully',
                                    'success');
                                setTimeout(() => window.location.reload(), 500);
                            } else {
                                showNotification(data.message || 'Error processing request',
                                    'error');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showNotification('An error occurred', 'error');
                        }
                    });
                }
            });

            // Modal close handlers
            document.querySelectorAll('[id$="-modal"]').forEach(modal => {
                // Close when clicking outside
                modal.addEventListener('click', function(e) {
                    if (e.target === this || e.target.classList.contains('bg-gray-600')) {
                        closeModal(this.id);
                    }
                });
            });

            // Close button handlers
            document.querySelectorAll('.modal-close').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('[id$="-modal"]');
                    if (modal) {
                        closeModal(modal.id);
                    }
                });
            });

            // ESC key handler
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const openModal = document.querySelector('[id$="-modal"]:not(.hidden)');
                    if (openModal) {
                        closeModal(openModal.id);
                    }
                }
            });
        });
    </script>
@endsection
