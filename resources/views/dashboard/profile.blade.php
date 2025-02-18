@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="space-y-8">
        <h2 class="text-2xl font-bold">Profile Settings</h2>

        @if (session('success'))
            <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            {{-- Profile Picture Section --}}
            <div class="flex items-center space-x-6">
                <div class="shrink-0">
                    <img class="h-24 w-24 object-cover rounded-full outline outline-primary-color/30"
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
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                        <input type="text" name="last_name" id="last_name"
                            value="{{ old('last_name', $user->last_name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                    </div>
                </div>
            </div>

            {{-- WMSU ID Information --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">WMSU ID Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Front</label>
                        <div class="relative">
                            <img class="w-full h-48 object-cover rounded-lg"
                                src="{{ $user->wmsu_id_front ? asset('storage/' . $user->wmsu_id_front) : asset('images/id-placeholder.png') }}"
                                alt="ID Front">
                            <input type="file" name="wmsu_id_front" accept="image/*"
                                class="mt-2 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-color/10 file:text-primary-color
                                hover:file:bg-primary-color/20">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Back</label>
                        <div class="relative">
                            <img class="w-full h-48 object-cover rounded-lg"
                                src="{{ $user->wmsu_id_back ? asset('storage/' . $user->wmsu_id_back) : asset('images/id-placeholder.png') }}"
                                alt="ID Back">
                            <input type="file" name="wmsu_id_back" accept="image/*"
                                class="mt-2 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-color/10 file:text-primary-color
                                hover:file:bg-primary-color/20">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
            </div>

            {{-- Account Information --}}
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Account Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                    </div>

                    <div>
                        <label for="wmsu_email" class="block text-sm font-medium text-gray-700">WMSU Email</label>
                        <input type="email" name="wmsu_email" id="wmsu_email"
                            value="{{ old('wmsu_email', $user->wmsu_email) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                    </div>
                </div>
            </div>

            @if ($user->wmsu_dept_id)
                {{-- Department/Grade Level Information --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900">Academic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="wmsu_dept_id" class="block text-sm font-medium text-gray-700">Department</label>
                            <select name="wmsu_dept_id" id="wmsu_dept_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('wmsu_dept_id', $user->wmsu_dept_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            @if ($user->grade_level_id)
                {{-- Department/Grade Level Information --}}
                <div>
                    <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level</label>
                    <select name="grade_level_id" id="grade_level_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                        <option value="">Select Grade Level</option>
                        @foreach ($gradeLevels as $gradeLevel)
                            <option value="{{ $gradeLevel->id }}"
                                {{ old('grade_level_id', $user->grade_level_id) == $gradeLevel->id ? 'selected' : '' }}>
                                {{ $gradeLevel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif


            {{-- Seller Information --}}
            @if ($user->is_seller)
                <div class="bg-gray-50 p-6 rounded-lg space-y-3">
                    <h3 class="text-lg font-medium mb-2">Seller Information</h3>
                    <p class="text-sm text-gray-600">Seller Code: {{ $user->seller_code }}</p>
                    <p class="text-sm text-gray-600">Verified: {{ $user->is_verified ? 'Yes' : 'No' }}</p>
                    @if ($user->verified_at)
                        <p class="text-sm text-gray-600">Verified at: {{ $user->verified_at->format('M d, Y') }}</p>
                    @endif
                </div>
            @endif

            <div class="flex justify-end gap-4 pt-4">
                <button type="submit" class="bg-primary-color text-white px-6 py-2 rounded-md hover:bg-primary-color/90">
                    Save Changes
                </button>
            </div>
        </form>

        @if (!$user->is_seller)
            <div class="border-t pt-8 mt-8">
                <h3 class="text-lg font-medium mb-4">Become a Seller</h3>
                <p class="text-gray-600 mb-6">Start selling your products on our platform</p>
                <a href="{{ route('dashboard.become-seller') }}"
                    class="inline-block bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
                    Apply Now
                </a>
            </div>
        @endif
    </div>
@endsection
