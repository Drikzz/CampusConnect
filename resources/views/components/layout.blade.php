<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/jquery-3.7.1.min.js', 'public/js/app.js', '../public/js/flowbite.min.js', 'resources/css/seller.css'])
</head>

<body>

    <header class="header-container">

        {{-- nav container --}}
        <nav class="flex justify-between items-center w-auto h-auto bg-primary-color px-16 py-6 sticky">

            {{-- logo button --}}
            <a href="{{ route('index') }}" class="flex flex-col items-center">
                <img src="{{ asset('imgs/campusconnect_btn.png') }}" alt="logo" class="w-[150px] h-auto">
            </a>

            <div class="flex justify-center items-center gap-12">

                {{-- shop now button --}}
                <a href="{{ route('products') }}" class="font-Satoshi text-base text-white w-auto">
                    Shop Now
                </a>

                {{-- trade now button --}}
                <a href="#" class="font-Satoshi text-base text-white w-auto hover:bg-red-950 p-2 rounded-lg">
                    Trade Now
                </a>
            </div>

            {{-- for searching --}}
            <form action="#">

                @csrf

                <div class="relative flex items-center fill-slate-500 focus-within:fill-black transition-colors">
                    <svg class="w-5 h-5 absolute ml-3 pointer-events-none" xmlns="http://www.w3.org/2000/svg"
                        id="Outline" viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" />
                    </svg>

                    <input type="text" name="search-text" id="search-text" autocomplete="off"
                        placeholder="Search for products.."
                        class="pl-12 pr-6 px-3 py-2 w-[20rem] rounded-full text-black text-left border-none focus:ring-2 focus:ring-black focus:outline-none font-Satoshi block border-0  shadow-sm bg-white placeholder-custom">

                </div>

            </form>

            <div class="flex justify-between items-center w-fit gap-4">
                <a href="">
                    <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Zm-3.585,18.4a2.973,2.973,0,0,1-3.83,0C4.947,16.006,2,11.87,2,8.967a4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,11,8.967a1,1,0,0,0,2,0,4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,22,8.967C22,11.87,19.053,16.006,13.915,20.313Z" />
                    </svg>
                </a>

                <a href="#">
                    <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                        data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="m19,4h-1.101c-.465-2.279-2.485-4-4.899-4H5C2.243,0,0,2.243,0,5v12.854c0,.794.435,1.52,1.134,1.894.318.171.667.255,1.015.255.416,0,.831-.121,1.19-.36l2.95-1.967c.691,1.935,2.541,3.324,4.711,3.324h5.697l3.964,2.643c.36.24.774.361,1.19.361.348,0,.696-.085,1.015-.256.7-.374,1.134-1.1,1.134-1.894v-12.854c0-2.757-2.243-5-5-5ZM2.23,17.979c-.019.012-.075.048-.152.007-.079-.042-.079-.109-.079-.131V5c0-1.654,1.346-3,3-3h8c1.654,0,3,1.346,3,3v7c0,1.654-1.346,3-3,3h-6c-.327,0-.541.159-.565.175l-4.205,2.804Zm19.77,3.876c0,.021,0,.089-.079.131-.079.041-.133.005-.151-.007l-4.215-2.811c-.164-.109-.357-.168-.555-.168h-6c-1.304,0-2.415-.836-2.828-2h4.828c2.757,0,5-2.243,5-5v-6h1c1.654,0,3,1.346,3,3v12.854Z" />
                    </svg>
                </a>

                @guest
                    {{-- <a href="{{ route('login') }}">
                    <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                        <path d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-4,21.164v-.164c0-2.206,1.794-4,4-4s4,1.794,4,4v.164c-1.226.537-2.578.836-4,.836s-2.774-.299-4-.836Zm9.925-1.113c-.456-2.859-2.939-5.051-5.925-5.051s-5.468,2.192-5.925,5.051c-2.47-1.823-4.075-4.753-4.075-8.051C2,6.486,6.486,2,12,2s10,4.486,10,10c0,3.298-1.605,6.228-4.075,8.051Zm-5.925-15.051c-2.206,0-4,1.794-4,4s1.794,4,4,4,4-1.794,4-4-1.794-4-4-4Zm0,6c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z"/>
                    </svg>
                </a> --}}

                    <!-- Profile Button -->
                    <button type="button" id="profileButton" data-dropdown-toggle="profileDropdown"
                        data-dropdown-trigger="hover" class="flex items-center gap-2 hover:opacity-80 transition-all">
                        <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-4,21.164v-.164c0-2.206,1.794-4,4-4s4,1.794,4,4v.164c-1.226.537-2.578.836-4,.836s-2.774-.299-4-.836Zm9.925-1.113c-.456-2.859-2.939-5.051-5.925-5.051s-5.468,2.192-5.925,5.051c-2.47-1.823-4.075-4.753-4.075-8.051C2,6.486,6.486,2,12,2s10,4.486,10,10c0,3.298-1.605,6.228-4.075,8.051Zm-5.925-15.051c-2.206,0-4,1.794-4,4s1.794,4,4,4,4-1.794,4-4-1.794-4-4-4Zm0,6c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z" />
                        </svg>
                        <span class="text-white text-sm">Profile</span>
                        <svg class="w-5 h-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M18.71,8.21a1,1,0,0,0-1.42,0l-4.58,4.58a1,1,0,0,1-1.42,0L6.71,8.21a1,1,0,0,0-1.42,0,1,1,0,0,0,0,1.41l4.59,4.59a3,3,0,0,0,4.24,0l4.59-4.59A1,1,0,0,0,18.71,8.21Z" />
                        </svg>
                    </button>
                @endguest

                @auth

                    <!-- Profile Button -->
                    <button type="button" id="profileButton" data-dropdown-toggle="profileDropdown"
                        data-dropdown-trigger="hover" class="flex items-center gap-2 hover:opacity-80 transition-all">
                        <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-4,21.164v-.164c0-2.206,1.794-4,4-4s4,1.794,4,4v.164c-1.226.537-2.578.836-4,.836s-2.774-.299-4-.836Zm9.925-1.113c-.456-2.859-2.939-5.051-5.925-5.051s-5.468,2.192-5.925,5.051c-2.47-1.823-4.075-4.753-4.075-8.051C2,6.486,6.486,2,12,2s10,4.486,10,10c0,3.298-1.605,6.228-4.075,8.051Zm-5.925-15.051c-2.206,0-4,1.794-4,4s1.794,4,4,4,4-1.794,4-4-1.794-4-4-4Zm0,6c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z" />
                        </svg>
                        <span class="text-white text-sm">Profile</span>
                        <svg class="w-5 h-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M18.71,8.21a1,1,0,0,0-1.42,0l-4.58,4.58a1,1,0,0,1-1.42,0L6.71,8.21a1,1,0,0,0-1.42,0,1,1,0,0,0,0,1.41l4.59,4.59a3,3,0,0,0,4.24,0l4.59-4.59A1,1,0,0,0,18.71,8.21Z" />
                        </svg>
                    </button>

                @endauth


            </div>

            {{-- @auth
            <div>
                <form action="{{ route('logout')}}" method="POST">
                    @csrf
                    <input type="submit">
                </form>
            </div>
            @endauth --}}
        </nav>
    </header>
    {{-- 
    @guest
        <p>guest</p>
    @endguest

    @auth
        <p>logged in</p>
    @endauth --}}
    {{-- px-16 --}}
    <main class="w-full relative">
        {{ $slot }}
    </main>

    {{-- finish footer tomorrow --}}
    <footer class="w-full bg-footer flex flex-col items-center justify-center relative py-10 px-16">

        <div
            class="flex justify-center items-center max-w-screen-lg py-6 px-8 bg-black rounded-xl absolute top-[-4rem]">
            <div>
                <p class="font-Footer italic text-2xl text-white w-[26rem] ">STAY UP TO DATE ABOUT OUR LATEST OFFERS</p>
            </div>

            {{-- input email --}}
            <form action="">
                @csrf

                <div class="relative flex items-center focus-within:fill-black transition-colors mb-2">
                    <svg class="w-5 h-5 absolute ml-3 pointer-events-none" xmlns="http://www.w3.org/2000/svg"
                        id="Outline" viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M19,1H5A5.006,5.006,0,0,0,0,6V18a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V6A5.006,5.006,0,0,0,19,1ZM5,3H19a3,3,0,0,1,2.78,1.887l-7.658,7.659a3.007,3.007,0,0,1-4.244,0L2.22,4.887A3,3,0,0,1,5,3ZM19,21H5a3,3,0,0,1-3-3V7.5L8.464,13.96a5.007,5.007,0,0,0,7.072,0L22,7.5V18A3,3,0,0,1,19,21Z" />
                    </svg>

                    <input type="text" name="email-input" id="email-input" autocomplete="off"
                        placeholder="Enter your Email Address"
                        class="pl-12 pr-3 px-3 py-2 w-[16rem] rounded-full text-black border-none outline-none font-Satoshi text-base">
                </div>

                <input type="submit" name="email-send" id="email-send" value="Subscribe to Newsletter"
                    class="px-3 py-2 w-[16rem] bg-white rounded-full text-black border-none outline-none font-Satoshi text-base">
            </form>
        </div>

        <div class="w-full h-64 mt-4 grid grid-cols-4 gap-16">

            <div class="flex flex-col justify-center items-start h-full gap-4">
                {{-- <img src="{{ asset('imgs/campusconnect_btn.png') }}" alt="logo" class="w-32 h-auto fill-black"> --}}
                <svg class="w-32 h-auto fill-black" xmlns="http://www.w3.org/2000/svg" width="421" height="179"
                    viewBox="0 0 421 179">
                    <defs>
                        <style>
                            .cls-1 {
                                font-size: 72px;
                                font-family: "FONTSPRING DEMO - Integral CF Heavy";
                                font-style: italic;
                            }
                        </style>
                    </defs>
                    <text id="Campus_Connect" data-name="Campus Connect" class="cls-1" y="58">Campus<tspan x="0"
                            dy="86.4">Connect</tspan></text>
                </svg>


                <div>
                    <p class="font-Satoshi text-sm">We have clothes that suits your style and which you're proud to
                        wear. From women to men.</p>
                </div>

                <div class="flex justify-start items-center w-full gap-4">
                    <a href="#"
                        class="focus-within:opacity-35 focus-within:ring-2 focus-within:ring-black rounded-full">
                        <div
                            class="w-10 h-10 bg-white rounded-full flex justify-center items-center hover:ring-2 hover:ring-black hover:transition-all">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                <path
                                    d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z" />
                            </svg>
                        </div>
                    </a>
                    <a href="#"
                        class="focus-within:opacity-35 focus-within:ring-2 focus-within:ring-black rounded-full">
                        <div
                            class="w-10 h-10 bg-white rounded-full flex justify-center items-center hover:ring-2 hover:ring-black hover:transition-all">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                <path
                                    d="M459.4 151.7c.3 4.5 .3 9.1 .3 13.6 0 138.7-105.6 298.6-298.6 298.6-59.5 0-114.7-17.2-161.1-47.1 8.4 1 16.6 1.3 25.3 1.3 49.1 0 94.2-16.6 130.3-44.8-46.1-1-84.8-31.2-98.1-72.8 6.5 1 13 1.6 19.8 1.6 9.4 0 18.8-1.3 27.6-3.6-48.1-9.7-84.1-52-84.1-103v-1.3c14 7.8 30.2 12.7 47.4 13.3-28.3-18.8-46.8-51-46.8-87.4 0-19.5 5.2-37.4 14.3-53 51.7 63.7 129.3 105.3 216.4 109.8-1.6-7.8-2.6-15.9-2.6-24 0-57.8 46.8-104.9 104.9-104.9 30.2 0 57.5 12.7 76.7 33.1 23.7-4.5 46.5-13.3 66.6-25.3-7.8 24.4-24.4 44.8-46.1 57.8 21.1-2.3 41.6-8.1 60.4-16.2-14.3 20.8-32.2 39.3-52.6 54.3z" />
                            </svg>
                        </div>
                    </a>
                    <a href="#"
                        class="focus-within:opacity-35 focus-within:ring-2 focus-within:ring-black rounded-full">
                        <div
                            class="w-10 h-10 bg-white rounded-full flex justify-center items-center hover:ring-2 hover:ring-black hover:transition-all">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                <path
                                    d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                            </svg>
                        </div>
                    </a>
                    <a href="#"
                        class="focus-within:opacity-35 focus-within:ring-2 focus-within:ring-black rounded-full">
                        <div
                            class="w-10 h-10 bg-white rounded-full flex justify-center items-center hover:ring-2 hover:ring-black hover:transition-all ">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                <path
                                    d="M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z" />
                            </svg>
                        </div>
                    </a>
                </div>

            </div>

            <div class="flex flex-col justify-center items-start h-full gap-4">
                <div>
                    <p class="font-Satoshi text-md">Company</p>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-sm">About</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Features</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Works</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Career</p>
                    </a>
                </div>
            </div>

            <div class="flex flex-col justify-center items-start h-full gap-4">
                <div>
                    <p class="font-Satoshi text-[18px]">Help</p>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Customer Support</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Delivery Details</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Terms & Conditions</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Privacy Policy</p>
                    </a>
                </div>
            </div>

            <div class="flex flex-col justify-center items-start h-full gap-4">
                <div>
                    <p class="font-Satoshi text-[18px]">FAQ</p>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Account</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Manage Deliveries</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Orders</p>
                    </a>
                </div>

                <div>
                    <a href="#" class="hover:underline focus:underline">
                        <p class="font-Satoshi text-[16px]">Payments</p>
                    </a>
                </div>
            </div>

        </div>

        <div class="w-full items-start mt-4">
            <p class="font-Satoshi text-[14px]">CampusConnect &copy; 2024, All Rights Reserved</p>
        </div>
    </footer>

    <script src="{{ asset('js/flowbite.min.js') }}"></script>
