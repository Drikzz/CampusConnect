<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Exception;

class Category extends Model
{
    protected $fillable = ['name'];
    
    // Enhanced boot method to handle auto-reassignment before deletion
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            $productsCount = $category->products()->count();
            
            // If category has products, reassign them to default category
            if ($productsCount > 0) {
                Log::info("Category {$category->name} (ID: {$category->id}) has {$productsCount} products, reassigning to default category");
                
                // Get or create default category
                $defaultCategory = self::firstOrCreate(
                    ['name' => 'Uncategorized'],
                    ['name' => 'Uncategorized']
                );
                
                // Don't proceed if trying to delete the default category
                if ($defaultCategory->id === $category->id) {
                    throw new Exception("Cannot delete the default category");
                }
                
                // Reassign all products to default category
                $category->products()->update(['category_id' => $defaultCategory->id]);
                
                Log::info("Reassigned {$productsCount} products to default category (ID: {$defaultCategory->id})");
            }
            
            Log::info("Deleting category: {$category->name} (ID: {$category->id})");
        });
        
        static::deleted(function ($category) {
            Log::info("Category deleted successfully: {$category->name} (ID: {$category->id})");
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
