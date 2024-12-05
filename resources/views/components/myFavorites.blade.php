{{-- Favorites Tab Content --}}
<div class="max-w-5xl mx-auto pt-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Saved Items</h3>
            <p class="text-sm text-gray-500">Items you've bookmarked for later.</p>
        </div>

        {{-- Grid of Favorited Items --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Sample Favorite Item Card --}}
            <div class="border rounded-lg hover:shadow-md transition-shadow">
                {{-- Product Image --}}
                <div class="relative">
                    <img src="path_to_product_image" alt="Product" class="w-full h-48 object-cover rounded-t-lg">
                    {{-- Remove from Favorites Button --}}
                    <button
                        class="absolute top-2 right-2 p-2 rounded-full bg-white/80 hover:bg-white text-red-500 hover:text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                {{-- Product Details --}}
                <div class="p-4">
                    <h4 class="font-medium text-lg mb-2">Product Name</h4>
                    <p class="text-primary-color font-semibold mb-2">₱299.00</p>
                    <p class="text-sm text-gray-600 mb-4">Short product description here...</p>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        <a href="#"
                            class="flex-1 bg-primary-color text-white text-center py-2 rounded-lg hover:bg-primary-color/90 text-sm">
                            View Details
                        </a>
                        <button
                            class="px-4 py-2 border border-primary-color text-primary-color rounded-lg hover:bg-primary-color/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Empty State --}}
            {{-- @if (!count($favorites)) --}}
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No saved items</h3>
                <p class="mt-1 text-sm text-gray-500">Start saving items you're interested in!</p>
                <div class="mt-6">
                    <a href="{{ route('products') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-color hover:bg-primary-color/90">
                        Browse Products
                    </a>
                </div>
            </div>
            {{-- @endif --}}
        </div>
    </div>
</div>
