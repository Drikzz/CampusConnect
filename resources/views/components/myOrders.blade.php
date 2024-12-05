{{-- Orders Tab Content --}}
<div class="max-w-5xl mx-auto py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        {{-- Order Status Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8" aria-label="Orders">
                <button class="border-b-2 border-primary-color text-primary-color py-4 px-1 text-sm font-medium">
                    Pending
                </button>
                <button
                    class="border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    To Pay
                </button>
                <button
                    class="border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium">
                    Completed
                </button>
            </nav>
        </div>

        {{-- Orders List --}}
        <div class="space-y-4">
            @forelse (auth()->user()->orders as $order)
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Order #{{ $order->id }}</p>
                            <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('F d, Y') }}</p>
                        </div>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-medium 
                            @if ($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'completed') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    @foreach ($order->items as $item)
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                class="w-20 h-20 object-cover rounded-md">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium">{{ $item->product->name }}</h4>
                                <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                <p class="text-lg font-semibold text-primary-color">
                                    ₱{{ number_format($item->price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-end space-x-2 border-t pt-4">
                        <button
                            onclick="document.getElementById('order-modal-{{ $order->id }}').classList.remove('hidden')"
                            class="px-4 py-2 bg-primary-color text-white rounded-lg hover:bg-primary-color/90 text-sm">
                            View Details
                        </button>
                        @if ($order->status === 'pending')
                            <button
                                class="px-4 py-2 border border-primary-color text-primary-color rounded-lg hover:bg-primary-color/10 text-sm">
                                Cancel Order
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders</h3>
                    <p class="mt-1 text-sm text-gray-500">You haven't placed any orders yet.</p>
                    <div class="mt-6">
                        <a href="{{ route('products') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-color hover:bg-primary-color/90">
                            Start Shopping
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Modals Container (outside of space-y-4) --}}
        @foreach (auth()->user()->orders as $order)
            <div id="order-modal-{{ $order->id }}"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                <div class="relative mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold">Order Details #{{ $order->id }}</h3>
                        <button
                            onclick="document.getElementById('order-modal-{{ $order->id }}').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-500">Order Date</p>
                                <p class="font-medium">{{ $order->created_at->format('F d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="font-medium capitalize">{{ $order->status }}</p>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <h4 class="font-medium mb-2">Order Items</h4>
                            @foreach ($order->items as $item)
                                <div class="flex items-center space-x-4 mb-4 border-b pb-4">
                                    <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                        class="w-20 h-20 object-cover rounded-md">
                                    <div class="flex-1">
                                        <h5 class="font-medium">{{ $item->product->name }}</h5>
                                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                        <p class="text-primary-color font-medium">
                                            ₱{{ number_format($item->price, 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">Total Amount:</span>
                                <span class="text-lg font-semibold text-primary-color">
                                    ₱{{ number_format($order->items->sum('price'), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
