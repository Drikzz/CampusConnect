@php
    use App\Models\UserType;
    use App\Models\Department;
    use App\Models\GradeLevel;

    $userTypes = UserType::all();
    $departments = Department::all();
    $gradeLevels = GradeLevel::all();
@endphp

<x-adminLayout2>
    <body class="bg-gray-100 p-6">
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">User Management</h2>
                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500" onclick="openModal('add-user-modal')">+ Add User</button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="search" class="w-full p-2 border rounded md:w-1/2" placeholder="Search users..." onkeyup="searchUsers()">
            </div>

            <!-- Bulk Actions -->
            <div class="mb-4 flex space-x-2">
                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500">Remove Selected</button>
            </div>

            <!-- User Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-200 text-left text-sm font-semibold">
                            <th class="p-3"><input type="checkbox" id="select-all" onclick="toggleSelectAll()"></th>
                            <th class="p-3">Username</th>
                            <th class="p-3">First Name</th>
                            <th class="p-3">Last Name</th>
                            <th class="p-3">WMSU Email</th>
                            <th class="p-3">Date Added</th>
                            <th class="p-3">Actions</th>
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
                            <td class="p-3 flex space-x-2">
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="showEditForm({{ $user->id }})">Edit</button>
                                <form action="{{ route('admin-userManagement.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded border border-red-500">Remove</button>
                                </form>
                                <button class="bg-red-500 text-white px-2 py-1 rounded border border-red-500" onclick="showUserDetails({{ $user->id }})">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <div id="add-user-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 w-1/2 overflow-y-auto max-h-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Add User</h2>
                    <button class="text-gray-600 hover:text-gray-900" onclick="closeModal('add-user-modal')">✖</button>
                </div>
                <form action="{{ route('admin-userManagement.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="wmsu_email" class="block text-sm font-medium text-gray-700">WMSU Email</label>
                        <input type="email" name="wmsu_email" id="wmsu_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone" id="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="non-binary">Non-binary</option>
                            <option value="prefer-not-to-say">Prefer not to say</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="user_type_id" class="block text-sm font-medium text-gray-700">User Type</label>
                        <select name="user_type_id" id="user_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($userTypes as $userType)
                                <option value="{{ $userType->id }}">{{ $userType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="wmsu_dept_id" class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="wmsu_dept_id" id="wmsu_dept_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level</label>
                        <select name="grade_level_id" id="grade_level_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="wmsu_id_front" class="block text-sm font-medium text-gray-700">WMSU ID Front</label>
                        <input type="file" name="wmsu_id_front" id="wmsu_id_front" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="wmsu_id_back" class="block text-sm font-medium text-gray-700">WMSU ID Back</label>
                        <input type="file" name="wmsu_id_back" id="wmsu_id_back" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="seller_code" class="block text-sm font-medium text-gray-700">Seller Code</label>
                        <input type="text" name="seller_code" id="seller_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded border border-blue-500">Add User</button>
                    </div>
                </form>
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
                const searchInput = document.getElementById('search');

                selectAllCheckbox.addEventListener('change', function() {
                    userCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    updateSelectedCount();
                });

                userCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCount);
                });

                searchInput.addEventListener('input', searchUsers);

                function updateSelectedCount() {
                    const selected = document.querySelectorAll('.user-checkbox:checked').length;
                    selectedCount.textContent = `${selected} users selected`;
                }

                function searchUsers() {
                    const query = searchInput.value.toLowerCase();
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

                function showEditForm(userId) {
                    fetch(`/admin/userManagement/${userId}/edit`)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('edit-user-content').innerHTML = html;
                            document.getElementById('edit-user-modal').classList.remove('hidden');
                        });
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

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            function openModal(modalId) {
                document.getElementById(modalId).classList.remove('hidden');
            }

            function toggleSelectAll() {
                let checkboxes = document.querySelectorAll('.user-checkbox');
                let selectAll = document.getElementById('select-all').checked;
                checkboxes.forEach(cb => cb.checked = selectAll);
            }
        </script>
    </body>
</x-adminLayout2>