<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetupLocation extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'location_id',
    'full_name',
    'phone',
    'description',
    'custom_location',
    'latitude',
    'longitude',
    'is_active',
    'is_default',
    'available_from',
    'available_until',
    'available_days',
    'max_daily_meetups'
  ];

  // Cast available_days as an array
  protected $casts = [
    'available_days' => 'array',
    'is_active' => 'boolean',
    'is_default' => 'boolean',
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8'
  ];

  public static $rules = [
    'full_name' => 'required|string|max:255',
    'phone' => 'required|string|max:20',
    'description' => 'nullable|string',
    'custom_location' => 'nullable|string',
    'latitude' => 'nullable|numeric|between:-90,90',
    'longitude' => 'nullable|numeric|between:-180,180',
    'available_from' => 'nullable|date_format:H:i',
    'available_until' => 'nullable|date_format:H:i|after:available_from',
    'available_days' => 'nullable|json',
    'max_daily_meetups' => 'required|integer|min:1|max:50'
  ];

  // Relationships
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function location()
  {
    return $this->belongsTo(Location::class);
  }

  public function orders()
  {
    return $this->hasMany(Order::class);
  }

  // Helper methods
  public function getFullAddressAttribute()
  {
    $parts = [$this->address];
    if ($this->landmark) {
      $parts[] = "Near " . $this->landmark;
    }
    return implode(', ', $parts);
  }

  public function isAvailableOn($date)
  {
    $dayOfWeek = date('l', strtotime($date));
    return in_array($dayOfWeek, $this->available_days ?? []);
  }

  public function getMeetupCountForDate($date)
  {
    return $this->orders()
      ->where('meetup_schedule', 'LIKE', $date . '%')
      ->count();
  }

  public function canScheduleMeetup($dateTime)
  {
    // Check if date is available
    if (!$this->isAvailableOn($dateTime)) {
      return false;
    }

    // Check if within operating hours
    $time = date('H:i:s', strtotime($dateTime));
    if ($this->available_from && $this->available_until) {
      if ($time < $this->available_from || $time > $this->available_until) {
        return false;
      }
    }

    // Check if max daily meetups reached
    $date = date('Y-m-d', strtotime($dateTime));
    if ($this->getMeetupCountForDate($date) >= $this->max_daily_meetups) {
      return false;
    }

    return true;
  }

  // Add method to get available time slots
  public function getAvailableTimeSlots($date, $duration = 30)
  {
    if (!$this->isAvailableOn($date)) {
      return [];
    }

    $slots = [];
    $start = Carbon::parse($date . ' ' . $this->available_from);
    $end = Carbon::parse($date . ' ' . $this->available_until);

    while ($start->lt($end)) {
      if ($this->canScheduleMeetup($start)) {
        $slots[] = $start->format('H:i');
      }
      $start->addMinutes($duration);
    }

    return $slots;
  }

  // Add method to check if location is currently available
  public function isCurrentlyAvailable(): bool
  {
    $now = Carbon::now();
    $currentTime = $now->format('H:i:s');
    $currentDay = $now->format('l');

    return $this->is_active &&
      in_array($currentDay, $this->available_days ?? []) &&
      $currentTime >= $this->available_from &&
      $currentTime <= $this->available_until;
  }

  // Set only one default location per user
  public function setAsDefault()
  {
    if ($this->is_default) {
      return;
    }

    $this->user->meetupLocations()
      ->where('is_default', true)
      ->update(['is_default' => false]);

    $this->update(['is_default' => true]);
  }

  protected static function boot()
  {
    parent::boot();

    // Always order by is_default first (descending), then by created_at
    static::addGlobalScope('orderByDefault', function ($builder) {
      $builder->orderByDesc('is_default')->orderByDesc('created_at');
    });
  }
}
