<x-layout>
    <div class="background w-full h-full absolute z-0"></div>

    <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">
        {{-- logo container --}}
        <div class="w-1/2">
            <img class="w-[30rem] h-[30rem]" src="{{ asset('imgs/CampusConnect.png') }}" alt="CampusConnect Logo">
        </div>

        {{-- form container --}}
        <div class="flex flex-col justify-center items-end">
            {{-- Progress indicator --}}
            @include('auth.partials.progress-indicator', ['currentStep' => 2])

            <form action="{{ route('register.complete') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="w-[40rem] h-auto bg-slate-100 p-10 rounded-sm">
                    <div class="mb-6">
                        <p class="font-FontSpring-bold text-3xl text-primary-color">Account Details</p>
                        <p class="text-gray-600">Complete your profile with additional information</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        {{-- Username field --}}
                        <div class="col-span-2">
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" id="username"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                                required>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Dynamic fields based on user type --}}
                        @if (in_array($registrationData['user_type'], ['highschool', 'college', 'postgraduate', 'employee']))
                            <div class="col-span-2">
                                <label for="wmsu_email" class="block text-sm font-medium text-gray-700">WMSU
                                    Email</label>
                                <input type="email" name="wmsu_email" id="wmsu_email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                                    pattern="^eh[0-9]{9}@wmsu\.edu\.ph$" placeholder="eh123456789@wmsu.edu.ph" required>
                                @error('wmsu_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if ($registrationData['user_type'] === 'highschool')
                            <div class="col-span-2">
                                <label for="grade_level" class="block text-sm font-medium text-gray-700">Grade
                                    Level</label>
                                <select name="grade_level" id="grade_level"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                                    required>
                                    <option value="">Select Grade Level</option>
                                    @foreach ($gradeLevels as $level)
                                        <option value="{{ $level->name }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                                @error('grade_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if (in_array($registrationData['user_type'], ['college', 'postgraduate']))
                            <div class="col-span-2">
                                <label for="wmsu_dept"
                                    class="block text-sm font-medium text-gray-700">Department</label>
                                <select name="wmsu_dept" id="wmsu_dept"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color"
                                    required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->code }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('wmsu_dept')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        {{-- Profile Picture Upload --}}
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="profile_picture"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-primary-color hover:text-primary-color-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-color">
                                            <span>Upload a file</span>
                                            <input id="profile_picture" name="profile_picture" type="file"
                                                class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                </div>
                            </div>
                            @error('profile_picture')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ID Upload Fields for specific user types --}}
                        @if (in_array($registrationData['user_type'], ['highschool', 'college', 'postgraduate', 'alumni']))
                            <div class="col-span-2 space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">WMSU ID (Front)</label>
                                    <input type="file" name="wmsu_id_front" required
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-color file:text-white hover:file:bg-primary-color-dark">
                                    @error('wmsu_id_front')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">WMSU ID (Back)</label>
                                    <input type="file" name="wmsu_id_back" required
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-color file:text-white hover:file:bg-primary-color-dark">
                                    @error('wmsu_id_back')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('register.personal-info') }}"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Back
                        </a>
                        <button type="submit"
                            class="bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color-dark">
                            Complete Registration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Add client-side file upload preview functionality here if needed
            document.addEventListener('DOMContentLoaded', function() {
                // File upload preview logic
            });
        </script>
    @endpush
</x-layout>
