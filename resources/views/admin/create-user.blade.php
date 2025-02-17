<x-adminLayout2>
    <body class="bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-semibold mb-4">Add User</h2>
            <form action="{{ route('admin-userManagement.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" class="border p-2 rounded w-full" required>
                </div>
                <div class="mb-4">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="border p-2 rounded w-full" required>
                </div>
                <div class="mb-4">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="border p-2 rounded w-full" required>
                </div>
                <div class="mb-4">
                    <label for="wmsu_email" class="block text-sm font-medium text-gray-700">WMSU Email</label>
                    <input type="email" name="wmsu_email" id="wmsu_email" class="border p-2 rounded w-full" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" class="border p-2 rounded w-full" required>
                </div>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Add User</button>
            </form>
        </div>
    </body>
</x-adminLayout2>
