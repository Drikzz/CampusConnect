<x-sellerLayout>

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
                <img src="jose marie.png" alt="Profile Image">
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN CONTENT -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Add New Product</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Add Product</a></li>
                    </ul>
                </div>
            </div>

            <!-- Add Product Form -->
            <div class="form-container">
                <form action="{{ route('seller.products.store') }}" method="POST" class="add-product-form"
                    enctype="multipart/form-data">
                    @csrf
                    <!-- Product Name -->
                    <div class="form-group">
                        <label for="product-name">Product Name</label>
                        <input type="text" id="product-name" name="name" placeholder="Enter product name"
                            required>
                    </div>

                    <!-- Product Description -->
                    <div class="form-group">
                        <label for="product-description">Product Description</label>
                        <textarea id="product-description" name="description" placeholder="Enter product description" required></textarea>
                    </div>

                    <!-- Product Category -->
                    <div class="form-group">
                        <label for="product-category">Product Category</label>
                        <input type="text" id="product-category" name="category" placeholder="Enter product category"
                            required>
                    </div>

                    <!-- Product Type (Sell, Trade, All) -->
                    <div class="form-group">
                        <label for="product-type">Trade Method</label>
                        <select id="product-type" name="trade_method" required>
                            <option value="" disabled selected>Select product type</option>
                            <option value="sell">Sell</option>
                            <option value="trade">Trade</option>
                            <option value="all">All</option>
                        </select>
                    </div>

                    <!-- Product Price -->
                    <div class="form-group">
                        <label for="product-price">Product Price</label>
                        <input type="number" id="product-price" name="price" placeholder="Enter product price"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="product-quantity">Product Quantity</label>
                        <input type="number" id="product-quantity" name="quantity" placeholder="Enter product quantity"
                            required>
                    </div>

                    <!-- Multiple Image Upload -->
                    <div class="form-group">
                        <label for="product-images">Product Images</label>
                        <input type="file" id="product-images" name="images[]" accept="image/*" multiple
                            onchange="previewImages(event)">
                        <div id="image-preview" class="image-preview">
                            <!-- Image preview will be inserted here -->
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">Add Product</button>
                </form>
            </div>
        </main>
        <!-- MAIN CONTENT -->
    </section>
    <!-- CONTENT -->

</x-sellerLayout>
