<x-adminLayout>
    <div class="container mx-auto p-4">
        <div class="font-std mb-10 w-full rounded-2xl bg-white p-10 font-normal leading-relaxed text-gray-900 shadow-xl">
            <div class="flex flex-col">
                <div class="flex flex-col md:flex-row justify-between mb-5 items-start">
                    <h2 class="mb-5 text-4xl font-bold text-red-700">User Profile</h2>
                    <div class="text-center">
                        <div>
                            <img src="https://i.pravatar.cc/300" alt="Profile Picture" class="rounded-full w-32 h-32 mx-auto border-4 border-indigo-800 mb-4 transition-transform duration-300 hover:scale-105 ring ring-gray-300">
                            <input type="file" name="profile" id="upload_profile" hidden required>
                            <label for="upload_profile" class="inline-flex items-center">
                                <svg data-slot="icon" class="w-5 h-5 text-blue-700" fill="none" stroke-width="1.5"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z">
                                    </path>
                                </svg>
                            </label>
                        </div>
                        <button class="bg-red-700 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors duration-300 ring ring-gray-300 hover:ring-indigo-300" onclick="document.getElementById('editFormModal').classList.remove('hidden')">
                           Edit Profile
                        </button>
                    </div>
                    <!-- New Menu -->
                    <div class="relative">
                        <button id="menuButton" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors duration-300">
                            Menu
                        </button>
                        <div id="menuDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg">
                            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Chat</a>
                            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Ban</a>
                            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Warn</a>
                            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Remove Account  </a>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-10">
                    <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
                        <div class="text-black">
                            <h3 class="text-lg font-semibold text-blue-900">Total Transaction</h3>
                            <p class="text-2xl font-bold text-gray-800">1</p>
                        </div>
                    </div>
                    <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
                        <div class="text-black">
                            <h3 class="text-lg font-semibold text-blue-900">Sales</h3>
                            <p class="text-2xl font-bold text-gray-800">1500</p>
                        </div>
                    </div>
                    <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
                        <div class="text-black">
                            <h3 class="text-lg font-semibold text-blue-900">Wallet Balance</h3>
                            <p class="text-2xl font-bold text-gray-800">1000</p>
                        </div>
                    </div>
                    <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
                        <div class="text-black">
                            <h3 class="text-lg font-semibold text-blue-900">Revenue Balance</h3>
                            <p class="text-2xl font-bold text-gray-800">1000</p>
                        </div>
                    </div>
                    <div class="bg-white w-full h-[150px] rounded-[20px] shadow-lg p-[20px] flex items-center justify-between cursor-pointer transition-all ease-in-out hover:scale-105 hover:shadow-xl">
                        <div class="text-black">
                            <h3 class="text-lg font-semibold text-blue-900">Total Withdrawn</h3>
                            <p class="text-2xl font-bold text-gray-800">500</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                    <div class="bg-white p-4 rounded-lg shadow-md w-full h-80 mx-auto transition-transform duration-300 hover:scale-105 hover:shadow-xl">
                        <h3 class="text-lg font-semibold text-blue-900">ID Front</h3>
                        <img src="{{ asset('imgs/sample sticker.jpg') }}" alt="ID Front" class="w-full h-full object-cover rounded-lg mt-2 transition-transform duration-300 hover:scale-105 object-contain">
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md w-full h-80 mx-auto transition-transform duration-300 hover:scale-105 hover:shadow-xl">
                        <h3 class="text-lg font-semibold text-blue-900">ID Back</h3>
                        <img src="{{ asset('imgs/sample sticker.jpg') }}" alt="ID Back" class="w-full h-full object-cover rounded-lg mt-2 transition-transform duration-300 hover:scale-105 object-contain">
                    </div>
                </div>
                <form class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="jason.tatum" readonly>
                    </div>
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="Jason" readonly>
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="Tatum" readonly>
                    </div>
                    <div>
                        <label for="wmsu_email" class="block text-sm font-medium text-gray-700">WMSU Email</label>
                        <input type="email" id="wmsu_email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="jason.tatum@wmsu.edu" readonly>
                    </div>
                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700">User Type</label>
                        <input type="text" id="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="Admin" readonly>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="editFormModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <form class="space-y-8 container m-auto divide-y divide-gray-200">
                <div class="space-y-8 divide-y divide-gray-200">
                    <div>
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit User</h3>
                            <p class="mt-1 text-sm text-gray-500">Update the user information below.</p>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label for="user-name" class="block text-sm font-medium text-gray-700">User Name</label>
                                <div class="mt-1">
                                    <input type="text" name="user-name" id="user-name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="first-name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <div class="mt-1">
                                    <input type="text" name="first-name" id="first-name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="last-name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <div class="mt-1">
                                    <input type="text" name="last-name" id="last-name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="wmsu-email" class="block text-sm font-medium text-gray-700">WMSU Email</label>
                                <div class="mt-1">
                                    <input type="email" name="wmsu-email" id="wmsu-email" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="user-type" class="block text-sm font-medium text-gray-700">User Type</label>
                                <div class="mt-1">
                                    <select id="user-type" name="user-type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option>Student</option>
                                        <option>Faculty</option>
                                        <option>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="profile-image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                                <input id="file-upload" name="file-upload" type="file" class="sr-only" onchange="previewImage(event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <img id="image-preview" class="h-24 w-24 object-cover rounded-full mx-auto" src="#" alt="Image Preview" style="display: none;">
                                </div>
                            </div>
                            <div class="sm:col-span-6 flex justify-between">
                                <div class="w-1/2 pr-2">
                                    <label for="id-front" class="block text-sm font-medium text-gray-700">ID Front</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="id-front" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload a file</span>
                                                    <input id="id-front" name="id-front" type="file" class="sr-only" onchange="previewImage(event, 'id-front-preview')">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <img id="id-front-preview" class="h-24 w-24 object-cover rounded-full mx-auto" src="#" alt="ID Front Preview" style="display: none;">
                                    </div>
                                </div>
                                <div class="w-1/2 pl-2">
                                    <label for="id-back" class="block text-sm font-medium text-gray-700">ID Back</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="id-back" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload a file</span>
                                                    <input id="id-back" name="id-back" type="file" class="sr-only" onchange="previewImage(event, 'id-back-preview')">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <img id="id-back-preview" class="h-24 w-24 object-cover rounded-full mx-auto" src="#" alt="ID Back Preview" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-5">
                    <div class="flex justify-end">
                        <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="document.getElementById('editFormModal').classList.add('hidden')">Cancel</button>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('menuButton').addEventListener('click', function() {
            var menuDropdown = document.getElementById('menuDropdown');
            menuDropdown.classList.toggle('hidden');
        });

        function previewImage(event, previewId) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById(previewId);
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-adminLayout>