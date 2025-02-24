@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="space-y-6">
        <!-- Back Button -->
        <a href="{{ route('seller.orders') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Orders
        </a>

        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Order Details #{{ $order->id }}</h2>
            <span
                class="px-3 py-1 text-sm font-semibold rounded-full
                @if ($order->status == 'Completed') bg-green-100 text-green-800
                @elseif($order->status == 'Cancelled') bg-red-100 text-red-800
                @elseif($order->status == 'Disputed') bg-orange-100 text-orange-800
                @elseif($order->status == 'Processing') bg-yellow-100 text-yellow-800
                @else bg-blue-100 text-blue-800 @endif">
                {{ $order->status }}
            </span>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Customer Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Name</p>
                    <p class="font-medium">{{ $order->buyer->first_name }} {{ $order->buyer->last_name }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Email</p>
                    <p class="font-medium">{{ $order->buyer->wmsu_email }}</p>
                </div>
            </div>
        </div>

        <!-- Order Status and Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Order Status</h3>

            @if ($order->status === 'Pending')
                <div class="space-y-4">
                    <p class="text-gray-600">This order is waiting for your acceptance.</p>
                    <button id="acceptOrderBtn"
                        class="bg-primary-color text-white px-4 py-2 rounded-lg hover:bg-primary-color/90">
                        Accept Order
                    </button>
                </div>
            @elseif($order->status === 'Accepted')
                <div class="space-y-4">
                    <p class="text-gray-600">Please schedule a meetup with the buyer.</p>
                    <form id="meetupForm" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Meetup Location</label>
                            <select name="meetup_location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required>
                                <option value="">Select a location</option>
                                @foreach ($meetupLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }} - {{ $location->address }}
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('seller.meetup-locations') }}"
                                class="text-sm text-primary-color hover:text-primary-color/90">
                                Manage meetup locations
                            </a>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Meetup Schedule</label>
                            <input type="datetime-local" name="meetup_schedule"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <button type="submit"
                            class="bg-primary-color text-white px-4 py-2 rounded-lg hover:bg-primary-color/90">
                            Schedule Meetup
                        </button>
                    </form>
                </div>
            @elseif($order->status === 'Meetup Scheduled')
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="font-medium">Scheduled Meetup Details:</p>
                        <p>Location: {{ $order->meetupLocation->name }}</p>
                        <p>Address: {{ $order->meetupLocation->address }}</p>
                        @if ($order->meetupLocation->landmark)
                            <p>Landmark: {{ $order->meetupLocation->landmark }}</p>
                        @endif
                        <p>Schedule: {{ \Carbon\Carbon::parse($order->meetup_schedule)->format('F j, Y g:i A') }}</p>
                    </div>
                    <button id="markDeliveredBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Mark as Delivered
                    </button>
                </div>
            @elseif($order->status === 'Delivered')
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">Item has been delivered. Waiting for buyer confirmation.</p>
                    <p class="text-sm text-gray-500 mt-2">Payment will be released once the buyer confirms receipt.</p>
                </div>
            @elseif($order->status === 'Disputed')
                <div class="p-4 bg-orange-50 rounded-lg">
                    <p class="text-orange-700 font-medium">This order is under dispute</p>
                    <p class="text-sm text-orange-600 mt-2">Our admin team will contact you soon.</p>
                </div>
            @elseif($order->status === 'Completed')
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-green-700">Transaction completed successfully</p>
                    <p class="text-sm text-green-600 mt-2">Payment has been released to your account.</p>
                </div>
            @endif

            @if (in_array($order->status, ['Pending', 'Accepted', 'Meetup Scheduled']))
                <button id="cancelOrderBtn" class="mt-4 text-red-600 hover:text-red-800">
                    Cancel Order
                </button>
            @endif
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Order Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="{{ asset('storage/' . $item->product->images[0]) }}"
                                            alt="{{ $item->product->name }}"
                                            class="h-10 w-10 rounded-lg object-cover mr-3">
                                        <span class="font-medium">{{ $item->product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">₱{{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4">{{ $item->quantity }}</td>
                                <td class="px-6 py-4">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-semibold">Total Amount:</td>
                            <td class="px-6 py-4 font-semibold">₱{{ number_format($order->sub_total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight the Orders nav item when in order details
            const ordersNavItem = document.querySelector('[href="{{ route('seller.orders') }}"]');
            if (ordersNavItem) {
                ordersNavItem.classList.add('bg-primary-color/10', 'text-primary-color');
            }

            // Accept Order
            const acceptOrderBtn = document.getElementById('acceptOrderBtn');
            if (acceptOrderBtn) {
                acceptOrderBtn.addEventListener('click', function() {
                    updateOrderStatus('Accepted');
                });
            }

            // Schedule Meetup
            const meetupForm = document.getElementById('meetupForm');
            if (meetupForm) {
                meetupForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(meetupForm);
                    fetch(`/seller/orders/{{ $order->id }}/schedule-meetup`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        });
                });
            }

            // Mark as Delivered
            const markDeliveredBtn = document.getElementById('markDeliveredBtn');
            if (markDeliveredBtn) {
                markDeliveredBtn.addEventListener('click', function() {
                    updateOrderStatus('Delivered');
                });
            }

            // Cancel Order
            const cancelOrderBtn = document.getElementById('cancelOrderBtn');
            if (cancelOrderBtn) {
                cancelOrderBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to cancel this order?')) {
                        updateOrderStatus('Cancelled');
                    }
                });
            }

            function updateOrderStatus(status) {
                fetch(`/seller/orders/{{ $order->id }}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
            }
        });
    </script>
@endsection
