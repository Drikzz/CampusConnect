<x-seller-layout>
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Order #{{ $order->id }}</h2>
                <div class="flex items-center gap-4">
                    <form action="{{ route('seller.orders.status', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300">
                            <option value="Pending" {{ $order->status === 'Pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="Processing" {{ $order->status === 'Processing' ? 'selected' : '' }}>
                                Processing</option>
                            <option value="Completed" {{ $order->status === 'Completed' ? 'selected' : '' }}>Completed
                            </option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="font-semibold mb-2">Customer Details</h3>
                    <p>Name: {{ $order->user->first_name }} {{ $order->user->last_name }}</p>
                    <p>Email: {{ $order->email }}</p>
                    <p>Phone: {{ $order->phone }}</p>
                    <p>Address: {{ $order->address }}</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2">Order Details</h3>
                    <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
                    <p>Status: {{ $order->status }}</p>
                    <p>Payment Method: {{ $order->payment_method }}</p>
                    <p>Delivery Estimate: {{ $order->delivery_estimate }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="font-semibold mb-4">Order Items</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                Product</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Price
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                Quantity</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($order->orderItems as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->product->name }}</td>
                                <td class="px-6 py-4">₱{{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4">{{ $item->quantity }}</td>
                                <td class="px-6 py-4">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-semibold">Total:</td>
                            <td class="px-6 py-4 font-semibold">₱{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-seller-layout>
