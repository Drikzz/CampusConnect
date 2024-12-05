<x-seller-layout>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<a href="#" class="nav-link"></a>
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

		<!-- MAIN CONTENT -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>My Store</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">My Store</a>
						</li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li>
							<a class="active" href="#">Listed Products</a>
						</li>
					</ul>
				</div>
				<a href="{{ route('addproduct') }}" class="btn-add-product">
					<span class="text">Add Product</span>
				</a>
			</div>

			<!-- Product Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Listed Products</h3>
						<i class='bx bx-search'></i>
						<i class='bx bx-filter'></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>Image</th>
								<th>Product Name</th>
								<th>Price</th>
								<th>Quantity</th>
								<th>Trade Process</th>
								<th>Actions</th> <!-- New Actions Column -->
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<img src="{{ asset('assets/seller-img/sample-profile.jpg') }}" alt="User Image" id=product-img>
								</td>
								<td>Product A</td>
								<td>$10</td>
								<td>10</td>
								<td><span class="status completed">Completed</span></td>
								<td>
									<a href="{{ route('editproduct') }}"><button class="btn-edit"><i class='bx bx-edit'></i> Edit</button></a>
									<a href=""><button class="btn-delete"><i class='bx bx-trash'></i> Delete</button></a>
								</td>
							</tr>
							<tr>
								<td>
									<img src="{{ asset('assets/seller-img/campconnect.jpg') }}" alt="User Image">
								</td>
								<td>Product B</td>
								<td>$15</td>
								<td>20</td>
								<td><span class="status pending">Pending</span></td>
								<td>
									<a href="editproduct.html"><button class="btn-edit"><i class='bx bx-edit'></i> Edit</button></a>
									<a href=""><button class="btn-delete"><i class='bx bx-trash'></i> Delete</button></a>
								</td>
							</tr>
							<tr>
								<td>
									<img src="{{ asset('assets/seller-img/campconnect.jpg') }}" alt="User Image">
								</td>
								<td>Product C</td>
								<td>$30</td>
								<td>50</td>
								<td><span class="status process">In Process</span></td>
								<td>
									<a href="editproduct.html"><button class="btn-edit"><i class='bx bx-edit'></i> Edit</button></a>
									<a href=""><button class="btn-delete"><i class='bx bx-trash'></i> Delete</button></a>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Modal (hidden by default, will be shown on button click) -->
<!-- Modal HTML (Hidden initially) -->
<div id="crud-modal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 flex justify-center items-start z-50 bg-gray-900 bg-opacity-50 mt-16">
    <div class="relative p-4 w-full max-w-lg max-h-[90vh] bg-white rounded-lg shadow-lg overflow-auto">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Edit Product
                </h3>
                <button id="closeModalButton" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form class="p-4 md:p-5">
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                        <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Type product name" required="">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Price</label>
                        <input type="number" name="price" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="$2999" required="">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Quantity" required="">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category</label>
                        <select id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option selected="">Select category</option>
                            <option value="TV">Electronics</option>
                            <option value="PC">Books</option>
                            <option value="GA">Laboratory Material</option>
                            <option value="PH">Clothes</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="trade-method" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Trade Method</label>
                        <select id="trade-method" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option selected="">Select Method</option>
                            <option value="TV">Face to Face</option>
                            <option value="PC">Online</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product Description</label>
                        <textarea id="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write product description here"></textarea>                    
                    </div>
                    <!-- Image Upload Section -->
                    <div class="col-span-2">
                        <label for="product-images" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Product Images</label>
                        <input type="file" id="product-images" name="product-images[]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" accept="image/*" multiple required>
                        <small class="text-gray-500 dark:text-gray-400">You can select multiple images</small>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="text-white inline-flex items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 mx-auto">
                        <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-seller-layout>