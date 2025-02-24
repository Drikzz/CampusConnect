@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            {{-- Back Button and Order Status Header --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('seller.index') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold">Order #{{ $order->id }}</h1>
                </div>
                <span
                    class="px-4 py-2 rounded-full text-sm font-semibold
                    @if ($order->status === 'Completed') bg-green-100 text-green-800
                    @else bg-blue-100 text-blue-800 @endif">
                    {{ $order->status }}
                </span>
            </div>

            {{-- Order Details Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Buyer Information Card --}}
                <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Buyer Information</h2>
                    <div class="flex items-center mb-4">
                        <img class="h-12 w-12 rounded-full object-cover"
                            src="{{ asset('storage/' . $order->buyer->profile_picture) }}"
                            alt="{{ $order->buyer->first_name }}">
                        <div class="ml-4">
                            <div class="font-medium text-gray-900">
                                {{ $order->buyer->first_name }} {{ $order->buyer->last_name }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $order->buyer->wmsu_email }}</div>
                            <div class="text-sm text-gray-500">{{ $order->phone }}</div>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <div class="text-sm font-medium text-gray-600">Delivery Address:</div>
                        <div class="text-gray-800 mt-1">{{ $order->address }}</div>
                    </div>
                </div>

                {{-- Order Summary Card --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Order Date</span>
                            <span class="text-gray-900">{{ $order->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Order Status</span>
                            <span
                                class="font-medium 
                                @if ($order->status === 'Completed') text-green-600
                                @elseif($order->status === 'Processing') text-yellow-600
                                @else text-blue-600 @endif">
                                {{ $order->status }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Method</span>
                            <span class="text-gray-900">{{ $order->payment_method }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t pt-3">
                            <span class="text-gray-600">Total Amount</span>
                            <span
                                class="text-lg font-semibold text-gray-900">₱{{ number_format($order->sub_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Items Table --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach ($order->items as $item)
                        <div class="p-6 flex items-center">
                            <img class="h-20 w-20 rounded-lg object-cover"
                                src="{{ asset('storage/' . $item->product->main_image) }}"
                                alt="{{ $item->product->name }}">
                            <div class="ml-6 flex-1">
                                <h3 class="text-base font-medium text-gray-900">{{ $item->product->name }}</h3>
                                <p class="text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                                <p class="text-sm text-gray-500">Price per item: ₱{{ number_format($item->price, 2) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-medium text-gray-900">
                                    ₱{{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Update Status Form --}}
            @if ($order->status !== 'Completed')
                <form action="{{ route('seller.orders.status', $order) }}" method="POST"
                    class="bg-white rounded-lg shadow-md p-6">
                    @csrf
                    @method('PATCH')
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Order Status</h2>
                    <div class="flex items-center space-x-4">
                        <select name="status"
                            class="flex-1 rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color">
                            <option value="Pending" {{ $order->status === 'Pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="Completed" {{ $order->status === 'Completed' ? 'selected' : '' }}>Completed
                            </option>
                        </select>
                        <button type="submit"
                            class="px-6 py-2 bg-primary-color text-white rounded-lg hover:bg-primary-color/90 transition-colors">
                            Update Status
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
