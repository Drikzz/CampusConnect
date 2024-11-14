<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME') }}</title>
    @vite(['resources/css/seller.css', 'resources/js/seller.js'])
    <!-- Boxicons -->
	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	
	<title>CC</title>
</head>
<body>

	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<img src="wmsu pic.jfif" alt="company img" id="companyimg">
			<span class="text">CampusConnect</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
				<a href="new.html">
					<i class='bx bxs-dashboard'></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="products.html">
					<i class='bx bxs-shopping-bag-alt'></i>
					<span class="text">My Store</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-doughnut-chart'></i>
					<span class="text">Analytics</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-message-dots'></i>
					<span class="text">Message</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#">
					<i class='bx bxs-cog'></i>
					<span class="text">Settings</span>
				</a>
			</li>
			<li>
				<a href="#" class="logout">
					<i class='bx bxs-log-out-circle'></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->

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
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell'></i>
				<span class="num">8</span>
			</a>
			<a href="#" class="profile">
				<img src="jose marie.png">
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
				<a href="addproduct.html" class="btn-add-product">
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
									<img src="img/people.png" alt="User Image">
								</td>
								<td>Product A</td>
								<td>$10</td>
								<td>10</td>
								<td><span class="status completed">Completed</span></td>
								<td>
									<a href="editproduct.html"><button class="btn-edit"><i class='bx bx-edit'></i> Edit</button></a>
									<a href=""><button class="btn-delete"><i class='bx bx-trash'></i> Delete</button></a>
								</td>
							</tr>
							<tr>
								<td>
									<img src="img/people.png" alt="User Image">
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
									<img src="img/people.png" alt="User Image">
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
		</main>
		<!-- MAIN CONTENT -->

	</section>
	<!-- CONTENT -->

	<script src="new.js"></script>
</body>
</html>
