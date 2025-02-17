<x-layout>
    <div class="background w-full h-full absolute z-0"></div>

    <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">
        {{-- logo container --}}
        <div class="w-1/2">
            <img class="w-[30rem] h-[30rem]" src="{{ asset('imgs/CampusConnect.png') }}" alt="" srcset="">
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
                        {{-- WMSU Email (Only for HS, COL, PG, EMP) --}}
                        @if (in_array($registrationData['user_type_id'], ['HS', 'COL', 'PG', 'EMP']))
                            <div>
                                <label for="wmsu_email" class="block mb-2 text-sm font-medium text-black">WMSU
                                    Email*</label>
                                <input type="email" id="wmsu_email" name="wmsu_email"
                                    class="bg-gray-50 border @error('wmsu_email') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5"
                                    value="{{ old('wmsu_email') }}" placeholder="youremail@wmsu.edu.ph">
                                @error('wmsu_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        {{-- Username and Password section always visible --}}
                        <div>
                            <label for="username" class="block mb-2 text-sm font-medium text-black">Username*</label>
                            <div class="relative">
                                <input type="username" id="username" name="username"
                                    class="bg-gray-50 border @error('username') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5 pr-10"
                                    value="{{ old('username') }}">
                            </div>
                            @error('username')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password fields remain the same --}}
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-black">Password*</label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    class="bg-gray-50 border @error('password') border-red-500 @else border-primary-color @enderror text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5 pr-10">
                                <button type="button" id="togglePassword"
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-strength" class="mt-2"></div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-black">Confirm
                                Password*</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="bg-gray-50 border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5 pr-10">
                                <button type="button" id="toggleConfirmPassword"
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-match" class="mt-2 text-sm"></div>
                        </div>



                        {{-- ID Verification (For all except EMP) --}}
                        @if ($registrationData['user_type_id'] !== 'EMP')
                            <div class="col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">ID Verification</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Front ID --}}
                                    <div class="relative">
                                        <label class="block mb-2 text-sm font-medium text-black">Front of ID</label>
                                        <div class="flex flex-col justify-center items-center gap-4">
                                            <div class="w-full h-48">
                                                <input type="file" name="wmsu_id_front" id="wmsu_id_front"
                                                    accept="image/jpeg,image/png,image/jpg" class="hidden"
                                                    onchange="handleImageUpload(this, 'front_preview', 'front_upload')">
                                                <label for="wmsu_id_front"
                                                    class="block w-full h-full cursor-pointer overflow-hidden border-2 border-dashed border-primary-color hover:bg-gray-50 transition-all rounded-lg">
                                                    <div id="front_upload"
                                                        class="h-full flex flex-col items-center justify-center">
                                                        <i class="fas fa-id-card text-4xl text-primary-color mb-1"></i>
                                                        <p class="text-xs text-gray-600 text-center px-2">Click to
                                                            upload
                                                            front of ID</p>
                                                    </div>
                                                    <div id="front_preview" class="hidden w-full h-full">
                                                        <img class="w-full h-full object-cover" src=""
                                                            alt="ID Front preview">
                                                    </div>
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Accepted formats: JPG, JPEG, PNG<br>
                                                Maximum size: 2MB
                                            </p>
                                        </div>
                                        @error('wmsu_id_front')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Back ID --}}
                                    <div class="relative">
                                        <label class="block mb-2 text-sm font-medium text-black">Back of ID</label>
                                        <div class="flex flex-col justify-center items-center gap-4">
                                            <div class="w-full h-48">
                                                <input type="file" name="wmsu_id_back" id="wmsu_id_back"
                                                    accept="image/jpeg,image/png,image/jpg" class="hidden"
                                                    onchange="handleImageUpload(this, 'back_preview', 'back_upload')">
                                                <label for="wmsu_id_back"
                                                    class="block w-full h-full cursor-pointer overflow-hidden border-2 border-dashed border-primary-color hover:bg-gray-50 transition-all rounded-lg">
                                                    <div id="back_upload"
                                                        class="h-full flex flex-col items-center justify-center">
                                                        <i class="fas fa-id-card text-4xl text-primary-color mb-1"></i>
                                                        <p class="text-xs text-gray-600 text-center px-2">Click to
                                                            upload
                                                            back of ID</p>
                                                    </div>
                                                    <div id="back_preview" class="hidden w-full h-full">
                                                        <img class="w-full h-full object-cover" src=""
                                                            alt="ID Back preview">
                                                    </div>
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Accepted formats: JPG, JPEG, PNG<br>
                                                Maximum size: 2MB
                                            </p>
                                        </div>
                                        @error('wmsu_id_back')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between mt-6">
                        <a href="{{ route('register.personal-info') }}"
                            class="text-primary-color hover:underline">Back</a>
                        <button type="submit"
                            class="px-6 py-2 bg-primary-color text-white rounded-lg hover:bg-opacity-90">Complete
                            Registration</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ID Front handling
        function handleIdFront(input) {
            handleImageUpload(input, 'front_preview', 'front_upload');
        }

        // ID Back handling
        function handleIdBack(input) {
            handleImageUpload(input, 'back_preview', 'back_upload');
        }

        // Shared image upload handler
        function handleImageUpload(input, previewId, uploadId) {
            const previewContainer = document.getElementById(previewId);
            const uploadContainer = document.getElementById(uploadId);
            const previewImg = previewContainer.querySelector('img');

            if (input.files && input.files[0]) {
                // Check file size (2MB limit)
                if (input.files[0].size > 2 * 1024 * 1024) {
                    alert('File is too large! Please select an image under 2MB.');
                    input.value = '';
                    return;
                }

                // Check file type
                const fileType = input.files[0].type;
                if (!['image/jpeg', 'image/png', 'image/jpg'].includes(fileType)) {
                    alert('Please upload only JPG, JPEG, or PNG files.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    uploadContainer.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remove preview handler
        function removePreview(inputId, previewId, uploadId) {
            const input = document.getElementById(inputId);
            const previewContainer = document.getElementById(previewId);
            const uploadContainer = document.getElementById(uploadId);

            input.value = '';
            previewContainer.classList.add('hidden');
            uploadContainer.classList.remove('hidden');
        }

        // Add click handlers for ID previews
        document.addEventListener('DOMContentLoaded', function() {
            // Front ID preview
            document.getElementById('front_preview')?.addEventListener('click', function(e) {
                if (e.target.tagName === 'IMG') {
                    removePreview('wmsu_id_front', 'front_preview', 'front_upload');
                    e.stopPropagation();
                }
            });

            // Back ID preview
            document.getElementById('back_preview')?.addEventListener('click', function(e) {
                if (e.target.tagName === 'IMG') {
                    removePreview('wmsu_id_back', 'back_preview', 'back_upload');
                    e.stopPropagation();
                }
            });

            // Update onchange handlers for file inputs
            const frontInput = document.getElementById('wmsu_id_front');
            const backInput = document.getElementById('wmsu_id_back');

            if (frontInput) {
                frontInput.onchange = function() {
                    handleIdFront(this);
                };
            }

            if (backInput) {
                backInput.onchange = function() {
                    handleIdBack(this);
                };
            }
        });

        // Password visibility toggles
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');

            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const feedback = [];

            // Length check
            if (password.length >= 8) {
                strength += 1;
            } else {
                feedback.push('At least 8 characters');
            }

            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('At least one uppercase letter');
            }

            // Lowercase check
            if (/[a-z]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('At least one lowercase letter');
            }

            // Number check
            if (/[0-9]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('At least one number');
            }

            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('At least one special character');
            }

            const strengthMap = {
                0: {
                    text: 'Very Weak',
                    class: 'text-red-600'
                },
                1: {
                    text: 'Weak',
                    class: 'text-orange-600'
                },
                2: {
                    text: 'Fair',
                    class: 'text-yellow-600'
                },
                3: {
                    text: 'Good',
                    class: 'text-blue-600'
                },
                4: {
                    text: 'Strong',
                    class: 'text-green-600'
                },
                5: {
                    text: 'Very Strong',
                    class: 'text-green-600'
                }
            };

            return {
                strength: strengthMap[strength].text,
                class: strengthMap[strength].class,
                feedback: feedback
            };
        }

        // Password input handler
        document.getElementById('password').addEventListener('input', function() {
            const result = checkPasswordStrength(this.value);
            const strengthDiv = document.getElementById('password-strength');

            if (this.value) {
                let html = `<p class="${result.class} font-medium">${result.strength}</p>`;
                if (result.feedback.length > 0) {
                    html += '<ul class="text-gray-600 text-xs mt-1 list-disc list-inside">';
                    result.feedback.forEach(item => {
                        html += `<li>${item}</li>`;
                    });
                    html += '</ul>';
                }
                strengthDiv.innerHTML = html;
            } else {
                strengthDiv.innerHTML = '';
            }

            checkPasswordMatch();
        });

        // Password comparison checker
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const matchDisplay = document.getElementById('password-match');
            const confirmInput = document.getElementById('password_confirmation');

            if (confirmPassword === '') {
                matchDisplay.innerHTML = '';
                confirmInput.className =
                    'bg-gray-50 border border-primary-color text-black rounded-lg focus:ring-primary-color focus:border-primary-color block w-full p-2.5 pr-10';
            } else if (password === confirmPassword) {
                matchDisplay.innerHTML = '<p class="text-green-600">✓ Passwords match</p>';
                confirmInput.className =
                    'bg-gray-50 border border-green-500 text-black rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 pr-10';
            } else {
                matchDisplay.innerHTML = '<p class="text-red-600">✗ Passwords do not match</p>';
                confirmInput.className =
                    'bg-gray-50 border border-red-500 text-black rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 pr-10';
            }
        }

        // Add password confirmation input handler
        document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);

        // Initialize password strength and match check if values exist (e.g., after validation error)
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            if (password.value) {
                password.dispatchEvent(new Event('input'));
            }
        });

        // Get user type from registration data
        const userType = @json($registrationData['user_type_id']);
        console.log('User Type:', userType); // For debugging

        // Email format hint based on user type
        const emailInput = document.getElementById('wmsu_email');
        if (emailInput) {
            emailInput.placeholder = 'youremail@wmsu.edu.ph';
            emailInput.pattern = '^[a-zA-Z0-9._%+-]+@wmsu\\.edu\\.ph$';
        }

        // Add this to your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Save form data to localStorage on input change
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input:not([type="file"])');

            inputs.forEach(input => {
                // Restore saved value if exists
                const savedValue = localStorage.getItem(`register_${input.name}`);
                if (savedValue && input.type !== 'password') {
                    input.value = savedValue;
                }

                // Save value on change
                input.addEventListener('input', function() {
                    if (input.type !== 'password') {
                        localStorage.setItem(`register_${input.name}`, input.value);
                    }
                });
            });

            // Clear localStorage after successful form submission
            form.addEventListener('submit', function() {
                if (form.checkValidity()) {
                    inputs.forEach(input => {
                        localStorage.removeItem(`register_${input.name}`);
                    });
                }
            });
        });
    </script>
</x-layout>
