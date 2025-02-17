<x-adminLayout>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-[30px] justify-start">
        <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
            <div class="text-black">
                <h2 class="text-[28px] font-bold leading-[1.2]">0</h2>
                <p class="text-[14px] text-[#a5a5a5]">Seller Revenue Pool</p>
            </div>
            <img src="https://media.geeksforgeeks.org/wp-content/uploads/20221210183320/2.png" class="h-[50px]" alt="posts-icon">
        </div>
        <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
            <div class="text-black">
                <h2 class="text-[28px] font-bold leading-[1.2]">0</h2>
                <p class="text-[14px] text-[#a5a5a5]">Wallet Balance Deposited</p>
            </div>
            <img src="https://media.geeksforgeeks.org/wp-content/uploads/20221210183320/2.png" class="h-[50px]" alt="posts-icon">
        </div>
        <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
            <div class="text-black">
                <h2 class="text-[28px] font-bold leading-[1.2]">0</h2>
                <p class="text-[14px] text-[#a5a5a5]">Withdrawn Revenue Pool</p>
            </div>
            <img src="https://media.geeksforgeeks.org/wp-content/uploads/20221210183320/2.png" class="h-[50px]" alt="posts-icon">
        </div>

       
    </div>

    <!-- Cards -->
    <div class="flex flex-wrap gap-[30px] w-full justify-start">

        <!-- First Card -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg w-full mt-10">
            <!-- Search Bar -->
            <div class="flex justify-start mb-[5px]">
                <form class="max-w-lg ml-0">
                    <div class="flex">
                        <label for="search-dropdown" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Your Email</label>
                        <button id="dropdown-button" data-dropdown-toggle="dropdown" class="flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600" type="button">All categories
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>
                        <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdown-button">
                                <li><button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Mockups</button></li>
                                <li><button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Templates</button></li>
                                <li><button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Design</button></li>
                                <li><button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Logos</button></li>
                            </ul>
                        </div>
                        <div class="relative w-full">
                            <input type="search" id="search-dropdown" class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-e-lg border-s-gray-50 border-s-2 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-s-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500" placeholder="Search" required />
                            <button type="submit" class="absolute top-0 end-0 p-2.5 text-sm font-medium h-full text-white bg-red-700 rounded-e-lg border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                                <span class="sr-only">Search</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        
            <table class="w-full text-sm text-center rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">User ID</th>
                        <th scope="col" class="px-6 py-3">Amount Requested</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">John Harold</th>
                        <td class="px-6 py-4">1</td>
                        <td class="px-6 py-4">500</td>
                        <td class="px-6 py-4">Pending</td>
                        <td class="px-6 py-4">12/05/2024</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center space-x-2">
                                <button type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center edit-button">Edit</button>
                                <button type="button" class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center">View</button>
                                <button type="button" class="text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center">Remove</button>
                            </div>
                        </td>
                    </tr>
                    <!-- Repeat for other rows -->
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">John Harold</th>
                        <td class="px-6 py-4">1</td>
                        <td class="px-6 py-4">500</td>
                        <td class="px-6 py-4">Pending</td>
                        <td class="px-6 py-4">12/05/2024</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center space-x-2">
                                <button type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center edit-button">Edit</button>
                                <button type="button" class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center">View</button>
                                <button type="button" class="text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center">Remove</button>
                            </div>
                        </td>
                    </tr>

                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">John Harold</th>
                        <td class="px-6 py-4">1</td>
                        <td class="px-6 py-4">500</td>
                        <td class="px-6 py-4">Pending</td>
                        <td class="px-6 py-4">12/05/2024</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center space-x-2">
                                <button type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center edit-button">Edit</button>
                                <button type="button" class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center">View</button>
                                <button type="button" class="text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-20 py-2.5 text-center">Remove</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        
    </div>

    <!-- Edit Form Modal -->
    <div id="editFormModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <form class="max-w-sm mx-auto">
                <h2 class="text-xl font-bold mb-4 text-center">Edit Product</h2>
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="seller-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Seller Name</label>
                        <input type="text" id="seller-name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                    <div>
                        <label for="product-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product Name</label>
                        <input type="text" id="product-name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category</label>
                        <input type="text" id="category" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                    <div>
                        <label for="price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Price</label>
                        <input type="number" id="price" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="promo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Promo</label>
                        <input type="text" id="promo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                    <div>
                        <label for="date-listed" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Listed</label>
                        <input type="date" id="date-listed" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                </div>
                <div class="mb-5">
                    <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                    <input type="text" id="status" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                </div>
                <div class="mb-5">
                    <label for="product-image" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product Image</label>
                    <div class="relative">
                        <input type="file" id="product-image" class="w-full h-full" required />
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" class="text-gray-700 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" onclick="document.getElementById('editFormModal').classList.add('hidden')">Cancel</button>
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('editFormModal').classList.remove('hidden');
            });
        });

        document.getElementById('editFormModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                e.currentTarget.classList.add('hidden');
            }
        });

        document.getElementById('search').addEventListener('input', function() {
            let filter = this.value.toUpperCase();
            let rows = document.getElementById('productTable').getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('th');
                let match = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        if (cells[j].innerText.toUpperCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                }
                if (match) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        });
    </script>
</x-adminLayout>