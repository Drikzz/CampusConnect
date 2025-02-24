@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="container mx-auto">

        {{-- Breadcrumb Navigation --}}
        <nav class="flex items-center space-x-3 mb-6 text-gray-500 font-medium">
            <a href="{{ route('seller.dashboard') }}" class="hover:text-primary-color">Dashboard</a>
            <span class="text-gray-400">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a 1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </span>
            <span class="text-gray-900">Orders Management</span>
        </nav>

        <div class="container mx-auto">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">Orders Management</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage and track all your orders in one place.</p>
                </div>

                {{-- Orders Navigation Tabs --}}
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button type="button" data-status="all"
                            class="order-tab px-6 py-3 text-sm font-medium border-b-2 border-primary-color text-primary-color">
                            All Orders
                        </button>
                        <button type="button" data-status="pending"
                            class="order-tab px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                            Pending
                            @if ($pendingCount > 0)
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-600 rounded-full">{{ $pendingCount }}</span>
                            @endif
                        </button>
                        <button type="button" data-status="completed"
                            class="order-tab px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                            Completed
                            @if ($completedCount > 0)
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-600 rounded-full">{{ $completedCount }}</span>
                            @endif
                        </button>
                    </nav>
                </div>

                {{-- Search and Filter Section --}}
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="relative rounded-md shadow-sm">
                                <input type="text" name="search" id="search"
                                    class="block w-full rounded-md border-gray-300 pl-10 focus:border-primary-color focus:ring-primary-color sm:text-sm"
                                    placeholder="Search orders...">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <select
                                class="rounded-md border-gray-300 text-sm focus:border-primary-color focus:ring-primary-color">
                                <option value="">Sort by</option>
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="highest">Highest Amount</option>
                                <option value="lowest">Lowest Amount</option>
                            </select>
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-color">
                                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Orders List --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order Info</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table">
                            @include('seller.orders.partials.orders-table')
                        </tbody>
                    </table>
                </div>

                {{-- Loading State --}}
                <div id="loading-indicator" class="hidden w-full">
                    <div class="flex items-center justify-center p-6">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-color"></div>
                    </div>
                </div>

                {{-- Pagination --}}

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $recentOrders->links() }}
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.order-tab');
                const ordersTable = document.getElementById('orders-table');
                const loadingIndicator = document.getElementById('loading-indicator');
                const paginationContainer = document.querySelector('.px-6.py-4.border-t.border-gray-200');

                function setActiveTab(clickedTab) {
                    // Remove active classes from all tabs
                    tabs.forEach(tab => {
                        tab.classList.remove('border-primary-color', 'text-primary-color');
                        tab.classList.add('text-gray-500', 'border-transparent');
                    });

                    // Add active classes to clicked tab
                    clickedTab.classList.remove('text-gray-500', 'border-transparent');
                    clickedTab.classList.add('border-primary-color', 'text-primary-color');
                }

                function showLoading() {
                    ordersTable.classList.add('opacity-50');
                    loadingIndicator.classList.remove('hidden');
                }

                function hideLoading() {
                    ordersTable.classList.remove('opacity-50');
                    loadingIndicator.classList.add('hidden');
                }

                async function fetchOrders(status) {
                    try {
                        showLoading();

                        const response = await fetch(`/seller/orders/filter/${status}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) throw new Error('Network response was not ok');

                        const data = await response.json();

                        ordersTable.innerHTML = data.html;
                        if (paginationContainer) {
                            paginationContainer.innerHTML = data.pagination;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        ordersTable.innerHTML = `
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-red-500">
                                        Error loading orders. Please try again.
                                    </td>
                                </tr>`;
                    } finally {
                        hideLoading();
                    }
                }

                // Add click handlers to tabs
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        setActiveTab(this);
                        fetchOrders(this.dataset.status);
                    });
                });

                // Handle pagination clicks
                document.addEventListener('click', function(e) {
                    const paginationLink = e.target.closest('.pagination a');
                    if (paginationLink) {
                        e.preventDefault();
                        const href = paginationLink.getAttribute('href');
                        if (href) {
                            const url = new URL(href);
                            const activeTab = document.querySelector('.order-tab.border-primary-color');
                            if (activeTab) {
                                fetchOrders(activeTab.dataset.status);
                            }
                        }
                    }
                });
            });
        </script>

    </div>
@endsection
