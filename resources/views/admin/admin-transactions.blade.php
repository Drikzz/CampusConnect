<x-adminLayout2>
    <body class="bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">Transaction Management</h2>
            </div>
            <div class="flex gap-4 mb-4">
                <input type="text" id="search" placeholder="Search transactions..." class="border p-2 rounded w-full">
                <button class="border p-2 rounded flex items-center gap-2" onclick="searchTransactions()">
                    <span>Filters</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-200 text-left text-sm font-semibold">
                            <th class="p-3"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                            <th class="p-3">Order ID</th>
                            <th class="p-3">Buyer</th>
                            <th class="p-3">Seller Code</th>
                            <th class="p-3">Address</th>
                            <th class="p-3">Delivery Estimate</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Sub Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Payment Method</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transaction-table-body">
                        @foreach ($transactions as $transaction)
                        <tr class="border-t">
                            <td class="p-3"><input type="checkbox" class="selectTransaction" value="{{ $transaction->id }}"></td>
                            <td class="p-3">{{ $transaction->id }}</td>
                            <td class="p-3">{{ $transaction->buyer->name ?? 'N/A' }}</td>
                            <td class="p-3">{{ $transaction->seller->name ?? 'N/A' }}</td>
                            <td class="p-3">{{ $transaction->address }}</td>
                            <td class="p-3">{{ $transaction->delivery_estimate }}</td>
                            <td class="p-3">{{ $transaction->phone }}</td>
                            <td class="p-3">{{ $transaction->email }}</td>
                            <td class="p-3">${{ number_format($transaction->sub_total, 2) }}</td>
                            <td class="p-3">{{ $transaction->status }}</td>
                            <td class="p-3">{{ $transaction->payment_method }}</td>
                            <td class="p-3">{{ $transaction->created_at ? $transaction->created_at->format('F j, Y') : 'N/A' }}</td>
                            <td class="p-3">
                                <button class="bg-blue-500 text-white px-2 py-1 rounded" onclick="openViewModal({{ json_encode($transaction) }})">View</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded ml-2" onclick="deleteTransaction({{ $transaction->id }})">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-3 bg-gray-200 rounded">
                <p id="selected-count">0 transactions selected</p>
            </div>
        </div>

        <!-- View Transaction Modal -->
        <div id="viewTransactionModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="w-full max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg overflow-y-auto max-h-screen">
                <h1 class="text-2xl font-bold mb-4">Transaction Details</h1>
                <p class="text-gray-500">Order ID: <span id="viewTransactionId" class="font-semibold"></span></p>
                
                <!-- Order Status -->
                <div class="bg-gray-100 p-4 mt-4 shadow rounded-lg flex items-center gap-4">
                    <span id="orderStatusIcon" class="text-2xl"></span>
                    <span id="viewOrderStatus" class="text-lg font-semibold"></span>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4 relative">
                    <div id="progressBar" class="h-2.5 rounded-full"></div>
                    <div class="absolute inset-0 flex justify-between items-center text-xs text-gray-500">
                        <div class="w-1/4 text-center">
                            <i class="fas fa-hourglass-start"></i>
                            <span>Pending</span>
                        </div>
                        <div class="w-1/4 text-center">
                            <i class="fas fa-cogs"></i>
                            <span>Processing</span>
                        </div>
                        <div class="w-1/4 text-center">
                            <i class="fas fa-truck"></i>
                            <span>Shipped</span>
                        </div>
                        <div class="w-1/4 text-center">
                            <i class="fas fa-check-circle"></i>
                            <span>Delivered</span>
                        </div>
                    </div>
                </div>
                
                <!-- Buyer Info -->
                <div class="bg-white p-4 mt-6 shadow rounded-lg">
                    <h2 class="text-xl font-semibold mb-4">Buyer Details</h2>
                    <p><strong>Buyer:</strong> <span id="viewBuyerInfo"></span></p>
                    <p><strong>Phone:</strong> <span id="viewPhone"></span></p>
                    <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                </div>

                <!-- Seller Info -->
                <div class="bg-white p-4 mt-6 shadow rounded-lg">
                    <h2 class="text-xl font-semibold mb-4">Seller Details</h2>
                    <p><strong>Seller:</strong> <span id="viewSellerInfo"></span></p>
                </div>

                <!-- Order Info -->
                <div class="bg-white p-4 mt-6 shadow rounded-lg">
                    <h2 class="text-xl font-semibold mb-4">Order Details</h2>
                    <p><strong>Address:</strong> <span id="viewAddress"></span></p>
                    <p><strong>Delivery Estimate:</strong> <span id="viewDeliveryEstimate"></span></p>
                </div>
                
                <!-- Order Summary -->
                <div class="bg-white p-4 mt-6 shadow rounded-lg">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    <div id="viewOrderSummary"></div>
                    <hr class="my-4" />
                    <div class="flex justify-between font-bold">
                        <span>Total</span>
                        <span id="viewOrderTotal"></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" class="bg-red-500 text-white px-4 py-2 rounded" onclick="closeViewModal()">Back</button>
                </div>
            </div>
        </div>

        <script>
            function openViewModal(transaction) {
                document.getElementById('viewTransactionId').innerText = transaction.id;
                document.getElementById('viewOrderStatus').innerText = transaction.status;
                document.getElementById('viewBuyerInfo').innerText = transaction.buyer ? transaction.buyer.name : 'N/A';
                document.getElementById('viewSellerInfo').innerText = transaction.seller ? transaction.seller.name : 'N/A';
                document.getElementById('viewAddress').innerText = transaction.address;
                document.getElementById('viewDeliveryEstimate').innerText = transaction.delivery_estimate;
                document.getElementById('viewPhone').innerText = transaction.phone;
                document.getElementById('viewEmail').innerText = transaction.email;

                // Set progress bar and icon based on status
                const progressBar = document.getElementById('progressBar');
                const orderStatusIcon = document.getElementById('orderStatusIcon');
                progressBar.className = 'h-2.5 rounded-full'; // Reset class
                orderStatusIcon.className = 'text-2xl'; // Reset class
                switch (transaction.status) {
                    case 'Pending':
                        progressBar.style.width = '25%';
                        progressBar.classList.add('bg-yellow-500');
                        orderStatusIcon.classList.add('fas', 'fa-hourglass-start', 'text-yellow-500');
                        break;
                    case 'Processing':
                        progressBar.style.width = '50%';
                        progressBar.classList.add('bg-blue-500');
                        orderStatusIcon.classList.add('fas', 'fa-cogs', 'text-blue-500');
                        break;
                    case 'Shipped':
                        progressBar.style.width = '75%';
                        progressBar.classList.add('bg-indigo-500');
                        orderStatusIcon.classList.add('fas', 'fa-truck', 'text-indigo-500');
                        break;
                    case 'Delivered':
                        progressBar.style.width = '100%';
                        progressBar.classList.add('bg-green-500');
                        orderStatusIcon.classList.add('fas', 'fa-check-circle', 'text-green-500');
                        break;
                    default:
                        progressBar.style.width = '0%';
                        progressBar.classList.add('bg-gray-500');
                        orderStatusIcon.classList.add('fas', 'fa-question-circle', 'text-gray-500');
                        break;
                }
                
                let orderSummary = '';
                if (transaction.items) {
                    transaction.items.forEach(item => {
                        const imagePath = item.product && item.product.images.length > 0 ? `{{ asset('storage') }}/${item.product.images[0].path}` : `{{ asset('storage/default-image.jpg') }}`;
                        orderSummary += `<div class="flex justify-between border-b pb-2">
                                            <span>${item.product ? item.product.name : 'N/A'} (Quantity: ${item.quantity}, Price: ${item.price})</span>
                                            <img src="${imagePath}" class="w-12 h-12 rounded">
                                         </div>`;
                    });
                }
                document.getElementById('viewOrderSummary').innerHTML = orderSummary;
                document.getElementById('viewOrderTotal').innerText = transaction.total;

                document.getElementById('viewTransactionModal').classList.remove('hidden');
            }

            function closeViewModal() {
                document.getElementById('viewTransactionModal').classList.add('hidden');
            }

            function toggleSelectAll() {
                let checkboxes = document.querySelectorAll('.selectTransaction');
                let selectAll = document.getElementById('selectAll').checked;
                checkboxes.forEach(cb => cb.checked = selectAll);
            }

            function deleteTransaction(transactionId) {
                if (confirm('Are you sure you want to delete this transaction?')) {
                    fetch(`/admin/transactions/${transactionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete transaction.');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting transaction:', error);
                    });
                }
            }

            function searchTransactions() {
                const query = document.getElementById('search').value.toLowerCase();
                const rows = document.querySelectorAll('#transaction-table-body tr');

                rows.forEach(row => {
                    const orderId = row.cells[1].textContent.toLowerCase();
                    const buyer = row.cells[2].textContent.toLowerCase();
                    const sellerCode = row.cells[3].textContent.toLowerCase();
                    const address = row.cells[4].textContent.toLowerCase();
                    const deliveryEstimate = row.cells[5].textContent.toLowerCase();
                    const phone = row.cells[6].textContent.toLowerCase();
                    const email = row.cells[7].textContent.toLowerCase();
                    const subTotal = row.cells[8].textContent.toLowerCase();
                    const status = row.cells[9].textContent.toLowerCase();
                    const paymentMethod = row.cells[10].textContent.toLowerCase();
                    const date = row.cells[11].textContent.toLowerCase();

                    if (orderId.includes(query) || buyer.includes(query) || sellerCode.includes(query) || address.includes(query) || deliveryEstimate.includes(query) || phone.includes(query) || email.includes(query) || subTotal.includes(query) || status.includes(query) || paymentMethod.includes(query) || date.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        </script>
        <!-- Font Awesome for icons -->
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    </body>
</x-adminLayout2>