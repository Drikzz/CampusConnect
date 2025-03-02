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
                    <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="openAddModal()">Add Product</button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="searchBar" class="w-full p-2 border rounded md:w-1/2" placeholder="Search products..." onkeyup="searchProducts()">
            </div>

            <!-- Bulk Actions -->
            <div class="mb-4 flex space-x-2">
                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="bulkDelete()">Delete Selected</button>
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
                            <td class="p-3">₱{{ number_format($product->price, 2) }}</td>
                            <td class="p-3">{{ $product->discount }}%</td>
                            <td class="p-3">₱{{ number_format($product->discounted_price, 2) }}</td>
                            <td class="p-3 flex space-x-2">
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="editProduct({{ $product->id }})">Edit</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="deleteProduct({{ $product->id }})">Delete</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="openViewModal({{ json_encode($product) }})">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div id="addProductModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg overflow-y-auto max-h-screen">
                <h2 class="text-2xl font-bold mb-4">Add Product</h2>
                <form id="addProductForm" method="POST" action="{{ route('admin.productManagement.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="addProductName">Product Name</label>
                            <input type="text" class="form-control" id="addProductName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="addProductCategory">Category</label>
                            <select class="form-control" id="addProductCategory" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="addProductPrice">Price</label>
                            <input type="number" class="form-control" id="addProductPrice" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="addProductDiscount">Discount (%)</label>
                            <input type="number" class="form-control" id="addProductDiscount" name="discount" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="addProductDiscountedPrice">Discounted Price</label>
                            <input type="number" class="form-control" id="addProductDiscountedPrice" name="discounted_price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="addProductStock">Stock</label>
                            <input type="number" class="form-control" id="addProductStock" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label for="addProductSellerCode">Seller Code</label>
                            <input type="text" class="form-control" id="addProductSellerCode" name="seller_code" required>
                        </div>
                        <div class="form-group">
                            <label for="addProductImages">Images</label>
                            <input type="file" class="form-control" id="addProductImages" name="images[]" multiple>
                        </div>
                        <div class="form-group">
                            <label for="addProductIsBuyable">Is Buyable</label>
                            <input type="checkbox" class="form-control" id="addProductIsBuyable" name="is_buyable" checked>
                        </div>
                        <div class="form-group">
                            <label for="addProductIsTradable">Is Tradable</label>
                            <input type="checkbox" class="form-control" id="addProductIsTradable" name="is_tradable">
                        </div>
                        <div class="form-group">
                            <label for="addProductStatus">Status</label>
                            <select class="form-control" id="addProductStatus" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" class="bg-red-500 text-white px-4 py-2 rounded" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Product</button>
                    </div>
                </form>
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
                    <div class="border-2 border-gray-300 p-2 rounded-lg text-center">
                        <img id="viewProductImage" class="mx-auto w-48 h-48 rounded-lg object-cover">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" class="bg-red-500 text-white px-4 py-2 rounded" onclick="closeViewModal()">Back</button>
                </div>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div id="editProductModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg overflow-y-auto max-h-screen">
                <h2 class="text-2xl font-bold mb-4">Edit Product</h2>
                <form id="editProductForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="productName">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="productDescription">Description</label>
                            <textarea class="form-control" id="productDescription" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="productCategory">Category</label>
                            <select class="form-control" id="productCategory" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="productPrice">Price</label>
                            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="productDiscount">Discount (%)</label>
                            <input type="number" class="form-control" id="productDiscount" name="discount" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="productDiscountedPrice">Discounted Price</label>
                            <input type="number" class="form-control" id="productDiscountedPrice" name="discounted_price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="productStock">Stock</label>
                            <input type="number" class="form-control" id="productStock" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label for="productSellerCode">Seller Code</label>
                            <input type="text" class="form-control" id="productSellerCode" name="seller_code" required>
                        </div>
                        <div class="form-group">
                            <label for="productImages">Images</label>
                            <input type="file" class="form-control" id="productImages" name="images[]" multiple>
                        </div>
                        <div class="form-group">
                            <label for="productIsBuyable">Is Buyable</label>
                            <input type="checkbox" class="form-control" id="productIsBuyable" name="is_buyable">
                        </div>
                        <div class="form-group">
                            <label for="productIsTradable">Is Tradable</label>
                            <input type="checkbox" class="form-control" id="productIsTradable" name="is_tradable">
                        </div>
                        <div class="form-group">
                            <label for="productStatus">Status</label>
                            <select class="form-control" id="productStatus" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" class="bg-red-500 text-white px-4 py-2 rounded" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save changes</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openAddModal() {
                document.getElementById('addProductModal').classList.remove('hidden');
            }

            function closeAddModal() {
                document.getElementById('addProductModal').classList.add('hidden');
            }

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

            function searchProducts() {
                let input = document.getElementById('searchBar').value.toLowerCase();
                let rows = document.querySelectorAll('#productTable tr');
                rows.forEach(row => {
                    let productName = row.querySelector('td:nth-child(3)').innerText.toLowerCase();
                    if (productName.includes(input)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            function deleteProduct(productId) {
                if (confirm('Are you sure you want to delete this product?')) {
                    fetch(`/admin/products/${productId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            location.reload();
                        } else {
                            alert('Failed to delete the product.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete the product.');
                    });
                }
            }

            function bulkDelete() {
                let selectedProducts = document.querySelectorAll('.selectProduct:checked');
                if (selectedProducts.length === 0) {
                    alert('No products selected.');
                    return;
                }

                if (confirm('Are you sure you want to delete the selected products?')) {
                    let productIds = Array.from(selectedProducts).map(cb => cb.value);
                    fetch(`/admin/products/bulk-delete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ ids: productIds })
                    })
                    .then(response => {
                        if (response.ok) {
                            location.reload();
                        } else {
                            alert('Failed to delete the selected products.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete the selected products.');
                    });
                }
            }

            function editProduct(productId) {
                fetch(`/admin/productManagement/${productId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(product => {
                        document.getElementById('editProductForm').action = `/admin/productManagement/${productId}`;
                        document.getElementById('productName').value = product.name;
                        document.getElementById('productDescription').value = product.description;
                        document.getElementById('productPrice').value = product.price;
                        document.getElementById('productDiscount').value = product.discount;
                        document.getElementById('productDiscountedPrice').value = product.discounted_price;
                        document.getElementById('productStock').value = product.stock;
                        document.getElementById('productSellerCode').value = product.seller_code;
                        document.getElementById('productCategory').value = product.category_id;
                        document.getElementById('productIsBuyable').checked = product.is_buyable;
                        document.getElementById('productIsTradable').checked = product.is_tradable;
                        document.getElementById('productStatus').value = product.status;
                        document.getElementById('editProductModal').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to fetch product details. Please try again later.');
                    });
            }

            function closeEditModal() {
                document.getElementById('editProductModal').classList.add('hidden');
            }
        </script>

    </body>
    </html>
</x-adminLayout2>