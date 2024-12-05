
<x-seller-layout>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<a href="#" class="nav-link">Categories</a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
				</div>
			</form>
			
			<a href="#" class="notification">
				<i class='bx bxs-bell'></i>
				<span class="num">8</span>
			</a>
			<a href="#" class="profile">
				<img src="{{ asset('assets/seller-img/sample-profile.jpg') }}">
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Wallet</h1>
					<ul class="breadcrumb">
						<li class="active">
							<a href="#">My wallet</a>
						</li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li>
							<a class="active" href="#">Insight</a>
						</li>
					</ul>
				</div>
			</div>
		
			<ul class="box-info">
				<!-- First list item -->
				<li style="display: flex; flex-direction: column; align-items: center; margin-bottom: 2px;">
					<i class='bx bx-wallet'></i>
					<span class="text" style="text-align: center;">
						<h3>995</h3>
						<p>My Wallet</p>
					</span>
					<!-- Button below the content -->
					<!-- Button to show modal -->
<button type="button" 
class="text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-10 py-2.5 text-center me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
style="display: block; margin-top: 2px;"
id="refillBtn">Refill</button>

				</li>
		
				<!-- Second list item -->
				<li style="display: flex; flex-direction: column; align-items: center; margin-bottom: 2px;">
					<i class='bx bxs-dollar-circle'></i>
					<span class="text" style="text-align: center;">
						<h3>100</h3>
						<p>Revenue</p>
					</span>
					<!-- Button below the content -->
					<button type="button" 
							class="text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-10 py-2.5 text-center me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
							style="display: block; margin-top: 2px;"
							id="withdrawBtn">
						Withdraw
					</button>
				</li>
		
				<!-- Third list item -->
				<li style="display: flex; flex-direction: column; align-items: center; margin-bottom: 2px;">
					<i class='bx bx-minus-circle'></i>
					<span class="text" style="text-align: center;">
						<h3>5</h3>
						<p>Total Deduction</p>
					</span>
					<!-- Button below the content -->
					<button type="button" 
							class="text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-10 py-2.5 text-center me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
							style="display: block; margin-top: 2px;">
						View
					</button>
				</li>
			</ul>
		
			<!-- Modal content, below the list -->
			<div id="crypto-modal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50">
				<div class="relative p-4 w-full max-w-md max-h-full">
					<!-- Modal content -->
					<div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
						<!-- Modal header -->
						<div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white">
								Choose Refill Method
							</h3>
							<button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm h-8 w-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="crypto-modal" id="closeModalBtn">
								<svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
									<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
								</svg>
								<span class="sr-only">Close modal</span>
							</button>
						</div>
						<!-- Modal body -->
						<div class="p-4 md:p-5">
							<p class="text-sm font-normal text-gray-500 dark:text-gray-400">Choose your Refilling method.</p>
							<ul class="my-4 space-y-3">
								<!-- method options  list -->
								<li>
									<a href="#" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
										<!-- Replace the SVG with an image -->
										<img src="{{ asset('assets/seller-img/gcash.png') }}" alt="Gcash" class="h-4" />
										<span class="flex-1 ms-3 whitespace-nowrap">Gcash</span>
										<span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-gray-500 bg-gray-200 rounded dark:bg-gray-700 dark:text-gray-400">Popular</span>
									</a>
								</li>
								<li>
									<a href="#" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
										<!-- Replace the SVG with an image -->
										<img src="{{ asset('assets/seller-img/paymaya.jpg') }}" alt="Gcash" class="h-4" />
										<span class="flex-1 ms-3 whitespace-nowrap">Paymaya</span>
										<span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-gray-500 bg-gray-200 rounded dark:bg-gray-700 dark:text-gray-400">Popular</span>
									</a>
								</li>
								
								<!-- Add wallet provider options here -->
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div id="withdraw-modal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50">
				<div class="relative p-4 w-full max-w-md max-h-full">
					<!-- Modal content -->
					<div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
						<!-- Modal header -->
						<div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white">
								Choose Refill Method
							</h3>
							<button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm h-8 w-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="crypto-modal" id="closeWithdrawModalBtn">
								<svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
									<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
								</svg>
								<span class="sr-only">Close modal</span>
							</button>
						</div>
						<!-- Modal body -->
						<div class="p-4 md:p-5">
							<p class="text-sm font-normal text-gray-500 dark:text-gray-400">Choose your Refilling method.</p>
							<ul class="my-4 space-y-3">
								<!-- method options  list -->
								<li>
									<a href="#" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
										<!-- Replace the SVG with an image -->
										<img src="{{ asset('assets/seller-img/gcash.png') }}" alt="Gcash" class="h-4" />
										<span class="flex-1 ms-3 whitespace-nowrap">Gcash</span>
										<span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-gray-500 bg-gray-200 rounded dark:bg-gray-700 dark:text-gray-400">Popular</span>
									</a>
								</li>
								<li>
									<a href="#" class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
										<!-- Replace the SVG with an image -->
										<img src="{{ asset('assets/seller-img/paymaya.jpg') }}" alt="Gcash" class="h-4" />
										<span class="flex-1 ms-3 whitespace-nowrap">Paymaya</span>
										<span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-gray-500 bg-gray-200 rounded dark:bg-gray-700 dark:text-gray-400">Popular</span>
									</a>
								</li>
								
								<!-- Add wallet provider options here -->
							</ul>
						</div>
					</div>
				</div>
			</div>
		
		</main>
	</x-seller-layout>
		