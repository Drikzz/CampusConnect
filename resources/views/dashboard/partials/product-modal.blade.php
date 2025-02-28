<div id="{{ $modalId }}" class="hidden fixed inset-0 z-50 overflow-y-auto h-full w-full" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity"></div>

    <div class="relative w-full max-w-4xl p-5 mx-auto flex items-center min-h-screen">
        <div class="relative bg-white rounded-lg shadow-xl w-full">
            <!-- Header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t">
                <h3 class="text-xl font-semibold text-gray-900">
                    {{ $title }}
                </h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="{{ $modalId }}-form" method="POST" enctype="multipart/form-data"
                @if ($modalId === 'edit-product-modal') action="{{ route('dashboard.seller.products.update', ['product' => ':productId']) }}"
                @else
                    action="{{ route('dashboard.seller.products.store') }}" @endif>
                @csrf
                @if ($modalId === 'edit-product-modal')
                    @method('PUT')
                @endif

                <div class="p-6 max-h-[calc(100vh-250px)] overflow-y-auto">
                    <!-- Form Content -->
                    <div class="space-y-6">
                        <!-- Product Info -->
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

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" required class="form-textarea w-full rounded-md"></textarea>
                        </div>

                        <!-- Price & Stock -->
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                                <input type="number" name="stock" min="0" required
                                    class="form-input w-full rounded-md">
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Main Image</label>
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img id="{{ $modalId }}-main-preview"
                                            class="h-32 w-32 object-cover rounded-lg border hidden">
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="main_image" accept="image/*"
                                            {{ $modalId === 'add-product-modal' ? 'required' : '' }}
                                            onchange="previewImage(this, '{{ $modalId }}-main-preview')"
                                            class="form-input w-full">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Additional Images</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @for ($i = 1; $i <= 4; $i++)
                                        <div class="space-y-2">
                                            <img id="{{ $modalId }}-preview-{{ $i }}"
                                                class="h-24 w-24 object-cover rounded-lg border hidden">
                                            <input type="file" name="additional_images[]" accept="image/*"
                                                onchange="previewImage(this, '{{ $modalId }}-preview-{{ $i }}')"
                                                class="form-input w-full">
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <!-- Trade Options -->
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

                        @if ($modalId === 'edit-product-modal')
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
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('{{ $modalId }}')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary-color rounded-md hover:bg-primary-color/90">
                        {{ $modalId === 'add-product-modal' ? 'Add Product' : 'Update Product' }}
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

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('[id$="-modal"]');
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('bg-gray-600')) {
                    closeModal(this.id);
                }
            });
        });

        // Close modal when clicking the close button
        document.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('[id$="-modal"]');
                if (modal) {
                    closeModal(modal.id);
                }
            });
        });

        // Close on ESC key
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
