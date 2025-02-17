<x-layout>
    <div x-data="{ sidebarOpen: false }" class="relative min-h-screen bg-gray-50">
        <!-- Mobile menu button -->
        <div class="lg:hidden fixed top-0 left-0 z-20 m-4">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md bg-white shadow-lg">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Mobile sidebar -->
        <div x-show="sidebarOpen" @click.away="sidebarOpen = false"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
            class="fixed inset-0 z-10 transform lg:hidden">

            <div class="relative flex w-full max-w-xs min-h-screen bg-white shadow-xl">
                <!-- Sidebar content -->
                <aside class="w-full">
                    <div class="h-16 flex items-center px-6 border-b border-gray-100">
                        <img src="{{ asset('assets/seller-img/campconnect.jpg') }}"
                            class="h-10 w-10 rounded-lg shadow-sm" alt="Logo">
                        <span
                            class="ml-3 text-xl font-bold bg-gradient-to-r from-primary-color to-blue-600 bg-clip-text text-transparent">Seller
                            Central</span>
                    </div>

                    <nav class="p-4 space-y-1">
                        <div class="flex items-center gap-3 px-4 py-3 mb-6 bg-gray-50 rounded-lg">
                            <div
                                class="w-10 h-10 bg-primary-color text-white rounded-full flex items-center justify-center shadow-sm">
                                <span class="text-sm font-medium">{{ substr(Auth::user()->first_name, 0, 1) }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">{{ Auth::user()->first_name }}</span>
                                <span class="text-xs text-gray-500">Seller Account</span>
                            </div>
                        </div>

                        @foreach ([['route' => 'seller.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'name' => 'Dashboard'], ['route' => 'seller.products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'name' => 'Products'], ['route' => 'seller.orders.index', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'name' => 'Orders'], ['route' => '#', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'name' => 'Finance']] as $item)
                            <a href="{{ route($item['route']) }}"
                                class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-150 {{ request()->routeIs($item['route']) ? 'bg-primary-color/10 text-primary-color font-medium' : '' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $item['icon'] }}" />
                                </svg>
                                {{ $item['name'] }}
                            </a>
                        @endforeach

                        <div class="border-t my-6"></div>

                        <a href="{{ route('index') }}"
                            class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg group transition-colors duration-150">
                            <svg class="w-5 h-5 mr-3 transform group-hover:-translate-x-1 transition-transform duration-150"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Store
                        </a>
                    </nav>
                </aside>
            </div>

            <!-- Overlay -->
            <div class="absolute inset-0 bg-gray-600 opacity-50"></div>
        </div>

        <!-- Desktop sidebar -->
        <div class="hidden lg:flex">
            <aside class="w-64 bg-white min-h-screen shadow-lg transition-all duration-300">
                <div class="h-16 flex items-center px-6 border-b border-gray-100">
                    <img src="{{ asset('assets/seller-img/campconnect.jpg') }}" class="h-10 w-10 rounded-lg shadow-sm"
                        alt="Logo">
                    <span
                        class="ml-3 text-xl font-bold bg-gradient-to-r from-primary-color to-blue-600 bg-clip-text text-transparent">Seller
                        Central</span>
                </div>

                <nav class="p-4 space-y-1">
                    <div class="flex items-center gap-3 px-4 py-3 mb-6 bg-gray-50 rounded-lg">
                        <div
                            class="w-10 h-10 bg-primary-color text-white rounded-full flex items-center justify-center shadow-sm">
                            <span class="text-sm font-medium">{{ substr(Auth::user()->first_name, 0, 1) }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">{{ Auth::user()->first_name }}</span>
                            <span class="text-xs text-gray-500">Seller Account</span>
                        </div>
                    </div>

                    @foreach ([['route' => 'seller.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'name' => 'Dashboard'], ['route' => 'seller.products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'name' => 'Products'], ['route' => 'seller.orders.index', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'name' => 'Orders'], ['route' => '#', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'name' => 'Finance']] as $item)
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-150 {{ request()->routeIs($item['route']) ? 'bg-primary-color/10 text-primary-color font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['name'] }}
                        </a>
                    @endforeach

                    <div class="border-t my-6"></div>

                    <a href="{{ route('index') }}"
                        class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg group transition-colors duration-150">
                        <svg class="w-5 h-5 mr-3 transform group-hover:-translate-x-1 transition-transform duration-150"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Store
                    </a>
                </nav>
            </aside>
        </div>

        <!-- Main content -->
        <div class="lg:ml-64 min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">{{ $header ?? 'Dashboard' }}</h1>
                </div>
            </header>
            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layout>
