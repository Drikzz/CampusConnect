@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">Profile Settings</h2>

        @if (session('success'))
            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            {{-- Profile Picture Section --}}
            <div class="flex items-center space-x-6 mb-6">
                <div class="shrink-0">
                    <img class="h-24 w-24 object-cover rounded-full"
                        src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/default-avatar.png') }}"
                        alt="{{ $user->username }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-primary-color/10 file:text-primary-color
                        hover:file:bg-primary-color/20">
                </div>
            </div>

            {{-- Basic Information --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name"
                        value="{{ old('first_name', $user->first_name) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>

                <div>
                    <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                    <input type="text" name="middle_name" id="middle_name"
                        value="{{ old('middle_name', $user->middle_name) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth"
                        value="{{ old('date_of_birth', $user->date_of_birth) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>
            </div>

            {{-- Account Information --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>

                @if ($user->wmsu_email)
                    <div>
                        <label for="wmsu_email" class="block text-sm font-medium text-gray-700">WMSU Email</label>
                        <input type="email" id="wmsu_email" value="{{ $user->wmsu_email }}" disabled
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>
                @endif
            </div>

            {{-- Department/Grade Level Information --}}
            @if ($user->wmsu_dept_id)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" value="{{ $user->department->name }}" disabled
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>
            @endif

            @if ($user->grade_level_id)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Grade Level</label>
                    <input type="text" value="{{ $user->gradeLevel->name }}" disabled
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>
            @endif

            {{-- Seller Information --}}
            @if ($user->is_seller)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium mb-2">Seller Information</h3>
                    <p class="text-sm text-gray-600">Seller Code: {{ $user->seller_code }}</p>
                    <p class="text-sm text-gray-600">Verified: {{ $user->is_verified ? 'Yes' : 'No' }}</p>
                    @if ($user->verified_at)
                        <p class="text-sm text-gray-600">Verified at: {{ $user->verified_at->format('M d, Y') }}</p>
                    @endif
                </div>
            @endif

            <div class="flex justify-end gap-4">
                <button type="submit" class="bg-primary-color text-white px-6 py-2 rounded-md hover:bg-primary-color/90">
                    Save Changes
                </button>
            </div>
        </form>

        @if (!$user->is_seller)
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-medium mb-4">Become a Seller</h3>
                <p class="text-gray-600 mb-4">Start selling your products on our platform</p>
                <a href="{{ route('dashboard.become-seller') }}"
                    class="inline-block bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
                    Apply Now
                </a>
            </div>
        @endif
    </div>
@endsection
