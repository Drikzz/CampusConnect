<x-layout>

    {{-- for the image background --}}
    <div class="background w-full h-full absolute overflow-hidden"></div>

    <div class="w-full h-auto px-16 pt-16 pb-32 flex justify-center items-center">
    
        {{-- logo container --}}
        <div class="w-1/2 z-10">
            <img class="w-[30rem] h-[30rem]" src="{{ asset('imgs/CampusConnect.png') }}" alt="" srcset="">
        </div>
        
        {{-- form container --}}
        <div class="flex flex-col justify-center items-end z-10">
            <div class="mb-4">
                <p class="font-FontSpring-bold text-xl text-white">
                    Welcome Back to Campus Connect
                </p>
            </div>
    
            <div class="w-[30rem] h-auto gap-10 bg-slate-100 p-10 rounded-sm">
                <form action="{{ route('login') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="w-full">
    
                        <div class="relative">
                            <label for="username" class="block mb-2 text-sm font-medium text-black">Username:</label>
                            <div class="relative mb-6">
                                <div class="relative mb-1">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 fill-primary-color" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                            <path d="M9.5,2.5c0-1.381,1.119-2.5,2.5-2.5s2.5,1.119,2.5,2.5-1.119,2.5-2.5,2.5-2.5-1.119-2.5-2.5Zm7.5,7.5v3c0,1.474-.81,2.75-2,3.444v6.556c0,.552-.447,1-1,1s-1-.448-1-1v-6h-2v6c0,.552-.447,1-1,1s-1-.448-1-1v-6.556c-1.19-.694-2-1.97-2-3.444v-3c0-2.206,1.794-4,4-4h2c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2h-2c-1.103,0-2,.897-2,2v3c0,1.103,.897,2,2,2h2c1.103,0,2-.897,2-2v-3Z"/>
                                        </svg>

                                    </div>
                                    <input type="text" id="username" name="username" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('username') ring-red-500 @enderror" placeholder="juancruz@gmail.com" value="{{ old('username') }}">
                                </div>
                                
                                <div class="w-full flex justify-end items-center">
                                    @error('username')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative">
                            
                            <label for="password" class="block mb-2 text-sm font-medium text-black">Password:</label>
                            <div class="relative mb-6">
                                <div class="relative mb-1">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 fill-primary-color text-primary-color" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                            <path d="M7.505,24A7.5,7.5,0,0,1,5.469,9.283,7.368,7.368,0,0,1,9.35,9.235l7.908-7.906A4.5,4.5,0,0,1,20.464,0h0A3.539,3.539,0,0,1,24,3.536a4.508,4.508,0,0,1-1.328,3.207L22,7.415A2.014,2.014,0,0,1,20.586,8H19V9a2,2,0,0,1-2,2H16v1.586A1.986,1.986,0,0,1,15.414,14l-.65.65a7.334,7.334,0,0,1-.047,3.88,7.529,7.529,0,0,1-6.428,5.429A7.654,7.654,0,0,1,7.505,24Zm0-13a5.5,5.5,0,1,0,5.289,6.99,5.4,5.4,0,0,0-.1-3.3,1,1,0,0,1,.238-1.035L14,12.586V11a2,2,0,0,1,2-2h1V8a2,2,0,0,1,2-2h1.586l.672-.672A2.519,2.519,0,0,0,22,3.536,1.537,1.537,0,0,0,20.465,2a2.52,2.52,0,0,0-1.793.743l-8.331,8.33a1,1,0,0,1-1.036.237A5.462,5.462,0,0,0,7.5,11ZM5,18a1,1,0,1,0,1-1A1,1,0,0,0,5,18Z"/>
                                        </svg>
                                    </div>
                                    <input type="password" id="password" name="password" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('password') ring-red-500 @enderror" placeholder="password123">
                                </div>
                        
                                <div class="w-full flex justify-between items-center">
                                    <a href="#">
                                        <p class="text-sm text-primary-color">Forgot password?</p>
                                    </a>

                                    @error('password')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <label for="wmsu_email" class="block mb-2 text-sm font-medium text-black">WMSU Email:</label>
                            <div class="relative mb-6">
                                <div class="relative mb-1">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 text-primary-color" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                                            <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                                            <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                                        </svg>
                                    </div>
                                    <input type="text" id="wmsu_email" name="wmsu_email" class="bg-gray-50 border border-primary-color text-black placeholder:text-gray-400 text-sm rounded-lg focus:ring-primary-color focus:ring focus:outline-none focus:transition-all block w-full ps-10 p-2.5 @error('wmsu_email') ring-red-500 @enderror" placeholder="eh1234000@wmsu.edu.ph" value="{{ old('wmsu_email') }}">
                                </div>
                        
                                <div class="w-full flex justify-end items-center">
                                    @error('wmsu_email')
                                        <p class="text-red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-start items-center gap-2">
                                <input class="w-4 h-4 appearance-none border border-gray-300 rounded 
                                checked:bg-primary-color checked:accent-primary-color
                                focus:outline-none focus:ring-2 focus:ring-primary-color focus:ring-offset-2
                                cursor-pointer transition-all duration-200" 
                                type="checkbox" 
                                name="remember" 
                                id="remember">
                                <label for="remember" class="text-gray-700">Remember Me</label>
                            </div>
                        </div>

                        @error('failed')
                                <p class="text-red">{{ $message }}</p>
                        @enderror
                        
                        <div class="mb-4">
                            <button type="submit" class="w-full bg-primary-color text-white rounded-lg py-2.5 mt-4">Login</button>
                        </div>
                    </div>
                </form>
                
                <div class="w-full flex justify-end items-center">
                    <p class="text-sm text-primary-color">Don't have an account?
                        <!-- Select User Type button -->
                    <span 
                    data-modal-target="default-modal" 
                    data-modal-toggle="default-modal" 
                    class="text-primary-color hover:underline cursor-pointer"
                    type="button"
                    >
                    Register here
                    </span>
                    
                </div>

            </div>

        </div>
    </div>

