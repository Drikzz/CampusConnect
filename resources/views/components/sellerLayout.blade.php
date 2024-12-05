<x-layout>
    <div class="min-h-screen bg-gray-100">
        {{-- Top Navigation Bar --}}
        <nav class="bg-white shadow-sm border-b">
            <div class="px-4 mx-auto">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <img src="{{ asset('assets/seller-img/campconnect.jpg') }}" class="h-8 w-8 rounded" alt="Logo">
                        <span class="ml-2 text-xl font-bold">Seller Central</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="ml-4">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex">
            {{-- Sidebar with scrollable styling --}}
            <aside class="w-64 sticky top-0 h-[calc(100vh-4rem)] bg-white border-r overflow-y-auto">
                <nav class="mt-5 px-4 pb-24">
                    <div class="space-y-4">
                        <div class="pb-4">
                            <span class="text-xs font-semibold text-gray-400 uppercase">Main Menu</span>
                            <a href="{{ route('dashboard.seller') }}"
                                class="mt-2 group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                                Dashboard
                            </a>
                        </div>

                        <div class="pb-4">
                            <span class="text-xs font-semibold text-gray-400 uppercase">Products</span>
                            <div class="mt-2 space-y-2">
                                <a href="#"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">All
                                    Products</a>
                                <a href="#"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Add
                                    New</a>
                                <a href="#"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Categories</a>
                            </div>
                        </div>

                        <div class="pb-4">
                            <span class="text-xs font-semibold text-gray-400 uppercase">Orders</span>
                            <div class="mt-2 space-y-2">
                                <a href="{{ route('seller.orders.pending') }}"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">
                                    Pending
                                    @php
                                        $pendingCount = Auth::user()->soldOrders()->pending()->count();
                                    @endphp
                                    @if ($pendingCount > 0)
                                        <span
                                            class="ml-auto bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-0.5 rounded">
                                            {{ $pendingCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('seller.orders.processing') }}"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Processing</a>
                                <a href="{{ route('seller.orders.completed') }}"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Completed</a>
                            </div>
                        </div>

                        <div class="pb-4">
                            <span class="text-xs font-semibold text-gray-400 uppercase">Finance</span>
                            <div class="mt-2 space-y-2">
                                <a href="#"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Earnings</a>
                                <a href="#"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Withdrawals</a>
                                <a href="#"
                                    class="group flex items-center px-3 py-2 text-gray-800 hover:bg-gray-100 rounded-lg">Reports</a>
                            </div>
                        </div>
                    </div>
                </nav>
            </aside>

            {{-- Main Content Area --}}
            <main class="flex-1 p-8 bg-gray-50">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-layout>