</body>

</html>

{{-- modify this --}}
<!-- Dropdown menu -->
<div id="profileDropdown"
    class="hidden z-10 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600">
    <div class="px-4 py-3 text-sm text-gray-900 dark:text-white">
        <div class="font-medium truncate">
            @auth
                {{ Auth::user()->first_name }}
            @endauth

            @guest
                <p>Guest</p>
            @endguest
        </div>
        @auth
            <div class="truncate">{{ Auth::user()->wmsu_email }}</div>
        @endauth
    </div>
    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
        @guest
            <button data-modal-target="default-modal" data-modal-toggle="default-modal"
                class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                Register
            </button>
            <form action="{{ route('login') }}">
                @csrf
                <button type="submit" class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                    Login
                </button>
            </form>
        @endguest

        @auth
            <a href="{{ route('dashboard.profile') }}">
                <button type="button"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left transition-all">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="512" height="512">
                        <path d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12ZM12,2A4,4,0,1,1,8,6,4,4,0,0,1,12,2Z" />
                        <path
                            d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,2,0,7,7,0,0,1,14,0,1,1,0,0,0,2,0A9.01,9.01,0,0,0,12,14Z" />
                    </svg>
                    <span class="text-gray-700 dark:text-gray-200">My Account</span>
                </button>
            </a>
            <!-- Settings Button -->
            <a href="{{ route('dashboard.orders') }}">
                @csrf
                <button type="button"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left transition-all">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                        <path
                            d="M22,14c0,.553-.448,1-1,1H6.737c.416,1.174,1.528,2,2.82,2h9.443c.552,0,1,.447,1,1s-.448,1-1,1H9.557c-2.535,0-4.67-1.898-4.966-4.415L3.215,2.884c-.059-.504-.486-.884-.993-.884H1c-.552,0-1-.447-1-1S.448,0,1,0h1.222c1.521,0,2.802,1.139,2.979,2.649l.041,.351h3.758c.552,0,1,.447,1,1s-.448,1-1,1h-3.522l.941,8h14.581c.552,0,1,.447,1,1Zm-15,6c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm10,0c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm2-14.414v-1.586c0-.553-.448-1-1-1s-1,.447-1,1v2c0,.266,.105,.52,.293,.707l1,1c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414l-.707-.707Zm5,.414c0,3.309-2.691,6-6,6s-6-2.691-6-6S14.691,0,18,0s6,2.691,6,6Zm-2,0c0-2.206-1.794-4-4-4s-4,1.794-4,4,1.794,4,4,4,4-1.794,4-4Z" />
                    </svg>
                    <span class="text-gray-700 dark:text-gray-200">My Orders</span>
                </button>
            </a>

            <!-- Help & Support Button -->
            <form action="#" method="POST">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left transition-all">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                        <path
                            d="m8.5,9.5c0,.551.128,1.073.356,1.537-.49.628-.795,1.407-.836,2.256-.941-.988-1.52-2.324-1.52-3.792,0-3.411,3.122-6.107,6.659-5.381,2.082.428,3.769,2.105,4.213,4.184.134.628.159,1.243.091,1.831-.058.498-.495.866-.997.866h-.045c-.592,0-1.008-.527-.943-1.115.044-.395.021-.81-.08-1.233-.298-1.253-1.32-2.268-2.575-2.557-2.286-.525-4.324,1.207-4.324,3.405Zm-3.89-1.295c.274-1.593,1.053-3.045,2.261-4.178,1.529-1.433,3.531-2.141,5.63-2.011,3.953.256,7.044,3.719,6.998,7.865-.019,1.736-1.473,3.118-3.208,3.118h-2.406c-.244-.829-1.002-1.439-1.91-1.439-1.105,0-2,.895-2,2s.895,2,2,2c.538,0,1.025-.215,1.384-.561h2.932c2.819,0,5.168-2.245,5.208-5.063C21.573,4.715,17.651.345,12.63.021c-2.664-.173-5.191.732-7.126,2.548-1.499,1.405-2.496,3.265-2.855,5.266-.109.608.372,1.166.989,1.166.472,0,.893-.329.972-.795Zm7.39,8.795c-3.695,0-6.892,2.292-7.955,5.702-.165.527.13,1.088.657,1.253.526.159,1.087-.131,1.252-.657.789-2.53,3.274-4.298,6.045-4.298s5.257,1.768,6.045,4.298c.134.428.528.702.955.702.099,0,.198-.015.298-.045.527-.165.821-.726.657-1.253-1.063-3.41-4.26-5.702-7.955-5.702Z" />
                    </svg>

                    <span class="text-gray-700 dark:text-gray-200">Help and Support</span>
                </button>
            </form>
        @endauth

    </ul>
    @auth
        <div class="py-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white text-left">
                    Log out
                </button>
            </form>
        </div>
    @endauth
