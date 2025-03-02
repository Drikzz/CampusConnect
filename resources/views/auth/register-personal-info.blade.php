<x-layout>
    <div class="background w-full h-full absolute z-0" data-nav-loader></div>

    <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">
        {{-- logo container --}}
        <div class="w-1/2">
            <img class="w-[30rem] h-[30rem]" src="{{ asset('imgs/CampusConnect.png') }}" alt="CampusConnect Logo">
        </div>

        {{-- form container --}}
        <div class="flex flex-col justify-center items-end">
            {{-- Progress indicator --}}
            @include('auth.partials.progress-indicator', ['currentStep' => 1, 'showSellerStep' => true])

            <form id="personalInfoForm" action="{{ route('register.step1') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="w-[40rem] h-auto bg-slate-100 p-10 rounded-sm">
                    <div class="mb-6">
                        <p class="font-FontSpring-bold text-3xl text-primary-color">Personal Information</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                <h3 class="text-red-800 font-medium">Please fix the following errors:</h3>
                            </div>
                            <ul class="text-sm text-red-600 space-y-1 ml-6 list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-6">
                        {{-- User Type --}}
                        <div class="col-span-2">
                            <label for="user_type_id" class="block mb-2 text-sm font-medium text-black">User
                                Type</label>
                            <select id="user_type_id" name="user_type_id"
                                class="bg-gray-50 border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5">
                                <option value="">Select user type</option>
                                @foreach ($userTypes as $type)
                                    <option value="{{ $type->code }}"
                                        {{ old('user_type_id') == $type->code ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_type_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Highschool level --}}
                        <div id="gradeLevelContainer" class="col-span-2 hidden">
                            <label for="grade_level_id" class="block mb-2 text-sm font-medium text-black">Select
                                Highschool
                                level</label>
                            <select id="grade_level_id" name="grade_level_id"
                                class="bg-gray-50 border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5">
                                <option value="">--Select highshool level--</option>
                                @foreach ($gradeLevels as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('grade_level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Department --}}
                        <div id="departmentContainer" class="col-span-2 hidden">
                            <label for="wmsu_dept_id" class="block mb-2 text-sm font-medium text-black">Select
                                Department</label>
                            <select id="wmsu_dept_id" name="wmsu_dept_id"
                                class="bg-gray-50 border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5">
                                <option value="">--Select Department--</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('wmsu_dept_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('wmsu_dept_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remove the Profile Picture Upload section that was here --}}

                        {{-- Names Section --}}
                        <div class="col-span-2 grid grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block mb-2 text-sm font-medium text-black">First
                                    Name*</label>
                                <input type="text" id="first_name" name="first_name"
                                    class="bg-gray-50 border @error('first_name') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5"
                                    value="{{ old('first_name') }}">
                                @error('first_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="middle_name" class="block mb-2 text-sm font-medium text-black">Middle
                                    Name</label>
                                <input type="text" id="middle_name" name="middle_name"
                                    class="bg-gray-50 border @error('middle_name') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5"
                                    value="{{ old('middle_name') }}">
                                @error('middle_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_name" class="block mb-2 text-sm font-medium text-black">Last
                                    Name*</label>
                                <input type="text" id="last_name" name="last_name"
                                    class="bg-gray-50 border @error('last_name') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5"
                                    value="{{ old('last_name') }}">
                                @error('last_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gender" class="block mb-2 text-sm font-medium text-black">Gender*</label>
                                <select id="gender" name="gender"
                                    class="bg-gray-50 border @error('gender') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5">
                                    <option value="">Select gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="non-binary" {{ old('gender') == 'non-binary' ? 'selected' : '' }}>
                                        Non-binary</option>
                                    <option value="prefer-not-to-say"
                                        {{ old('gender') == 'prefer-not-to-say' ? 'selected' : '' }}>Prefer not to say
                                    </option>
                                </select>
                                @error('gender')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Additional Fields --}}
                        <div class="col-span-2">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label for="date_of_birth" class="block mb-2 text-sm font-medium text-black">Date
                                        of
                                        Birth*</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth"
                                        class="bg-gray-50 border @error('date_of_birth') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5"
                                        value="{{ old('date_of_birth') }}">
                                    @error('date_of_birth')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block mb-2 text-sm font-medium text-black">Phone
                                        Number*</label>
                                    <input type="text" id="phone" name="phone" placeholder="09XXXXXXXXX"
                                        class="bg-gray-50 border @error('phone') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5"
                                        value="{{ old('phone') }}">
                                    @error('phone')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-between mt-6">
                        <a href="{{ route('login') }}" class="text-primary-color hover:underline">Already have an
                            account?</a>
                        <button type="submit"
                            class="px-6 py-2 bg-primary-color text-white rounded-lg hover:bg-opacity-90">Continue</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Clear all storage data immediately when the page loads
        @if (isset($clearLocalStorage) && $clearLocalStorage)
            // Clear all storage data (both session and local)
            function clearAllStorageData() {
                const fieldsToRemove = [
                    'date_of_birth',
                    'first_name',
                    'gender',
                    'last_name',
                    'middle_name',
                    'phone',
                    'user_type_id',
                    'wmsu_dept_id',
                    'grade_level_id'
                ];

                // Clear from sessionStorage
                fieldsToRemove.forEach(field => {
                    sessionStorage.removeItem(field);
                });

                // Clear from localStorage
                fieldsToRemove.forEach(field => {
                    localStorage.removeItem(`register_${field}`);
                });

                // Also clear any other items that start with 'register_'
                Object.keys(sessionStorage).forEach(key => {
                    if (key.startsWith('register_')) {
                        sessionStorage.removeItem(key);
                    }
                });
                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('register_')) {
                        localStorage.removeItem(key);
                    }
                });
            }

            // Execute immediately
            clearAllStorageData();

            // Also execute after DOM loads to ensure complete cleanup
            document.addEventListener('DOMContentLoaded', clearAllStorageData);
        @endif

        // Form persistence
        document.addEventListener('DOMContentLoaded', function() {
            // Restore form data from sessionStorage
            const formFields = ['user_type_id', 'grade_level_id', 'wmsu_dept_id', 'first_name', 'last_name',
                'date_of_birth', 'phone'
            ];
            formFields.forEach(field => {
                const value = sessionStorage.getItem(field);
                if (value) {
                    document.getElementById(field).value = value;
                }
            });

            // Save form data to sessionStorage on change
            const form = document.getElementById('personalInfoForm');
            form.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('change', () => {
                    sessionStorage.setItem(input.name, input.value);
                });
            });
        });

        // Add user type change handler
        document.getElementById('user_type_id').addEventListener('change', function() {
            const userType = this.value;
            const gradeLevelContainer = document.getElementById('gradeLevelContainer');
            const departmentContainer = document.getElementById('departmentContainer');

            // Hide both containers by default
            gradeLevelContainer.classList.add('hidden');
            departmentContainer.classList.add('hidden');

            // Show relevant container based on user type
            switch (userType) {
                case 'HS':
                    gradeLevelContainer.classList.remove('hidden');
                    break;
                case 'COL':
                case 'PG':
                    departmentContainer.classList.remove('hidden');
                    break;
            }

            // Clear values when hidden
            if (gradeLevelContainer.classList.contains('hidden')) {
                document.getElementById('grade_level_id').value = '';
            }
            if (departmentContainer.classList.contains('hidden')) {
                document.getElementById('wmsu_dept_id').value = '';
            }
        });

        // Trigger the change event on page load if there's a selected value
        if (document.getElementById('user_type_id').value) {
            document.getElementById('user_type_id').dispatchEvent(new Event('change'));
        }

        // Add gender change handler
        document.getElementById('gender').addEventListener('change', function() {
            const otherGenderContainer = document.getElementById('otherGenderContainer');
            if (this.value === 'other') {
                otherGenderContainer.classList.remove('hidden');
            } else {
                otherGenderContainer.classList.add('hidden');
                document.getElementById('other_gender').value = '';
            }
        });

        // Trigger gender change event on page load if gender is already selected
        if (document.getElementById('gender').value === 'other') {
            document.getElementById('gender').dispatchEvent(new Event('change'));
        }
    </script>
</x-layout>
