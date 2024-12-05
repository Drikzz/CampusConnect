<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .sidebar-collapsed {
            width: 80px !important;
        }
        .sidebar-collapsed h3 {
            display: none;
        }
        .sidebar-collapsed div.flex {
            justify-content: center;
        }
        .transform-active {
            transform: rotate(90deg);
        }
    </style>
</head>

<body class="bg-[#cad7fda4] max-w-full overflow-x-hidden">
    <!-- Header -->
    <header class="h-[70px] w-full px-[30px] bg-[#fafaff] fixed border-b-2 top-0 left-0 z-50 flex justify-between items-center">
        <div class="flex gap-[60px] items-center">
            <div class="flex items-center gap-2">
                <img src="{{ asset('imgs/CampusConnect.png') }}" alt="CampusConnect Logo" class="h-10 w-auto">
                <div class="text-black font-extrabold italic text-[27px]">ADMIN</div>
            </div>
            <svg class="w-5 h-5 hover:scale-150 cursor-pointer transition-all duration-300" 
                 id="menuToggle"
                 xmlns="http://www.w3.org/2000/svg" 
                 viewBox="0 0 24 24" 
                 width="512" 
                 height="512">
                <rect y="11" width="24" height="2" rx="1"/>
                <rect y="4" width="24" height="2" rx="1"/>
                <rect y="18" width="24" height="2" rx="1"/>
            </svg>

        </div>

        <!-- Search Bar -->
        <form class="flex items-center max-w-sm mx-auto">   
            <label for="simple-search" class="sr-only">Search</label>
            <div class="relative w-full">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5v10M3 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm12 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0V6a3 3 0 0 0-3-3H9m1.5-2-2 2 2 2"/>
                    </svg>
                </div>
                <input type="text" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" placeholder="Search branch name..." required />
            </div>
            <button type="submit" class="p-2.5 ms-2 text-sm font-medium text-white bg-red-700 rounded-lg border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
                <span class="sr-only">Search</span>
            </button>
        </form>

        <div class="relative cursor-pointer">
            <div class="absolute top-[8px] left-[19px] bg-[#fa7bb4] w-[7px] h-[7px] rounded-full"></div>
            <img src="" class="h-[25px]" alt="notification-icon">
        </div>

        <div class="w-[40px] h-[40px] bg-[#626262] rounded-full flex items-center justify-center overflow-hidden">
            <img src="https://media.geeksforgeeks.org/wp-content/uploads/20221210180014/profile-removebg-preview.png" class="h-[42px]" alt="profile-image">
        </div>
    </header>

    <!-- Sidebar and Content -->
    <div class="flex pt-[70px]">
        <!-- Sidebar -->
        <div id="sidebar" class="min-h-[91vh] w-[250px] bg-[#ffffff] shadow-md border-r-2 flex flex-col justify-between py-[30px] px-[10px] transition-all duration-300">
            <nav class="flex flex-col gap-[30px] items-center">
                <div class="flex flex-col gap-[30px]">
                    <!-- Dashboard link -->
                    <div class="flex items-center gap-4 px-5 py-4  border-gray-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"  width="40" height="40" class="text-red-600 fill-current">
                            <path d="M14,12c0,1.019-.308,1.964-.832,2.754l-2.875-2.875c-.188-.188-.293-.442-.293-.707V7.101c2.282,.463,4,2.48,4,4.899Zm-6-.414V7.101c-2.55,.518-4.396,2.976-3.927,5.767,.325,1.934,1.82,3.543,3.729,3.992,1.47,.345,2.86,.033,3.952-.691l-3.169-3.169c-.375-.375-.586-.884-.586-1.414Zm11-4.586h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm5-7v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V8C0,5.243,2.243,3,5,3h14c2.757,0,5,2.243,5,5Zm-2,0c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v8c0,1.654,1.346,3,3,3h14c1.654,0,3-1.346,3-3V8Z"/>
                        </svg>
                        <h3 class="text-lg text-red-600">Dashboard</h3>
                    </div>

                    <!-- Articles link -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-200 hover:border-l-4 border-gray-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"  width="40" height="40">
                            <path d="m24,8.5C24,3.813,20.187,0,15.5,0S7,3.813,7,8.5s3.813,8.5,8.5,8.5,8.5-3.813,8.5-8.5Zm-8.5,6.5c-3.584,0-6.5-2.916-6.5-6.5s2.916-6.5,6.5-6.5,6.5,2.916,6.5,6.5-2.916,6.5-6.5,6.5Zm-.789,4.191c.447.324.547.949.223,1.396-1.549,2.137-4.048,3.413-6.684,3.413C3.701,24,0,20.299,0,15.75c0-2.636,1.276-5.134,3.413-6.684.448-.324,1.073-.225,1.396.223.324.447.225,1.072-.223,1.396-1.62,1.174-2.587,3.067-2.587,5.064,0,3.446,2.804,6.25,6.25,6.25,1.998,0,3.891-.967,5.065-2.586.323-.448.949-.548,1.396-.223Zm4.094-12.441c0-1.391-1.12-2.521-2.506-2.545v-.474c0-.442-.358-.8-.8-.8s-.8.358-.8.8v.469h-.652c-.717,0-1.3.583-1.3,1.3v6c0,.717.583,1.3,1.3,1.3h.652v.45c0,.442.358.8.8.8s.8-.358.8-.8v-.45h.143c1.406,0,2.55-1.144,2.55-2.55,0-.727-.305-1.383-.795-1.849.379-.445.608-1.022.608-1.651Zm-4.458-.95h1.908c.524,0,.95.426.95.95s-.426.95-.95.95h-1.908v-1.9Zm2.094,5.4h-2.094v-1.9h2.094c.524,0,.95.426.95.95s-.426.95-.95.95Zm7.009,10.125l-2.224,2.361c-.196.208-.462.314-.728.314-.246,0-.493-.09-.686-.272-.402-.378-.42-1.011-.042-1.414l1.238-1.314h-3.011c-.552,0-1-.448-1-1s.448-1,1-1h3.011l-1.239-1.314c-.378-.402-.36-1.035.042-1.414.401-.378,1.034-.36,1.414.042l2.244,2.382c.709.709.709,1.898-.021,2.629ZM.528,5.304c-.709-.71-.709-1.899.021-2.629L2.772.314c.379-.403,1.012-.421,1.414-.042.402.379.421,1.012.042,1.414l-1.238,1.314h3.01c.552,0,1,.448,1,1s-.448,1-1,1h-3.011l1.239,1.314c.378.402.36,1.035-.042,1.414-.193.182-.439.272-.686.272-.266,0-.531-.105-.728-.314L.528,5.304Z"/>
                        </svg>
                        <h3 class="text-lg">Sales</h3>
                    </div>

                    <!-- Report link -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-200 hover:border-l-4 border-gray-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24"  width="40" height="40"><path d="M0,7A1,1,0,0,1,1,6H18V2.639a.792.792,0,0,1,1.35-.552l4.418,4.361a.773.773,0,0,1,0,1.1L19.35,11.913A.792.792,0,0,1,18,11.361V8H1A1,1,0,0,1,0,7Zm23,9H6V12.639a.792.792,0,0,0-1.35-.552L.232,16.448a.773.773,0,0,0,0,1.1L4.65,21.913A.792.792,0,0,0,6,21.361V18H23a1,1,0,0,0,0-2Z"/></svg>

                        <h3 class="text-lg">Transactions</h3>
                    </div>

                    <!-- Institution link -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-200 hover:border-l-4 border-gray-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="40" height="40"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>

                        <h3 class="text-md">User Management</h3>
                    </div>

                    <!-- Profile link -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-200 hover:border-l-4 border-gray-400 transition-all">
                        <svg id="Layer_1" width="40" height="40"viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m20 4h-5a4 4 0 0 0 -4-4h-7a4 4 0 0 0 -4 4v19a1 1 0 0 0 2 0v-10h8a4 4 0 0 0 4 4h6a4 4 0 0 0 4-4v-5a4 4 0 0 0 -4-4zm-18 7v-7a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v5a2 2 0 0 1 -2 2zm20 2a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2-2v-.142a4 4 0 0 0 3-3.858v-3h5a2 2 0 0 1 2 2z"/></svg>
                        <h3 class="text-md">Reports management</h3>
                    </div>

                    <!-- Settings link -->
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-200 hover:border-l-4 border-gray-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="40" height="40">
                            <path d="m8.433,18.607c.76.762.76,2.023-.013,2.798l-2.199,2.288c-.196.204-.458.307-.721.307-.25,0-.499-.093-.693-.279-.398-.383-.411-1.016-.028-1.414l1.256-1.307h-2.035c-2.206,0-4-1.794-4-4v-2c0-.553.448-1,1-1s1,.447,1,1v2c0,1.103.897,2,2,2h2.029l-1.25-1.307c-.383-.398-.371-1.031.028-1.414.397-.383,1.03-.37,1.414.027l2.212,2.301ZM20,3h-2.035l1.256-1.307c.383-.398.371-1.031-.028-1.414-.397-.382-1.03-.37-1.414.027l-2.199,2.288c-.773.774-.773,2.036-.013,2.798l2.212,2.301c.196.204.458.307.721.307.25,0,.499-.093.693-.279.398-.383.411-1.016.028-1.414l-1.25-1.307h2.029c1.103,0,2,.897,2,2v2c0,.553.448,1,1,1s1-.447,1-1v-2c0-2.206-1.794-4-4-4Zm3.996,12.715v3.555c0,1.074-.579,2.072-1.512,2.604l-3,1.715c-.459.263-.974.394-1.488.394s-1.029-.131-1.489-.394l-3-1.715c-.932-.531-1.512-1.529-1.512-2.604v-3.555c0-1.074.579-2.072,1.512-2.605l3-1.714c.918-.525,2.058-.525,2.977,0l3,1.714c.933.533,1.512,1.532,1.512,2.605Zm-9.262-1.003l3.266,1.837,3.262-1.834-2.77-1.583c-.153-.088-.325-.131-.496-.131s-.343.043-.496.131l-2.767,1.58Zm2.266,6.854v-3.285l-3.004-1.689v2.677c0,.358.193.69.503.867l2.501,1.43Zm4.996-2.297v-2.672l-2.996,1.685v3.28l2.492-1.424h0c.311-.178.503-.51.503-.868ZM13,6.5c0,3.59-2.91,6.5-6.5,6.5S0,10.09,0,6.5,2.91,0,6.5,0s6.5,2.91,6.5,6.5Zm-2.5,1.897c.01-1.297-.916-2.397-2.193-2.61l-3.261-.544c-.289-.048-.523-.281-.544-.574-.026-.365.263-.67.622-.67h2.376c.367,0,.689.199.862.494.182.31.509.506.869.506.763,0,1.253-.825.874-1.487-.518-.903-1.491-1.513-2.605-1.513,0-.552-.448-1-1-1s-1,.448-1,1h-.315c-1.451,0-2.673,1.152-2.685,2.603-.01,1.297.915,2.397,2.192,2.61l3.286.548c.315.052.54.331.52.655-.02.334-.315.585-.649.585h-2.35c-.367,0-.688-.199-.862-.494-.182-.31-.509-.506-.869-.506-.763,0-1.253.825-.874,1.487.518.903,1.491,1.513,2.605,1.513,0,.552.448,1,1,1s1-.448,1-1h.315c1.451,0,2.673-1.152,2.685-2.603Z"/>
                        </svg>
                        
                        <h3 class="text-md">Products management</h3>
                    </div>

                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-200 hover:border-l-4 border-gray-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="40" height="40">
                            <path d="m19,18c-1.379,0-2.5-1.121-2.5-2.5s1.121-2.5,2.5-2.5,2.5,1.121,2.5,2.5-1.121,2.5-2.5,2.5Zm2-14H5c-.856,0-1.653-.381-2.216-1.004.549-.607,1.335-.996,2.216-.996h18c.552,0,1-.448,1-1s-.448-1-1-1H5C2.239,0,0,2.239,0,5v10c0,2.761,2.239,5,5,5h8c.552,0,1-.448,1-1s-.448-1-1-1H5c-1.657,0-3-1.343-3-3V5s.002-.001.005-.002c.853.638,1.901,1.002,2.995,1.002h16c.552,0,1,.448,1,1v5c0,.552.448,1,1,1s1-.448,1-1v-5c0-1.657-1.343-3-3-3Zm-2,15c-2.333,0-4.375,1.538-4.966,3.741-.143.533.173,1.082.707,1.225.534.143,1.081-.173,1.225-.707.357-1.33,1.605-2.259,3.034-2.259s2.677.929,3.034,2.259c.12.447.524.741.965.741.085,0,.173-.011.26-.035.533-.143.85-.692.707-1.225-.591-2.203-2.633-3.741-4.966-3.741Z"/>
                        </svg>
                        <h3 class="text-md">Funds management</h3>
                    </div>

                    <!-- Logout link -->
                
                </div>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="w-full">
            <div class="flex-1 h-[calc(100vh-70px)] overflow-y-auto px-[30px] py-[40px] bg-[#f6f6f6]">
                {{ $slot }}
            </div>
        </div>
    </div>
    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.getElementById('menuToggle');
            
            sidebar.classList.toggle('sidebar-collapsed');
            menuIcon.classList.toggle('transform-active');
        });
    </script>
</body>
</html>