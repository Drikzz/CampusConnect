<x-sellerLayout>
    <!-- Header -->
    <div class="bg-gray-50 border-b py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-semibold text-gray-900">Add New Product</h1>
            <div class="flex items-center gap-2 mt-2 text-sm text-gray-600">
                <a href="{{ route('seller.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>Add Product</span>
            </div>
        </div>
    </div>

    <main class="flex-1 p-8 bg-gray-50">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white shadow-md rounded-lg">
                <form action="{{ route('seller.addproduct') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 space-y-8">
                        <!-- Product Images -->
                        <div class="space-y-4">
                            <h2 class="text-xl font-semibold text-gray-900">Product Images</h2>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <!-- Main Image -->
                                <div class="col-span-1 md:col-span-2">
                                    <div class="text-sm font-medium text-gray-900 mb-4">Main Image (Required)</div>
                                    <div
                                        class="relative group aspect-square bg-gray-50 border-2 border-dashed border-primary-color rounded-lg overflow-hidden">
                                        <input type="file" id="mainImage" name="main_image" class="hidden"
                                            accept="image/*">
                                        <label for="mainImage"
                                            class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100">
                                            <div id="mainImagePlaceholder" class="text-center p-4">
                                                <svg class="mx-auto w-12 h-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <p class="mt-2 text-sm text-gray-600">Click to upload main image</p>
                                            </div>
                                            <div id="mainPreview" class="hidden w-full h-full">
                                                <img src="" alt="Preview" class="w-full h-full object-cover">
                                            </div>
                                        </label>
                                    </div>
                                    @error('main_image')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Additional Images -->
                                <div class="col-span-1 md:col-span-2">
                                    <div class="text-sm font-medium text-gray-900 mb-4">Additional Images</div>
                                    <div class="grid grid-cols-2 gap-4">
                                        @for ($i = 1; $i <= 4; $i++)
                                            <div
                                                class="relative aspect-square bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
                                                <input type="file" id="image{{ $i }}"
                                                    name="additional_images[]" class="hidden" accept="image/*">
                                                <label for="image{{ $i }}"
                                                    class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100">
                                                    <div id="placeholder{{ $i }}"
                                                        class="flex flex-col items-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        <span class="mt-2 text-sm text-gray-500">Add Image</span>
                                                    </div>
                                                    <div id="preview{{ $i }}" class="hidden w-full h-full">
                                                        <img src="" alt="Preview {{ $i }}"
                                                            class="w-full h-full object-cover">
                                                    </div>
                                                </label>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
                            <div class="grid gap-6">
                                <div>
                                    <label for="product-name" class="block text-sm font-medium text-gray-700">Product
                                        Name</label>
                                    <input type="text" id="product-name" name="name"
                                        class="mt-1 w-full rounded-lg @error('name') border-red-500 @else border-gray-300 @enderror focus:border-primary-color focus:ring-primary-color transition-all"
                                        placeholder="Enter product name" value="{{ old('name') }}">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="description"
                                        class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="description" name="description" rows="4"
                                        class="mt-1 w-full rounded-lg @error('description') border-red-500 @else border-gray-300 @enderror focus:border-primary-color focus:ring-primary-color transition-all"
                                        placeholder="Describe your product">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="space-y-4">
                            <h2 class="text-xl font-semibold text-gray-900">Product Details</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="category"
                                        class="block mb-2 text-sm font-medium text-gray-900">Category</label>
                                    <select id="category" name="category"
                                        class="w-full p-3 bg-gray-50 @error('category') border-red-500 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled {{ old('category') ? '' : 'selected' }}>
                                            Select a category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Replace the trade-method select with this -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Trade Method</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" id="trade_buy" name="trade_availability"
                                                value="buy"
                                                {{ old('trade_availability') == 'buy' ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                                            <label for="trade_buy" class="ml-2 text-sm font-medium text-gray-900">
                                                For Purchase Only
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="trade_trade" name="trade_availability"
                                                value="trade"
                                                {{ old('trade_availability') == 'trade' ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                                            <label for="trade_trade" class="ml-2 text-sm font-medium text-gray-900">
                                                For Trade Only
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="trade_both" name="trade_availability"
                                                value="both"
                                                {{ old('trade_availability') == 'both' ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                                            <label for="trade_both" class="ml-2 text-sm font-medium text-gray-900">
                                                Both Purchase and Trade
                                            </label>
                                        </div>
                                    </div>
                                    @error('trade_availability')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Price
                                        (₱)</label>
                                    <input type="number" id="price" name="price" min="0"
                                        step="0.01"
                                        class="w-full p-3 bg-gray-50 @error('price') border-red-500 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.00" value="{{ old('price') }}">
                                    @error('price')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Add Discount Field -->
                                <div>
                                    <label for="discount"
                                        class="block mb-2 text-sm font-medium text-gray-900">Discount
                                        (%)</label>
                                    <input type="number" id="discount" name="discount" min="0"
                                        max="100" step="0.01"
                                        class="w-full p-3 bg-gray-50 @error('discount') border-red-500 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0" value="{{ old('discount', 0) }}">
                                    @error('discount')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="quantity"
                                        class="block mb-2 text-sm font-medium text-gray-900">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" min="1"
                                        class="w-full p-3 bg-gray-50 @error('quantity') border-red-500 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="1" value="{{ old('quantity') }}">
                                    @error('quantity')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status"
                                        class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                                    <select id="status" name="status"
                                        class="w-full p-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-white py-4 px-6 border-t border-gray-200">
                            <div class="flex justify-end gap-4">
                                <button type="reset"
                                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all">
                                    Reset Form
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 text-sm font-medium text-white bg-primary-color rounded-lg hover:bg-primary-color/90 transition-all">
                                    Add Product
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <!-- Add this script section at the bottom of the file -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Main image preview
            const mainImageInput = document.getElementById('mainImage');
            const mainPreview = document.getElementById('mainPreview');
            const mainPlaceholder = document.getElementById('mainImagePlaceholder');

            mainImageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        mainPreview.querySelector('img').src = e.target.result;
                        mainPreview.classList.remove('hidden');
                        mainPlaceholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Additional images preview
            for (let i = 1; i <= 4; i++) {
                const input = document.getElementById(`image${i}`);
                const preview = document.getElementById(`preview${i}`);
                const placeholder = document.getElementById(`placeholder${i}`);

                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.querySelector('img').src = e.target.result;
                            preview.classList.remove('hidden');
                            placeholder.classList.add('hidden');
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Add form submit handler
            document.querySelector('form').addEventListener('submit', function(e) {
                const formData = new FormData(this);
                const tradeAvailability = document.querySelector('input[name="trade_availability"]:checked')
                    .value;

                // Set is_buyable and is_tradable based on trade_availability selection
                if (tradeAvailability === 'buy') {
                    formData.append('is_buyable', '1');
                    formData.append('is_tradable', '0');
                } else if (tradeAvailability === 'trade') {
                    formData.append('is_buyable', '0');
                    formData.append('is_tradable', '1');
                } else if (tradeAvailability === 'both') {
                    formData.append('is_buyable', '1');
                    formData.append('is_tradable', '1');
                }

                // Submit the form
                this.submit();
            });
        });
    </script>
</x-sellerLayout>
