@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Orders Management</h2>
        </div>

        <!-- Order Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Pending Orders</h3>
                <p class="text-2xl font-bold">{{ $pendingOrders }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Active Orders</h3>
                <p class="text-2xl font-bold">{{ $activeOrders }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Total Orders</h3>
                <p class="text-2xl font-bold">{{ $totalOrders }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Total Sales</h3>
                <p class="text-2xl font-bold">₱{{ number_format($totalSales, 2) }}</p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
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
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $order->buyer->first_name }} {{ $order->buyer->last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $order->buyer->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ₱{{ number_format($order->sub_total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select onchange="updateOrderStatus({{ $order->id }}, this.value)"
                                        class="rounded-full text-sm font-semibold px-3 py-1
                                        @if ($order->status == 'Completed') bg-green-100 text-green-800
                                        @elseif($order->status == 'Cancelled') bg-red-100 text-red-800
                                        @elseif($order->status == 'Processing') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        <option value="Pending" @if ($order->status == 'Pending') selected @endif>Pending
                                        </option>
                                        <option value="Processing" @if ($order->status == 'Processing') selected @endif>
                                            Processing</option>
                                        <option value="Completed" @if ($order->status == 'Completed') selected @endif>
                                            Completed</option>
                                        <option value="Cancelled" @if ($order->status == 'Cancelled') selected @endif>
                                            Cancelled</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('seller.orders.show', $order->id) }}"
                                        class="text-primary-color hover:text-primary-color/80">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function updateOrderStatus(orderId, newStatus) {
            fetch(`/dashboard/seller/orders/${orderId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the page to update the stats
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error updating order status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating order status');
                });
        }
    </script>
@endsection