</div>

<!-- Main modal -->
<div id="default-modal" tabindex="-1" aria-hidden="true"
    class="hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full h-full">
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
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                    data-modal-hide="default-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
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
                    data-route-postgraduate="{{ route('register_form_postgraduate') }}">
                    @csrf

                    <ul class="space-y-3">
                        <li>
                            <input type="radio" id="highschool" name="userType" value="highschool"
                                class="hidden peer">
                            <label for="highschool"
                                class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">High School Student</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="college" name="userType" value="college"
                                class="hidden peer">
                            <label for="college"
                                class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">College Student</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="alumni" name="userType" value="alumni"
                                class="hidden peer">
                            <label for="alumni"
                                class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">Alumni</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="postgraduate" name="userType" value="postgraduate"
                                class="hidden peer">
                            <label for="postgraduate"
                                class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">Post Graduate</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="employee" name="userType" value="employee"
                                class="hidden peer">
                            <label for="employee"
                                class="transition-all inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-color peer-checked:text-primary-color hover:text-gray-600 hover:bg-gray-100">
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
                <button type="button" id="continueBtn"
                    class="text-white bg-primary-color hover:opacity-80 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Continue
                </button>
                <button data-modal-hide="default-modal" type="button"
                    class="ms-3 text-gray-500 bg-white focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">Cancel</button>
            </div>
        </div>
    </div>
</div>
