<x-layout>
    <div class="p-4 sm:ml-64">
        {{-- Breadcrumb --}}
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="" class="text-gray-700 hover:text-primary-600">Home</a></li>
                <li aria-current="page" class="flex items-center">
                    <svg class="w-3 h-3 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <span class="text-gray-500">Dashboard</span>
                </li>
            </ol>
        </nav>

        {{-- Dashboard Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ auth()->user()->first_name }}!</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your account, orders, and listings all in one place.</p>
        </div>

        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-dashboard.stat-card title="Pending Orders" value="{{ $pendingOrdersCount ?? 0 }}" icon="shopping-bag"
                color="blue" />

            <x-dashboard.stat-card title="To Pay" value="{{ $toPayCount ?? 0 }}" icon="credit-card" color="yellow" />

            <x-dashboard.stat-card title="Completed Orders" value="{{ $completedOrdersCount ?? 0 }}" icon="check-circle"
                color="green" />

            <x-dashboard.stat-card title="Favorites" value="{{ $favoritesCount ?? 0 }}" icon="heart" color="red" />
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recent Orders --}}
            <div class="lg:col-span-2">
                <div class="p-6 bg-white rounded-lg shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Recent Orders</h2>
                        <a href="" class="text-primary-600 hover:underline text-sm">View
                            all</a>
                    </div>

                    {{-- @if ($recentOrders && $recentOrders->count() > 0) --}}
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            {{-- @foreach ($recentOrders as $order) --}}
                            <x-dashboard.order-item />
                            {{-- :order="$order" --}}
                            {{-- @endforeach --}}
                        </ul>
                    </div>
                    {{-- @else --}}
                    {{-- <p class="text-gray-500 text-center py-4">No recent orders found.</p> --}}
                    {{-- @endif --}}
                </div>
            </div>

            {{-- Account Summary --}}
            <div class="lg:col-span-1">
                <div class="p-6 bg-white rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Account Summary</h2>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                                @if (auth()->user()->profile_picture)
                                    <img src="{{ Storage::url(auth()->user()->profile_picture) }}" alt="Profile"
                                        class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ auth()->user()->wmsu_email }}</p>
                            </div>
                        </div>

                        @if (!auth()->user()->is_seller)
                            <div class="border-t pt-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-2">Want to start selling?</h3>
                                <a href=""
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                    Become a Seller
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="mt-6 p-6 bg-white rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <x-dashboard.action-button route="" icon="user" text="Edit Profile" />

                        <x-dashboard.action-button route="" icon="heart" text="View Favorites" />

                        @if (auth()->user()->is_seller)
                            <x-dashboard.action-button route="" icon="store" text="Seller Dashboard" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
