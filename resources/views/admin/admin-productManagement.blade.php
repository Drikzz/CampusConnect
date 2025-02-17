<x-adminLayout2>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Product Dashboard</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 p-6">
        
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Products</h2>
                <div class="flex space-x-2">
                    <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="openModal()">Add Product</button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="mb-4 flex space-x-2">
                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="bulkDelete()">Delete Selected</button>
                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="bulkEdit()">Edit Selected</button>
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-200 text-left text-sm font-semibold">
                            <th class="p-3"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                            <th class="p-3">Image</th>
                            <th class="p-3">Product</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Stock</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Discount</th>
                            <th class="p-3">Discounted Price</th>
                            <th class="p-3">Condition</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTable">
                        @foreach ($products as $product)
                        <tr class="border-t">
                            <td class="p-3"><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                            <td class="p-3">
                                <img src="{{ asset('storage/' . ($product->images[0] ?? 'default-image.jpg')) }}" class="w-12 h-12 rounded">
                            </td>
                            <td class="p-3">{{ $product->name }}</td>
                            <td class="p-3">{{ $product->category->name }}</td>
                            <td class="p-3">{{ $product->stock }}</td>
                            <td class="p-3">${{ number_format($product->price, 2) }}</td>
                            <td class="p-3">{{ $product->discount }}%</td>
                            <td class="p-3">${{ number_format($product->discounted_price, 2) }}</td>
                            <td class="p-3">{{ $product->condition }}</td>
                            <td class="p-3">
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="editProduct({{ $product->id }})">Edit</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500 ml-2" onclick="deleteProduct({{ $product->id }})">Delete</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500 ml-2" onclick="openViewModal({{ json_encode($product) }})">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View Product Modal -->
        <div id="viewProductModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg overflow-y-auto max-h-screen">
                <h2 class="text-2xl font-bold mb-4">View Product</h2>

                <!-- Basic Information -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Product Name</label>
                    <p id="viewProductName" class="w-full border-gray-300 rounded-lg p-3 border bg-gray-100"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Description</label>
                    <p id="viewProductDescription" class="w-full border-gray-300 rounded-lg p-3 border bg-gray-100 h-28"></p>
                </div>

                <!-- Stock & Pricing -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Stock</label>
                        <p id="viewProductStock" class="w-full border-gray-300 rounded-lg p-3 border bg-gray-100"></p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Price ($)</label>
                        <p id="viewProductPrice" class="w-full border-gray-300 rounded-lg p-3 border bg-gray-100"></p>
                    </div>
                </div>

                <!-- Product Image -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Product Image</label>
                    <div class="border-2 border-gray-300 p-6 rounded-lg text-center">
                        <img id="viewProductImage" class="mx-auto w-32 h-32 rounded-lg object-cover">
                    </div>
                </div>

                <!-- Condition -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Condition</label>
                    <p id="viewProductCondition" class="w-full border-gray-300 rounded-lg p-3 border bg-gray-100"></p>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" class="bg-red-500 text-white px-4 py-2 rounded" onclick="closeViewModal()">Back</button>
                </div>
            </div>
        </div>

        <script>
            function openViewModal(product) {
                document.getElementById('viewProductName').innerText = product.name;
                document.getElementById('viewProductDescription').innerText = product.description;
                document.getElementById('viewProductStock').innerText = product.stock;
                document.getElementById('viewProductPrice').innerText = `$${parseFloat(product.price).toFixed(2)}`;

                if (product.images && product.images.length > 0) {
                    document.getElementById('viewProductImage').src = '{{ asset('storage') }}/' + product.images[0];
                } else {
                    document.getElementById('viewProductImage').src = '{{ asset('storage/default-image.jpg') }}';
                }

                document.getElementById('viewProductCondition').innerText = product.condition;
                document.getElementById('viewProductModal').classList.remove('hidden');
            }

            function closeViewModal() {
                document.getElementById('viewProductModal').classList.add('hidden');
            }

            function toggleSelectAll() {
                let checkboxes = document.querySelectorAll('.selectProduct');
                let selectAll = document.getElementById('selectAll').checked;
                checkboxes.forEach(cb => cb.checked = selectAll);
            }
        </script>

    </body>
    </html>
</x-adminLayout2>