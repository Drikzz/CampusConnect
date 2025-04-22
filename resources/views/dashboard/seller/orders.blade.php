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
                <p class="text-2xl font-bold">{{ $totalSales }}</p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white p-4 rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->created_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->buyer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->buyer->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->price }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->status }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($order->status !== 'Delivered')
                            <form action="{{ route('orders.mark-as-delivered', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Mark as Delivered</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $orders->links() }}
        </div>
    </div>
@endsection
