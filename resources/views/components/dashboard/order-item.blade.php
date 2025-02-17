<li class="py-4">
    <div class="flex items-center space-x-4">
        <div class="flex-shrink-0">
            {{-- @if ($order->products->first()?->image) --}}
            <img class="h-12 w-12 rounded-lg object-cover" src="" alt="">
            {{-- @else --}}
            <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            {{-- @endif --}}
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900">
                Order #{{ $order->id }}
            </p>
            <p class="truncate text-sm text-gray-500">
                {{-- {{ $order->created_at->format('M d, Y') }} --}}
            </p>
        </div>
        <div>
            <span @class([
                'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                'bg-blue-100 text-blue-800' => $order->status === 'processing',
                'bg-green-100 text-green-800' => $order->status === 'completed',
                'bg-red-100 text-red-800' => $order->status === 'cancelled',
            ])>
                {{ ucfirst($order->status) }}
            </span>
        </div>
        <div class="text-sm font-medium text-gray-900">
            ₱{{ number_format($order->total_amount, 2) }}
        </div>
        <div>
            <a href="" class="text-primary-600 hover:text-primary-900">
                View
            </a>
        </div>
    </div>
</li>
