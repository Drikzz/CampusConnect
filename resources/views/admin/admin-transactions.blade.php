<x-adminLayout2>
    <body class="bg-gray-100 p-6">
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Transaction Management</h2>
            </div>

            <!-- Cards for Transaction Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Total Transactions</h3>
                        <p class="text-2xl">{{ $totalTransactions }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Pending</h3>
                        <p class="text-2xl">{{ $pendingTransactions }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Processing</h3>
                        <p class="text-2xl">{{ $processingTransactions }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Shipped</h3>
                        <p class="text-2xl">{{ $shippedTransactions }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Delivered</h3>
                        <p class="text-2xl">{{ $deliveredTransactions }}</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="search" class="w-full p-2 border rounded md:w-1/2" placeholder="Search transactions..." onkeyup="searchTransactions()">
            </div>

            <!-- Bulk Actions -->
            <div class="mb-4 flex space-x-2">
                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="deleteSelectedTransactions()">Delete Selected</button>
            </div>

            <!-- Transaction Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-200 text-left text-sm font-semibold">
                            <th class="p-3"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                            <th class="p-3">Order ID</th>
                            <th class="p-3">Buyer</th>
                            <th class="p-3">Seller</th>
                            <th class="p-3">Product Name</th>
                            <th class="p-3">Product Quantity</th>
                            <th class="p-3">Sub Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transaction-table-body">
                        @foreach ($transactions as $transaction)
                        <tr class="border-t">
                            <td class="p-3"><input type="checkbox" class="selectTransaction" value="{{ $transaction->id }}"></td>
                            <td class="p-3">{{ $transaction->id }}</td>
                            <td class="p-3">{{ $transaction->buyer->first_name ?? 'N/A' }} {{ $transaction->buyer->last_name ?? '' }}<br>{{ $transaction->buyer->wmsu_email ?? 'N/A' }}</td>
                            <td class="p-3">{{ $transaction->seller->first_name ?? 'N/A' }} {{ $transaction->seller->last_name ?? '' }}<br>{{ $transaction->seller->wmsu_email ?? 'N/A' }}</td>
                            <td class="p-3">
                                @foreach ($transaction->items as $item)
                                    {{ $item->product->name ?? 'N/A' }}<br>
                                @endforeach
                            </td>
                            <td class="p-3">
                                @foreach ($transaction->items as $item)
                                    {{ $item->quantity }}<br>
                                @endforeach
                            </td>
                            <td class="p-3">${{ number_format($transaction->sub_total, 2) }}</td>
                            <td class="p-3">{{ $transaction->status }}</td>
                            <td class="p-3">{{ $transaction->created_at ? $transaction->created_at->format('F j, Y') : 'N/A' }}</td>
                            <td class="p-3 flex space-x-2">
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="openViewModal({{ json_encode($transaction) }})">View</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="deleteTransaction({{ $transaction->id }})">Delete</button>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="cancelTransaction({{ $transaction->id }})">Cancel</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
                            <i class="fas fa-box status-icon"></i> ⏳ Pending
                        </div>
                        <div class="w-1/4 text-center">
                            <i class="fas fa-box status-icon"></i> 🔄 Processing
                        </div>
                        <div class="w-1/4 text-center">
                            <i class="fas fa-shipping-fast status-icon"></i> 🚚 Shipped
                        </div>
                        <div class="w-1/4 text-center">
                            <i class="fas fa-box-open status-icon"></i> ✅ Delivered
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
                document.getElementById('viewBuyerInfo').innerText = transaction.buyer ? `${transaction.buyer.first_name} ${transaction.buyer.last_name}` : 'N/A';
                document.getElementById('viewSellerInfo').innerText = transaction.seller ? `${transaction.seller.first_name} ${transaction.seller.last_name}` : 'N/A';
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
                        progressBar.classList.add('bg-red-500');
                        orderStatusIcon.classList.add('fas', 'fa-box', 'text-red-500');
                        break;
                    case 'Processing':
                        progressBar.style.width = '50%';
                        progressBar.classList.add('bg-red-500');
                        orderStatusIcon.classList.add('fas', 'fa-box', 'text-red-500');
                        break;
                    case 'Shipped':
                        progressBar.style.width = '75%';
                        progressBar.classList.add('bg-red-500');
                        orderStatusIcon.classList.add('fas', 'fa-shipping-fast', 'text-red-500');
                        break;
                    case 'Delivered':
                        progressBar.style.width = '100%';
                        progressBar.classList.add('bg-red-500');
                        orderStatusIcon.classList.add('fas', 'fa-box-open', 'text-red-500');
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
                document.getElementById('viewOrderTotal').innerText = `$${parseFloat(transaction.sub_total).toFixed(2)}`;

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
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'Transaction deleted successfully') {
                            location.reload();
                        } else {
                            alert('Failed to delete transaction.');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting transaction:', error);
                        alert('Failed to delete transaction.');
                    });
                }
            }

            function deleteSelectedTransactions() {
                const selectedIds = Array.from(document.querySelectorAll('.selectTransaction:checked')).map(cb => cb.value);
                if (selectedIds.length === 0) {
                    alert('No transactions selected.');
                    return;
                }

                if (confirm('Are you sure you want to delete the selected transactions?')) {
                    fetch(`/admin/transactions/bulk-delete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'Transactions deleted successfully') {
                            location.reload();
                        } else {
                            alert('Failed to delete transactions.');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting transactions:', error);
                        alert('Failed to delete transactions.');
                    });
                }
            }

            function cancelTransaction(transactionId) {
                if (confirm('Are you sure you want to cancel this transaction?')) {
                    fetch(`/admin/transactions/${transactionId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'Transaction cancelled successfully') {
                            location.reload();
                        } else {
                            alert('Failed to cancel transaction.');
                        }
                    })
                    .catch(error => {
                        console.error('Error cancelling transaction:', error);
                        alert('Failed to cancel transaction.');
                    });
                }
            }

            function searchTransactions() {
                const query = document.getElementById('search').value.toLowerCase();
                const rows = document.querySelectorAll('#transaction-table-body tr');

                rows.forEach(row => {
                    const orderId = row.cells[1].textContent.toLowerCase();
                    const buyer = row.cells[2].textContent.toLowerCase();
                    const seller = row.cells[3].textContent.toLowerCase();
                    const productName = row.cells[4].textContent.toLowerCase();
                    const productQuantity = row.cells[5].textContent.toLowerCase();
                    const subTotal = row.cells[6].textContent.toLowerCase();
                    const status = row.cells[7].textContent.toLowerCase();
                    const date = row.cells[8].textContent.toLowerCase();

                    if (orderId.includes(query) || buyer.includes(query) || seller.includes(query) || productName.includes(query) || productQuantity.includes(query) || subTotal.includes(query) || status.includes(query) || date.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Add event listener for responsive search
            document.getElementById('search').addEventListener('input', searchTransactions);
        </script>
        <!-- Font Awesome for icons -->
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    </body>
</x-adminLayout2>