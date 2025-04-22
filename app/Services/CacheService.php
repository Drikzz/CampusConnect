<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\Tag;

class CacheService
{
    /**
     * Get all categories with caching
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCategories()
    {
        return Cache::remember('all_categories', 60 * 5, function () {
            return Category::select('id', 'name')
                ->withCount('products')
                ->get();
        });
    }
    
    /**
     * Get all tags with caching
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTags()
    {
        return Cache::remember('all_tags', 60 * 5, function () {
            return Tag::select('id', 'name', 'slug')
                ->withCount('products')
                ->get();
        });
    }
    
    /**
     * Clear caches when data changes
     */
    public static function clearCategoriesCache()
    {
        Cache::forget('all_categories');
    }
    
    /**
     * Clear tags cache
     */
    public static function clearTagsCache()
    {
        Cache::forget('all_tags');
    }
}