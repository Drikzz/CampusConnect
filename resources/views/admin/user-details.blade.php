<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">User ID</label>
            <p class="mt-1 text-gray-900">{{ $user->id }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Username</label>
            <p class="mt-1 text-gray-900">{{ $user->username }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">First Name</label>
            <p class="mt-1 text-gray-900">{{ $user->first_name }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Last Name</label>
            <p class="mt-1 text-gray-900">{{ $user->last_name }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <p class="mt-1 text-gray-900">{{ $user->wmsu_email }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <p class="mt-1 text-gray-900">{{ $user->phone }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Gender</label>
            <p class="mt-1 text-gray-900">{{ $user->gender }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <p class="mt-1 text-gray-900">{{ $user->date_of_birth }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">WMSU ID Front</label>
            <img src="{{ Storage::url($user->wmsu_id_front) }}" alt="WMSU ID Front" class="w-full rounded-md mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">WMSU ID Back</label>
            <img src="{{ Storage::url($user->wmsu_id_back) }}" alt="WMSU ID Back" class="w-full rounded-md mt-1">
        </div>
    </div>
</div>
