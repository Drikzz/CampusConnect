<x-layout>
    <div class="mt-10 mb-28 px-16">
        <div class="grid grid-cols-2 place-items-center w-full gap-8">
            <div class="h-full w-full flex justify-between items-center gap-8">

                {{-- image gallery --}}
                <div class="h-full w-[20%] flex flex-col justify-start items-center gap-4">
                    @foreach ($product->images as $index => $image)
                        <div class="w-28 h-28">
                            <img id="img{{ $index + 1 }}"
                                class="w-full h-full aspect-square object-cover hover:outline hover:outline-black"
                                src="{{ asset('storage/' . $image) }}" alt="">
                        </div>
                    @endforeach
                </div>

                <div class="h-full w-[80%] bg-black relative">
                    <img id="mainImage" class="object-cover aspect-square w-full h-full"
                        src="{{ asset('storage/' . $product->images[0]) }}" alt="">
                    <button id="prevButton"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 bg-white p-4 rounded-full flex justify-center items-center">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"
                            width="512" height="512">
                            <path
                                d="M17.17,24a1,1,0,0,1-.71-.29L8.29,15.54a5,5,0,0,1,0-7.08L16.46.29a1,1,0,1,1,1.42,1.42L9.71,9.88a3,3,0,0,0,0,4.24l8.17,8.17a1,1,0,0,1,0,1.42A1,1,0,0,1,17.17,24Z" />
                        </svg>
                    </button>
                    <button id="nextButton"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-white p-4 rounded-full flex justify-center items-center">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"
                            width="512" height="512">
                            <path
                                d="M7,24a1,1,0,0,1-.71-.29,1,1,0,0,1,0-1.42l8.17-8.17a3,3,0,0,0,0-4.24L6.29,1.71A1,1,0,0,1,7.71.29l8.17,8.17a5,5,0,0,1,0,7.08L7.71,23.71A1,1,0,0,1,7,24Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="w-full">

                {{-- navigation, tags, and bookmark icon --}}
                <div class="flex justify-between items-center w-full">

                    <div>

                        {{-- navigation --}}
                        <div class="flex justify-start items-center gap-2">
                            <a href="{{ route('index') }}" class="font-Satoshi text-base">
                                Home
                            </a>

                            <p class="font-Satoshi text-base">/</p>

                            <a href="{{ route('products') }}" class="font-Satoshi text-base">
                                Products
                            </a>

                            <p class="font-Satoshi text-base">/</p>

                            <a href="#" class="font-Satoshi-bold text-base">
                                Uniforms
                            </a>
                        </div>

                        {{-- tags --}}
                        <div class="flex justify-center items-center gap-2 mt-4">
                            <p class="font-Satoshi-bold text-base">Tags:</p>

                            <div
                                class="flex justify-center items-center px-4 py-2 ring-2 ring-gray-400 rounded-full w-auto gap-2">

                                <svg class="w-2 h-2 fill-gray-400 stroke-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512"
                                    height="512">
                                    <path
                                        d="M23,7H18.191l.8-5.865a1,1,0,1,0-1.982-.27L16.173,7H9.191l.8-5.865A1,1,0,1,0,8.009.865L7.173,7H2A1,1,0,0,0,2,9H6.9l-.818,6H1a1,1,0,0,0,0,2H5.809l-.8,5.865a1,1,0,0,0,1.982.27L7.827,17h6.982l-.8,5.865a1,1,0,0,0,1.982.27L16.827,17H22a1,1,0,0,0,0-2H17.1l.818-6H23A1,1,0,0,0,23,7Zm-7.918,8H8.1l.818-6H15.9Z" />
                                </svg>

                                <p class="text-gray-400 text-sm">
                                    Uniforms
                                </p>
                            </div>

                            <div
                                class="flex justify-center items-center px-4 py-2 ring-2 ring-gray-400 rounded-full w-auto gap-2">

                                <svg class="w-2 h-2 fill-gray-400 stroke-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512"
                                    height="512">
                                    <path
                                        d="M23,7H18.191l.8-5.865a1,1,0,1,0-1.982-.27L16.173,7H9.191l.8-5.865A1,1,0,1,0,8.009.865L7.173,7H2A1,1,0,0,0,2,9H6.9l-.818,6H1a1,1,0,0,0,0,2H5.809l-.8,5.865a1,1,0,0,0,1.982.27L7.827,17h6.982l-.8,5.865a1,1,0,0,0,1.982.27L16.827,17H22a1,1,0,0,0,0-2H17.1l.818-6H23A1,1,0,0,0,23,7Zm-7.918,8H8.1l.818-6H15.9Z" />
                                </svg>

                                <p class="text-gray-400 text-sm">
                                    CCS
                                </p>
                            </div>

                            <div
                                class="flex justify-center items-center px-4 py-2 ring-2 ring-gray-400 rounded-full w-auto gap-2">

                                <svg class="w-2 h-2 fill-gray-400 stroke-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512"
                                    height="512">
                                    <path
                                        d="M23,7H18.191l.8-5.865a1,1,0,1,0-1.982-.27L16.173,7H9.191l.8-5.865A1,1,0,1,0,8.009.865L7.173,7H2A1,1,0,0,0,2,9H6.9l-.818,6H1a1,1,0,0,0,0,2H5.809l-.8,5.865a1,1,0,0,0,1.982.27L7.827,17h6.982l-.8,5.865a1,1,0,0,0,1.982.27L16.827,17H22a1,1,0,0,0,0-2H17.1l.818-6H23A1,1,0,0,0,23,7Zm-7.918,8H8.1l.818-6H15.9Z" />
                                </svg>

                                <p class="text-gray-400 text-sm">
                                    Male
                                </p>
                            </div>
                        </div>

                    </div>

                    <div
                        class="w-fit bg-white p-2 rounded-full hover:shadow-md hover:ring-2 hover:ring-black hover:transition-all">
                        <svg class="w-5 h-5 fill-black cursor-pointer bookmarked" xmlns="http://www.w3.org/2000/svg"
                            id="Filled" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M2.849,23.55a2.954,2.954,0,0,0,3.266-.644L12,17.053l5.885,5.853a2.956,2.956,0,0,0,2.1.881,3.05,3.05,0,0,0,1.17-.237A2.953,2.953,0,0,0,23,20.779V5a5.006,5.006,0,0,0-5-5H6A5.006,5.006,0,0,0,1,5V20.779A2.953,2.953,0,0,0,2.849,23.55Z" />
                        </svg>

                        <svg class="w-5 h-5 fill-black cursor-pointer hidden unbookmark"
                            xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"
                            width="512" height="512">
                            <path
                                d="m21,19.586V3c0-1.654-1.346-3-3-3H6.559c-1.393,0-2.566.954-2.903,2.242L1.457.043.043,1.457l22.5,22.5,1.414-1.414-2.957-2.957Zm-10.083-3.661l-7.917,8.075v-15.991l7.917,7.916Z" />
                        </svg>
                    </div>
                </div>

                {{-- product title --}}
                <div class="mt-4">
                    <p class="font-Satoshi-bold text-2xl">
                        {{ $product->name }}
                    </p>
                </div>

                {{-- product price, sale, stock --}}
                <div class="flex justify-start items-center gap-4 mt-4">
                    <p class="font-Satoshi-bold text-2xl">
                        &#8369;{{ number_format($product->discounted_price) }}
                    </p>

                    <p class="font-Satoshi-bold text-2xl line-through text-gray-400">
                        &#8369;{{ number_format($product->price) }}
                    </p>

                    <p class="font-Satoshi-bold text-2xl text-red">
                        -{{ $product->discount }}%
                    </p>

                    <p class="font-Satoshi text-xl">
                        In stock: <span> {{ $product->stock }} </span> pc
                    </p>
                </div>

                {{-- product owner info --}}
                <div class="flex justify-start items-center gap-4 mt-4">
                    <img class="w-10 h-10 rounded-full"
                        src="{{ $product->seller && $product->seller->profile_picture ? Storage::url($product->seller->profile_picture) : asset('imgs/img1.jpg') }}"
                        alt="user's profile photo">

                    <p class="font-Satoshi-bold text-base">
                        {{ $product->seller ? $product->seller->username : 'Unknown Seller' }}
                    </p>

                    <div class="flex justify-center items-center gap-1">
                        <svg class="w-5 h-5 fill-yellow-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M19.467,23.316,12,17.828,4.533,23.316,7.4,14.453-.063,9H9.151L12,.122,14.849,9h9.213L16.6,14.453Z" />
                        </svg>

                        <svg class="w-5 h-5 fill-yellow-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M19.467,23.316,12,17.828,4.533,23.316,7.4,14.453-.063,9H9.151L12,.122,14.849,9h9.213L16.6,14.453Z" />
                        </svg>

                        <svg class="w-5 h-5 fill-yellow-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M19.467,23.316,12,17.828,4.533,23.316,7.4,14.453-.063,9H9.151L12,.122,14.849,9h9.213L16.6,14.453Z" />
                        </svg>

                        <svg class="w-5 h-5 fill-yellow-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M19.467,23.316,12,17.828,4.533,23.316,7.4,14.453-.063,9H9.151L12,.122,14.849,9h9.213L16.6,14.453Z" />
                        </svg>

                        <svg class="w-5 h-5 fill-yellow-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M19.467,23.316,12,17.828,4.533,23.316,7.4,14.453-.063,9H9.151L12,.122,14.849,9h9.213L16.6,14.453Z" />
                        </svg>

                    </div>

                    {{-- rating --}}
                    <div class="flex justify-center items-center">
                        <p class="font-Satoshi underline">
                            5 <span> / 5</span>
                        </p>
                    </div>

                    {{-- review numbers --}}
                    <div class="flex justify-center items-center">
                        <p class="font-Satoshi">
                            5 <span> Reviews</span>
                        </p>
                    </div>

                    {{-- chat button --}}
                    <div>
                        <a href="#">
                            <button
                                class="font-Satoshi-bold text-base bg-black text-white px-4 py-2 rounded-full hover:bg-gray-800 hover:transition-all">
                                Chat Seller
                            </button>
                        </a>
                    </div>
                </div>

                {{-- extra stuff --}}
                <div class="mt-4">
                    <div class="flex justify-start items-center gap-2">
                        <img class="w-5 h-5" src="{{ asset('assets/icons/svg/marker.svg') }}" alt="">

                        {{-- user location --}}
                        <p class="font-Satoshi-bold">
                            Sinunuc, Zamboanga City
                        </p>
                    </div>

                    <div class="flex justify-start items-center gap-2 mt-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-.091,15.419c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701,1.404,1.425-5.793,5.707Z" />
                        </svg>

                        <p class="font-Satoshi-bold">
                            Verified User
                        </p>
                    </div>

                    <div class="flex justify-start items-center gap-2 mt-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-.091,15.419c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701,1.404,1.425-5.793,5.707Z" />
                        </svg>

                        <p class="font-Satoshi-bold">
                            No Hassle Refunds
                        </p>
                    </div>

                    <div class="flex justify-start items-center gap-2 mt-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm-.091,15.419c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701,1.404,1.425-5.793,5.707Z" />
                        </svg>

                        <p class="font-Satoshi-bold">
                            Secure Payments
                        </p>
                    </div>
                </div>

                <div class="flex justify-evenly items-center mt-4 w-full">
                    @if ($product->is_buyable == 1 && $product->is_tradable == 1)
                        <a href="{{ route('summary', $product->id) }}" id="buyNowButton"
                            class="font-Satoshi-bold text-base bg-black text-white px-20 py-4 rounded-full hover:bg-gray-800 hover:transition-all">
                            Buy Now
                        </a>

                        <a href="#" id="tradeNowButton"
                            class="font-Satoshi-bold text-base bg-black text-white px-20 py-4 rounded-full hover:bg-gray-800 hover:transition-all">
                            Trade Now
                        </a>
                    @elseif ($product->is_buyable == 1 && $product->is_tradable == 0)
                        <a href="{{ route('summary', $product->id) }}" id="buyNowButton"
                            class="font-Satoshi-bold text-base bg-black text-white px-20 py-4 rounded-full hover:bg-gray-800 hover:transition-all">
                            Buy Now
                        </a>
                    @elseif ($product->is_buyable == 0 && $product->is_tradable == 1)
                        <a href="#" id="tradeNowButton"
                            class="font-Satoshi-bold text-base bg-black text-white px-20 py-4 rounded-full hover:bg-gray-800 hover:transition-all">
                            Trade Now
                        </a>
                    @else
                        <p class="font-Satoshi text-gray-500">Not available for purchase or trade</p>
                    @endif
                </div>

            </div>

        </div>

        <div class="w-full h-auto mt-8 flex justify-start items-center">

            <button
                class="tab w-64 h-auto px-8 py-2 flex justify-center items-center border-b-4 border-transparent cursor-pointer font-Satoshi text-base"
                data-tab="ProductDetails">
                Product Details
            </button>

            <button
                class="tab w-64 h-auto px-8 py-2 flex justify-center items-center border-b-4 border-transparent cursor-pointer font-Satoshi text-base"
                data-tab="AnotherTab">
                Another Tab
            </button>

        </div>

        {{-- for product details --}}
        <div id="ProductDetails"
            class="tabcontent w-full py-8 flex flex-col justify-center items-start gap-6 product-description">
            <p class="font-Satoshi-bold text-xl">
                Product Description
            </p>

            <p>
                {{ $product->description }}
            </p>
        </div>

        {{-- for another tab --}}
        <div id="AnotherTab"
            class="tabcontent w-full py-8 flex flex-col justify-center items-start gap-6 product-description">
            <p class="font-Satoshi-bold text-xl">
                Another Sample Tab title
            </p>

            <p>
                Another Sample tab description
            </p>
        </div>

        {{-- separator --}}
        <div class="w-full h-1 bg-black">

        </div>
        <div class="w-full mt-14">
            <div class="flex justify-center items-center mb-4">
                <p class="font-Footer italic text-4xl">
                    YOU MIGHT ALSO LIKE
                </p>
            </div>

            {{-- static products --}}
            <div class="flex justify-center items-center">
                @foreach ($randomProducts as $randomProduct)
                    <x-productcard :product="$randomProduct" />
                @endforeach
            </div>
        </div>
    </div>
</x-layout>
