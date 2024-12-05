@props(['user', 'user_type'])

{{-- Profile Card --}}
{{-- Profile Tab Content --}}
<div class="max-w-4xl mx-auto py-5">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-primary-color text-white px-6 py-4">
            <h2 class="text-2xl font-semibold">Profile Information</h2>
            <p class="text-sm opacity-90">Update your account's profile information.</p>
        </div>

        {{-- Form --}}
        <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
            @csrf

            {{-- Profile Picture Section --}}
            <div class="flex flex-col items-center text-center">
                <div class="relative group">
                    {{-- Current Profile Picture --}}
                    <div id="current-picture" class="relative">
                        <img id="current-img" class="h-32 w-32 object-cover rounded-full border-4 border-primary-color"
                            src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : asset('imgs/default-avatar.png') }}"
                            alt="Current Profile Picture">

                        {{-- Upload Overlay --}}
                        <label
                            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-full">
                            <span class="text-sm font-medium">Change Photo</span>
                            <input id="file-input" type="file" name="profile_picture" class="hidden">
                        </label>
                    </div>

                    {{-- New Profile Picture Preview --}}
                    <div id="preview-picture" class="hidden relative">
                        <img id="preview-img" class="h-32 w-32 object-cover rounded-full border-4 border-primary-color"
                            src="" alt="Preview Profile Picture">
                        <button id="reset-upload" type="button"
                            class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white text-xs px-3 py-1 rounded-lg">
                            Cancel
                        </button>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">Upload a new profile picture (max size: 2MB).</p>
            </div>


            {{-- Form Fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" value="{{ auth()->user()->username }}"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" value="{{ auth()->user()->wmsu_email }}"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                </div>

                {{-- First Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" value="{{ auth()->user()->first_name }}"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                </div>

                {{-- Last Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" value="{{ auth()->user()->last_name }}"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" value="{{ auth()->user()->phone ?? '' }}"
                        placeholder="Enter phone number"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                </div>

                {{-- Gender --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Gender</label>
                    <select name="gender"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                        <option value="">Not Specified</option>
                        <option value="male" {{ auth()->user()->gender === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ auth()->user()->gender === 'female' ? 'selected' : '' }}>Female
                        </option>
                    </select>
                </div>

                {{-- User Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">User Type</label>
                    <input type="text" name="user_type" value="{{ $user_type ?? '' }}" placeholder="Enter user type"
                        readonly
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-gray-300 focus:ring-0 bg-gray-100 text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" value="{{ auth()->user()->phone ?? '' }}"
                        placeholder="Enter phone number"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring-primary-color transition-all">
                </div>

                {{-- wmsu front and back --}}
                {{-- WMSU ID Front --}}
                <div class="flex flex-col items-center text-center">
                    <label class="block text-sm font-medium text-gray-700 mb-1">WMSU ID Front</label>
                    <div class="relative group">
                        {{-- Current WMSU ID Front --}}
                        <div id="current-wmsu-id-front" class="relative">
                            <img id="wmsu_id_front_preview"
                                class="h-64 w-64 object-cover rounded-lg border-4 border-primary-color"
                                src="{{ auth()->user()->wmsu_id_front ? Storage::url(auth()->user()->wmsu_id_front) : asset('imgs/default-id-front.png') }}"
                                alt="WMSU ID Front">

                            {{-- Upload Overlay --}}
                            <label
                                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-lg">
                                <span class="text-sm font-medium">Change Photo</span>
                                <input id="wmsu_id_front_input" type="file" name="wmsu_id_front" class="hidden">
                            </label>
                        </div>

                        {{-- New WMSU ID Front Preview --}}
                        <div id="preview-wmsu-id-front" class="hidden relative">
                            <img id="wmsu_id_front_preview_img"
                                class="h-64 w-64 object-cover rounded-lg border-4 border-primary-color" src=""
                                alt="Preview WMSU ID Front">
                            <button id="reset-wmsu-id-front-upload" type="button"
                                class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white text-xs px-3 py-1 rounded-lg">
                                Cancel
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-4">Upload a new WMSU ID Front (max size: 2MB).</p>
                </div>

                {{-- WMSU ID Back --}}
                <div class="flex flex-col items-center text-center">
                    <label class="block text-sm font-medium text-gray-700 mb-1">WMSU ID Back</label>
                    <div class="relative group">
                        {{-- Current WMSU ID Back --}}
                        <div id="current-wmsu-id-back" class="relative">
                            <img id="wmsu_id_back_preview"
                                class="h-64 w-64 object-cover rounded-lg border-4 border-primary-color"
                                src="{{ auth()->user()->wmsu_id_back ? Storage::url(auth()->user()->wmsu_id_back) : asset('imgs/default-id-back.png') }}"
                                alt="WMSU ID Back">

                            {{-- Upload Overlay --}}
                            <label
                                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-lg">
                                <span class="text-sm font-medium">Change Photo</span>
                                <input id="wmsu_id_back_input" type="file" name="wmsu_id_back" class="hidden">
                            </label>
                        </div>

                        {{-- New WMSU ID Back Preview --}}
                        <div id="preview-wmsu-id-back" class="hidden relative">
                            <img id="wmsu_id_back_preview_img"
                                class="h-64 w-64 object-cover rounded-lg border-4 border-primary-color" src=""
                                alt="Preview WMSU ID Back">
                            <button id="reset-wmsu-id-back-upload" type="button"
                                class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white text-xs px-3 py-1 rounded-lg">
                                Cancel
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-4">Upload a new WMSU ID Back (max size: 2MB).</p>
                </div>

            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2 bg-primary-color text-white rounded-lg hover:bg-primary-color/90 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
