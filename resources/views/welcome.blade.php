<x-layout>
    @include('partials._loading-screen')

    <div class="relative bg-primary-color">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-color/90 to-primary-color"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <div class="space-y-6 sm:space-y-8 text-center lg:text-left">
                    <div class="space-y-4">
                        <span
                            class="inline-block px-3 py-1 sm:px-4 sm:py-1 bg-white/10 rounded-full text-white text-xs sm:text-sm font-medium">
                            WELCOME TO CAMPUS CONNECT!
                        </span>
                        <h1
                            class="font-FontSpring-bold text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-white leading-tight">
                            Your Campus Shopping
                            <span class="text-white">Destination</span>
                            <span class="font-Satoshi-bold">!</span>
                        </h1>
                        <p class="text-base sm:text-lg text-white/90 max-w-lg mx-auto lg:mx-0">
                            Discover amazing deals on campus essentials, books, and more. Shop smart, shop local!
                        </p>
                    </div>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="{{ route('products') }}"
                            class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-white rounded-lg text-primary-color font-bold hover:bg-gray-100 transition-colors duration-150 shadow-lg hover:shadow-xl text-sm sm:text-base">
                            SHOP NOW
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-4 sm:gap-6">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-white">Best Prices</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-white">Quality Products</span>
                        </div>
                    </div>
                </div>

                <div class="relative mt-8 lg:mt-0">
                    <div class="absolute -top-10 right-0">
                        <svg class="w-20 h-20 text-white" viewBox="0 0 104 93" fill="currentColor">
                            <path
                                d="M52 0.626999C53.7654 25.3579 76.0448 45.068 104 46.6297C76.0448 48.1915 53.7654 67.9014 52 92.6325C50.2347 67.9014 27.955 48.1915 0 46.6297C27.955 45.068 50.2347 25.3579 52 0.626999Z" />
                        </svg>
                    </div>
                    <img class="w-full max-w-md mx-auto rounded-lg shadow-sm" src="{{ asset('imgs/wmsu_logo.png') }}"
                        alt="WMSU Logo">
                </div>
            </div>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl font-FontSpring-extra-bold text-gray-900">
                Recently Uploaded
            </h2>
            <p class="mt-2 text-gray-600 text-sm sm:text-base">Check out our latest additions to the marketplace</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            @foreach ($products as $product)
                <div class="flex flex-col h-full">
                    <x-productcard :product="$product" />
                </div>
            @endforeach
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 mb-8 sm:mb-12">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl font-FontSpring-extra-bold text-gray-900">
                Special Deals
            </h2>
            <p class="mt-2 text-gray-600 text-sm sm:text-base">Limited time offers you don't want to miss</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            @foreach ($products as $product)
                <div class="flex flex-col h-full">
                    <x-productcard :product="$product" />
                </div>
            @endforeach
        </div>
    </section>

    {{-- <div id="app">
        <example-component></example-component>
    </div> --}}

    {{-- <script src="{{ mix('js/app.js') }}"></script> --}}
</x-layout>
