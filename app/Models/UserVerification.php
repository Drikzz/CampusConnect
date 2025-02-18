<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerification extends Model
{
  protected $fillable = [
    'user_id',
    'username_changed_at',
    'phone_verified_at',
    'phone_verification_code',
    'email_verification_code_expires_at',
    'email_verification_code',
    'is_phone_verified',
    'is_email_verified'
  ];

  protected $casts = [
    'username_changed_at' => 'datetime',
    'phone_verified_at' => 'datetime',
    'email_verification_code_expires_at' => 'datetime',
    'is_phone_verified' => 'boolean',
    'is_email_verified' => 'boolean'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
