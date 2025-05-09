<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Exception;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];
    
    // Add slug_display to show proper slug names
    protected $appends = ['display_name', 'slug_display'];
    
    // Cast name to string to prevent numeric issues
    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
    ];
    
    // Remove or conditionally add the expensive debug logging
    public function toArray()
    {
        $array = parent::toArray();
        // Only log in local environment to improve production performance
        if (app()->environment('local') && app()->hasDebugModeEnabled()) {
            Log::debug("Tag serialized: ", ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug]);
        }
        return $array;
    }
    
    // Fixed accessor to better handle edge cases with numeric names
    public function getDisplayNameAttribute()
    {
        // Use lookup table for both numeric and string IDs for robustness
        $tagNames = [
            1 => 'Books',
            2 => 'Electronics',
            3 => 'School Supplies',
            4 => 'Notes',
            5 => 'Uniforms',
            6 => 'Lab Equipment',
            7 => 'Sports',
            8 => 'Art Supplies',
            9 => 'Computing',
            10 => 'Stationery',
            // Also include string keys for robustness
            'Books',
            'Electronics',
            'School Supplies',
            'Notes',
            'Uniforms',
            'Lab Equipment',
            'Sports',
            'Art Supplies',
            'Computing',
            'Stationery'
        ];
        
        // First check if we can use the name as is (if it's not numeric or empty)
        if (is_string($this->name) && !is_numeric($this->name) && !empty($this->name)) {
            return $this->name;
        }
        
        // Then check if we have a mapping for this ID
        if (isset($tagNames[$this->id])) {
            return $tagNames[$this->id];
        }
        
        // Finally try to map numeric name to proper name
        if (is_numeric($this->name) && isset($tagNames[$this->name])) {
            return $tagNames[$this->name];
        }
        
        // Last resort - create a formatted name from ID
        return "Tag #{$this->id}";
    }
    
    // Add a slug display accessor for better slug handling
    public function getSlugDisplayAttribute()
    {
        // Use lookup table for generating proper slugs
        $tagSlugs = [
            1 => 'books',
            2 => 'electronics',
            3 => 'school-supplies',
            4 => 'notes',
            5 => 'uniforms',
            6 => 'lab-equipment',
            7 => 'sports',
            8 => 'art-supplies',
            9 => 'computing',
            10 => 'stationery',
            // Also include string keys for robustness
            'books',
            'electronics',
            'school-supplies',
            'notes',
            'uniforms',
            'lab-equipment',
            'sports',
            'art-supplies',
            'computing',
            'stationery'
        ];
        
        // First check if we have a valid slug already
        if (is_string($this->slug) && !is_numeric($this->slug) && !empty($this->slug)) {
            return $this->slug;
        }
        
        // Check if we have a mapping for this ID
        if (isset($tagSlugs[$this->id])) {
            return $tagSlugs[$this->id];
        }
        
        // Finally try to map numeric slug to proper slug
        if (is_numeric($this->slug) && isset($tagSlugs[$this->slug])) {
            return $tagSlugs[$this->slug];
        }
        
        // Last resort - create a slug from ID
        return "tag-{$this->id}";
    }
    
    // Enhanced boot method to handle product detachment before deletion
    protected static function boot()
    {
        parent::boot();
     static::deleting(function ($tag) {
            $productCount = $tag->products()->count();
            
            if ($productCount > 0) {
                // Get or create default tag
                $defaultTag = self::firstOrCreate(
                    ['name' => 'General', 'slug' => 'general'],
                    ['name' => 'General', 'slug' => 'general']
                );
                
                // Don't proceed if trying to delete the default tag
                if ($defaultTag->id === $tag->id) {
                    throw new Exception("Cannot delete the default tag");
                }
                
                Log::info("Tag {$tag->name} (ID: {$tag->id}) has {$productCount} associated products, reassigning to default tag");
                
                // Get products associated with this tag
                $productIds = $tag->products()->pluck('products.id')->toArray();
                
                // First detach the tag from all products
                $tag->products()->detach();
                
                // Then attach default tag to those products (if not already attached)
                foreach ($productIds as $productId) {
                    // Check if product already has the default tag
                    if (!$defaultTag->products()->where('product_id', $productId)->exists()) {
                        $defaultTag->products()->attach($productId);
                    }
                }
                
                Log::info("Reassigned {$productCount} products to default tag (ID: {$defaultTag->id})");
            }
            
            Log::info("Deleting tag: {$tag->name} (ID: {$tag->id})");
        });
        
        static::deleted(function ($tag) {
            Log::info("Tag deleted successfully: {$tag->name} (ID: {$tag->id})");
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}