</x-layout>

<!-- Main modal -->
<div id="default-modal" tabindex="-1" aria-hidden="true" class="hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full h-full">
    <!-- Modal backdrop -->
    <div class="fixed inset-0 bg-slate-900/50 z-40"></div>
    
    <div class="relative p-4 w-full max-w-md z-50">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                <h3 class="text-xl font-semibold text-gray-400">
                    What type of user are you?
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="default-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4">
                <form id="userTypeForm" action="" method="POST"
                    data-route-highschool="{{ route('register_form_highschool') }}"
                    data-route-college="{{ route('register_form_college') }}"
                    data-route-employee="{{ route('register_form_employee') }}"
                    data-route-alumni="{{ route('register_form_alumni') }}"
                    data-route-postgraduate="{{ route('register_form_postgraduate') }}"
                >
                    @csrf

                    <ul class="space-y-3">
                        <li>
                            <input type="radio" id="highschool" name="userType" value="highschool" class="hidden peer">
                            <label for="highschool" class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">High School Student</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="college" name="userType" value="college" class="hidden peer">
                            <label for="college" class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">College Student</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="alumni" name="userType" value="alumni" class="hidden peer">
                            <label for="alumni" class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">Alumni</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="postgraduate" name="userType" value="postgraduate" class="hidden peer">
                            <label for="postgraduate" class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">Post Graduate</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="employee" name="userType" value="employee" class="hidden peer">
                            <label for="employee" class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">Employee</div>
                                </div>
                            </label>
                        </li>
                    </ul>
                </form>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b">
                <button 
                    type="button" 
                    id="continueBtn" 
                    class="text-white bg-primary-color hover:opacity-80 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Continue
                </button>
                <button data-modal-hide="default-modal" type="button" class="ms-3 text-gray-500 bg-white focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">Cancel</button>
            </div>
        </div>
    </div>
</div>