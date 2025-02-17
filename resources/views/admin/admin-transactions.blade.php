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
                <table class="w-full bg-white shadow rounded-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3 text-left">Order ID</th>
                            <th class="p-3 text-left">Buyer</th>
                            <th class="p-3 text-left">Seller Code</th>
                            <th class="p-3 text-left">Address</th>
                            <th class="p-3 text-left">Delivery Estimate</th>
                            <th class="p-3 text-left">Phone</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Sub Total</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Payment Method</th>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transaction-table-body">
                        @foreach ($transactions as $transaction)
                        <tr class="border-t">
                            <td class="p-3">{{ $transaction->id }}</td>
                            <td class="p-3">{{ $transaction->buyer->name }}</td>
                            <td class="p-3">{{ $transaction->seller_code }}</td>
                            <td class="p-3">{{ $transaction->address }}</td>
                            <td class="p-3">{{ $transaction->delivery_estimate }}</td>
                            <td class="p-3">{{ $transaction->phone }}</td>
                            <td class="p-3">{{ $transaction->email }}</td>
                            <td class="p-3">${{ number_format($transaction->sub_total, 2) }}</td>
                            <td class="p-3">{{ $transaction->status }}</td>
                            <td class="p-3">{{ $transaction->payment_method }}</td>
                            <td class="p-3">{{ $transaction->created_at->format('F j, Y') }}</td>
                            <td class="p-3">
                                <button class="text-blue-600" onclick="showTransactionDetails({{ $transaction->id }})">View</button>
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

        <!-- Transaction Details Modal -->
        <div id="transaction-details-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 w-1/2 overflow-y-auto max-h-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Transaction Details</h2>
                    <button class="text-gray-600 hover:text-gray-900" onclick="closeModal('transaction-details-modal')">✖</button>
                </div>
                <div id="transaction-details-content">
                    <!-- Transaction details will be loaded here -->
                </div>
            </div>
        </div>

        <script>
            function showTransactionDetails(transactionId) {
                fetch(`/admin/transactions/${transactionId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('transaction-details-content').innerHTML = html;
                        document.getElementById('transaction-details-modal').classList.remove('hidden');
                    });
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            function searchTransactions() {
                const query = document.getElementById('search').value.toLowerCase();
                const rows = document.querySelectorAll('#transaction-table-body tr');

                rows.forEach(row => {
                    const orderId = row.cells[0].textContent.toLowerCase();
                    const buyer = row.cells[1].textContent.toLowerCase();
                    const sellerCode = row.cells[2].textContent.toLowerCase();
                    const address = row.cells[3].textContent.toLowerCase();
                    const deliveryEstimate = row.cells[4].textContent.toLowerCase();
                    const phone = row.cells[5].textContent.toLowerCase();
                    const email = row.cells[6].textContent.toLowerCase();
                    const subTotal = row.cells[7].textContent.toLowerCase();
                    const status = row.cells[8].textContent.toLowerCase();
                    const paymentMethod = row.cells[9].textContent.toLowerCase();
                    const date = row.cells[10].textContent.toLowerCase();

                    if (orderId.includes(query) || buyer.includes(query) || sellerCode.includes(query) || address.includes(query) || deliveryEstimate.includes(query) || phone.includes(query) || email.includes(query) || subTotal.includes(query) || status.includes(query) || paymentMethod.includes(query) || date.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        </script>
    </body>
</x-adminLayout2>