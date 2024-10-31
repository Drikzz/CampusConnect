<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    
    <header class="header-container">
        
        {{-- nav container --}}
        <nav class="flex justify-between items-center w-auto h-auto bg-primary-color px-16 py-4 sticky">
            
            {{-- logo button --}}
            <a href="#" class="flex flex-col items-center">
                    <img src="{{ asset('imgs/campusconnect_btn.png') }}" alt="logo" class="w-[150px] h-auto">
            </a>

            <div class="flex justify-center items-center gap-12">
                
                {{-- shop now button --}}
                <a href="#" class="font-Satoshi text-base text-white w-auto">
                    Shop Now
                </a>

                {{-- trade now button --}}
                <a href="#" class="font-Satoshi text-base text-white w-auto">
                    Trade Now
                </a>
            </div>
            
            {{-- for searching --}}
            <form action="" method="post">

                @csrf

                <div class="relative flex items-center fill-slate-500 focus-within:fill-black transition-colors">
                    <svg class="w-5 h-5 absolute ml-3 " xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
                        <path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/>
                    </svg>
                    
                    <input type="text" name="search-text" id="search-text" autocomplete="off" placeholder="Search for products.." class="pl-10 pr-3 px-3 py-2 w-[20rem] rounded-full text-black border-none focus:ring-2 focus:ring-black focus:outline-none font-Satoshi">
                    
                </div>

            </form>

            <div class="flex justify-between items-center w-[6rem]">
                <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
                    <path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Zm-3.585,18.4a2.973,2.973,0,0,1-3.83,0C4.947,16.006,2,11.87,2,8.967a4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,11,8.967a1,1,0,0,0,2,0,4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,22,8.967C22,11.87,19.053,16.006,13.915,20.313Z"/>
                </svg>

                <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
                    <path d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12ZM12,2A4,4,0,1,1,8,6,4,4,0,0,1,12,2Z"/><path d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,2,0,7,7,0,0,1,14,0,1,1,0,0,0,2,0A9.01,9.01,0,0,0,12,14Z"/>
                </svg>

                <svg class="w-5 h-auto fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                    <path d="m19,4h-1.101c-.465-2.279-2.485-4-4.899-4H5C2.243,0,0,2.243,0,5v12.854c0,.794.435,1.52,1.134,1.894.318.171.667.255,1.015.255.416,0,.831-.121,1.19-.36l2.95-1.967c.691,1.935,2.541,3.324,4.711,3.324h5.697l3.964,2.643c.36.24.774.361,1.19.361.348,0,.696-.085,1.015-.256.7-.374,1.134-1.1,1.134-1.894v-12.854c0-2.757-2.243-5-5-5ZM2.23,17.979c-.019.012-.075.048-.152.007-.079-.042-.079-.109-.079-.131V5c0-1.654,1.346-3,3-3h8c1.654,0,3,1.346,3,3v7c0,1.654-1.346,3-3,3h-6c-.327,0-.541.159-.565.175l-4.205,2.804Zm19.77,3.876c0,.021,0,.089-.079.131-.079.041-.133.005-.151-.007l-4.215-2.811c-.164-.109-.357-.168-.555-.168h-6c-1.304,0-2.415-.836-2.828-2h4.828c2.757,0,5-2.243,5-5v-6h1c1.654,0,3,1.346,3,3v12.854Z"/>
                </svg>
            </div>
        </nav>
    </header>

    <main class="m-[20rem]">
        {{ $slot }}
    </main>

    {{-- finish footer tomorrow --}}
    <footer class="w-full h-5 bg-slate-600">
        <div>
            
        </div>
        <div>

        </div>
    </footer>
</body>
</html>