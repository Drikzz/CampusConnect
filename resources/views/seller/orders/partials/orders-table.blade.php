@forelse($recentOrders as $order)
    <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">Order #{{ $order->id }}</div>
            <div class="text-sm text-gray-500">{{ $order->payment_method }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full overflow-hidden">
                    <img src="{{ asset('storage/' . $order->buyer->profile_picture) }}" alt=""
                        class="h-full w-full object-cover">
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                        {{ $order->buyer->first_name }} {{ $order->buyer->last_name }}
                    </div>
                    <div class="text-sm text-gray-500">{{ $order->buyer->wmsu_email }}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">₱{{ number_format($order->sub_total, 2) }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                {{ $order->status === 'Completed'
                    ? 'bg-green-100 text-green-800'
                    : ($order->status === 'Pending'
                        ? 'bg-yellow-100 text-yellow-800'
                        : 'bg-gray-100 text-gray-800') }}">
                {{ $order->status }}
            </span>
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
@if ($recentOrders->hasPages())
    <tr>
        <td colspan="6" class="px-6 py-4 border-t border-gray-200">
            {{ $recentOrders->links() }}
        </td>
    </tr>
@endif
