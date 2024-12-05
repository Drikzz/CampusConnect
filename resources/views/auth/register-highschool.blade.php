<x-layout>
    {{-- for the image background --}}
    <div class="background w-full h-full absolute z-0"></div>

    <div class="w-full h-full px-16 pt-16 pb-32 flex justify-center items-center relative z-10">

        {{-- logo container --}}
        <div class="w-1/2">
            <img class="w-[30rem] h-[30rem]" src="{{ asset('imgs/CampusConnect.png') }}" alt="" srcset="">
        </div>
        
        {{-- form container --}}
        <div class="flex flex-col justify-center items-end">

            
            {{-- label sa taas ng form --}}
            <div class="mb-4">
                <p class="font-FontSpring-bold text-xl text-white">
                    Welcome to Campus Connect <span class="font-Satoshi-bold text-xl">!</span>
                </p>
            </div>
        
            {{-- form --}}
            <form action="{{ route('registerHSStudent') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="w-[40rem] h-auto bg-slate-100 p-10 rounded-sm">

                    {{-- Form heading --}}
                    <div class="mb-6">
                        <p class="font-FontSpring-bold text-3xl text-primary-color">
                            High School Student Registration
                        </p>
                    </div>

                    {{-- Form progress indicator --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-4">

                            <!-- Step 1: Select User Type -->
                            <div class="flex items-center gap-2">
                                <span class="flex items-center justify-center w-5 h-5 text-xs border border-primary-color bg-primary-color text-white rounded-full">1</span>

                                {{-- <svg class="w-5 h-5 fill-primary-color" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                    <path d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-.091,15.419c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701,1.404,1.425-5.793,5.707Z"/>
                                </svg> --}}
                                <span class="text-sm font-medium text-primary-color">Select User Type</span>
                            </div>
                        
                            <!-- Arrow -->
                            <svg class="w-3 h-3 text-primary-color" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                        
                            <!-- Step 2: College Registration -->
                            <div class="flex items-center gap-2">
                                <span class="flex items-center justify-center w-5 h-5 text-xs border border-primary-color bg-primary-color text-white rounded-full">2</span>
                                <span class="text-sm font-medium text-primary-color underline">High School Registration</span>
                            </div>
                            
                            <!-- Arrow -->
                            <svg class="w-3 h-3 text-primary-color" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>

                            <!-- Step 3: Registration Done -->
                            <div class="flex items-center gap-2">
                                <span class="flex items-center justify-center w-5 h-5 text-xs border border-primary-color bg-primary-color text-white rounded-full">3</span>
                                <span class="text-sm font-medium text-primary-color">Done</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-9 mb-6">
                        
                        {{-- left side of the form --}}
                        <div class="w-full flex flex-col gap-4">
    
                            <div class="relative">
                                <label for="username" class="block mb-2 text-sm font-medium text-black">Username:</label>
                                <div>
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                                <path d="M9.5,2.5c0-1.381,1.119-2.5,2.5-2.5s2.5,1.119,2.5,2.5-1.119,2.5-2.5,2.5-2.5-1.119-2.5-2.5Zm7.5,7.5v3c0,1.474-.81,2.75-2,3.444v6.556c0,.552-.447,1-1,1s-1-.448-1-1v-6h-2v6c0,.552-.447,1-1,1s-1-.448-1-1v-6.556c-1.19-.694-2-1.97-2-3.444v-3c0-2.206,1.794-4,4-4h2c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2h-2c-1.103,0-2,.897-2,2v3c0,1.103,.897,2,2,2h2c1.103,0,2-.897,2-2v-3Z"/>
                                            </svg>
                                        </div>
                                        <input type="text" id="username" name="username" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('username') ring-red-500 @enderror" placeholder="Username" value="{{ old('username') }}">
                                    </div>
                            
                                    @error('username')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="relative">    
                                <label for="password" class="block mb-2 text-sm font-medium text-black">Password:</label>
                                <div>
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color text-primary-color" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                                <path d="M7.505,24A7.5,7.5,0,0,1,5.469,9.283,7.368,7.368,0,0,1,9.35,9.235l7.908-7.906A4.5,4.5,0,0,1,20.464,0h0A3.539,3.539,0,0,1,24,3.536a4.508,4.508,0,0,1-1.328,3.207L22,7.415A2.014,2.014,0,0,1,20.586,8H19V9a2,2,0,0,1-2,2H16v1.586A1.986,1.986,0,0,1,15.414,14l-.65.65a7.334,7.334,0,0,1-.047,3.88,7.529,7.529,0,0,1-6.428,5.429A7.654,7.654,0,0,1,7.505,24Zm0-13a5.5,5.5,0,1,0,5.289,6.99,5.4,5.4,0,0,0-.1-3.3,1,1,0,0,1,.238-1.035L14,12.586V11a2,2,0,0,1,2-2h1V8a2,2,0,0,1,2-2h1.586l.672-.672A2.519,2.519,0,0,0,22,3.536,1.537,1.537,0,0,0,20.465,2a2.52,2.52,0,0,0-1.793.743l-8.331,8.33a1,1,0,0,1-1.036.237A5.462,5.462,0,0,0,7.5,11ZM5,18a1,1,0,1,0,1-1A1,1,0,0,0,5,18Z"/>
                                            </svg>
                                        </div>
                                        <input type="password" id="password" name="password" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('password') ring-red-500 @enderror" placeholder="Password">
                                    </div>
                            
                                    @error('password')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="relative">
                                <label for="first_name" class="block mb-2 text-sm font-medium text-black">First Name:</label>
                                <div>
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color " id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                                                <path d="m19 4h-4v-1a3 3 0 0 0 -6 0v1h-4a5.006 5.006 0 0 0 -5 5v10a5.006 5.006 0 0 0 5 5h14a5.006 5.006 0 0 0 5-5v-10a5.006 5.006 0 0 0 -5-5zm-8-1a1 1 0 0 1 2 0v2a1 1 0 0 1 -2 0zm11 16a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3-3v-10a3 3 0 0 1 3-3h4.184a2.982 2.982 0 0 0 5.632 0h4.184a3 3 0 0 1 3 3zm-12-9h-5a1 1 0 0 0 -1 1v8a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-8a1 1 0 0 0 -1-1zm-1 8h-3v-6h3zm11-3a1 1 0 0 1 -1 1h-5a1 1 0 0 1 0-2h5a1 1 0 0 1 1 1zm0-4a1 1 0 0 1 -1 1h-5a1 1 0 0 1 0-2h5a1 1 0 0 1 1 1zm-2 8a1 1 0 0 1 -1 1h-3a1 1 0 0 1 0-2h3a1 1 0 0 1 1 1z"/>
                                            </svg>
                                        </div>
                                        <input type="text" id="first_name" name="first_name" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('first_name') ring-red-500 @enderror" placeholder="First Name" value="{{ old('first_name') }}">
                                    </div>
                            
                                    @error('first_name')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="relative">
                                <label for="last_name" class="block mb-2 text-sm font-medium text-black">Last Name:</label>
                                <div>
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color " id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                                                <path d="m19 4h-4v-1a3 3 0 0 0 -6 0v1h-4a5.006 5.006 0 0 0 -5 5v10a5.006 5.006 0 0 0 5 5h14a5.006 5.006 0 0 0 5-5v-10a5.006 5.006 0 0 0 -5-5zm-8-1a1 1 0 0 1 2 0v2a1 1 0 0 1 -2 0zm11 16a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3-3v-10a3 3 0 0 1 3-3h4.184a2.982 2.982 0 0 0 5.632 0h4.184a3 3 0 0 1 3 3zm-12-9h-5a1 1 0 0 0 -1 1v8a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-8a1 1 0 0 0 -1-1zm-1 8h-3v-6h3zm11-3a1 1 0 0 1 -1 1h-5a1 1 0 0 1 0-2h5a1 1 0 0 1 1 1zm0-4a1 1 0 0 1 -1 1h-5a1 1 0 0 1 0-2h5a1 1 0 0 1 1 1zm-2 8a1 1 0 0 1 -1 1h-3a1 1 0 0 1 0-2h3a1 1 0 0 1 1 1z"/>
                                            </svg>
                                        </div>
                                        <input type="text" id="last_name" name="last_name" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('last_name') ring-red-500 @enderror" placeholder="Last Name"
                                        value="{{ old('last_name') }}">
                                    </div>
                            
                                    @error('last_name')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="relative">
                                <label for="wmsu_email" class="block mb-2 text-sm font-medium text-black">WMSU Email:</label>
                                <div>
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 text-primary-color" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                                                <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                                                <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                                            </svg>
                                        </div>
                                        <input type="text" id="wmsu_email" name="wmsu_email" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('wmsu_email') ring-red-500 @enderror" placeholder="WMSU Email" value="{{ old('wmsu_email') }}">
                                    </div>
                                
                                    @error('wmsu_email')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
    
                        {{-- right side of the form --}}
                        <div class="w-full flex flex-col gap-4">
                            
                            <div class="relative">
                                <label for="grade_level" class="block mb-2 text-sm font-medium text-black">Grade Level:</label>
                                <div>
                                    <div class="relative mb-1">
                                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 fill-primary-color" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M12,2L1,8l11,6l11-6L12,2z M12,15L3,10.4v4.8L12,21l9-5.8v-4.8L12,15z"/>
                                            </svg>
                                        </div>
                                        <select id="grade_level" name="grade_level" class="bg-gray-50 border border-primary-color text-black text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('grade_level') ring-red-500 @enderror">
                                            <option value="">-- Select Grade Level --</option>
                                            @foreach ($gradeLevels as $level)
                                                <option value="{{ $level->name }}" {{ old('grade_level') == $level->name ? 'selected' : '' }}>{{ $level->name ." ". $level->type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('grade_level')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- profile picture --}}
                            <div class="w-full h-full flex flex-col">
                                <div class="h-full">
                                    {{-- Label for Profile Picture --}}
                                    <label for="profile_picture" class="block mb-2 text-sm font-medium text-black">Profile Picture:</label>
                                    
                                    {{-- Upload Container --}}
                                    <label for="profile_picture" id="uploadContainer" class="flex flex-col items-center justify-center w-full h-[calc(100%-2rem)] border-2 border-primary-color border-dashed rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer">
                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX SIZE 2MB)</p>
                                        <input id="profile_picture" name="profile_picture" type="file" class="hidden"  />
                                    </label>
                                    
                                    <div class="flex flex-col h-full gap-4">
                                        {{-- Preview Container --}}
                                        <div id="previewContainer" class="w-full h-80 border-2 border-primary-color border-dashed rounded-lg bg-gray-50 flex items-center justify-center {{ session('preview_image') ? 'flex' : 'hidden' }}">
                                            <img id="preview" class="h-full w-full object-cover" src="" alt="Preview">
                                        </div>
                                        {{-- Change Photo Button --}}
                                        <button id="changePhotoBtn" class="px-4 py-2 bg-primary-color text-white rounded-lg justify-center" type="button">
                                            Change Photo
                                        </button>
                                    </div>
                                </div>
                                

                                {{-- Error Message --}}
                                @error('profile_picture')
                                    <p class="text-red relative mb-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                        </div>
                        
                        {{-- picture id front --}}
                        <div class="w-full h-full flex flex-col">
                            <div class="h-full">
                                {{-- Label for Profile Picture --}}
                                <label for="wmsu_id_front" class="block mb-2 text-sm font-medium text-black">WMSU ID (Front):</label>
                                
                                {{-- Upload Container --}}
                                <label for="wmsu_id_front" id="uploadContainerFront" class="flex flex-col items-center justify-center w-full h-64 border-2 border-primary-color border-dashed rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX SIZE 2MB)</p>
                                    <input id="wmsu_id_front" name="wmsu_id_front" type="file" class="hidden"  />
                                </label>
                                
                                <div class="flex flex-col h-full gap-4">
                                    {{-- Preview Container --}}
                                    <div id="previewContainerFront" class="w-full h-64 border-2 border-primary-color border-dashed rounded-lg bg-gray-50 items-center justify-center hidden">
                                        <img id="previewFront" class="h-full w-full object-cover" src="" alt="Preview">
                                    </div>
                                    {{-- Change Photo Button --}}
                                    <button id="changePhotoBtnFront" class="px-4 py-2 bg-primary-color text-white rounded-lg justify-center" type="button">
                                        Change Photo
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Error Message --}}
                            @error('wmsu_id_front')
                                <p class="text-red relative mb-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        
                        {{-- picture id back --}}
                        <div class="w-full h-full flex flex-col">
                            <div class="h-full">
                                {{-- Label for  --}}
                                <label for="wmsu_id_back" class="block mb-2 text-sm font-medium text-black">WMSU ID (Back):</label>
                                
                                {{-- Upload Container --}}
                                <label for="wmsu_id_back" id="uploadContainerBack" class="flex flex-col items-center justify-center w-full h-64 border-2 border-primary-color border-dashed rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX SIZE 2MB)</p>
                                    <input id="wmsu_id_back" name="wmsu_id_back" type="file" class="hidden"  />
                                </label>
                                
                                <div class="flex flex-col h-full gap-4">
                                    {{-- Preview Container --}}
                                    <div id="previewContainerBack" class="w-full h-64 border-2 border-primary-color border-dashed rounded-lg bg-gray-50 items-center justify-center hidden">
                                        <img id="previewBack" class="h-full w-full object-cover" src="" alt="Preview">
                                    </div>
                                    {{-- Change Photo Button --}}
                                    <button id="changePhotoBtnBack" class="px-4 py-2 bg-primary-color text-white rounded-lg justify-center" type="button">
                                        Change Photo
                                    </button>
                                </div>
                            </div>
                            

                            {{-- Error Message --}}
                            @error('wmsu_id_back')
                                <p class="text-red relative mb-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="w-full">
                        {{-- login link --}}
                        <button type="submit" class="w-full bg-primary-color text-white rounded-lg py-2.5 mb-4">Register</button>
                        <a class="flex justify-end items-center" href="{{ route('login') }}">
                            <p class="text-sm text-primary-color">Already have an account? <span class="hover:underline">Login here.</span></p>
                        </a>
                    </div>

                </div>

            </form>
            
        </div>
        
    </div>
    
</x-layout>