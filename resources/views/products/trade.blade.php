<x-layout>
    <div class="flex flex-col w-full mt-10 mb-28 px-16">

        <div class="flex justify-start items-center gap-2 w-full pt-4">
            <a href="{{ route('index') }}" class="font-Satoshi text-base">
                Home
            </a>

            <p class="font-Satoshi text-base">/</p>

            <a href="{{ route('products') }}" class="font-Satoshi-bold text-base">
                Products
            </a>
        </div>

        <div class="flex justify-center items-center w-full py-8">
            <p class="font-Footer italic text-4xl">
                ALL PRODUCTS
            </p>
        </div>

        <x-filterProduct />

        <div class="flex flex-wrap justify-center gap-10">
            @foreach ($products as $product)
                <x-productcard :product="$product" />
            @endforeach
        </div>
    </div>

</x-layout>
