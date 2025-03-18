<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminCategoriesTagsController extends Controller
{
    // Categories methods
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();
        
        return Inertia::render('Admin/admin-categoriestagsManagement', [
            'categories' => $categories,
            'tags' => $tags
        ]);
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
        $category = Category::findOrFail($id);
        // Check if there are products in this category
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated products');
        }
        
        $category->delete();
        return back()->with('success', 'Category deleted successfully');
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

        Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Tag created successfully');
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

        $tag = Tag::findOrFail($id);
        $tag->name = $request->name;
        $tag->slug = Str::slug($request->name);
        $tag->description = $request->description;
        $tag->save();

        return back()->with('success', 'Tag updated successfully');
    }

    public function destroyTag($id)
    {
        $tag = Tag::findOrFail($id);
        // Detach the tag from all products before deleting
        $tag->products()->detach();
        $tag->delete();
        
        return back()->with('success', 'Tag deleted successfully');
    }

    // Bulk operations
    public function bulkDeleteCategories(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return back()->with('error', 'No categories selected');
        }
        
        // Check if any categories have products
        $categoriesWithProducts = Category::whereIn('id', $ids)->whereHas('products')->get();
        if ($categoriesWithProducts->count() > 0) {
            return back()->with('error', 'Some categories cannot be deleted because they have associated products');
        }
        
        Category::whereIn('id', $ids)->delete();
        return back()->with('success', 'Selected categories deleted successfully');
    }

    public function bulkDeleteTags(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return back()->with('error', 'No tags selected');
        }
        
        // Detach tags from products before deleting
        $tags = Tag::whereIn('id', $ids)->get();
        foreach ($tags as $tag) {
            $tag->products()->detach();
        }
        
        Tag::whereIn('id', $ids)->delete();
        return back()->with('success', 'Selected tags deleted successfully');
    }
}
