<x-layout>
    <div class="w-full mt-10 mb-28 px-16">
        {{-- Navigation Breadcrumb --}}
        <div class="flex justify-start items-center gap-2 w-full p-4">
            <a href="{{ route('index') }}" class="font-Satoshi text-base">Home</a>
            <p class="font-Satoshi text-base">/</p>
            <a href="{{ route('dashboard.profile') }}" class="font-Satoshi-bold text-base">Dashboard</a>
        </div>

        <div class="flex justify-center items-center w-full py-4 md:py-8">
            <p class="font-Footer italic text-2xl md:text-4xl">MY DASHBOARD</p>
        </div>

        {{-- Main Dashboard Content --}}
        <div class="flex">
            {{-- Sidebar --}}
            <aside id="sidebar-multi-level-sidebar"
                class="w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
                <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
                    <ul class="space-y-2 font-medium">
                        <li>
                            <button type="button"
                                class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                aria-controls="account-dropdown" data-collapse-toggle="account-dropdown">
                                <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                    xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
                                    height="512">
                                    <path
                                        d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12ZM12,2A4,4,0,1,1,8,6,4,4,0,0,1,12,2Z" />
                                    <path
                                        d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,2,0,7,7,0,0,1,14,0,1,1,0,0,0,2,0A9.01,9.01,0,0,0,12,14Z" />
                                </svg>

                                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">My Account</span>
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul id="account-dropdown" class="hidden py-2 space-y-2">
                                <li>
                                    <a href="#" data-target="profile"
                                        class="load-content flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Profile</a>
                                </li>
                                <li>
                                    <a href="#" data-target="address"
                                        class="load-content flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Address</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <button type="button"
                                class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                aria-controls="orders-dropdown" data-collapse-toggle="orders-dropdown">
                                <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                    xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M22,14c0,.553-.448,1-1,1H6.737c.416,1.174,1.528,2,2.82,2h9.443c.552,0,1,.447,1,1s-.448,1-1,1H9.557c-2.535,0-4.67-1.898-4.966-4.415L3.215,2.884c-.059-.504-.486-.884-.993-.884H1c-.552,0-1-.447-1-1S.448,0,1,0h1.222c1.521,0,2.802,1.139,2.979,2.649l.041,.351h3.758c.552,0,1,.447,1,1s-.448,1-1,1h-3.522l.941,8h14.581c.552,0,1,.447,1,1Zm-15,6c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm10,0c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm2-14.414v-1.586c0-.553-.448-1-1-1s-1,.447-1,1v2c0,.266,.105,.52,.293,.707l1,1c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414l-.707-.707Zm5,.414c0,3.309-2.691,6-6,6s-6-2.691-6-6S14.691,0,18,0s6,2.691,6,6Zm-2,0c0-2.206-1.794-4-4-4s-4,1.794-4,4,1.794,4,4,4,4-1.794,4-4Z" />
                                </svg>
                                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">My Orders</span>
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul id="orders-dropdown" class="hidden py-2 space-y-2">
                                <li>
                                    <a href="#" data-target="pending"
                                        class="load-content flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Pending</a>
                                </li>
                                <li>
                                    <a href="#" data-target="to-pay"
                                        class="load-content flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">To
                                        Pay</a>
                                </li>
                                <li>
                                    <a href="#" data-target="completed"
                                        class="load-content flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Completed</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" data-target="favorites"
                                class="load-content flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                    xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
                                    height="512">
                                    <path
                                        d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Zm-3.585,18.4a2.973,2.973,0,0,1-3.83,0C4.947,16.006,2,11.87,2,8.967a4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,11,8.967a1,1,0,0,0,2,0,4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,22,8.967C22,11.87,19.053,16.006,13.915,20.313Z" />
                                </svg>
                                <span class="flex-1 ms-3 whitespace-nowrap">Favorites</span>
                            </a>
                        </li>
                        @if (Auth::user()->is_seller == 1)
                            <li>
                                <a href="{{ route('dashboard') }}"
                                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                    <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                        viewBox="0 0 24 24" width="512" height="512">
                                        <path
                                            d="M16.5,0c-4.206,0-7.5,1.977-7.5,4.5v2.587c-.483-.057-.985-.087-1.5-.087C3.294,7,0,8.977,0,11.5v8c0,2.523,3.294,4.5,7.5,4.5,3.407,0,6.216-1.297,7.16-3.131,.598,.087,1.214,.131,1.84,.131,4.206,0,7.5-1.977,7.5-4.5V4.5c0-2.523-3.294-4.5-7.5-4.5Zm5.5,12.5c0,1.18-2.352,2.5-5.5,2.5-.512,0-1.014-.035-1.5-.103v-1.984c.49,.057,.992,.087,1.5,.087,2.194,0,4.14-.538,5.5-1.411v.911ZM2,14.589c1.36,.873,3.306,1.411,5.5,1.411s4.14-.538,5.5-1.411v.911c0,1.18-2.352,2.5-5.5,2.5s-5.5-1.32-5.5-2.5v-.911Zm20-6.089c0,1.18-2.352,2.5-5.5,2.5-.535,0-1.06-.038-1.566-.112-.193-.887-.8-1.684-1.706-2.323,.984,.28,2.092,.435,3.272,.435,2.194,0,4.14-.538,5.5-1.411v.911Zm-5.5-6.5c3.148,0,5.5,1.32,5.5,2.5s-2.352,2.5-5.5,2.5-5.5-1.32-5.5-2.5,2.352-2.5,5.5-2.5ZM7.5,9c3.148,0,5.5,1.32,5.5,2.5s-2.352,2.5-5.5,2.5-5.5-1.32-5.5-2.5,2.352-2.5,5.5-2.5Zm0,13c-3.148,0-5.5-1.32-5.5-2.5v-.911c1.36,.873,3.306,1.411,5.5,1.411s4.14-.538,5.5-1.411v.911c0,1.18-2.352,2.5-5.5,2.5Zm9-3c-.512,0-1.014-.035-1.5-.103v-1.984c.49,.057,.992,.087,1.5,.087,2.194,0,4.14-.538,5.5-1.411v.911c0,1.18-2.352,2.5-5.5,2.5Z" />
                                    </svg>

                                    <span class="flex-1 ms-3 whitespace-nowrap">Sell on Campus Connect</span>
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('dashboard') }}" data-target="sell"
                                    class="load-content flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                    <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                        viewBox="0 0 24 24" width="512" height="512">
                                        <path
                                            d="M16.5,0c-4.206,0-7.5,1.977-7.5,4.5v2.587c-.483-.057-.985-.087-1.5-.087C3.294,7,0,8.977,0,11.5v8c0,2.523,3.294,4.5,7.5,4.5,3.407,0,6.216-1.297,7.16-3.131,.598,.087,1.214,.131,1.84,.131,4.206,0,7.5-1.977,7.5-4.5V4.5c0-2.523-3.294-4.5-7.5-4.5Zm5.5,12.5c0,1.18-2.352,2.5-5.5,2.5-.512,0-1.014-.035-1.5-.103v-1.984c.49,.057,.992,.087,1.5,.087,2.194,0,4.14-.538,5.5-1.411v.911ZM2,14.589c1.36,.873,3.306,1.411,5.5,1.411s4.14-.538,5.5-1.411v.911c0,1.18-2.352,2.5-5.5,2.5s-5.5-1.32-5.5-2.5v-.911Zm20-6.089c0,1.18-2.352,2.5-5.5,2.5-.535,0-1.06-.038-1.566-.112-.193-.887-.8-1.684-1.706-2.323,.984,.28,2.092,.435,3.272,.435,2.194,0,4.14-.538,5.5-1.411v.911Zm-5.5-6.5c3.148,0,5.5,1.32,5.5,2.5s-2.352,2.5-5.5,2.5-5.5-1.32-5.5-2.5,2.352-2.5,5.5-2.5ZM7.5,9c3.148,0,5.5,1.32,5.5,2.5s-2.352,2.5-5.5,2.5-5.5-1.32-5.5-2.5,2.352-2.5,5.5-2.5Zm0,13c-3.148,0-5.5-1.32-5.5-2.5v-.911c1.36,.873,3.306,1.411,5.5,1.411s4.14-.538,5.5-1.411v.911c0,1.18-2.352,2.5-5.5,2.5Zm9-3c-.512,0-1.014-.035-1.5-.103v-1.984c.49,.057,.992,.087,1.5,.087,2.194,0,4.14-.538,5.5-1.411v.911c0,1.18-2.352,2.5-5.5,2.5Z" />
                                    </svg>

                                    <span class="flex-1 ms-3 whitespace-nowrap">Sell on Campus Connect</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center p-2 text-red/80 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-5 h-5 fill-red/80 transition duration-75 group-hover:text-gray-900"
                                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="m24,15c0,.617-.24,1.197-.678,1.634l-2.072,2.073c-.195.195-.451.293-.707.293s-.512-.098-.707-.293c-.391-.391-.391-1.023,0-1.414l1.292-1.293h-6.128c-.553,0-1-.447-1-1s.447-1,1-1h6.128l-1.292-1.293c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.073,2.074c.437.436.677,1.016.677,1.633ZM6.5,11c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm9.5,8v2c0,1.654-1.346,3-3,3H3c-1.654,0-3-1.346-3-3V5.621C0,3.246,1.69,1.184,4.019.718L7.216.079c1.181-.236,2.391.066,3.321.829.375.307.665.685.902,1.092h.561c2.206,0,4,1.794,4,4v5c0,.553-.447,1-1,1s-1-.447-1-1v-5c0-1.103-.897-2-2-2h0s0,0,0,0v17.999h1c.552,0,1-.448,1-1v-2c0-.553.447-1,1-1s1,.447,1,1Zm-6-14.999c0-.602-.267-1.165-.731-1.546-.362-.297-.808-.454-1.266-.454-.131,0-.264.013-.396.039l-3.196.639c-1.397.279-2.411,1.517-2.411,2.942v15.379c0,.552.449,1,1,1h7V4.001Z" />
                                    </svg>

                                    <span class="flex-1 ms-3 whitespace-nowrap">Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </aside>

            {{-- Tab Contents --}}
            <div id="myTabContent" class="w-3/4 p-4 rounded-lg bg-gray-50">
                <h2 class="mb-3 text-2xl font-bold text-primary-color">Welcome
                    {{ Str::ucfirst(Auth()->user()->first_name) }}!</h2>
                <p class="text-gray-500">Customize and manage your profile below.</p>

                {{-- Profile Card --}}
                <div id="profile" class="tab-content hidden">
                    <x-profileCard :user="$user" :user_type="$user_type" />
                </div>

                {{-- Address Card --}}
                <div id="address" class="tab-content hidden">
                    <x-addressCard :user="$user" />
                </div>

                {{-- My Orders --}}
                {{-- <div id="pending" class="tab-content hidden">
                    <x-myOrders status="pending" :orders="$pendingOrders" />
                </div> --}}
                <div id="pending" class="tab-content hidden">
                    <x-myOrders />
                </div>
                {{-- <div id="to-pay" class="tab-content hidden">
                    <x-myOrders status="to-pay" :orders="$toPayOrders" />
                </div>
                <div id="completed" class="tab-content hidden">
                    <x-myOrders status="completed" :orders="$completedOrders" />
                </div> --}}

                {{-- Favorites --}}
                <div id="favorites" class="tab-content hidden">
                    <x-myFavorites />
                </div>

                {{-- sell on campus connect --}}
                <div id="sell" class="tab-content hidden">
                    <x-sell />
                </div>
            </div>
        </div>
    </div>
</x-layout>
