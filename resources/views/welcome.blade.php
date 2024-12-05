<x-layout>

    <div class="max-w-full bg-primary-color px-16 py-20 flex justify-evenly items-center">
        <div class="w-[60%]] flex flex-col justify-center items-start">
        
            <div>
                <p class="font-Satoshi-bold text-lg text-white">
                    WELCOME TO CAMPUS CONNECT!
                </p>
            </div>

            <div class="mt-4 mb-8 w-[30rem]">
                <p class="font-FontSpring-bold text-5xl gap-0 text-white">
                    BROWSE CAMPUS CONNECT NOW <span class="font-Satoshi-bold text-5xl">!</span>
                </p>
            </div>

            <div>
                <p class="font-Satoshi text-base text-white">
                    Browse through our diverse range of affordable items here!
                </p>
            </div>

            <div class="mt-12 mb-12">
                <a href="{{ route('products') }}"
                    class="font-Satoshi-bold px-8 py-4 bg-white rounded-md text-primary-color text-sm">
                    SHOP NOW
                </a>
            </div>

            <div class="flex justify-center items-center gap-4">
                <p class="text-white">
                    Affordable items!
                </p>
                <p class="text-white">
                    High Quality Products!
                </p>
            </div>
        </div>

        <div class="w-[40%] flex flex-col justify-evenly items-center">
            <div class="w-full flex justify-end items-center">
                <svg class ="w-20 h-20" viewBox="0 0 104 93" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M52 0.626999C53.7654 25.3579 76.0448 45.068 104 46.6297C76.0448 48.1915 53.7654 67.9014 52 92.6325C50.2347 67.9014 27.955 48.1915 0 46.6297C27.955 45.068 50.2347 25.3579 52 0.626999Z"
                        fill="white" />
                </svg>
            </div>

            <img class="w-[250px] h-auto" src="{{ asset('imgs/wmsu_logo.png') }}" alt="logo">

            <div class="w-full flex justify-start items-center">
                <svg class="w-12 h-12" viewBox="0 0 104 93" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M52 0.626999C53.7654 25.3579 76.0448 45.068 104 46.6297C76.0448 48.1915 53.7654 67.9014 52 92.6325C50.2347 67.9014 27.955 48.1915 0 46.6297C27.955 45.068 50.2347 25.3579 52 0.626999Z"
                        fill="white" />
                </svg>
            </div>
        </div>
    </div>

    <section class="px-16 mt-16">
        <div class="text-center">
            <p class="font-FontSpring-extra-bold text-4xl">
                Recently Uploaded
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-10 font-Satoshi mt-16"> <!-- Increased gap -->
            @foreach ($products as $product)
                <x-productcard :product="$product" />
            @endforeach
        </div>

    </section>

    <section class="px-16 mt-16 mb-28">
        <div class="text-center p-10">
            <p class="font-FontSpring-extra-bold text-4xl">
                Deals
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-10 font-Satoshi"> <!-- Increased gap -->
            @foreach ($products as $product)
                <x-productcard :product="$product" />
            @endforeach
        </div>

    </section>

</x-layout>
