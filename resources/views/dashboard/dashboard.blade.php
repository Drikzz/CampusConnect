<x-layout>
    <div class="w-full mt-10 mb-28 px-4 md:px-16">
        {{-- Header with Role Badge --}}
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold">My Dashboard</h1>
                @if (auth()->user()->is_seller)
                    <span
                        class="px-3 py-1 text-sm font-medium bg-primary-color/10 text-primary-color rounded-full">Seller</span>
                @endif
            </div>
            @if (!auth()->user()->is_seller)
                <a href="{{ route('dashboard.profile') }}" class="text-primary-color hover:text-primary-color/90">
                    Become a Seller
                </a>
            @endif
        </div>

        {{-- Stats Overview - Now centralized here --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @if (auth()->user()->is_seller)
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-primary-color/10 rounded-full">
                            <svg class="w-6 h-6 text-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-gray-600">Total Sales</h2>
                            <p class="text-lg font-semibold">₱{{ number_format($totalSales ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-primary-color/10 rounded-full">
                            <svg class="w-6 h-6 text-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-gray-600">Active Products</h2>
                            <p class="text-lg font-semibold">{{ $activeProducts ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-primary-color/10 rounded-full">
                            <svg class="w-6 h-6 text-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-gray-600">Pending Orders</h2>
                            <p class="text-lg font-semibold">{{ $pendingOrders ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            @else
                <x-dashboard.stat-card title="Total Orders" value="{{ $totalOrders ?? 0 }}" icon="shopping-bag" />
                <x-dashboard.stat-card title="Wishlist Items" value="{{ $wishlistCount ?? 0 }}" icon="heart" />
                <x-dashboard.stat-card title="Active Orders" value="{{ $activeOrders ?? 0 }}" icon="truck" />
            @endif
        </div>

        {{-- Main Content Grid --}}
        <div class="flex flex-col md:flex-row gap-6">
            {{-- Sidebar Navigation --}}
            <aside class="w-full md:w-64 md:sticky md:top-4 md:h-[calc(100vh-8rem)]">
                <nav class="bg-white rounded-lg shadow p-4">
                    <x-dashboard.nav-section title="Account">
                        <x-dashboard.nav-item route="dashboard.profile" icon="user">Profile</x-dashboard.nav-item>
                        <x-dashboard.nav-item route="dashboard.address" icon="map">Meet-Up
                            Locations</x-dashboard.nav-item>
                    </x-dashboard.nav-section>

                    @if (auth()->user()->is_seller)
                        <x-dashboard.nav-section title="Store Management">
                            <x-dashboard.nav-item route="seller.products" icon="tag">Products</x-dashboard.nav-item>
                            <x-dashboard.nav-item route="seller.orders"
                                icon="shopping-cart">Orders</x-dashboard.nav-item>
                            <x-dashboard.nav-item route="seller.analytics"
                                icon="chart">Analytics</x-dashboard.nav-item>
                        </x-dashboard.nav-section>
                    @else
                        <x-dashboard.nav-section title="Shopping">
                            <x-dashboard.nav-item route="dashboard.orders" icon="shopping-bag">My
                                Orders</x-dashboard.nav-item>
                            <x-dashboard.nav-item route="dashboard.wishlist"
                                icon="heart">Wishlist</x-dashboard.nav-item>
                            <x-dashboard.nav-item route="dashboard.reviews" icon="star">My
                                Reviews</x-dashboard.nav-item>
                        </x-dashboard.nav-section>

                        {{-- New Seller Section --}}
                        <x-dashboard.nav-section title="Seller">
                            <x-dashboard.nav-item route="dashboard.become-seller" icon="store">Become a
                                Seller</x-dashboard.nav-item>
                        </x-dashboard.nav-section>
                    @endif

                    {{-- System Actions --}}
                    <x-dashboard.nav-section title="System">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2 text-gray-600 hover:text-primary-color hover:bg-primary-color/10 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </x-dashboard.nav-section>
                </nav>
            </aside>

            {{-- Main Content Area --}}
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow">
                    {{-- Content container with consistent padding --}}
                    <div class="p-6">
                        @yield('dashboard-content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Action Buttons --}}
    <div class="fixed bottom-6 right-6 flex flex-col gap-4">
        @if (auth()->user()->is_seller)
            <button onclick="showAddProductModal()"
                class="p-4 bg-primary-color text-white rounded-full shadow-lg hover:bg-primary-color/90">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        @endif
        <button onclick="scrollToTop()" class="p-4 bg-gray-100 text-gray-600 rounded-full shadow-lg hover:bg-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </button>
    </div>
</x-layout>
