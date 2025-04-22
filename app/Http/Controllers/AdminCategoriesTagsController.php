<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;
use Illuminate\Support\Facades\Schema;

class AdminCategoriesTagsController extends Controller
{
    // Categories methods
    public function index()
    {
        // Optimize query by only loading necessary data
        $categories = Category::select('id', 'name')
            ->withCount('products')
            ->get();
        
        // Optimize tags query by adding explicit casts to ensure string values
        $tags = Tag::select('id', 'name', 'slug')
            ->withCount('products')
            ->get()
            ->map(function($tag) {
                // Ensure name and slug are properly cast to strings
                $tag->name = (string)$tag->name;
                $tag->slug = (string)$tag->slug;
                return $tag;
            });
        
        // Debug the actual tag data to identify any issues
        if (app()->environment('local')) {
            $tagInfo = $tags->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'name_type' => gettype($tag->name),
                    'display_name' => $tag->display_name,
                    'slug' => $tag->slug,
                    'slug_type' => gettype($tag->slug),
                    'slug_display' => $tag->slug_display,
                ];
            });
            Log::info('Tags being loaded:', $tagInfo->take(5)->toArray());
        }
        
        return Inertia::render('Admin/admin-categoriestagsManagement', [
            'categories' => $categories,
            'tags' => $tags
        ]);
    }
    
    // Add this new method to the controller to retrieve category and tag data
    public function getCategoriesTagsData()
    {
        try {
            // Optimize query by only loading necessary data
            $categories = Category::select('id', 'name')
                ->withCount('products')
                ->get();
            
            // Optimize tags query by adding explicit casts to ensure string values
            $tags = Tag::select('id', 'name', 'slug')
                ->withCount('products')
                ->get()
                ->map(function($tag) {
                    // Ensure name and slug are properly cast to strings
                    $tag->name = (string)$tag->name;
                    $tag->slug = (string)$tag->slug;
                    return $tag;
                });
            
            return response()->json([
                'categories' => $categories,
                'tags' => $tags
            ]);
        } catch (Exception $e) {
            Log::error("Error fetching categories and tags data: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Category CRUD methods
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Category::create([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Category created successfully');
    }

    public function updateCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->save();

        return back()->with('success', 'Category updated successfully');
    }

    public function destroyCategory($id)
    {
        try {
            DB::beginTransaction();
            
            $category = Category::find($id);
            
            if (!$category) {
                DB::rollBack();
                Log::warning("Attempted to delete non-existent category with ID: {$id}");
                return response()->json([
                    'error' => 'Category not found',
                    'message' => 'The requested category could not be found. It may have been already deleted.'
                ], 404);
            }
            
            // Check if this is the default category
            if ($category->name === 'Uncategorized') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Cannot delete default category',
                    'message' => 'The default category cannot be deleted as it\'s used as a fallback for products.'
                ], 422);
            }
            
            $categoryName = $category->name;
            $productsCount = $category->products()->count();
            
            // Products will be automatically reassigned in the model's boot method
            $category->delete();
            
            DB::commit();
            
            $message = "Category deleted successfully";
            if ($productsCount > 0) {
                $message .= ". {$productsCount} products were moved to the default category.";
            }
            
            Log::info("{$message} Category name: {$categoryName} (ID: {$id})");
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting category {$id}: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete category',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tag CRUD methods
    public function storeTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Ensure we have a string for the name
        $name = (string) $request->name;
        
        DB::beginTransaction();
        try {
            // Create the tag
            $tag = Tag::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $request->description,
            ]);
            
            // Reload the tag with all necessary data
            $tag = Tag::withCount('products')->find($tag->id);
            
            // Make sure all accessors are properly processed
            $displayName = $tag->display_name;
            $slugDisplay = $tag->slug_display;
            
            // Convert to array to ensure all attributes are included
            $tagData = $tag->toArray();
            
            // Log for debugging
            Log::info("Tag created with name: {$name}, slug: {$tag->slug}, id: {$tag->id}");
            Log::info("Tag data for frontend: ", $tagData);
            
            DB::commit();
            
            // Use multiple flash keys in case one doesn't work
            return redirect()->route('admin.categories-tags-management')
                ->with('success', 'Tag created successfully')
                ->with('newTag', $tagData)
                ->with('createdTag', $tagData);
                
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to create tag: " . $e->getMessage());
            return back()->with('error', 'Failed to create tag: ' . $e->getMessage())->withInput();
        }
    }

    public function updateTag(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            
            $tag = Tag::findOrFail($id);
            $tag->name = (string) $request->name;
            $tag->slug = Str::slug($tag->name);
            
            // Only try to update description if the column exists in the table
            if (Schema::hasColumn('tags', 'description') && $request->has('description')) {
                $tag->description = $request->description;
            }
            
            $tag->save();
            
            DB::commit();
            
            // Log for debugging
            Log::info("Tag updated with name: {$tag->name}, slug: {$tag->slug}");

            return back()->with('success', 'Tag updated successfully');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to update tag: " . $e->getMessage());
            return back()->with('error', 'Failed to update tag: ' . $e->getMessage())->withInput();
        }
    }

    public function destroyTag($id)
    {
        try {
            DB::beginTransaction();
            
            $tag = Tag::find($id);
            
            if (!$tag) {
                DB::rollBack();
                Log::warning("Attempted to delete non-existent tag with ID: {$id}");
                return response()->json([
                    'error' => 'Tag not found',
                    'message' => 'The requested tag could not be found. It may have been already deleted.'
                ], 404);
            }
            
            // Check if this is the default tag
            if ($tag->name === 'General' && $tag->slug === 'general') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Cannot delete default tag',
                    'message' => 'The default tag cannot be deleted as it\'s used as a fallback for products.'
                ], 422);
            }
            
            $tagName = $tag->name;
            $productsCount = $tag->products()->count();
            
            // Products will be reassigned in the model's boot method
            $tag->delete();
            
            DB::commit();
            
            $message = "Tag deleted successfully";
            if ($productsCount > 0) {
                $message .= ". Products using this tag were reassigned to the default tag.";
            }
            
            Log::info("{$message} Tag name: {$tagName} (ID: {$id})");
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting tag {$id}: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete tag',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bulk operations
    public function bulkDeleteCategories(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $ids = $request->ids;
            if (empty($ids)) {
                DB::rollBack();
                return response()->json([
                    'error' => 'No categories selected',
                    'message' => 'Please select at least one category to delete.'
                ], 422);
            }
            
            // Check if default category is included in delete request
            $defaultCategoryExists = Category::where('name', 'Uncategorized')
                ->whereIn('id', $ids)
                ->exists();
                
            if ($defaultCategoryExists) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Cannot delete default category',
                    'message' => 'The default "Uncategorized" category cannot be deleted as it\'s used for product reassignment.'
                ], 422);
            }
            
            $totalReassigned = 0;
            $deletedCategories = [];
            
            // Process one by one to allow model events to fire properly
            foreach ($ids as $id) {
                $category = Category::find($id);
                if ($category) {
                    $productsCount = $category->products()->count();
                    $totalReassigned += $productsCount;
                    $deletedCategories[] = $category->name;
                    
                    $category->delete(); // This triggers the boot method
                }
            }
            
            $deletedCount = count($deletedCategories);
            
            DB::commit();
            
            $message = "{$deletedCount} categories deleted successfully.";
            if ($totalReassigned > 0) {
                $message .= " {$totalReassigned} products were reassigned to the default category.";
            }
            
            Log::info($message);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in bulk delete categories: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete categories',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDeleteTags(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $ids = $request->ids;
            if (empty($ids)) {
                DB::rollBack();
                return response()->json([
                    'error' => 'No tags selected',
                    'message' => 'Please select at least one tag to delete.'
                ], 422);
            }
            
            // Check if default tag is included in delete request
            $defaultTagExists = Tag::where('name', 'General')
                ->where('slug', 'general')
                ->whereIn('id', $ids)
                ->exists();
                
            if ($defaultTagExists) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Cannot delete default tag',
                    'message' => 'The default "General" tag cannot be deleted as it\'s used for product reassignment.'
                ], 422);
            }
            
            $totalReassigned = 0;
            $deletedTags = [];
            
            // Process one by one to allow model events to fire properly
            foreach ($ids as $id) {
                $tag = Tag::find($id);
                if ($tag) {
                    $productsCount = $tag->products()->count();
                    $totalReassigned += $productsCount;
                    $deletedTags[] = $tag->name;
                    
                    $tag->delete(); // This triggers the boot method
                }
            }
            
            $deletedCount = count($deletedTags);
            
            DB::commit();
            
            $message = "{$deletedCount} tags deleted successfully.";
            if ($totalReassigned > 0) {
                $message .= " Products associated with these tags were reassigned to the default tag.";
            }
            
            Log::info($message);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in bulk delete tags: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete tags',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
