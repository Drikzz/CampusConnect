<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AdminProductsController extends Controller
{
    /**
     * Display a listing of products for admin management.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        try {
            // Fetch products with pagination
            $products = Product::select(['id', 'name', 'price', 'status', 'created_at', 'seller_code', 'images', 'stock', 'description', 'is_buyable', 'is_tradable', 'category_id'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            // Process each product to ensure images are properly formatted
            foreach ($products as $product) {
                // Load seller information
                if ($product->seller_code) {
                    $product->load(['seller' => function ($query) {
                        $query->select(['id', 'first_name', 'last_name', 'seller_code']);
                    }]);
                }
                
                // Load category
                if ($product->category_id) {
                    $product->load(['category:id,name']);
                }
                
                // Format images with full URLs
                $formattedImages = $this->formatProductImages($product->images);
                $product->formatted_images = $formattedImages;
            }

            $categories = Category::select(['id', 'name'])->get();

            // Log the pagination structure to confirm it's correct
            \Log::info('Pagination data:', [
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'total' => $products->total(),
                    'has_pages' => $products->hasPages(),
                ]
            ]);
            
            return Inertia::render('Admin/admin-productManagement', [
                'products' => $products,
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in product index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a fallback if there's an error
            return Inertia::render('Admin/admin-productManagement', [
                'products' => [
                    'data' => [],
                    'links' => [],
                    'meta' => [
                        'current_page' => 1,
                        'per_page' => 10,
                        'total' => 0,
                    ],
                ],
                'categories' => [],
                'error' => 'Failed to load products. Please try again.',
            ]);
        }
    }
    
    /**
     * Format product images to ensure they have proper URLs
     *
     * @param array|string $images
     * @return array
     */
    private function formatProductImages($images)
    {
        // If images is a JSON string, decode it
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = $decoded !== null ? $decoded : [$images]; // If not valid JSON, treat as a single image path
        }
        
        // If images is null, return an empty array
        if ($images === null) {
            return [];
        }

        // If not an array, convert to array
        if (!is_array($images)) {
            $images = [$images];
        }
        
        $formattedImages = [];
        
        foreach ($images as $image) {
            // Skip if image is null or empty
            if (empty($image) || trim($image) === '') {
                continue;
            }
            
            // Already a valid URL - keep it as is
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                $formattedImages[] = $image;
                continue;
            }
            
            // Clean up the path - ensure no double slashes and no leading slash
            $cleanPath = trim($image, '/');
            
            // If the path already includes 'storage/', use direct public URL
            if (strpos($cleanPath, 'storage/') === 0) {
                $formattedImages[] = url('/' . $cleanPath);
                continue;
            }
            
            // For product images that might be in different locations, add all possible paths
            $formattedImages[] = url('/storage/' . $cleanPath);
            
            // If path likely indicates a product image and doesn't already have a complete path
            if (strpos($cleanPath, 'product_images') !== false || 
                strpos($cleanPath, 'products') !== false) {
                if (Storage::disk('public')->exists($cleanPath)) {
                    // If file exists in public storage, use that
                    $formattedImages[] = Storage::disk('public')->url($cleanPath);
                } else {
                    // Try additional paths that might work
                    $formattedImages[] = url('/images/' . basename($cleanPath));
                    $formattedImages[] = url('/images/products/' . basename($cleanPath));
                }
            }
            
            // For images that might be direct public files
            $formattedImages[] = url($cleanPath);
            
            // Debug logging for this image
            \Log::debug("Product image path processing:", [
                'original' => $image,
                'clean' => $cleanPath,
                'formatted' => end($formattedImages)
            ]);
        }
        
        // Remove duplicates and ensure no empty or whitespace-only entries
        return array_filter(array_unique($formattedImages), function($url) {
            return !empty($url) && trim($url) !== '';
        });
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'seller_code' => 'required|exists:users,seller_code',
            'status' => 'required|in:active,inactive,pending',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $product = Product::create($validated);
        $product->categories()->sync($request->categories);

        return redirect()->route('admin.products')->with('success', 'Product created successfully.');
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive,pending',
            'is_buyable' => 'boolean',
            'is_tradable' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'new_images.*' => 'nullable|image|max:5120', // 5MB max per image
            'images_to_remove.*' => 'nullable|string',
            'images_to_keep.*' => 'nullable|string',
        ]);

        // Log the request data for debugging
        \Log::info('Product update request', [
            'product_id' => $id,
            'validated' => $validated,
            'has_new_images' => $request->hasFile('new_images'),
            'images_to_remove' => $request->input('images_to_remove', []),
        ]);

        try {
            // Start by handling the basic product data update
            $updateData = collect($validated)->except(['new_images', 'images_to_remove', 'images_to_keep'])->toArray();
            $product->update($updateData);
            
            // Handle image updates if there are any changes
            $currentImages = $product->images ?? [];
            
            // Convert to array if it's a string or JSON
            if (is_string($currentImages)) {
                try {
                    $currentImages = json_decode($currentImages, true) ?: [$currentImages];
                } catch (\Exception $e) {
                    $currentImages = [$currentImages];
                }
            } elseif (!is_array($currentImages)) {
                $currentImages = [];
            }
            
            // Filter out any null or empty entries
            $currentImages = array_filter($currentImages, function($img) {
                return !empty($img) && trim($img) !== '';
            });
            
            // 1. Process images to remove
            $imagesToRemove = $request->input('images_to_remove', []);
            if (!empty($imagesToRemove)) {
                // Filter out images marked for removal
                $currentImages = array_values(array_filter($currentImages, function($img) use ($imagesToRemove) {
                    return !in_array($img, $imagesToRemove);
                }));
                
                // Also delete the files from storage if they're owned by this product
                foreach ($imagesToRemove as $imagePath) {
                    $relativePath = str_replace('/storage/', '', $imagePath);
                    $relativePath = str_replace(url('/storage/'), '', $relativePath);
                    
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                        \Log::info("Deleted image file: {$relativePath}");
                    }
                }
            }
            
            // 2. Process images to keep (reordering)
            $imagesToKeep = $request->input('images_to_keep', []);
            if (!empty($imagesToKeep)) {
                // Replace current images with the kept ones in the order specified
                $currentImages = array_values($imagesToKeep);
            }
            
            // 3. Add newly uploaded images
            if ($request->hasFile('new_images')) {
                $uploadDirectory = 'products/' . date('Ymd'); // Daily folder for organization
                
                foreach ($request->file('new_images') as $imageFile) {
                    $path = $imageFile->store($uploadDirectory, 'public');
                    if (!empty($path)) {
                        $currentImages[] = $path;
                    }
                }
            }
            
            // 4. Filter out any empty values again
            $currentImages = array_filter($currentImages, function($img) {
                return !empty($img) && trim($img) !== '';
            });
            
            // 5. Limit to maximum 5 images and update the product's images field
            $currentImages = array_slice(array_values($currentImages), 0, 5);
            
            $product->images = $currentImages;
            $product->save();
            
            // Handle categories if needed
            if ($request->has('categories')) {
                $product->categories()->sync($request->categories);
            }

            // Return a proper redirect response
            return redirect()->route('admin.products')->with('success', 'Product updated successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Failed to update product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully.');
    }

    /**
     * Bulk delete products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        Product::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('admin.products')->with('success', 'Products deleted successfully.');
    }

    /**
     * Toggle product status (active/inactive).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return redirect()->route('admin.products')->with('success', 'Product status updated.');
    }
}
