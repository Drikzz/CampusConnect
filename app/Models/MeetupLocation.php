<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetupLocation extends Model
{
  protected $fillable = [
    'user_id',
    'location_id',
    'full_name',
    'phone',
    'custom_location',
    'latitude',
    'longitude',
    'is_default'
  ];

  protected $casts = [
    'is_default' => 'boolean'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function location()
  {
    return $this->belongsTo(Location::class);
  }
}
