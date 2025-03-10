<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
  protected $fillable = ['name', 'slug', 'description'];

  public function products(): BelongsToMany
  {
    return $this->belongsToMany(Product::class)->withTimestamps();
  }
}
