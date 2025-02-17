<x-adminLayout2>
    <body class="bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">User Management</h2>
                <button class="bg-red-600 text-white px-4 py-2 rounded" onclick="window.location.href='{{ route('admin-userManagement.create') }}'">+ Add User</button>
            </div>
            <div class="flex gap-4 mb-4">
                <input type="text" id="search" placeholder="Search users..." class="border p-2 rounded w-full">
                <button class="border p-2 rounded flex items-center gap-2" onclick="searchUsers()">
                    <span>Filters</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full bg-white shadow rounded-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3"><input type="checkbox" id="select-all"></th>
                            <th class="p-3 text-left">Username</th>
                            <th class="p-3 text-left">First Name</th>
                            <th class="p-3 text-left">Last Name</th>
                            <th class="p-3 text-left">WMSU Email</th>
                            <th class="p-3 text-left">Date Added</th>
                            <th class="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="p-3"><input type="checkbox" class="user-checkbox"></td>
                            <td class="p-3">{{ $user->username }}</td>
                            <td class="p-3">{{ $user->first_name }}</td>
                            <td class="p-3">{{ $user->last_name }}</td>
                            <td class="p-3">{{ $user->wmsu_email }}</td>
                            <td class="p-3">{{ $user->created_at->format('F j, Y') }}</td>
                            <td class="p-3">
                                <button class="text-blue-600" onclick="showEditForm({{ $user->id }})">Edit</button>
                                <form action="{{ route('admin-userManagement.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 ml-2">Remove</button>
                                </form>
                                <button class="text-green-600 ml-2" onclick="showUserDetails({{ $user->id }})">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-3 bg-gray-200 rounded">
                <p id="selected-count">0 users selected</p>
                <button class="bg-red-600 text-white px-4 py-2 rounded mt-2">Remove Selected</button>
            </div>
        </div>

        <!-- User Details Modal -->
        <div id="user-details-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 w-1/2 overflow-y-auto max-h-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">User Details</h2>
                    <button class="text-gray-600 hover:text-gray-900" onclick="closeModal('user-details-modal')">✖</button>
                </div>
                <div id="user-details-content">
                    <!-- User details will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div id="edit-user-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 w-1/2 overflow-y-auto max-h-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Edit User</h2>
                    <button class="text-gray-600 hover:text-gray-900" onclick="closeModal('edit-user-modal')">✖</button>
                </div>
                <div id="edit-user-content">
                    <!-- Edit user form will be loaded here -->
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAllCheckbox = document.getElementById('select-all');
                const userCheckboxes = document.querySelectorAll('.user-checkbox');
                const selectedCount = document.getElementById('selected-count');

                selectAllCheckbox.addEventListener('change', function() {
                    userCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    updateSelectedCount();
                });

                userCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCount);
                });

                function updateSelectedCount() {
                    const selected = document.querySelectorAll('.user-checkbox:checked').length;
                    selectedCount.textContent = `${selected} users selected`;
                }
            });

            function showUserDetails(userId) {
                fetch(`/admin/userManagement/${userId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('user-details-content').innerHTML = html;
                        document.getElementById('user-details-modal').classList.remove('hidden');
                    });
            }

            function showEditForm(userId) {
                fetch(`/admin/userManagement/${userId}/edit`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('edit-user-content').innerHTML = html;
                        document.getElementById('edit-user-modal').classList.remove('hidden');
                    });
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            function searchUsers() {
                const query = document.getElementById('search').value.toLowerCase();
                const rows = document.querySelectorAll('#user-table-body tr');

                rows.forEach(row => {
                    const username = row.cells[1].textContent.toLowerCase();
                    const firstName = row.cells[2].textContent.toLowerCase();
                    const lastName = row.cells[3].textContent.toLowerCase();
                    const email = row.cells[4].textContent.toLowerCase();

                    if (username.includes(query) || firstName.includes(query) || lastName.includes(query) || email.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        </script>
    </body>
</x-adminLayout2>