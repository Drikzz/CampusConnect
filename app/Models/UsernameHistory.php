<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsernameHistory extends Model
{
  protected $fillable = [
    'old_username',
    'new_username'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
