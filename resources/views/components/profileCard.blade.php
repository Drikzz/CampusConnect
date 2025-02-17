@props(['user', 'user_type'])

<div class="max-w-4xl mx-auto py-5">
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary-color to-primary-color/80 text-white px-8 py-6">
            <h2 class="text-3xl font-bold">Profile Information</h2>
            <p class="text-white/90 mt-2">Manage and update your account details</p>
        </div>

        {{-- Form --}}
        <form action="{{ route('dashboard.profile') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-10">
            @csrf
            @method('PUT')

            {{-- Profile Picture Section --}}
            <div class="flex flex-col items-center text-center">
                <div class="relative group">
                    {{-- Current Profile Picture --}}
                    <div class="relative">
                        <img id="profile-preview"
                            class="h-36 w-36 object-cover rounded-full border-4 border-primary-color shadow-lg"
                            src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture"
                            data-original="{{ asset('storage/' . $user->profile_picture) }}">

                        {{-- Upload Overlay --}}
                        <label
                            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-full">
                            <span class="text-sm font-medium">Change Photo</span>
                            <input type="file" name="profile_picture" id="profile-input" class="hidden"
                                accept="image/*"
                                onchange="handleImagePreview(this, 'profile-preview', 'cancel-profile')">
                        </label>
                    </div>

                    {{-- Cancel Button (Hidden by default) --}}
                    <button type="button" id="cancel-profile"
                        class="hidden absolute -bottom-4 left-1/2 transform -translate-x-1/2 bg-red-500 hover:bg-red-600 text-white text-xs px-4 py-1.5 rounded-full shadow-md transition-colors"
                        onclick="cancelImageChange('profile-input', 'profile-preview', 'cancel-profile')">
                        Cancel
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-6">Upload a new profile picture (max size: 2MB)</p>
            </div>

            {{-- Form Fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Basic Information Section --}}
                <div class="space-y-6 md:col-span-2">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">Basic Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Username --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" name="username" value="{{ auth()->user()->username }}"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring focus:ring-primary-color/20 transition-all">
                        </div>

                        {{-- Email (Disabled) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">WMSU Email</label>
                            <input type="email" value="{{ auth()->user()->wmsu_email }}" disabled
                                class="w-full rounded-lg bg-gray-50 border-gray-200 text-gray-500 cursor-not-allowed">
                        </div>

                        {{-- First Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="first_name" value="{{ auth()->user()->first_name }}"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring focus:ring-primary-color/20 transition-all">
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="{{ auth()->user()->last_name }}"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring focus:ring-primary-color/20 transition-all">
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" value="{{ auth()->user()->phone ?? '' }}"
                                placeholder="Enter phone number"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring focus:ring-primary-color/20 transition-all">
                        </div>

                        {{-- Gender --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select name="gender"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-color focus:ring focus:ring-primary-color/20 transition-all">
                                <option value="">Not Specified</option>
                                <option value="male" {{ auth()->user()->gender === 'male' ? 'selected' : '' }}>Male
                                </option>
                                <option value="female" {{ auth()->user()->gender === 'female' ? 'selected' : '' }}>
                                    Female</option>
                            </select>
                        </div>

                        {{-- User Type (Disabled) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                            <input type="text" value="{{ $user_type ?? '' }}" disabled
                                class="w-full rounded-lg bg-gray-50 border-gray-200 text-gray-500 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                {{-- ID Section --}}
                <div class="space-y-6 md:col-span-2">
                    <h3 class="text-xl font-semibold text-gray-800 border-b pb-2">WMSU ID Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- WMSU ID Front --}}
                        <div class="flex flex-col items-center text-center">
                            <label class="block text-sm font-medium text-gray-700 mb-3">ID Front Side</label>
                            <div class="relative group">
                                <div class="relative">
                                    <img id="wmsu-front-preview"
                                        class="h-64 w-full object-cover rounded-lg border-2 border-gray-200 shadow-md"
                                        src="{{ asset('storage/' . ($user->wmsu_id_front ?? 'default-id-front.png')) }}"
                                        alt="WMSU ID Front"
                                        data-original="{{ asset('storage/' . ($user->wmsu_id_front ?? 'default-id-front.png')) }}">

                                    {{-- Upload Overlay --}}
                                    <label
                                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-lg">
                                        <span class="text-sm font-medium">Change Photo</span>
                                        <input type="file" name="wmsu_id_front" id="wmsu-front-input" class="hidden"
                                            accept="image/*"
                                            onchange="handleImagePreview(this, 'wmsu-front-preview', 'cancel-wmsu-front')">
                                    </label>
                                </div>

                                {{-- Cancel Button --}}
                                <button type="button" id="cancel-wmsu-front"
                                    class="hidden absolute -bottom-4 left-1/2 transform -translate-x-1/2 bg-red-500 hover:bg-red-600 text-white text-xs px-4 py-1.5 rounded-full shadow-md transition-colors"
                                    onclick="cancelImageChange('wmsu-front-input', 'wmsu-front-preview', 'cancel-wmsu-front')">
                                    Cancel
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-4">Front side of your WMSU ID (max: 2MB)</p>
                        </div>

                        {{-- WMSU ID Back --}}
                        <div class="flex flex-col items-center text-center">
                            <label class="block text-sm font-medium text-gray-700 mb-3">ID Back Side</label>
                            <div class="relative group">
                                <div class="relative">
                                    <img id="wmsu-back-preview"
                                        class="h-64 w-full object-cover rounded-lg border-2 border-gray-200 shadow-md"
                                        src="{{ asset('storage/' . ($user->wmsu_id_back ?? 'default-id-back.png')) }}"
                                        alt="WMSU ID Back"
                                        data-original="{{ asset('storage/' . ($user->wmsu_id_back ?? 'default-id-back.png')) }}">

                                    {{-- Upload Overlay --}}
                                    <label
                                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-lg">
                                        <span class="text-sm font-medium">Change Photo</span>
                                        <input type="file" name="wmsu_id_back" id="wmsu-back-input"
                                            class="hidden" accept="image/*"
                                            onchange="handleImagePreview(this, 'wmsu-back-preview', 'cancel-wmsu-back')">
                                    </label>
                                </div>

                                {{-- Cancel Button --}}
                                <button type="button" id="cancel-wmsu-back"
                                    class="hidden absolute -bottom-4 left-1/2 transform -translate-x-1/2 bg-red-500 hover:bg-red-600 text-white text-xs px-4 py-1.5 rounded-full shadow-md transition-colors"
                                    onclick="cancelImageChange('wmsu-back-input', 'wmsu-back-preview', 'cancel-wmsu-back')">
                                    Cancel
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-4">Back side of your WMSU ID (max: 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end pt-6 border-t">
                <button type="submit"
                    class="px-8 py-3 bg-primary-color text-white rounded-lg hover:bg-primary-color/90 focus:ring-4 focus:ring-primary-color/20 transition-all font-medium">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle image preview when a new image is selected
    function handleImagePreview(input, previewId, cancelBtnId) {
        const preview = document.getElementById(previewId);
        const cancelBtn = document.getElementById(cancelBtnId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                cancelBtn.classList.remove('hidden');
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Cancel image change and reset to original
    function cancelImageChange(inputId, previewId, cancelBtnId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const cancelBtn = document.getElementById(cancelBtnId);

        preview.src = preview.dataset.original;
        input.value = '';
        cancelBtn.classList.add('hidden');
    }

    // Initialize all image previews on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Store original image URLs
        const images = ['profile-preview', 'wmsu-front-preview', 'wmsu-back-preview'];
        images.forEach(id => {
            const preview = document.getElementById(id);
            if (preview && !preview.dataset.original) {
                preview.dataset.original = preview.src;
            }
        });
    });
</script>
