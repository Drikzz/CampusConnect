<x-sellerLayout>
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Orders Management</h2>
            </div>

            {{-- Order Filters --}}
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-wrap gap-4">
                    <button data-filter="all"
                        class="filter-btn px-4 py-2 rounded-lg bg-primary-color text-white hover:bg-primary-color/90">
                        All Orders
                    </button>
                    <button data-filter="Pending"
                        class="filter-btn px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600">
                        Pending ({{ $orderCounts->pendingCount }})
                    </button>
                    <button data-filter="Completed"
                        class="filter-btn px-4 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600">
                        Completed ({{ $orderCounts->completedCount }})
                    </button>
                </div>
            </div>

            {{-- Orders Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr class="order-row" data-status="{{ $order->status }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full"
                                                src="{{ $order->buyer->profile_picture ? Storage::url($order->buyer->profile_picture) : asset('images/default-avatar.png') }}"
                                                alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $order->buyer->first_name }} {{ $order->buyer->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $order->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₱{{ number_format($order->sub_total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $order->status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openOrderModal({{ $order->id }})"
                                        class="text-primary-color hover:text-primary-color/90">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 justify-center items-center w-full md:inset-0 h-full max-h-full backdrop-blur-sm bg-black/30">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-2xl dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Order Details
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        onclick="closeModal()">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4" id="orderDetails">
                    <!-- Order details will be populated here -->
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button type="button" id="markCompleted"
                        class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Mark as Completed
                    </button>
                    <button type="button" onclick="closeModal()"
                        class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const orderRows = document.querySelectorAll('.order-row');

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const filter = button.dataset.filter;

                    orderRows.forEach(row => {
                        if (filter === 'all' || row.dataset.status === filter) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Update active button state
                    filterButtons.forEach(btn => btn.classList.remove('bg-gray-800'));
                    button.classList.add('bg-gray-800');
                });
            });
        });

        let currentOrderId = null;

        function openOrderModal(orderId) {
            currentOrderId = orderId;
            const modal = document.getElementById('orderModal');
            const orderDetails = document.getElementById('orderDetails');
            document.body.style.overflow = 'hidden'; // Prevent scrolling

            // Show modal with flex display
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Fetch order details
            fetch(`/seller/orders/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    orderDetails.innerHTML = `
                        <div class="space-y-3">
                            <p class="text-base leading-relaxed text-gray-500">
                                <strong>Order ID:</strong> #${data.id}
                            </p>
                            <p class="text-base leading-relaxed text-gray-500">
                                <strong>Customer:</strong> ${data.buyer.first_name} ${data.buyer.last_name}
                            </p>
                            <p class="text-base leading-relaxed text-gray-500">
                                <strong>Status:</strong> ${data.status}
                            </p>
                            <p class="text-base leading-relaxed text-gray-500">
                                <strong>Total:</strong> ₱${data.sub_total}
                            </p>
                            <div class="mt-4">
                                <h4 class="font-medium mb-2">Items:</h4>
                                ${data.items.map(item => `
                                                                <div class="p-3 bg-gray-50 rounded-lg mb-2">
                                                                    <p class="font-medium">${item.product.name}</p>
                                                                    <p class="text-gray-600">Quantity: ${item.quantity}</p>
                                                                    <p class="text-gray-600">Price: ₱${item.price}</p>
                                                                </div>
                                                            `).join('')}
                            </div>
                        </div>
                    `;
                });
        }

        function closeModal() {
            const modal = document.getElementById('orderModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        document.getElementById('markCompleted').addEventListener('click', () => {
            if (!currentOrderId) return;

            fetch(`/seller/orders/${currentOrderId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    }
                });
        });

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</x-sellerLayout>
