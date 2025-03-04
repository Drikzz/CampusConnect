<template>
  <div class="wishlist-container">
    <h2>My Wishlist</h2>
    
    <div v-if="loading" class="loading">
      Loading your wishlist...
    </div>
    
    <div v-else-if="wishlistProducts.length === 0" class="empty-wishlist">
      <p>Your wishlist is empty. Add some products to your wishlist!</p>
    </div>
    
    <div v-else class="wishlist-products">
      <div v-for="product in wishlistProducts" :key="product.product_id" class="wishlist-product">
        <div class="product-image">
          <img :src="product.image_url || 'assets/images/product-placeholder.jpg'" :alt="product.name">
        </div>
        <div class="product-details">
          <h3>{{ product.name }}</h3>
          <p class="product-price">₱{{ product.price }}</p>
          <p class="product-description">{{ product.description }}</p>
          <div class="product-actions">
            <button @click="addToCart(product)" class="add-to-cart-btn">Add to Cart</button>
            <button @click="removeFromWishlist(product.product_id)" class="remove-btn">
              Remove from Wishlist
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      wishlistProducts: [],
      loading: true,
      userId: null
    };
  },
  mounted() {
    // Get user ID from local storage
    this.userId = localStorage.getItem('user_id');
    if (this.userId) {
      this.fetchWishlistProducts();
    } else {
      this.loading = false;
    }
  },
  methods: {
    fetchWishlistProducts() {
      this.loading = true;
      fetch(`api/get_wishlist_products.php?user_id=${this.userId}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            this.wishlistProducts = data.products;
          } else {
            console.error('Error fetching wishlist products:', data.message);
          }
          this.loading = false;
        })
        .catch(error => {
          console.error('Error fetching wishlist products:', error);
          this.loading = false;
        });
    },
    removeFromWishlist(productId) {
      if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
        return;
      }
      
      fetch('api/toggle_wishlist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&user_id=${this.userId}&action=remove`
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            // Remove the product from the local wishlist array
            this.wishlistProducts = this.wishlistProducts.filter(
              product => product.product_id !== productId
            );
            alert('Product removed from wishlist');
          } else {
            alert('Error removing product from wishlist');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error removing product from wishlist');
        });
    },
    addToCart(product) {
      const cartItem = {
        product_id: product.product_id,
        name: product.name,
        price: product.price,
        image_url: product.image_url,
        quantity: 1
      };
      
      // Get existing cart or initialize an empty array
      let cart = JSON.parse(localStorage.getItem('cart')) || [];
      
      // Check if the product is already in the cart
      const existingProductIndex = cart.findIndex(item => item.product_id === product.product_id);
      
      if (existingProductIndex !== -1) {
        // Product already exists, increment quantity
        cart[existingProductIndex].quantity += 1;
      } else {
        // Add new product to cart
        cart.push(cartItem);
      }
      
      // Save updated cart back to localStorage
      localStorage.setItem('cart', JSON.stringify(cart));
      
      alert('Product added to cart!');
    }
  }
};
</script>

<style scoped>
.wishlist-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
  font-size: 28px;
}

.loading, .empty-wishlist {
  text-align: center;
  margin: 40px 0;
  color: #666;
  font-size: 18px;
}

.wishlist-products {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.wishlist-product {
  display: flex;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  background-color: #fff;
}

.product-image {
  width: 200px;
  height: 200px;
  flex-shrink: 0;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-details {
  flex: 1;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.product-details h3 {
  margin-top: 0;
  font-size: 20px;
  color: #333;
}

.product-price {
  font-weight: bold;
  color: #e74c3c;
  font-size: 18px;
  margin: 8px 0;
}

.product-description {
  color: #666;
  margin-bottom: 16px;
  flex-grow: 1;
}

.product-actions {
  display: flex;
  gap: 10px;
}

button {
  padding: 10px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.2s;
}

.add-to-cart-btn {
  background-color: #2ecc71;
  color: white;
}

.add-to-cart-btn:hover {
  background-color: #27ae60;
}

.remove-btn {
  background-color: #e74c3c;
  color: white;
}

.remove-btn:hover {
  background-color: #c0392b;
}

@media (max-width: 768px) {
  .wishlist-product {
    flex-direction: column;
  }
  
  .product-image {
    width: 100%;
    height: 220px;
  }
}
</style>
