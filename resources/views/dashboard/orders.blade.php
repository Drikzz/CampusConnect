@extends('dashboard.dashboard')

@section('dashboard-content')
    <h2 class="text-2xl font-bold mb-6">My Orders</h2>

    @if ($orders->isEmpty())
        <div class="text-center py-8">
            <p class="text-gray-500">You haven't placed any orders yet</p>
            <a href="{{ route('products') }}" class="text-primary-color hover:underline mt-2 inline-block">
                Start Shopping
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($orders as $order)
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-semibold">Order #{{ $order->id }}</h3>
                            <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <span
                            class="px-3 py-1 rounded-full text-sm 
                            @if ($order->status == 'Completed') bg-green-100 text-green-800
                            @elseif($order->status == 'Cancelled') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $order->status }}
                        </span>
                    </div>

                    <div class="divide-y">
                        @foreach ($order->items as $item)
                            <div class="py-4 flex items-center gap-4">
                                <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}"
                                    class="w-16 h-16 object-cover rounded">

                                <div class="flex-1">
                                    <h4 class="font-medium">{{ $item->product->name }}</h4>
                                    <p class="text-sm text-gray-500">
                                        ₱{{ number_format($item->price, 2) }} × {{ $item->quantity }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-medium">₱{{ number_format($item->subtotal, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total Amount</p>
                            <p class="text-lg font-semibold">₱{{ number_format($order->sub_total, 2) }}</p>
                        </div>

                        @if ($order->status == 'Pending')
                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-red-500 hover:underline">
                                    Cancel Order
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
