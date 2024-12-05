<x-seller-layout>
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-4">Orders</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order ID</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#{{ $order->id }}</td>
                                <td class="px-6 py-4">{{ $order->user->first_name }} {{ $order->user->last_name }}</td>
                                <td class="px-6 py-4">₱{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $order->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'Processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->status === 'Completed' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('seller.orders.show', $order) }}"
                                        class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-seller-layout>
