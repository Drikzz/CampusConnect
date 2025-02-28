<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editProductForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="productName">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Description</label>
                        <textarea class="form-control" id="productDescription" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Price</label>
                        <input type="number" class="form-control" id="productPrice" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="productDiscount">Discount</label>
                        <input type="number" class="form-control" id="productDiscount" name="discount" required>
                    </div>
                    <div class="form-group">
                        <label for="productDiscountedPrice">Discounted Price</label>
                        <input type="number" class="form-control" id="productDiscountedPrice" name="discounted_price" required>
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stock</label>
                        <input type="number" class="form-control" id="productStock" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label for="productCondition">Condition</label>
                        <input type="text" class="form-control" id="productCondition" name="condition" required>
                    </div>
                    <!-- Add more fields as necessary -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
