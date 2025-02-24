@props(['product'])

<div class="w-60 h-[30rem] p-4 flex flex-col justify-between items-start gap-4 hover:shadow-lg rounded">
    <div class="relative">
        <a href="{{ route('prod.details', $product->id) }}">
            <img src="{{ asset('storage/' . $product->images[0]) }}" alt="" class="w-52 h-64 object-cover">
        </a>
        @if ($product->discount > 0.0)
            <div class="absolute bottom-2 right-2 rounded-2xl bg-white px-3 py-1">
                <p class="font-Satoshi-bold text-sm text-black">
                    {{ round($product->discount * 100) }}%
                </p>
            </div>
        @endif

        <div
            class="absolute top-3 right-3 bg-white p-2 rounded-full hover:shadow-md hover:ring-2 hover:ring-black hover:transition-all">
            <svg class="w-5 h-5 fill-black cursor-pointer bookmarked" xmlns="http://www.w3.org/2000/svg" id="Filled"
                viewBox="0 0 24 24" width="512" height="512">
                <path
                    d="M2.849,23.55a2.954,2.954,0,0,0,3.266-.644L12,17.053l5.885,5.853a2.956,2.956,0,0,0,2.1.881,3.05,3.05,0,0,0,1.17-.237A2.953,2.953,0,0,0,23,20.779V5a5.006,5.006,0,0,0-5-5H6A5.006,5.006,0,0,0,1,5V20.779A2.953,2.953,0,0,0,2.849,23.55Z" />
            </svg>

            <svg class="w-5 h-5 fill-black cursor-pointer hidden unbookmark" xmlns="http://www.w3.org/2000/svg"
                id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                <path
                    d="m21,19.586V3c0-1.654-1.346-3-3-3H6.559c-1.393,0-2.566.954-2.903,2.242L1.457.043.043,1.457l22.5,22.5,1.414-1.414-2.957-2.957Zm-10.083-3.661l-7.917,8.075v-15.991l7.917,7.916Z" />
            </svg>
        </div>
    </div>

    <div>
        <p class="font-Satoshi-bold text-xl">
            {{ $product->name }}
        </p>
    </div>

    <div class="flex justify-center items-center w-fit gap-2">
        <p class="font-Satoshi-bold text-2xl">
            &#8369;{{ number_format($product->discounted_price) }}
        </p>

        <p class="font-Satoshi-bold line-through text-2xl text-gray-400">
            &#8369;{{ number_format($product->price) }}
        </p>
    </div>

    <div class="flex justify-between items-center w-full">
        @if ($product->is_buyable == 1 && $product->is_tradable == 1)
            <a href="{{ route('summary', $product->id) }}"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 hover:transition-all focus:opacity-60 focus:transition-all">
                <button class="font-Satoshi text-white">Buy Now!</button>
            </a>
            <a href="#"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 hover:transition-all focus:opacity-60 focus:transition-all">
                <button class="font-Satoshi text-white">Trade Now!</button>
            </a>
        @elseif ($product->is_buyable == 1 && $product->is_tradable == 0)
            <a href="{{ route('summary', $product->id) }}"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 hover:transition-all focus:opacity-60 focus:transition-all">
                <button class="font-Satoshi text-white">Buy Now!</button>
            </a>
        @elseif ($product->is_buyable == 0 && $product->is_tradable == 1)
            <a href="#"
                class="py-2 px-3 bg-black rounded-lg hover:opacity-80 hover:transition-all focus:opacity-60 focus:transition-all">
                <button class="font-Satoshi text-white">Trade Now!</button>
            </a>
        @else
            <p class="font-Satoshi text-gray-500">Not available for purchase or trade</p>
        @endif
    </div>
</div>
