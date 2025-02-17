@props([])

<div class="p-6">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Category Management</h2>
        <div class="flex items-center text-sm text-gray-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            <span>Organize and manage your product categories</span>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
        <!-- Category List -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Categories</h3>
                        <span class="px-3 py-1 text-sm text-primary-600 bg-primary-50 rounded-full">12 Total</span>
                    </div>
                </div>

                <div class="p-5">
                    <div class="space-y-3">
                        <!-- Search Bar -->
                        <div class="relative mb-6">
                            <input type="text" placeholder="Search categories..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Category Items -->
                        <div class="space-y-3">
                            <!-- Category Item -->
                            <div
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-150">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-primary-100 rounded-lg">
                                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Electronics</h4>
                                        <p class="text-sm text-gray-500">12 products</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition duration-150">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-150">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Additional category items... -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Category Form -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Add Category</h3>
                </div>
                <div class="p-5">
                    <form class="space-y-5">
                        <div>
                            <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">Category
                                Name</label>
                            <input type="text" id="category_name" name="category_name"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Enter category name">
                        </div>
                        <div>
                            <label for="category_description"
                                class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="category_description" name="category_description" rows="4"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Enter category description"></textarea>
                        </div>
                        <div class="pt-3">
                            <button type="submit"
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150">
                                Add Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
